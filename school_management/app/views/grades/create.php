<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Create New Exam</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/grades/create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Exam Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exam_date" class="form-control-label">Exam Date</label>
                                    <input type="date" class="form-control" id="exam_date" name="exam_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_id" class="form-control-label">Class</label>
                                    <select class="form-select" id="class_id" name="class_id" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>">
                                                <?= htmlspecialchars($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subject_id" class="form-control-label">Subject</label>
                                    <select class="form-select" id="subject_id" name="subject_id" required>
                                        <option value="">Select Subject</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject['id'] ?>">
                                                <?= htmlspecialchars($subject['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_marks" class="form-control-label">Total Marks</label>
                                    <input type="number" class="form-control" id="total_marks" name="total_marks" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="passing_marks" class="form-control-label">Passing Marks</label>
                                    <input type="number" class="form-control" id="passing_marks" name="passing_marks" required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary">Create Exam</button>
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
    $('#class_id, #subject_id').select2();
    
    // Validate that passing marks is less than total marks
    $('form').on('submit', function(e) {
        var totalMarks = parseInt($('#total_marks').val());
        var passingMarks = parseInt($('#passing_marks').val());
        
        if (passingMarks > totalMarks) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Marks',
                text: 'Passing marks cannot be greater than total marks!'
            });
        }
    });
});
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
