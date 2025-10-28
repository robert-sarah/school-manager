<?php
namespace App\Core;

class View {
    public static function render(string $view, array $data = []): string {
        $viewPath = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }
        
        extract($data, EXTR_OVERWRITE);
        
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
    
    public static function renderPartial(string $partial, array $data = []): string {
        return self::render("partials/{$partial}", $data);
    }
    
    public static function escape(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}