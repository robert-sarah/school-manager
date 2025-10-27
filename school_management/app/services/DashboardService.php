<?php
namespace App\Services;

class DashboardService {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    public function getAdminDashboard() {
        return [
            'student_stats' => $this->getStudentStatistics(),
            'attendance_stats' => $this->getAttendanceStatistics(),
            'financial_stats' => $this->getFinancialStatistics(),
            'academic_stats' => $this->getAcademicStatistics(),
            'recent_activities' => $this->getRecentActivities(),
            'upcoming_events' => $this->getUpcomingEvents()
        ];
    }

    public function getTeacherDashboard($teacher_id) {
        return [
            'classes' => $this->getTeacherClasses($teacher_id),
            'upcoming_lessons' => $this->getUpcomingLessons($teacher_id),
            'recent_submissions' => $this->getRecentSubmissions($teacher_id),
            'attendance_summary' => $this->getClassesAttendance($teacher_id),
            'performance_stats' => $this->getClassesPerformance($teacher_id)
        ];
    }

    public function getStudentDashboard($student_id) {
        return [
            'schedule' => $this->getStudentSchedule($student_id),
            'grades' => $this->getStudentGrades($student_id),
            'attendance' => $this->getStudentAttendance($student_id),
            'assignments' => $this->getPendingAssignments($student_id),
            'announcements' => $this->getRecentAnnouncements($student_id)
        ];
    }

