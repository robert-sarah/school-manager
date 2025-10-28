<?php
namespace App\Core;

class BaseController {
    protected $data = [];
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    protected function view($view, $data = []) {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$view}");
        }
        
        require_once $viewPath;
    }
    
    protected function requirePermission($permission) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        if (!hasPermission($permission)) {
            header('Location: /403');
            exit;
        }
    }
    
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}