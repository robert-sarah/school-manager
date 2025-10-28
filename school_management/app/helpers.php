<?php
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null) {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string {
        return rtrim(__DIR__ . '/../..', '/') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string {
        return base_path('storage') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string {
        return base_path('config') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('view_path')) {
    function view_path(string $path = ''): string {
        return base_path('app/views') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool {
        if (!isset($_SESSION['user_role'])) {
            return false;
        }

        $role = $_SESSION['user_role'];
        $permissions = require config_path('permissions.php');
        
        if (!isset($permissions['roles'][$role])) {
            return false;
        }

        $rolePermissions = $permissions['roles'][$role]['permissions'];
        
        if ($rolePermissions === '*' || in_array($permission, (array)$rolePermissions)) {
            return true;
        }

        return false;
    }
}
?>
