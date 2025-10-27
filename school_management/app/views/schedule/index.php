<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Schedule</h6>
                    </div>
                    <?php if (hasPermission('manage_schedule')): ?>
                        <a href="/schedule/create" class="btn btn-primary btn-sm">Add Schedule</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-5">
                                <select name="class_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= $class['id'] ?>" <?= $selectedClass == $class['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <select name="teacher_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher['id'] ?>" <?= $selectedTeacher == $teacher['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($teacher['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" onclick="printSchedule()" class="btn btn-secondary w-100">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($schedule)): ?>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <?php if (empty($selectedClass)): ?>
                                            <th>Class</th>
                                        <?php endif; ?>
                                        <?php if (empty($selectedTeacher)): ?>
                                            <th>Teacher</th>
                                        <?php endif; ?>
                                        <th>Subject</th>
                                        <th>Room</th>
                                        <?php if (hasPermission('manage_schedule')): ?>
                                            <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    foreach ($schedule as $item): 
                                    ?>
                                        <tr>
                                            <td><?= $days[$item['day_of_week'] - 1] ?></td>
                                            <td><?= date('H:i', strtotime($item['start_time'])) ?> - <?= date('H:i', strtotime($item['end_time'])) ?></td>
                                            <?php if (empty($selectedClass)): ?>
                                                <td><?= htmlspecialchars($item['class_name']) ?></td>
                                            <?php endif; ?>
                                            <?php if (empty($selectedTeacher)): ?>
                                                <td><?= htmlspecialchars($item['teacher_name']) ?></td>
                                            <?php endif; ?>
                                            <td><?= htmlspecialchars($item['subject_name']) ?></td>
                                            <td><?= htmlspecialchars($item['room_number']) ?></td>
                                            <?php if (hasPermission('manage_schedule')): ?>
                                                <td>
                                                    <a href="/schedule/edit/<?= $item['id'] ?>" class="btn btn-info btn-sm">Edit</a>
                                                    <button onclick="deleteSchedule(<?= $item['id'] ?>)" class="btn btn-danger btn-sm">Delete</button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No schedule found. Please select a class or teacher.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteSchedule(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This schedule entry will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/schedule/delete/${id}`;
        }
    });
}

function printSchedule() {
    window.print();
}

$(document).ready(function() {
    $('select').select2();
});
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
