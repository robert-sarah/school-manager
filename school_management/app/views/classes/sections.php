<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des sections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/classes">Classes</a></li>
                <li class="breadcrumb-item active">Sections</li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Ajouter une section</h5>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom de la section</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="room_number" class="form-label">Numéro de salle</label>
                                <input type="text" class="form-control" id="room_number" name="room_number" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Ajouter la section
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Sections existantes</h5>
                        
                        <?php if (empty($sections)): ?>
                            <div class="text-center text-muted my-4">
                                <i class="fas fa-door-open fa-3x mb-3"></i>
                                <p>Aucune section n'a été créée pour cette classe</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Salle</th>
                                            <th>Étudiants</th>
                                            <th>Enseignants</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sections as $section): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($section['name']); ?></td>
                                            <td><?php echo htmlspecialchars($section['room_number']); ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo $section['students_count']; ?> étudiants
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($section['teachers'] ?? 'Aucun enseignant'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/sections/<?php echo $section['id']; ?>/students" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                    <a href="/sections/<?php echo $section['id']; ?>/edit" 
                                                       class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm delete-section" 
                                                            data-id="<?php echo $section['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($section['name']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        document.querySelectorAll('.delete-section').forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.dataset.id;
                const sectionName = this.dataset.name;
                
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: `Voulez-vous vraiment supprimer la section "${sectionName}" ?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/sections/${sectionId}/delete`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>
