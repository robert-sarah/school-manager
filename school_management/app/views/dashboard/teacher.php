<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Enseignant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <h1 class="mb-4">Mon tableau de bord</h1>
        
        <!-- Matières enseignées -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Mes matières</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Matière</th>
                                        <th>Classe</th>
                                        <th>Section</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['subjects'] as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['section_name']); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="/attendance/create?subject=<?php echo $subject['id']; ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-clipboard-check"></i> Présence
                                                </a>
                                                <a href="/grades/create?subject=<?php echo $subject['id']; ?>" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="fas fa-star"></i> Notes
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Actions rapides -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="/attendance" class="btn btn-primary btn-block">
                                    <i class="fas fa-clipboard-list"></i> Gestion des présences
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/grades" class="btn btn-success btn-block">
                                    <i class="fas fa-graduation-cap"></i> Gestion des notes
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/assignments" class="btn btn-info btn-block">
                                    <i class="fas fa-tasks"></i> Devoirs
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/messages" class="btn btn-warning btn-block">
                                    <i class="fas fa-envelope"></i> Messages
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
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
