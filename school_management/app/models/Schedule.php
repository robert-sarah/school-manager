<?php
namespace App\Models;

class Schedule {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function createTimeSlot($data) {
        $stmt = $this->db->prepare("
            INSERT INTO time_slots (start_time, end_time, day_of_week)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['start_time'],
            $data['end_time'],
            $data['day_of_week']
        ]);
    }
    
    public function createSchedule($data) {
        $stmt = $this->db->prepare("
            INSERT INTO schedules (class_id, subject_id, teacher_id, time_slot_id, room_number)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['class_id'],
            $data['subject_id'],
            $data['teacher_id'],
            $data['time_slot_id'],
            $data['room_number']
        ]);
    }
    
    public function getClassSchedule($classId) {
        $stmt = $this->db->prepare("
            SELECT s.*, ts.start_time, ts.end_time, ts.day_of_week,
                   sub.name as subject_name, u.name as teacher_name
            FROM schedules s
            JOIN time_slots ts ON s.time_slot_id = ts.id
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN users u ON s.teacher_id = u.id
            WHERE s.class_id = ?
            ORDER BY ts.day_of_week, ts.start_time
        ");
        $stmt->execute([$classId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getTeacherSchedule($teacherId) {
        $stmt = $this->db->prepare("
            SELECT s.*, ts.start_time, ts.end_time, ts.day_of_week,
                   sub.name as subject_name, c.name as class_name
            FROM schedules s
            JOIN time_slots ts ON s.time_slot_id = ts.id
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN classes c ON s.class_id = c.id
            WHERE s.teacher_id = ?
            ORDER BY ts.day_of_week, ts.start_time
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function checkConflicts($timeSlotId, $teacherId, $classId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM schedules s
            WHERE s.time_slot_id = ?
            AND (s.teacher_id = ? OR s.class_id = ?)
        ");
        $stmt->execute([$timeSlotId, $teacherId, $classId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    public function updateSchedule($scheduleId, $data) {
        $stmt = $this->db->prepare("
            UPDATE schedules
            SET class_id = ?, subject_id = ?, teacher_id = ?, 
                time_slot_id = ?, room_number = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['class_id'],
            $data['subject_id'],
            $data['teacher_id'],
            $data['time_slot_id'],
            $data['room_number'],
            $scheduleId
        ]);
    }
    
    public function deleteSchedule($scheduleId) {
        $stmt = $this->db->prepare("DELETE FROM schedules WHERE id = ?");
        return $stmt->execute([$scheduleId]);
    }
    
    public function getTimeSlots() {
        $stmt = $this->db->query("
            SELECT * FROM time_slots 
            ORDER BY day_of_week, start_time
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