    private function getStudentStatistics() {
        // Total d'étudiants
        $sql = "SELECT 
                   COUNT(*) as total,
                   SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                       THEN 1 ELSE 0 END) as new_students,
                   COUNT(DISTINCT class_id) as total_classes
                FROM users u
                JOIN class_students cs ON u.id = cs.student_id
                WHERE u.role = 'student'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $basic_stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Distribution par classe
        $sql = "SELECT 
                   c.name,
                   COUNT(*) as count
                FROM class_students cs
                JOIN classes c ON cs.class_id = c.id
                GROUP BY c.id
                ORDER BY count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $class_distribution = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_merge($basic_stats, [
            'class_distribution' => $class_distribution
        ]);
    }

    private function getAttendanceStatistics() {
        $sql = "SELECT 
                   COUNT(*) as total_records,
                   SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                   SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                   SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                FROM attendance
                WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $monthly_stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Tendance journalière
        $sql = "SELECT 
                   DATE(date) as day,
                   COUNT(*) as total,
                   SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                FROM attendance
                WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(date)
                ORDER BY date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $daily_trend = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_merge($monthly_stats, [
            'daily_trend' => $daily_trend
        ]);
    }

    private function getFinancialStatistics() {
        // Revenus mensuels
        $sql = "SELECT 
                   SUM(amount) as total_revenue,
                   COUNT(*) as total_payments,
                   AVG(amount) as average_payment
                FROM payments
                WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $revenue_stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Paiements en attente
        $sql = "SELECT 
                   COUNT(*) as total_pending,
                   SUM(balance) as total_outstanding
                FROM student_accounts
                WHERE balance > 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pending_stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        return array_merge($revenue_stats, $pending_stats);
    }

    private function getAcademicStatistics() {
        // Moyenne des notes par matière
        $sql = "SELECT 
                   s.name as subject,
                   AVG(g.grade) as average_grade,
                   COUNT(*) as total_grades
                FROM grades g
                JOIN subjects s ON g.subject_id = s.id
                WHERE g.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY s.id
                ORDER BY average_grade DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $grade_stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Taux de réussite par classe
        $sql = "SELECT 
                   c.name as class,
                   COUNT(DISTINCT g.student_id) as total_students,
                   SUM(CASE WHEN g.grade >= 60 THEN 1 ELSE 0 END) as passing_students
                FROM grades g
                JOIN class_students cs ON g.student_id = cs.student_id
                JOIN classes c ON cs.class_id = c.id
                WHERE g.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY c.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $class_stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'subject_performance' => $grade_stats,
            'class_performance' => $class_stats
        ];
    }

    private function getRecentActivities() {
        $sql = "SELECT 'grade' as type,
                       u.name as user_name,
                       s.name as subject_name,
                       g.grade as detail,
                       g.created_at as date
                FROM grades g
                JOIN users u ON g.student_id = u.id
                JOIN subjects s ON g.subject_id = s.id
                WHERE g.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                UNION ALL
                SELECT 'attendance' as type,
                       u.name as user_name,
                       s.name as subject_name,
                       a.status as detail,
                       a.date
                FROM attendance a
                JOIN users u ON a.student_id = u.id
                JOIN schedules sch ON a.schedule_id = sch.id
                JOIN subjects s ON sch.subject_id = s.id
                WHERE a.date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY date DESC
                LIMIT 20";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getUpcomingEvents() {
        $sql = "SELECT title, 
                       description,
                       start_date,
                       end_date,
                       type
                FROM events
                WHERE start_date >= NOW()
                ORDER BY start_date
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTeacherClasses($teacher_id) {
        $sql = "SELECT DISTINCT 
                   c.*,
                   COUNT(cs.student_id) as student_count
                FROM schedules s
                JOIN classes c ON s.class_id = c.id
                LEFT JOIN class_students cs ON c.id = cs.class_id
                WHERE s.teacher_id = ?
                GROUP BY c.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getUpcomingLessons($teacher_id) {
        $sql = "SELECT s.*,
                   c.name as class_name,
                   sub.name as subject_name
                FROM schedules s
                JOIN classes c ON s.class_id = c.id
                JOIN subjects sub ON s.subject_id = sub.id
                WHERE s.teacher_id = ?
                AND s.day_of_week >= DAYOFWEEK(NOW())
                ORDER BY s.day_of_week, s.start_time
                LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getRecentSubmissions($teacher_id) {
        $sql = "SELECT a.*,
                   u.name as student_name,
                   s.name as subject_name
                FROM assignments_submissions as_sub
                JOIN assignments a ON as_sub.assignment_id = a.id
                JOIN users u ON as_sub.student_id = u.id
                JOIN subjects s ON a.subject_id = s.id
                WHERE a.teacher_id = ?
                AND as_sub.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY as_sub.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getClassesAttendance($teacher_id) {
        $sql = "SELECT 
                   c.name as class_name,
                   COUNT(*) as total_students,
                   SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                   SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                   SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late
                FROM schedules s
                JOIN classes c ON s.class_id = c.id
                JOIN attendance a ON s.id = a.schedule_id
                WHERE s.teacher_id = ?
                AND a.date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY c.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getClassesPerformance($teacher_id) {
        $sql = "SELECT 
                   c.name as class_name,
                   s.name as subject_name,
                   AVG(g.grade) as average_grade,
                   MIN(g.grade) as lowest_grade,
                   MAX(g.grade) as highest_grade
                FROM grades g
                JOIN class_students cs ON g.student_id = cs.student_id
                JOIN classes c ON cs.class_id = c.id
                JOIN subjects s ON g.subject_id = s.id
                JOIN schedules sch ON (c.id = sch.class_id AND s.id = sch.subject_id)
                WHERE sch.teacher_id = ?
                AND g.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY c.id, s.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getStudentSchedule($student_id) {
        $sql = "SELECT s.*,
                   sub.name as subject_name,
                   t.name as teacher_name,
                   r.name as room_name
                FROM schedules s
                JOIN class_students cs ON s.class_id = cs.class_id
                JOIN subjects sub ON s.subject_id = sub.id
                JOIN users t ON s.teacher_id = t.id
                JOIN rooms r ON s.room_id = r.id
                WHERE cs.student_id = ?
                ORDER BY s.day_of_week, s.start_time";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getStudentGrades($student_id) {
        $sql = "SELECT g.*,
                   s.name as subject_name,
                   t.name as teacher_name
                FROM grades g
                JOIN subjects s ON g.subject_id = s.id
                JOIN users t ON g.teacher_id = t.id
                WHERE g.student_id = ?
                ORDER BY g.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getStudentAttendance($student_id) {
        $sql = "SELECT a.*,
                   s.name as subject_name,
                   t.name as teacher_name
                FROM attendance a
                JOIN schedules sch ON a.schedule_id = sch.id
                JOIN subjects s ON sch.subject_id = s.id
                JOIN users t ON sch.teacher_id = t.id
                WHERE a.student_id = ?
                AND a.date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY a.date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getPendingAssignments($student_id) {
        $sql = "SELECT a.*,
                   s.name as subject_name,
                   t.name as teacher_name
                FROM assignments a
                JOIN subjects s ON a.subject_id = s.id
                JOIN users t ON a.teacher_id = t.id
                JOIN class_students cs ON (
                    a.class_id = cs.class_id 
                    AND cs.student_id = ?
                )
                WHERE a.due_date > NOW()
                AND a.id NOT IN (
                    SELECT assignment_id 
                    FROM assignments_submissions
                    WHERE student_id = ?
                )
                ORDER BY a.due_date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$student_id, $student_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getRecentAnnouncements($student_id) {
        $sql = "SELECT a.*,
                   u.name as author_name
                FROM announcements a
                JOIN users u ON a.author_id = u.id
                WHERE (a.class_id IN (
                    SELECT class_id 
                    FROM class_students
                    WHERE student_id = ?
                ) OR a.class_id IS NULL)
                AND a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY a.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
