<?php
namespace App\Core;

class Security {
    private static $instance = null;
    private $session;
    
    private function __construct() {
        $this->session = Session::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Protection CSRF
    public function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }
    
    public function validateCsrfToken($token) {
        $storedToken = $this->session->get('csrf_token');
        if (!$storedToken || !hash_equals($storedToken, $token)) {
            throw new \Exception('Invalid CSRF token');
        }
        return true;
    }
    
    // Validation des entrées
    public function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    public function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            if (!isset($data[$field]) && in_array('required', $fieldRules)) {
                $errors[$field][] = "Le champ est requis";
                continue;
            }
            
            if (!isset($data[$field])) {
                continue;
            }
            
            $value = $data[$field];
            
            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    switch ($rule) {
                        case 'required':
                            if (empty($value)) {
                                $errors[$field][] = "Le champ est requis";
                            }
                            break;
                            
                        case 'email':
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $errors[$field][] = "Email invalide";
                            }
                            break;
                            
                        case 'isbn':
                            if (!preg_match('/^(?:\d{10}|\d{13})$/', $value)) {
                                $errors[$field][] = "ISBN invalide";
                            }
                            break;
                            
                        case 'date':
                            if (!empty($value) && !strtotime($value)) {
                                $errors[$field][] = "Date invalide";
                            }
                            break;
                        case 'email':
                            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $errors[$field][] = "Email invalide";
                            }
                            break;
                        case 'url':
                            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                                $errors[$field][] = "URL invalide";
                            }
                            break;
                        case 'numeric':
                            if (!empty($value) && !is_numeric($value)) {
                                $errors[$field][] = "Doit être un nombre";
                            }
                            break;
                    }
                } elseif (is_array($rule)) {
                    $ruleName = key($rule);
                    $ruleValue = current($rule);
                    
                    switch ($ruleName) {
                        case 'min':
                            if (strlen($value) < $ruleValue) {
                                $errors[$field][] = "Minimum $ruleValue caractères requis";
                            }
                            break;
                            
                        case 'max':
                            if (strlen($value) > $ruleValue) {
                                $errors[$field][] = "Maximum $ruleValue caractères autorisés";
                            }
                            break;
                            
                        case 'regex':
                            if (!empty($value)) {
                                if (!preg_match($ruleValue, $value)) {
                                    $errors[$field][] = "Format invalide";
                                }
                            }
                            break;
                        case 'in':
                            if (!empty($value) && !in_array($value, (array)$ruleValue)) {
                                $errors[$field][] = "Valeur non autorisée";
                            }
                            break;
                        case 'unique':
                            if (!empty($value)) {
                                list($table, $column) = explode(',', $ruleValue);
                                $stmt = $this->db->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
                                $stmt->execute([$value]);
                                if ($stmt->fetchColumn() > 0) {
                                    $errors[$field][] = "Cette valeur existe déjà";
                                }
                            }
                            break;
                    }
                }
            }
        }
        
        return $errors;
    }
    
    // Permissions détaillées
    public function checkPermission($permission, $userId = null) {
        if ($userId === null) {
            $userId = $this->session->get('user_id');
        }
        
        if (!$userId) {
            return false;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Vérifier les rôles de l'utilisateur
        $stmt = $db->prepare("
            SELECT r.permissions
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$userId]);
        $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($roles as $role) {
            $permissions = json_decode($role['permissions'], true);
            if (in_array($permission, $permissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    // Logs d'activité
    public function logActivity($action, $details = [], $userId = null) {
        if ($userId === null) {
            $userId = $this->session->get('user_id');
        }
        
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO activity_logs (
                user_id, action, details, ip_address,
                user_agent, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $userId,
            $action,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    }
}
?>
