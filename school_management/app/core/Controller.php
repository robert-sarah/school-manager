<?php
namespace App\Core;

use App\Core\View;
use App\Core\Flash;

class Controller {
    protected $flash;

    public function __construct() {
        $this->flash = new Flash();
    }

    protected function view(string $view, array $data = []): void {
        echo View::render($view, $data);
    }

    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    protected function jsonResponse(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function validate(array $data, array $rules): bool {
        // Implémentation basique de validation, à étendre avec config/validation.php
        return true; // Placeholder
    }
}