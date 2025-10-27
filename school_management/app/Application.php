<?php
namespace App;

use App\Core\{Database, Router, Auth, Session, Cache, Logger};
use App\Services\{NotificationService, DashboardService, BackupService};
use App\Models\{Forum, Quiz, Resource, Finance};
use Dotenv\Dotenv;

require_once __DIR__ . '/helpers.php';

class Application {
    private static $instance = null;
    private $config;
    private $db;
    private $router;
    private $auth;
    private $session;
    private $cache;
    private $logger;
    private $notification;
    private $forum;
    private $quiz;
    private $resource;
    private $dashboard;
    private $finance;
    private $backup;

    private function __construct() {
        // Charger la configuration
        $this->loadConfig();
        
        // Initialiser les composants essentiels
        $this->initializeCore();
        
        // Configurer la gestion des erreurs
        $this->setupErrorHandling();
        
        // Charger les services
        $this->loadServices();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig() {
        // Charger le fichier .env
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        // Charger la configuration
        $this->config = require __DIR__ . '/../config/app.php';

        // Définir le fuseau horaire
        date_default_timezone_set($this->config['app']['timezone']);

        // Définir la locale
        setlocale(LC_ALL, $this->config['app']['locale']);
    }

    private function initializeCore() {
        // Initialiser la base de données
        $this->db = new \App\Core\Database($this->config['database']);

        // Initialiser le routeur
        $this->router = new \App\Core\Router();

        // Initialiser l'authentification
        $this->auth = new \App\Core\Auth($this->db);

        // Initialiser la session
        $this->session = new \App\Core\Session($this->config['session']);

        // Initialiser le cache
        $this->cache = new \App\Core\Cache($this->config['cache']);

        // Initialiser le logger
        $this->logger = new \App\Core\Logger($this->config['logging']);
    }

    private function setupErrorHandling() {
        // Configurer le reporting d'erreurs
        if ($this->config['app']['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        // Définir le gestionnaire d'erreurs personnalisé
        set_error_handler([$this, 'handleError']);
        
        // Définir le gestionnaire d'exceptions personnalisé
        set_exception_handler([$this, 'handleException']);
        
        // Définir le gestionnaire de fin de script
        register_shutdown_function([$this, 'handleShutdown']);
    }

    private function loadServices() {
        // Initialiser le service de notification
        $this->notification = new \App\Services\NotificationService();

        // Forum
        $this->forum = new \App\Models\Forum();

        // Quiz et évaluations
        $this->quiz = new \App\Models\Quiz();

        // Gestion des ressources
        $this->resource = new \App\Models\Resource();

        // Tableau de bord
        $this->dashboard = new \App\Services\DashboardService();

        // Gestion financière
        $this->finance = new \App\Models\Finance();

        // Service de sauvegarde
        $this->backup = new \App\Services\BackupService();
    }

    public function handleError($level, $message, $file, $line) {
        if (error_reporting() & $level) {
            $this->logger->error("$message in $file on line $line");

            if ($this->config['app']['debug']) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        }

        return true;
    }

    public function handleException($exception) {
        $this->logger->error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        if ($this->config['app']['debug']) {
            echo "<h1>Fatal Error</h1>";
            echo "<p>Message: " . $exception->getMessage() . "</p>";
            echo "<p>File: " . $exception->getFile() . "</p>";
            echo "<p>Line: " . $exception->getLine() . "</p>";
            echo "<h2>Stack Trace:</h2>";
            echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        } else {
            require __DIR__ . '/../views/errors/500.php';
        }

        exit(1);
    }

    public function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && $error['type'] === E_ERROR) {
            $this->logger->critical("Fatal Error: {$error['message']}", [
                'file' => $error['file'],
                'line' => $error['line']
            ]);

            if (!$this->config['app']['debug']) {
                require __DIR__ . '/../views/errors/500.php';
            }
        }
    }

    public function getConfig($key = null) {
        if ($key === null) {
            return $this->config;
        }

        return array_get($this->config, $key);
    }

    public function getDb() {
        return $this->db;
    }

    public function getRouter() {
        return $this->router;
    }

    public function getAuth() {
        return $this->auth;
    }

    public function getSession() {
        return $this->session;
    }

    public function getCache() {
        return $this->cache;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function getNotification() {
        return $this->notification;
    }

    public function getForum() {
        return $this->forum;
    }

    public function getQuiz() {
        return $this->quiz;
    }

    public function getResource() {
        return $this->resource;
    }

    public function getDashboard() {
        return $this->dashboard;
    }

    public function getFinance() {
        return $this->finance;
    }

    public function getBackup() {
        return $this->backup;
    }

    public function run() {
        try {
            // Démarrer la session
            $this->session->start();

            // Charger les routes
            require __DIR__ . '/../routes/web.php';
            require __DIR__ . '/../routes/api.php';

            // Traiter la requête
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $this->router->dispatch($uri);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
}
?>
