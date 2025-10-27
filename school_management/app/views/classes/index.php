<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des classes</h1>
            <a href="/classes/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle classe
            </a>
        </div>
        
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['flash']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <div class="row">
            <?php foreach ($classes as $class): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($class['name']); ?></h5>
                        <?php if ($class['description']): ?>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($class['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-users me-2 text-primary"></i>
                                    <div>
                                        <small class="text-muted">Étudiants</small>
                                        <div class="fw-bold"><?php echo $class['students_count']; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-door-open me-2 text-success"></i>
                                    <div>
                                        <small class="text-muted">Sections</small>
                                        <div class="fw-bold"><?php echo $class['sections_count']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <div class="btn-group">
                                <a href="/classes/<?php echo $class['id']; ?>/sections" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-door-open"></i> Sections
                                </a>
                                <a href="/classes/<?php echo $class['id']; ?>/students" 
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-users"></i> Étudiants
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="/classes/<?php echo $class['id']; ?>/subjects" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-book"></i> Matières
                                </a>
                                <a href="/classes/<?php echo $class['id']; ?>/syllabus" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-file-alt"></i> Programme
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
