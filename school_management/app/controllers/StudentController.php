<?php
namespace App\Controllers;

use App\Models\Student;

class StudentController {
    private $studentModel;
    
    public function __construct() {
        $this->studentModel = new Student();
    }
    
    public function index() {
        // Vérifier les permissions
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        
        $students = $this->studentModel->getAll();
        require_once __DIR__ . '/../views/students/index.php';
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userData = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password']
                ];
                
                $classData = [
                    'class_id' => $_POST['class_id'],
                    'section_id' => $_POST['section_id'],
                    'academic_year' => $_POST['academic_year']
                ];
                
                $this->studentModel->create($userData, $classData);
                header('Location: /students');
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/students/create.php';
    }
    
    public function attendance($id) {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        
        $attendance = $this->studentModel->getAttendance($id, $month, $year);
        require_once __DIR__ . '/../views/students/attendance.php';
    }
    
    public function grades($id) {
        $grades = $this->studentModel->getGrades($id);
        require_once __DIR__ . '/../views/students/grades.php';
    }
}
?>
