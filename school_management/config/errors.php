<?php
return [
    // Configuration générale des erreurs
    'display_errors' => env('APP_DEBUG', false),
    'log_errors' => true,
    'error_reporting' => E_ALL,

    // Configuration du logger
    'logger' => [
        'default' => env('LOG_CHANNEL', 'daily'),
        'channels' => [
            'daily' => [
                'driver' => 'daily',
                'path' => storage_path('logs/error.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 14,
            ],
            'slack' => [
                'driver' => 'slack',
                'url' => env('LOG_SLACK_WEBHOOK_URL'),
                'username' => 'School Management Error Log',
                'emoji' => ':boom:',
                'level' => 'critical',
            ],
        ],
    ],

    // Messages d'erreur personnalisés
    'messages' => [
        // Erreurs d'authentification
        'auth' => [
            'invalid_credentials' => 'Identifiants invalides.',
            'account_disabled' => 'Votre compte a été désactivé.',
            'session_expired' => 'Votre session a expiré. Veuillez vous reconnecter.',
            'unauthorized' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.',
            'invalid_token' => 'Jeton d\'authentification invalide.',
        ],

        // Erreurs de validation
        'validation' => [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
            'min' => [
                'numeric' => 'Le champ :attribute doit être supérieur à :min.',
                'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
            ],
            'max' => [
                'numeric' => 'Le champ :attribute ne peut pas être supérieur à :max.',
                'string' => 'Le champ :attribute ne peut pas contenir plus de :max caractères.',
            ],
            'unique' => 'La valeur du champ :attribute est déjà utilisée.',
            'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
            'date' => 'Le champ :attribute n\'est pas une date valide.',
        ],

        // Erreurs de base de données
        'database' => [
            'connection_failed' => 'Impossible de se connecter à la base de données.',
            'query_failed' => 'Erreur lors de l\'exécution de la requête.',
            'record_not_found' => 'Enregistrement non trouvé.',
            'foreign_key_violation' => 'Impossible de supprimer l\'enregistrement en raison de contraintes de clé étrangère.',
        ],

        // Erreurs de fichiers
        'file' => [
            'upload_failed' => 'Échec du téléchargement du fichier.',
            'invalid_type' => 'Type de fichier non autorisé.',
            'size_exceeded' => 'La taille du fichier dépasse la limite autorisée.',
            'not_found' => 'Fichier non trouvé.',
        ],

        // Erreurs HTTP
        'http' => [
            '400' => 'Requête invalide.',
            '401' => 'Non autorisé.',
            '403' => 'Accès interdit.',
            '404' => 'Page non trouvée.',
            '419' => 'La session a expiré.',
            '429' => 'Trop de requêtes.',
            '500' => 'Erreur interne du serveur.',
            '503' => 'Service indisponible.',
        ],
    ],

    // Pages d'erreur personnalisées
    'views' => [
        '400' => 'errors/400',
        '401' => 'errors/401',
        '403' => 'errors/403',
        '404' => 'errors/404',
        '419' => 'errors/419',
        '429' => 'errors/429',
        '500' => 'errors/500',
        '503' => 'errors/503',
    ],

    // Configuration des notifications d'erreur
    'notifications' => [
        'email' => [
            'enabled' => env('ERROR_NOTIFICATION_EMAIL', false),
            'to' => env('ERROR_NOTIFICATION_EMAIL_TO'),
            'from' => env('ERROR_NOTIFICATION_EMAIL_FROM'),
            'min_level' => 'error',
        ],
        'slack' => [
            'enabled' => env('ERROR_NOTIFICATION_SLACK', false),
            'webhook_url' => env('ERROR_NOTIFICATION_SLACK_WEBHOOK'),
            'channel' => env('ERROR_NOTIFICATION_SLACK_CHANNEL', '#errors'),
            'min_level' => 'critical',
        ],
    ],

    // Exclusions
    'ignore' => [
        // Classes d'exceptions à ne pas logger
        'dont_log' => [
            'App\Exceptions\ValidationException',
            'App\Exceptions\AuthenticationException',
        ],

        // URLs à ne pas logger
        'dont_log_urls' => [
            '/health',
            '/ping',
        ],

        // IPs à ne pas logger
        'dont_log_ips' => [
            '127.0.0.1',
        ],
    ],
];