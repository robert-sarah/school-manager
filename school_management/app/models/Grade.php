<?php
namespace App\Models;

class Grade {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function addExam($data) {
        $stmt = $this->db->prepare("
            INSERT INTO exams (name, subject_id, class_id, exam_date, total_marks, passing_marks)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['subject_id'],
            $data['class_id'],
            $data['exam_date'],
            $data['total_marks'],
            $data['passing_marks']
        ]);
    }
    
    public function addGrades($examId, $grades) {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO grades (exam_id, student_id, marks_obtained, remarks)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($grades as $grade) {
                $stmt->execute([
                    $examId,
                    $grade['student_id'],
                    $grade['marks'],
                    $grade['remarks'] ?? null
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getStudentGrades($studentId, $classId = null) {
        $query = "
            SELECT g.*, e.name as exam_name, s.name as subject_name,
                   e.total_marks, e.passing_marks, e.exam_date
            FROM grades g
            JOIN exams e ON g.exam_id = e.id
            JOIN subjects s ON e.subject_id = s.id
            WHERE g.student_id = ?
        ";
        $params = [$studentId];
        
        if ($classId) {
            $query .= " AND e.class_id = ?";
            $params[] = $classId;
        }
        
        $query .= " ORDER BY e.exam_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getClassGrades($classId, $examId) {
        $stmt = $this->db->prepare("
            SELECT g.*, u.name as student_name,
                   e.total_marks, e.passing_marks
            FROM grades g
            JOIN users u ON g.student_id = u.id
            JOIN exams e ON g.exam_id = e.id
            WHERE e.class_id = ? AND e.id = ?
            ORDER BY u.name
        ");
        $stmt->execute([$classId, $examId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getExams($classId = null, $subjectId = null) {
        $query = "
            SELECT e.*, s.name as subject_name,
                   COUNT(g.id) as grades_count
            FROM exams e
            JOIN subjects s ON e.subject_id = s.id
            LEFT JOIN grades g ON e.id = g.exam_id
            WHERE 1=1
        ";
        $params = [];
        
        if ($classId) {
            $query .= " AND e.class_id = ?";
            $params[] = $classId;
        }
        
        if ($subjectId) {
            $query .= " AND e.subject_id = ?";
            $params[] = $subjectId;
        }
        
        $query .= " GROUP BY e.id ORDER BY e.exam_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function generateReport($studentId, $classId, $semesterId) {
        // Calculer la moyenne par matière
        $stmt = $this->db->prepare("
            SELECT 
                s.id as subject_id,
                s.name as subject_name,
                AVG(g.marks_obtained) as average_marks,
                MIN(g.marks_obtained) as lowest_marks,
                MAX(g.marks_obtained) as highest_marks,
                COUNT(g.id) as exams_count
            FROM subjects s
            JOIN exams e ON s.id = e.subject_id
            JOIN grades g ON e.id = g.exam_id
            WHERE g.student_id = ? 
            AND e.class_id = ?
            AND e.semester_id = ?
            GROUP BY s.id, s.name
        ");
        $stmt->execute([$studentId, $classId, $semesterId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
