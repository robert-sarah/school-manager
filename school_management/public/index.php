<?php

// Charger l'application principale
require_once __DIR__ . '/../app/Application.php';

// Obtenir l'instance de l'application (singleton)
$app = \App\Application::getInstance();

// Exécuter l'application
$app->run();

?>
