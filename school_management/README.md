# Système de Gestion Scolaire

## Description
Un système de gestion scolaire complet permettant de gérer les étudiants, les enseignants, les classes, les notes, les présences et plus encore.

## Fonctionnalités
- Gestion des étudiants
- Gestion des enseignants
- Gestion des classes et des matières
- Suivi des présences
- Gestion des notes
- Bibliothèque
- Événements scolaires
- Notifications
- Rapports et statistiques

## Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Composer
- Node.js et NPM (pour les assets)

## Installation

1. Cloner le repository
```bash
git clone https://github.com/robert-sarah/school-manager.git
cd school_management
```

2. Installer les dépendances
```bash
composer install
npm install
```

3. Configuration de l'environnement
```bash
cp .env.example .env
# Modifier les variables d'environnement dans .env selon votre configuration
```

4. Créer la base de données
```bash
mysql -u root -p
CREATE DATABASE school_management;
EXIT;
```

5. Migrer la base de données
```bash
php migrate.php
```

6. Démarrer le serveur de développement
```bash
php -S localhost:8000 -t public
```

## Structure du Projet
```
school_management/
├── app/
│   ├── controllers/    # Contrôleurs de l'application
│   ├── core/          # Classes principales du framework
│   ├── models/        # Modèles de données
│   ├── services/      # Services métier
│   └── views/         # Vues de l'application
├── config/           # Fichiers de configuration
├── database/        # Migrations et seeds
├── public/          # Point d'entrée et assets publics
└── routes/          # Définition des routes
```

## Sécurité
- Sessions sécurisées
- Protection CSRF
- Validation des données
- Échappement des sorties
- Prévention XSS
- Authentification robuste

## Contribution
Les contributions sont les bienvenues ! Veuillez suivre ces étapes :
1. Forker le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Créer une Pull Request

## Licence
Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## Support
Pour toute question ou problème, veuillez ouvrir une issue dans le repository GitHub.