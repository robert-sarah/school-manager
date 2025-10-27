<?php
namespace App\Models;

class Attendance {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function markAttendance($data) {
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
    
    public function getAttendanceByDate($classId, $subjectId, $date) {
        $stmt = $this->db->prepare("
            SELECT a.*, u.name as student_name
            FROM attendance a
            JOIN users u ON a.student_id = u.id
            WHERE a.class_id = ? AND a.subject_id = ? AND a.date = ?
        ");
        $stmt->execute([$classId, $subjectId, $date]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getStudentAttendance($studentId, $month, $year) {
        $stmt = $this->db->prepare("
            SELECT a.*, s.name as subject_name
            FROM attendance a
            JOIN subjects s ON a.subject_id = s.id
            WHERE a.student_id = ? AND MONTH(a.date) = ? AND YEAR(a.date) = ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$studentId, $month, $year]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getAttendanceReport($classId, $sectionId = null, $startDate = null, $endDate = null) {
        $query = "
            SELECT 
                u.id as student_id,
                u.name as student_name,
                COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
                COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
                COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
                COUNT(*) as total_days
            FROM users u
            JOIN student_classes sc ON u.id = sc.student_id
            LEFT JOIN attendance a ON u.id = a.student_id
            WHERE sc.class_id = ?
        ";
        
        $params = [$classId];
        
        if ($sectionId) {
            $query .= " AND sc.section_id = ?";
            $params[] = $sectionId;
        }
        
        if ($startDate && $endDate) {
            $query .= " AND a.date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $query .= " GROUP BY u.id, u.name ORDER BY u.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
