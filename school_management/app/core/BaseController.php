<?php
namespace App\Core;

class BaseController {
    protected $data = [];
    
    public function __construct() {
        // Vérifier si la session est active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier l'authentification de l'utilisateur
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Régénérer l'ID de session périodiquement pour la sécurité
        if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    protected function view($view, $data = []) {
        try {
            $this->data = array_merge($this->data, $data);
            
            // Échapper les données pour prévenir les XSS
            array_walk_recursive($this->data, function(&$value) {
                if (is_string($value)) {
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            });
            
            extract($this->data);
            
            $viewPath = __DIR__ . '/../views/' . $view . '.php';
            if (!file_exists($viewPath)) {
                throw new \Exception("Vue introuvable: {$view}");
            }
            
            ob_start();
            require_once $viewPath;
            return ob_get_clean();
        } catch (\Exception $e) {
            $this->setFlash('error', 'Erreur lors du chargement de la vue: ' . $e->getMessage());
            return false;
        }
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