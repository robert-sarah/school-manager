<?php
namespace App\Models;

class Student {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("
            SELECT u.*, sc.class_id, c.name as class_name, s.name as section_name
            FROM users u
            JOIN student_classes sc ON u.id = sc.student_id
            JOIN classes c ON sc.class_id = c.id
            JOIN sections s ON sc.section_id = s.id
            WHERE u.role = 'student'
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function create($userData, $classData) {
        $this->db->beginTransaction();
        
        try {
            // Insérer l'utilisateur
            $userStmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, 'student')
            ");
            $userStmt->execute([
                $userData['name'],
                $userData['email'],
                password_hash($userData['password'], PASSWORD_DEFAULT)
            ]);
            
            $studentId = $this->db->lastInsertId();
            
            // Assigner l'élève à une classe
            $classStmt = $this->db->prepare("
                INSERT INTO student_classes (student_id, class_id, section_id, academic_year)
                VALUES (?, ?, ?, ?)
            ");
            $classStmt->execute([
                $studentId,
                $classData['class_id'],
                $classData['section_id'],
                $classData['academic_year']
            ]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getAttendance($studentId, $month, $year) {
        $stmt = $this->db->prepare("
            SELECT a.*, s.name as subject_name
            FROM attendance a
            JOIN subjects s ON a.subject_id = s.id
            WHERE a.student_id = ?
            AND MONTH(a.date) = ?
            AND YEAR(a.date) = ?
        ");
        $stmt->execute([$studentId, $month, $year]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getGrades($studentId) {
        $stmt = $this->db->prepare("
            SELECT g.*, s.name as subject_name
            FROM grades g
            JOIN subjects s ON g.subject_id = s.id
            WHERE g.student_id = ?
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
