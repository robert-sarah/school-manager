<?php
namespace App\Controllers;

use App\Models\Library;
use App\Core\Controller;
use App\Core\View;
use App\Core\Security;

class LibraryController extends Controller {
    private $libraryModel;
    private $security;
    
    public function __construct() {
        parent::__construct();
        $this->libraryModel = new Library();
        $this->security = Security::getInstance();
    }
    
    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'category_id' => $_GET['category'] ?? null,
            'available' => isset($_GET['available'])
        ];
        
        $books = $this->libraryModel->searchBooks($filters);
        
        return View::render('library/index', [
            'books' => $books,
            'filters' => $filters
        ]);
    }
    
    public function addBook() {
        // Vérifier les permissions
        if (!$this->security->checkPermission('library.add')) {
            $this->flash->error('Permission refusée');
            header('Location: /library');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Vérifier le token CSRF
                $this->security->validateCsrfToken($_POST['csrf_token']);
                
                // Valider les données
                $rules = [
                    'title' => ['required', ['min' => 2], ['max' => 255]],
                    'author' => ['required', ['min' => 2], ['max' => 255]],
                    'isbn' => ['isbn'],
                    'category_id' => ['required'],
                    'total_copies' => ['required', ['min' => 1]]
                ];
                
                $errors = $this->security->validate($_POST, $rules);
                if (!empty($errors)) {
                    $this->flash->error('Données invalides');
                    return View::render('library/add', [
                        'errors' => $errors,
                        'old' => $_POST
                    ]);
                }
                
                // Sanitiser les données
                $data = $this->security->sanitize($_POST);
                
                // Ajouter le livre
                $bookId = $this->libraryModel->addBook($data);
                
                // Logger l'action
                $this->security->logActivity('library.book.add', [
                    'book_id' => $bookId,
                    'title' => $data['title']
                ]);
                
                $this->flash->success('Livre ajouté avec succès');
                header("Location: /library/view/{$bookId}");
                exit;
            } catch (\Exception $e) {
                $this->flash->error('Erreur : ' . $e->getMessage());
            }
        }
        
        return View::render('library/add');
    }
    
    public function viewBook($id) {
        $book = $this->libraryModel->getBook($id);
        
        if (!$book) {
            $this->flash->error('Book not found');
            header('Location: /library');
            exit;
        }
        
        return View::render('library/view', [
            'book' => $book
        ]);
    }
    
    public function editBook($id) {
        $book = $this->libraryModel->getBook($id);
        
        if (!$book) {
            $this->flash->error('Book not found');
            header('Location: /library');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->libraryModel->updateBook($id, $_POST);
                $this->flash->success('Book updated successfully');
                header("Location: /library/view/{$id}");
                exit;
            } catch (\Exception $e) {
                $this->flash->error('Error updating book: ' . $e->getMessage());
            }
        }
        
        return View::render('library/edit', [
            'book' => $book
        ]);
    }
    
    public function loanBook() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->libraryModel->loanBook($_POST);
                $this->flash->success('Book loaned successfully');
                header("Location: /library/view/{$_POST['book_id']}");
                exit;
            } catch (\Exception $e) {
                $this->flash->error('Error loaning book: ' . $e->getMessage());
                header("Location: /library/view/{$_POST['book_id']}");
                exit;
            }
        }
    }
    
    public function returnBook($loanId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->libraryModel->returnBook($loanId);
                $this->flash->success('Book returned successfully');
            } catch (\Exception $e) {
                $this->flash->error('Error returning book: ' . $e->getMessage());
            }
        }
        
        // Rediriger vers la page précédente
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    public function myLoans() {
        $loans = $this->libraryModel->getUserLoans($this->auth->getUserId());
        
        return View::render('library/my-loans', [
            'loans' => $loans
        ]);
    }
    
    public function overdueLoans() {
        // Vérifier les permissions
        if (!$this->auth->hasRole('librarian')) {
            $this->flash->error('Access denied');
            header('Location: /library');
            exit;
        }
        
        $loans = $this->libraryModel->getOverdueLoans();
        
        return View::render('library/overdue', [
            'loans' => $loans
        ]);
    }
}
?>
