<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Exam Results - <?= htmlspecialchars($exam['name']) ?></h6>
                            <p class="text-sm mb-0">
                                Subject: <?= htmlspecialchars($exam['subject_name']) ?> |
                                Date: <?= date('Y-m-d', strtotime($exam['exam_date'])) ?>
                            </p>
                        </div>
                        <div>
                            <button onclick="printResults()" class="btn btn-secondary btn-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Marks Obtained</th>
                                    <th>Percentage</th>
                                    <th>Result</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                    <?php 
                                        $percentage = ($grade['marks_obtained'] / $exam['total_marks']) * 100;
                                        $isPassed = $grade['marks_obtained'] >= $exam['passing_marks'];
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="/grades/student-report/<?= $grade['student_id'] ?>">
                                                <?= htmlspecialchars($grade['student_name']) ?>
                                            </a>
                                        </td>
                                        <td><?= $grade['marks_obtained'] ?> / <?= $exam['total_marks'] ?></td>
                                        <td><?= number_format($percentage, 2) ?>%</td>
                                        <td>
                                            <span class="badge bg-<?= $isPassed ? 'success' : 'danger' ?>">
                                                <?= $isPassed ? 'Pass' : 'Fail' ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($grade['remarks'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="font-weight-bolder mb-0">Class Statistics</h5>
                                        </div>
                                    </div>
                                    <?php
                                        $totalStudents = count($grades);
                                        $passedStudents = count(array_filter($grades, function($g) use ($exam) {
                                            return $g['marks_obtained'] >= $exam['passing_marks'];
                                        }));
                                        $passPercentage = ($totalStudents > 0) ? ($passedStudents / $totalStudents) * 100 : 0;
                                        
                                        $marks = array_column($grades, 'marks_obtained');
                                        $avgMarks = $totalStudents > 0 ? array_sum($marks) / $totalStudents : 0;
                                        $highestMarks = $totalStudents > 0 ? max($marks) : 0;
                                        $lowestMarks = $totalStudents > 0 ? min($marks) : 0;
                                    ?>
                                    <p class="mb-1">Pass Percentage: <?= number_format($passPercentage, 2) ?>%</p>
                                    <p class="mb-1">Average Marks: <?= number_format($avgMarks, 2) ?></p>
                                    <p class="mb-1">Highest Marks: <?= $highestMarks ?></p>
                                    <p class="mb-0">Lowest Marks: <?= $lowestMarks ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printResults() {
    window.print();
}
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
