<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marquer les présences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/attendance">Présences</a></li>
                <li class="breadcrumb-item active">Marquer les présences</li>
            </ol>
        </nav>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="attendanceForm" class="mb-4">
                    <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
                    <input type="hidden" name="subject_id" value="<?php echo $subjectId; ?>">
                    
                    <div class="row align-items-end mb-4">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?php echo $date; ?>" required>
                        </div>
                        <div class="col-md-8">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success mark-all" data-status="present">
                                    <i class="fas fa-check"></i> Tous présents
                                </button>
                                <button type="button" class="btn btn-danger mark-all" data-status="absent">
                                    <i class="fas fa-times"></i> Tous absents
                                </button>
                                <button type="button" class="btn btn-warning mark-all" data-status="late">
                                    <i class="fas fa-clock"></i> Tous en retard
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Status</th>
                                    <th>Remarques</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <?php 
                                    $existingStatus = '';
                                    foreach ($existingAttendance as $att) {
                                        if ($att['student_id'] == $student['id']) {
                                            $existingStatus = $att['status'];
                                            break;
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $student['avatar'] ?? '/assets/img/default-avatar.png'; ?>" 
                                                 class="rounded-circle me-2" 
                                                 width="32" 
                                                 height="32"
                                                 alt="Avatar">
                                            <?php echo htmlspecialchars($student['name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="status_<?php echo $student['id']; ?>" 
                                                   id="present_<?php echo $student['id']; ?>" 
                                                   value="present"
                                                   <?php echo $existingStatus === 'present' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-success" 
                                                   for="present_<?php echo $student['id']; ?>">
                                                <i class="fas fa-check"></i> Présent
                                            </label>
                                            
                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="status_<?php echo $student['id']; ?>" 
                                                   id="absent_<?php echo $student['id']; ?>" 
                                                   value="absent"
                                                   <?php echo $existingStatus === 'absent' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-danger" 
                                                   for="absent_<?php echo $student['id']; ?>">
                                                <i class="fas fa-times"></i> Absent
                                            </label>
                                            
                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="status_<?php echo $student['id']; ?>" 
                                                   id="late_<?php echo $student['id']; ?>" 
                                                   value="late"
                                                   <?php echo $existingStatus === 'late' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-warning" 
                                                   for="late_<?php echo $student['id']; ?>">
                                                <i class="fas fa-clock"></i> Retard
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control" 
                                               name="remarks_<?php echo $student['id']; ?>" 
                                               placeholder="Remarques optionnelles">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les présences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Marquer tous les étudiants avec le même statut
            $('.mark-all').click(function() {
                const status = $(this).data('status');
                $(`input[value="${status}"]`).prop('checked', true);
            });
            
            // Soumission du formulaire
            $('#attendanceForm').submit(function(e) {
                e.preventDefault();
                
                const students = [];
                <?php foreach ($students as $student): ?>
                students.push({
                    id: <?php echo $student['id']; ?>,
                    status: $(`input[name="status_<?php echo $student['id']; ?>"]:checked`).val(),
                    remarks: $(`input[name="remarks_<?php echo $student['id']; ?>"]`).val()
                });
                <?php endforeach; ?>
                
                $.ajax({
                    url: '/attendance/mark',
                    method: 'POST',
                    data: {
                        class_id: $('input[name="class_id"]').val(),
                        subject_id: $('input[name="subject_id"]').val(),
                        date: $('#date').val(),
                        students: JSON.stringify(students)
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Présences enregistrées',
                            text: 'Les présences ont été enregistrées avec succès.'
                        }).then(() => {
                            window.location.href = '/attendance';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: xhr.responseJSON?.error || 'Une erreur est survenue lors de l\'enregistrement des présences.'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
