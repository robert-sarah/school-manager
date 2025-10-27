<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matières de la classe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/classes">Classes</a></li>
                <li class="breadcrumb-item active">Matières</li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Assigner une matière</h5>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Matière</label>
                                <select class="form-select select2" id="subject_id" name="subject_id" required>
                                    <option value="">Sélectionner une matière</option>
                                    <?php foreach ($allSubjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>">
                                        <?php echo htmlspecialchars($subject['name']); ?> 
                                        (<?php echo htmlspecialchars($subject['code']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="teacher_id" class="form-label">Enseignant</label>
                                <select class="form-select select2" id="teacher_id" name="teacher_id" required>
                                    <option value="">Sélectionner un enseignant</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>">
                                        <?php echo htmlspecialchars($teacher['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Assigner la matière
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Matières assignées</h5>
                        
                        <?php if (empty($subjects)): ?>
                            <div class="text-center text-muted my-4">
                                <i class="fas fa-book fa-3x mb-3"></i>
                                <p>Aucune matière n'a été assignée à cette classe</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Matière</th>
                                            <th>Enseignants</th>
                                            <th>Programme</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($subject['code']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($subject['teachers']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (isset($subject['syllabus_path'])): ?>
                                                    <a href="<?php echo $subject['syllabus_path']; ?>" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       target="_blank">
                                                        <i class="fas fa-download"></i> Télécharger
                                                    </a>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-secondary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadSyllabus" 
                                                            data-subject-id="<?php echo $subject['id']; ?>">
                                                        <i class="fas fa-upload"></i> Ajouter
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/subjects/<?php echo $subject['id']; ?>/teachers" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-chalkboard-teacher"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm delete-subject" 
                                                            data-id="<?php echo $subject['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($subject['name']); ?>">
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
    
    <!-- Modal pour télécharger le programme -->
    <div class="modal fade" id="uploadSyllabus" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un programme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" action="<?php echo "/classes/{$id}/syllabus"; ?>">
                    <div class="modal-body">
                        <input type="hidden" name="subject_id" id="modalSubjectId">
                        
                        <div class="mb-3">
                            <label for="syllabus" class="form-label">Fichier du programme</label>
                            <input type="file" class="form-control" id="syllabus" name="syllabus" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Télécharger</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialiser Select2
            $('.select2').select2();
            
            // Gestion du modal de téléchargement du programme
            $('#uploadSyllabus').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var subjectId = button.data('subject-id');
                $('#modalSubjectId').val(subjectId);
            });
            
            // Gestion de la suppression des matières
            $('.delete-subject').click(function() {
                const subjectId = $(this).data('id');
                const subjectName = $(this).data('name');
                
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: `Voulez-vous vraiment retirer la matière "${subjectName}" de cette classe ?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, retirer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/classes/<?php echo $id; ?>/subjects/${subjectId}/delete`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>
