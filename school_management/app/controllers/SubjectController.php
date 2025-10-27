<?php
namespace App\Controllers;

use App\Models\Subject;

class SubjectController {
    private $subjectModel;
    
    public function __construct() {
        $this->subjectModel = new Subject();
    }
    
    public function index() {
        // Vérifier les permissions
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        
        $subjects = $this->subjectModel->getAll();
        require_once __DIR__ . '/../views/subjects/index.php';
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'code' => $_POST['code'],
                    'description' => $_POST['description'] ?? null
                ];
                
                $this->subjectModel->create($data);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Matière ajoutée avec succès'
                ];
                header('Location: /subjects');
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/subjects/create.php';
    }
    
    public function edit($id) {
        $subject = $this->subjectModel->getById($id);
        
        if (!$subject) {
            header('Location: /subjects');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'code' => $_POST['code'],
                    'description' => $_POST['description'] ?? null
                ];
                
                $this->subjectModel->update($id, $data);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Matière mise à jour avec succès'
                ];
                header('Location: /subjects');
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/subjects/edit.php';
    }
    
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->subjectModel->delete($id);
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Matière supprimée avec succès'
                ];
            } catch (\Exception $e) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => $e->getMessage()
                ];
            }
            header('Location: /subjects');
            exit;
        }
    }
}
?>
