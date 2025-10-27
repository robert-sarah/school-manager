<?php
namespace App\Models;

class ClassRoom {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("
            SELECT c.*, 
                   COUNT(DISTINCT s.id) as sections_count,
                   COUNT(DISTINCT sc.student_id) as students_count
            FROM classes c
            LEFT JOIN sections s ON c.id = s.class_id
            LEFT JOIN student_classes sc ON c.id = sc.class_id
            GROUP BY c.id
            ORDER BY c.name
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO classes (name, description)
            VALUES (?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null
        ]);
    }
    
    public function getSections($classId) {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   COUNT(DISTINCT sc.student_id) as students_count,
                   GROUP_CONCAT(DISTINCT CONCAT(u.name, ' (', sub.name, ')')) as teachers
            FROM sections s
            LEFT JOIN student_classes sc ON s.id = sc.section_id
            LEFT JOIN teacher_subjects ts ON s.id = ts.section_id
            LEFT JOIN users u ON ts.teacher_id = u.id
            LEFT JOIN subjects sub ON ts.subject_id = sub.id
            WHERE s.class_id = ?
            GROUP BY s.id
            ORDER BY s.name
        ");
        $stmt->execute([$classId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function addSection($classId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO sections (class_id, name, room_number)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $classId,
            $data['name'],
            $data['room_number']
        ]);
    }
    
    public function getStudents($classId, $sectionId = null) {
        $query = "
            SELECT u.*, sc.section_id, s.name as section_name
            FROM users u
            JOIN student_classes sc ON u.id = sc.student_id
            JOIN sections s ON sc.section_id = s.id
            WHERE sc.class_id = ?
        ";
        $params = [$classId];
        
        if ($sectionId) {
            $query .= " AND sc.section_id = ?";
            $params[] = $sectionId;
        }
        
        $query .= " ORDER BY u.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getSubjects($classId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT s.*, 
                   GROUP_CONCAT(DISTINCT u.name) as teachers
            FROM subjects s
            JOIN teacher_subjects ts ON s.id = ts.subject_id
            JOIN users u ON ts.teacher_id = u.id
            WHERE ts.class_id = ?
            GROUP BY s.id
            ORDER BY s.name
        ");
        $stmt->execute([$classId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
