<?php
namespace App\Controllers;

use App\Models\Teacher;

class TeacherController {
    private $teacherModel;
    
    public function __construct() {
        $this->teacherModel = new Teacher();
    }
    
    public function index() {
        // Vérifier les permissions
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        
        $teachers = $this->teacherModel->getAll();
        require_once __DIR__ . '/../views/teachers/index.php';
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'subjects' => json_decode($_POST['subjects'], true)
                ];
                
                $this->teacherModel->create($data);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Enseignant ajouté avec succès'
                ];
                header('Location: /teachers');
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/teachers/create.php';
    }
    
    public function subjects($id) {
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] != $id) {
            header('Location: /dashboard');
            exit;
        }
        
        $subjects = $this->teacherModel->getSubjects($id);
        require_once __DIR__ . '/../views/teachers/subjects.php';
    }
    
    public function attendance() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'student_id' => $_POST['student_id'],
                'class_id' => $_POST['class_id'],
                'subject_id' => $_POST['subject_id'],
                'date' => $_POST['date'],
                'status' => $_POST['status']
            ];
            
            $this->teacherModel->addAttendance($data);
            echo json_encode(['success' => true]);
            exit;
        }
        
        $teacherId = $_SESSION['user_id'];
        $subjects = $this->teacherModel->getSubjects($teacherId);
        require_once __DIR__ . '/../views/teachers/attendance.php';
    }
    
    public function grades() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'student_id' => $_POST['student_id'],
                'subject_id' => $_POST['subject_id'],
                'exam_type' => $_POST['exam_type'],
                'grade' => $_POST['grade'],
                'remarks' => $_POST['remarks']
            ];
            
            $this->teacherModel->addGrade($data);
            echo json_encode(['success' => true]);
            exit;
        }
        
        $teacherId = $_SESSION['user_id'];
        $subjects = $this->teacherModel->getSubjects($teacherId);
        require_once __DIR__ . '/../views/teachers/grades.php';
    }
}
?>
