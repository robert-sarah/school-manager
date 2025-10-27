<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Mark Grades - <?= htmlspecialchars($exam['name']) ?></h6>
                    <p class="text-sm">
                        Subject: <?= htmlspecialchars($exam['subject_name']) ?> |
                        Total Marks: <?= $exam['total_marks'] ?> |
                        Passing Marks: <?= $exam['passing_marks'] ?>
                    </p>
                </div>
                <div class="card-body">
                    <form method="POST" id="gradeForm">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Roll Number</th>
                                        <th>Marks Obtained</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['name']) ?></td>
                                            <td><?= htmlspecialchars($student['roll_number']) ?></td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control marks-input" 
                                                       name="grades[<?= $student['id'] ?>][marks]" 
                                                       min="0" 
                                                       max="<?= $exam['total_marks'] ?>" 
                                                       required>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="grades[<?= $student['id'] ?>][remarks]" 
                                                       placeholder="Optional remarks">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary">Save Grades</button>
                                <a href="/grades" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Validate marks input
    $('.marks-input').on('input', function() {
        var value = parseInt($(this).val());
        var max = parseInt($(this).attr('max'));
        
        if (value > max) {
            $(this).val(max);
        } else if (value < 0) {
            $(this).val(0);
        }
    });
    
    // Form validation
    $('#gradeForm').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Confirm Submission',
            text: 'Are you sure you want to submit these grades? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
