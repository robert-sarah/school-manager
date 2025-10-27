<?php
namespace App\Models;

class Quiz {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    public function createQuiz($data) {
        $this->db->beginTransaction();

        try {
            // Créer le quiz
            $sql = "INSERT INTO quizzes (
                title, description, subject_id, class_id,
                teacher_id, time_limit, start_date,
                end_date, passing_score, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['subject_id'],
                $data['class_id'],
                $data['teacher_id'],
                $data['time_limit'],
                $data['start_date'],
                $data['end_date'],
                $data['passing_score']
            ]);

            $quiz_id = $this->db->lastInsertId();

            // Ajouter les questions
            foreach ($data['questions'] as $question) {
                $this->addQuestion($quiz_id, $question);
            }

            $this->db->commit();
            return $quiz_id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function addQuestion($quiz_id, $question_data) {
        // Ajouter la question
        $sql = "INSERT INTO quiz_questions (
            quiz_id, question_text, question_type,
            points, created_at
        ) VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $quiz_id,
            $question_data['text'],
            $question_data['type'],
            $question_data['points']
        ]);

        $question_id = $this->db->lastInsertId();

        // Ajouter les options de réponse
        if (!empty($question_data['options'])) {
            $sql = "INSERT INTO quiz_options (
                question_id, option_text, is_correct,
                created_at
            ) VALUES (?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            foreach ($question_data['options'] as $option) {
                $stmt->execute([
                    $question_id,
                    $option['text'],
                    $option['is_correct']
                ]);
            }
        }

        return $question_id;
    }

    public function startQuiz($quiz_id, $student_id) {
        // Vérifier si l'étudiant peut commencer le quiz
        $sql = "SELECT * FROM quizzes 
                WHERE id = ? 
                AND NOW() BETWEEN start_date AND end_date
                AND id NOT IN (
                    SELECT quiz_id FROM quiz_attempts
                    WHERE student_id = ?
                    AND status = 'completed'
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quiz_id, $student_id]);
        $quiz = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($quiz) {
            // Créer une nouvelle tentative
            $sql = "INSERT INTO quiz_attempts (
                quiz_id, student_id, start_time,
                status, created_at
            ) VALUES (?, ?, NOW(), 'in_progress', NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$quiz_id, $student_id]);

            return $this->db->lastInsertId();
        }

        return false;
    }

    public function submitAnswer($attempt_id, $question_id, $answer) {
        // Enregistrer la réponse
        $sql = "INSERT INTO quiz_answers (
            attempt_id, question_id, answer_text,
            created_at
        ) VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            answer_text = ?,
            updated_at = NOW()";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $attempt_id,
            $question_id,
            $answer,
            $answer
        ]);
    }

    public function finishQuiz($attempt_id) {
        $this->db->beginTransaction();

        try {
            // Calculer le score
            $sql = "SELECT 
                      q.id as question_id,
                      q.points,
                      qa.answer_text,
                      GROUP_CONCAT(
                          CASE WHEN qo.is_correct = 1 
                          THEN qo.option_text 
                          END
                      ) as correct_answer
                   FROM quiz_questions q
                   JOIN quiz_attempts att ON q.quiz_id = att.quiz_id
                   LEFT JOIN quiz_answers qa ON q.id = qa.question_id 
                      AND qa.attempt_id = att.id
                   LEFT JOIN quiz_options qo ON q.id = qo.question_id
                   WHERE att.id = ?
                   GROUP BY q.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$attempt_id]);
            $answers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $total_points = 0;
            $earned_points = 0;

            foreach ($answers as $answer) {
                $total_points += $answer['points'];
                if ($this->isAnswerCorrect($answer['answer_text'], $answer['correct_answer'])) {
                    $earned_points += $answer['points'];
                }
            }

            $score = ($total_points > 0) ? ($earned_points / $total_points) * 100 : 0;

            // Mettre à jour la tentative
            $sql = "UPDATE quiz_attempts SET 
                    end_time = NOW(),
                    score = ?,
                    status = 'completed',
                    updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$score, $attempt_id]);

            $this->db->commit();
            return $score;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function isAnswerCorrect($student_answer, $correct_answer) {
        if (empty($student_answer)) return false;
        
        // Pour les questions à choix multiples
        if (strpos($correct_answer, ',') !== false) {
            $correct_options = explode(',', $correct_answer);
            $student_options = explode(',', $student_answer);
            sort($correct_options);
            sort($student_options);
            return $correct_options == $student_options;
        }

        // Pour les questions à réponse unique
        return trim(strtolower($student_answer)) === trim(strtolower($correct_answer));
    }

    public function getQuizResults($quiz_id) {
        $sql = "SELECT 
                   u.name as student_name,
                   att.score,
                   att.start_time,
                   att.end_time,
                   TIMESTAMPDIFF(MINUTE, att.start_time, att.end_time) as duration
                FROM quiz_attempts att
                JOIN users u ON att.student_id = u.id
                WHERE att.quiz_id = ?
                AND att.status = 'completed'
                ORDER BY att.score DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quiz_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getStudentQuizzes($student_id) {
        $sql = "SELECT q.*,
                   s.name as subject_name,
                   t.name as teacher_name,
                   att.score,
                   att.status
                FROM quizzes q
                JOIN subjects s ON q.subject_id = s.id
                JOIN users t ON q.teacher_id = t.id
                LEFT JOIN quiz_attempts att ON q.id = att.quiz_id
                   AND att.student_id = ?
                WHERE q.class_id IN (
                    SELECT class_id 
                    FROM class_students 
                    WHERE student_id = ?
                )
                ORDER BY q.start_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$student_id, $student_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getQuizStatistics($quiz_id) {
        return [
            'participation' => $this->getParticipationRate($quiz_id),
            'average_score' => $this->getAverageScore($quiz_id),
            'score_distribution' => $this->getScoreDistribution($quiz_id),
            'question_analysis' => $this->getQuestionAnalysis($quiz_id),
            'completion_times' => $this->getCompletionTimes($quiz_id)
        ];
    }

    private function getParticipationRate($quiz_id) {
        $sql = "SELECT 
                   COUNT(DISTINCT cs.student_id) as total_students,
                   COUNT(DISTINCT att.student_id) as participants
                FROM quizzes q
                JOIN class_students cs ON q.class_id = cs.class_id
                LEFT JOIN quiz_attempts att ON q.id = att.quiz_id
                   AND att.status = 'completed'
                WHERE q.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quiz_id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data['total_students'] > 0 ? 
            ($data['participants'] / $data['total_students']) * 100 : 0;
    }

    private function getAverageScore($quiz_id) {
        $sql = "SELECT AVG(score) as average_score
                FROM quiz_attempts
                WHERE quiz_id = ?
                AND status = 'completed'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quiz_id]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function getScoreDistribution($quiz_id) {
        $sql = "SELECT 
                   CASE 
                      WHEN score >= 90 THEN '90-100'
                      WHEN score >= 80 THEN '80-89'
                      WHEN score >= 70 THEN '70-79'
                      WHEN score >= 60 THEN '60-69'
                      ELSE 'Below 60'
                   END as range,
                   COUNT(*) as count
                FROM quiz_attempts
                WHERE quiz_id = ?
                AND status = 'completed'
                GROUP BY range
                ORDER BY range";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quiz_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getQuestionAnalysis($quiz_id) {
        $sql = "SELECT 
                   q.id,
                   q.question_text,
                   COUNT(qa.id) as total_attempts,
                   SUM(CASE WHEN qa.answer_text = qo.option_text 
                        AND qo.is_correct = 1 
                        THEN 1 ELSE 0 END) as correct_answers
                FROM quiz_questions q
                LEFT JOIN quiz_answers qa ON q.id = qa.question_id
                LEFT JOIN quiz_options qo ON q.id = qo.question_id
                WHERE q.quiz_id = ?
                GROUP BY q.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quiz_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getCompletionTimes($quiz_id) {
        $sql = "SELECT 
                   TIMESTAMPDIFF(MINUTE, start_time, end_time) as duration,
                   COUNT(*) as count
                FROM quiz_attempts
                WHERE quiz_id = ?
                AND status = 'completed'
                GROUP BY duration
                ORDER BY duration";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quiz_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
