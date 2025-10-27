<?php
// Configuration principale
return [
    // Informations de base
    'app_name' => 'School Management System',
    'app_version' => '1.0.0',
    'app_url' => 'http://localhost',
    'time_zone' => 'Europe/Paris',
    
    // Base de données
    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'name' => 'school_management',
        'user' => 'school_user',
        'pass' => 'secure_password',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    
    // Sécurité
    'security' => [
        'cipher' => 'AES-256-CBC',
        'session_lifetime' => 7200, // 2 heures
        'password_algo' => PASSWORD_ARGON2ID,
        'password_options' => [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ],
        'allowed_html_tags' => '<p><br><b><i><u><strong><em>',
        'csrf_token_lifetime' => 3600, // 1 heure
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15 minutes
        'require_2fa' => true,
        'jwt_secret' => 'your-secret-key',
        'jwt_expiration' => 3600,
    ],
    
    // Logs
    'logging' => [
        'enabled' => true,
        'level' => 'debug', // debug, info, warning, error
        'file' => 'storage/logs/app.log',
        'max_files' => 30,
        'error_reporting' => E_ALL,
    ],
    
    // Mail
    'mail' => [
        'driver' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your-email@gmail.com',
        'password' => 'your-password',
        'encryption' => 'tls',
        'from_address' => 'noreply@yourschool.com',
        'from_name' => 'School Management System',
    ],
    
    // Upload
    'upload' => [
        'directory' => 'storage/uploads',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'max_size' => 5242880, // 5MB
    ],
    
    // Cache
    'cache' => [
        'driver' => 'file',
        'path' => 'storage/cache',
        'lifetime' => 3600,
    ],
    
    // Modules activés
    'modules' => [
        'users' => true,
        'classes' => true,
        'courses' => true,
        'attendance' => true,
        'grades' => true,
        'schedule' => true,
        'library' => true,
        'events' => true,
        'notifications' => true,
        'payments' => true,
    ],
    
    // Permissions par défaut
    'default_permissions' => [
        'admin' => ['*'],
        'teacher' => [
            'classes.view',
            'classes.edit',
            'attendance.manage',
            'grades.manage',
            'schedule.view',
            'library.access',
            'events.view',
            'notifications.send'
        ],
        'student' => [
            'classes.view',
            'attendance.view',
            'grades.view',
            'schedule.view',
            'library.browse',
            'events.view'
        ],
        'parent' => [
            'attendance.view',
            'grades.view',
            'schedule.view',
            'events.view',
            'payments.view'
        ]
    ],
    
    // Routes protégées
    'protected_routes' => [
        'admin/*' => ['admin'],
        'teacher/*' => ['admin', 'teacher'],
        'student/*' => ['admin', 'teacher', 'student'],
        'parent/*' => ['admin', 'parent']
    ],
    
    // API
    'api' => [
        'enabled' => true,
        'version' => 'v1',
        'rate_limit' => 60, // requêtes par minute
        'throttle_by' => 'ip', // ip ou user
    ]
];
?>
