<?php
namespace App\Core;

class Router {
    private $routes = [];
    
    public function __construct() {
        $this->routes = require_once __DIR__ . '/../../config/routes.php';
    }
    
    public function dispatch($uri) {
        // Supprimer les query strings de l'URI
        $uri = explode('?', $uri)[0];
        
        // Supprimer le trailing slash
        $uri = rtrim($uri, '/');
        
        // Si l'URI est vide, utiliser la route par défaut
        if (empty($uri)) {
            $uri = '';
        }
        
        // Chercher une correspondance dans les routes
        foreach ($this->routes as $route => $handler) {
            // Convertir les paramètres de route en regex
            $pattern = $this->convertRouteToRegex($route);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Supprimer la correspondance complète
                
                $controllerName = "\\App\\Controllers\\" . $handler[0];
                $methodName = $handler[1];
                
                if (!class_exists($controllerName)) {
                    throw new \Exception("Controller $controllerName not found");
                }
                
                $controller = new $controllerName();
                
                if (!method_exists($controller, $methodName)) {
                    throw new \Exception("Method $methodName not found in $controllerName");
                }
                
                return call_user_func_array([$controller, $methodName], $matches);
            }
        }
        
        // Si aucune route ne correspond
        header("HTTP/1.0 404 Not Found");
        require_once __DIR__ . '/../views/404.php';
    }
    
    private function convertRouteToRegex($route) {
        // Échapper les caractères spéciaux
        $route = preg_quote($route, '/');
        
        // Convertir les paramètres {param} en groupes de capture
        $route = preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '([^/]+)', $route);
        
        // Ajouter les délimiteurs
        return "/^" . $route . "$/";
    }
}
?>
