<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Exams & Grades</h6>
                    <?php if (hasPermission('manage_grades')): ?>
                        <a href="/grades/create" class="btn btn-primary btn-sm">Create New Exam</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="class_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Classes</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= $class['id'] ?>" <?= $selectedClass == $class['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="subject_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Subjects</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?= $subject['id'] ?>" <?= $selectedSubject == $subject['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($subject['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Total Marks</th>
                                    <th>Passing Marks</th>
                                    <th>Grades Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exams as $exam): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($exam['name']) ?></td>
                                        <td><?= htmlspecialchars($exam['subject_name']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($exam['exam_date'])) ?></td>
                                        <td><?= $exam['total_marks'] ?></td>
                                        <td><?= $exam['passing_marks'] ?></td>
                                        <td><?= $exam['grades_count'] ?></td>
                                        <td>
                                            <a href="/grades/view/<?= $exam['id'] ?>" class="btn btn-info btn-sm">View</a>
                                            <?php if (hasPermission('manage_grades') && !$exam['grades_count']): ?>
                                                <a href="/grades/mark/<?= $exam['id'] ?>" class="btn btn-primary btn-sm">Mark Grades</a>
                                            <?php endif; ?>
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
</div>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
