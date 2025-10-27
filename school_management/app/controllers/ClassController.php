<?php
namespace App\Controllers;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;

class ClassController {
    private $classModel;
    private $subjectModel;
    private $teacherModel;
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $this->classModel = new ClassRoom();
        $this->subjectModel = new Subject();
        $this->teacherModel = new Teacher();
    }
    
    public function index() {
        $classes = $this->classModel->getAll();
        require_once __DIR__ . '/../views/classes/index.php';
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? null
                ];
                
                $this->classModel->create($data);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Classe créée avec succès'
                ];
                header('Location: /classes');
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/classes/create.php';
    }
    
    public function sections($id) {
        $sections = $this->classModel->getSections($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'room_number' => $_POST['room_number']
                ];
                
                $this->classModel->addSection($id, $data);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Section ajoutée avec succès'
                ];
                header("Location: /classes/{$id}/sections");
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/classes/sections.php';
    }
    
    public function students($id) {
        $sectionId = $_GET['section'] ?? null;
        $students = $this->classModel->getStudents($id, $sectionId);
        $sections = $this->classModel->getSections($id);
        
        require_once __DIR__ . '/../views/classes/students.php';
    }
    
    public function subjects($id) {
        $subjects = $this->classModel->getSubjects($id);
        $allSubjects = $this->subjectModel->getAll();
        $teachers = $this->teacherModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'subject_id' => $_POST['subject_id'],
                    'teacher_id' => $_POST['teacher_id'],
                    'class_id' => $id
                ];
                
                $this->classModel->assignSubject($data);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Matière assignée avec succès'
                ];
                header("Location: /classes/{$id}/subjects");
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/classes/subjects.php';
    }
    
    public function syllabus($id) {
        $subjects = $this->classModel->getSubjects($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $subject_id = $_POST['subject_id'];
                $file = $_FILES['syllabus'];
                
                // Gestion du téléchargement du fichier
                $upload_dir = __DIR__ . '/../../public/uploads/syllabus/';
                $filename = time() . '_' . basename($file['name']);
                move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
                
                $data = [
                    'subject_id' => $subject_id,
                    'class_id' => $id,
                    'file_path' => 'uploads/syllabus/' . $filename,
                    'description' => $_POST['description'] ?? null
                ];
                
                $this->classModel->addSyllabus($data);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Programme ajouté avec succès'
                ];
                header("Location: /classes/{$id}/syllabus");
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/classes/syllabus.php';
    }
}
?>
