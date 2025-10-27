<?php
namespace App\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;

class DashboardController {
    private $studentModel;
    private $teacherModel;
    private $subjectModel;
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $this->studentModel = new Student();
        $this->teacherModel = new Teacher();
        $this->subjectModel = new Subject();
    }
    
    public function index() {
        $data = [];
        
        switch ($_SESSION['user_role']) {
            case 'admin':
                // Statistiques pour l'administrateur
                $data['totalStudents'] = count($this->studentModel->getAll());
                $data['totalTeachers'] = count($this->teacherModel->getAll());
                $data['totalSubjects'] = count($this->subjectModel->getAll());
                require_once __DIR__ . '/../views/dashboard/admin.php';
                break;
                
            case 'teacher':
                // Données pour l'enseignant
                $teacherId = $_SESSION['user_id'];
                $data['subjects'] = $this->teacherModel->getSubjects($teacherId);
                require_once __DIR__ . '/../views/dashboard/teacher.php';
                break;
                
            case 'student':
                // Données pour l'étudiant
                $studentId = $_SESSION['user_id'];
                $data['attendance'] = $this->studentModel->getAttendance($studentId, date('m'), date('Y'));
                $data['grades'] = $this->studentModel->getGrades($studentId);
                require_once __DIR__ . '/../views/dashboard/student.php';
                break;
        }
    }
}
?>
