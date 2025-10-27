<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Router.php';

session_start();

// Configuration des erreurs en mode développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chargement automatique des classes
spl_autoload_register(function ($className) {
    // Convertir le namespace en chemin de fichier
    $file = __DIR__ . '/../' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Créer et dispatcher le routeur
try {
    $router = new \App\Core\Router();
    $router->dispatch($_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    // Log de l'erreur
    error_log($e->getMessage());
    
    // Afficher une erreur générique à l'utilisateur
    header("HTTP/1.0 500 Internal Server Error");
    require_once __DIR__ . '/../app/views/500.php';
}
?>
