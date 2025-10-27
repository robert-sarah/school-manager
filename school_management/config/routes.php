<?php
return [
    // Routes publiques
    '' => ['HomeController', 'index'],
    'login' => ['AuthController', 'login'],
    'logout' => ['AuthController', 'logout'],
    
    // Routes pour l'administration
    'dashboard' => ['DashboardController', 'index'],
    
    // Routes pour les étudiants
    'students' => ['StudentController', 'index'],
    'students/create' => ['StudentController', 'create'],
    'students/{id}/edit' => ['StudentController', 'edit'],
    'students/{id}/attendance' => ['StudentController', 'attendance'],
    'students/{id}/grades' => ['StudentController', 'grades'],
    
    // Routes pour les enseignants
    'teachers' => ['TeacherController', 'index'],
    'teachers/create' => ['TeacherController', 'create'],
    'teachers/{id}/edit' => ['TeacherController', 'edit'],
    'teachers/{id}/subjects' => ['TeacherController', 'subjects'],
    'teachers/attendance' => ['TeacherController', 'attendance'],
    'teachers/grades' => ['TeacherController', 'grades'],
    
    // Routes pour les matières
    'subjects' => ['SubjectController', 'index'],
    'subjects/create' => ['SubjectController', 'create'],
    'subjects/{id}/edit' => ['SubjectController', 'edit'],
    'subjects/{id}/delete' => ['SubjectController', 'delete'],
    
    // Routes pour les classes
    'classes' => ['ClassController', 'index'],
    'classes/create' => ['ClassController', 'create'],
    'classes/{id}/edit' => ['ClassController', 'edit'],
    'classes/{id}/sections' => ['ClassController', 'sections'],
    
    // Routes pour le profil
    'profile' => ['ProfileController', 'index'],
    'profile/edit' => ['ProfileController', 'edit'],
    'profile/password' => ['ProfileController', 'password'],
    
    // Routes pour les notes
    'grades' => ['GradeController', 'index'],
    'grades/create' => ['GradeController', 'create'],
    'grades/{id}/edit' => ['GradeController', 'edit'],
    
    // Routes pour les présences
    'attendance' => ['AttendanceController', 'index'],
    'attendance/create' => ['AttendanceController', 'create'],
    'attendance/report' => ['AttendanceController', 'report'],
    
    // Routes pour les événements
    'events' => ['EventController', 'index'],
    'events/create' => ['EventController', 'create'],
    'events/{id}/edit' => ['EventController', 'edit'],
    'events/{id}/delete' => ['EventController', 'delete'],
    
    // Routes pour les notifications
    'notifications' => ['NotificationController', 'index'],
    'notifications/mark-read' => ['NotificationController', 'markRead'],
];
?>
