<?php
namespace App\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Subject;

class AttendanceController {
    private $attendanceModel;
    private $classModel;
    private $subjectModel;
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $this->attendanceModel = new Attendance();
        $this->classModel = new ClassRoom();
        $this->subjectModel = new Subject();
    }
    
    public function index() {
        switch ($_SESSION['user_role']) {
            case 'teacher':
                $this->teacherAttendanceView();
                break;
                
            case 'admin':
                $this->adminAttendanceView();
                break;
                
            case 'student':
                $this->studentAttendanceView();
                break;
        }
    }
    
    private function teacherAttendanceView() {
        $teacherId = $_SESSION['user_id'];
        $classes = $this->classModel->getTeacherClasses($teacherId);
        require_once __DIR__ . '/../views/attendance/teacher_index.php';
    }
    
    private function adminAttendanceView() {
        $classes = $this->classModel->getAll();
        require_once __DIR__ . '/../views/attendance/admin_index.php';
    }
    
    private function studentAttendanceView() {
        $studentId = $_SESSION['user_id'];
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        
        $attendance = $this->attendanceModel->getStudentAttendance($studentId, $month, $year);
        require_once __DIR__ . '/../views/attendance/student_index.php';
    }
    
    public function mark() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'class_id' => $_POST['class_id'],
                    'subject_id' => $_POST['subject_id'],
                    'date' => $_POST['date'],
                    'students' => json_decode($_POST['students'], true)
                ];
                
                foreach ($data['students'] as $student) {
                    $this->attendanceModel->markAttendance([
                        'student_id' => $student['id'],
                        'class_id' => $data['class_id'],
                        'subject_id' => $data['subject_id'],
                        'date' => $data['date'],
                        'status' => $student['status']
                    ]);
                }
                
                echo json_encode(['success' => true]);
                exit;
            } catch (\Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
        
        $classId = $_GET['class'] ?? null;
        $subjectId = $_GET['subject'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $students = $this->classModel->getStudents($classId);
        $existingAttendance = $this->attendanceModel->getAttendanceByDate($classId, $subjectId, $date);
        
        require_once __DIR__ . '/../views/attendance/mark.php';
    }
    
    public function report() {
        $classId = $_GET['class'] ?? null;
        $sectionId = $_GET['section'] ?? null;
        $startDate = $_GET['start'] ?? date('Y-m-01');
        $endDate = $_GET['end'] ?? date('Y-m-t');
        
        $report = $this->attendanceModel->getAttendanceReport($classId, $sectionId, $startDate, $endDate);
        $classes = $this->classModel->getAll();
        
        if ($classId) {
            $sections = $this->classModel->getSections($classId);
        }
        
        require_once __DIR__ . '/../views/attendance/report.php';
    }
}
?>
