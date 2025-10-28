<?php
return [
    // Rôles par défaut
    'roles' => [
        'admin' => [
            'description' => 'Administrateur système',
            'permissions' => '*' // Toutes les permissions
        ],
        'teacher' => [
            'description' => 'Enseignant',
            'permissions' => [
                'classes.view',
                'classes.manage',
                'students.view',
                'grades.manage',
                'attendance.manage',
                'events.view',
                'events.create',
                'library.access',
                'messages.send',
                'notifications.manage'
            ]
        ],
        'student' => [
            'description' => 'Étudiant',
            'permissions' => [
                'profile.view',
                'profile.edit',
                'grades.view',
                'attendance.view',
                'schedule.view',
                'library.browse',
                'events.view',
                'messages.send'
            ]
        ],
        'parent' => [
            'description' => 'Parent',
            'permissions' => [
                'student.view',
                'grades.view',
                'attendance.view',
                'messages.send',
                'events.view',
                'payments.view'
            ]
        ],
        'librarian' => [
            'description' => 'Bibliothécaire',
            'permissions' => [
                'library.*',
                'students.view',
                'messages.send'
            ]
        ]
    ],

    // Permissions détaillées
    'permissions' => [
        // Gestion des classes
        'classes.view' => 'Voir les classes',
        'classes.create' => 'Créer des classes',
        'classes.edit' => 'Modifier les classes',
        'classes.delete' => 'Supprimer des classes',
        'classes.manage' => 'Gérer les classes',

        // Gestion des étudiants
        'students.view' => 'Voir les étudiants',
        'students.create' => 'Ajouter des étudiants',
        'students.edit' => 'Modifier les étudiants',
        'students.delete' => 'Supprimer des étudiants',

        // Gestion des notes
        'grades.view' => 'Voir les notes',
        'grades.create' => 'Ajouter des notes',
        'grades.edit' => 'Modifier les notes',
        'grades.manage' => 'Gérer les notes',

        // Gestion des présences
        'attendance.view' => 'Voir les présences',
        'attendance.mark' => 'Marquer les présences',
        'attendance.manage' => 'Gérer les présences',

        // Bibliothèque
        'library.access' => 'Accéder à la bibliothèque',
        'library.browse' => 'Parcourir la bibliothèque',
        'library.manage' => 'Gérer la bibliothèque',
        'library.add' => 'Ajouter des livres',
        'library.edit' => 'Modifier les livres',
        'library.delete' => 'Supprimer des livres',

        // Événements
        'events.view' => 'Voir les événements',
        'events.create' => 'Créer des événements',
        'events.edit' => 'Modifier les événements',
        'events.delete' => 'Supprimer des événements',

        // Messages et notifications
        'messages.send' => 'Envoyer des messages',
        'notifications.manage' => 'Gérer les notifications',

        // Paiements
        'payments.view' => 'Voir les paiements',
        'payments.create' => 'Créer des paiements',
        'payments.manage' => 'Gérer les paiements',

        // Rapports
        'reports.view' => 'Voir les rapports',
        'reports.generate' => 'Générer des rapports',

        // Administration système
        'system.settings' => 'Gérer les paramètres système',
        'system.users' => 'Gérer les utilisateurs',
        'system.roles' => 'Gérer les rôles et permissions',
        'system.backup' => 'Gérer les sauvegardes'
    ],

    // Routes protégées par rôle
    'protected_routes' => [
        'admin/*' => ['admin'],
        'teacher/*' => ['admin', 'teacher'],
        'student/*' => ['admin', 'teacher', 'student'],
        'parent/*' => ['admin', 'parent'],
        'library/manage/*' => ['admin', 'librarian']
    ]
];