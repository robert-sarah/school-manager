<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <h1 class="mb-4">Tableau de bord</h1>
        
        <div class="row">
            <!-- Carte des étudiants -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Étudiants</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['totalStudents']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carte des enseignants -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Enseignants</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['totalTeachers']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carte des matières -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Matières</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['totalSubjects']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-book fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Actions rapides -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="/students/create" class="btn btn-primary btn-block">
                                    <i class="fas fa-user-plus"></i> Nouvel étudiant
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/teachers/create" class="btn btn-success btn-block">
                                    <i class="fas fa-user-tie"></i> Nouvel enseignant
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/subjects/create" class="btn btn-info btn-block">
                                    <i class="fas fa-book-medical"></i> Nouvelle matière
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/classes/create" class="btn btn-warning btn-block">
                                    <i class="fas fa-school"></i> Nouvelle classe
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications récentes -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Notifications récentes</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <!-- À implémenter: Liste des notifications -->
                            <div class="text-center text-muted">
                                <i class="fas fa-bell fa-3x mb-3"></i>
                                <p>Aucune notification pour le moment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
