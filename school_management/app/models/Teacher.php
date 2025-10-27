<?php
namespace App\Models;

class Teacher {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("
            SELECT DISTINCT u.*, 
                   GROUP_CONCAT(DISTINCT s.name) as subjects,
                   GROUP_CONCAT(DISTINCT c.name) as classes
            FROM users u
            LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id
            LEFT JOIN subjects s ON ts.subject_id = s.id
            LEFT JOIN classes c ON ts.class_id = c.id
            WHERE u.role = 'teacher'
            GROUP BY u.id
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            // Créer l'utilisateur enseignant
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, 'teacher')
            ");
            $stmt->execute([
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);
            
            $teacherId = $this->db->lastInsertId();
            
            // Assigner les matières à l'enseignant
            if (!empty($data['subjects'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO teacher_subjects (teacher_id, subject_id, class_id, section_id)
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($data['subjects'] as $subject) {
                    $stmt->execute([
                        $teacherId,
                        $subject['subject_id'],
                        $subject['class_id'],
                        $subject['section_id']
                    ]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getSubjects($teacherId) {
        $stmt = $this->db->prepare("
            SELECT ts.*, s.name as subject_name, c.name as class_name, sec.name as section_name
            FROM teacher_subjects ts
            JOIN subjects s ON ts.subject_id = s.id
            JOIN classes c ON ts.class_id = c.id
            JOIN sections sec ON ts.section_id = sec.id
            WHERE ts.teacher_id = ?
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function addAttendance($data) {
        $stmt = $this->db->prepare("
            INSERT INTO attendance (student_id, class_id, subject_id, date, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['student_id'],
            $data['class_id'],
            $data['subject_id'],
            $data['date'],
            $data['status']
        ]);
    }
    
    public function addGrade($data) {
        $stmt = $this->db->prepare("
            INSERT INTO grades (student_id, subject_id, exam_type, grade, remarks)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['student_id'],
            $data['subject_id'],
            $data['exam_type'],
            $data['grade'],
            $data['remarks'] ?? null
        ]);
    }
}
?>
