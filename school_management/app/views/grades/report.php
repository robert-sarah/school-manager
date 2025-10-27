<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Academic Report - <?= htmlspecialchars($student['name']) ?></h6>
                            <p class="text-sm mb-0">
                                Class: <?= htmlspecialchars($student['class_name']) ?> |
                                Roll Number: <?= htmlspecialchars($student['roll_number']) ?>
                            </p>
                        </div>
                        <div>
                            <button onclick="printReport()" class="btn btn-secondary btn-sm">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Semester Performance Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-sm">Semester Performance Summary</h6>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Average Score</th>
                                            <th>Highest Score</th>
                                            <th>Lowest Score</th>
                                            <th>Number of Exams</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report as $subject): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                                <td><?= number_format($subject['average_marks'], 2) ?></td>
                                                <td><?= $subject['highest_marks'] ?></td>
                                                <td><?= $subject['lowest_marks'] ?></td>
                                                <td><?= $subject['exams_count'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Exam Results -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-uppercase text-sm">Detailed Exam Results</h6>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Exam</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Marks</th>
                                            <th>Percentage</th>
                                            <th>Result</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grades as $grade): ?>
                                            <?php 
                                                $percentage = ($grade['marks_obtained'] / $grade['total_marks']) * 100;
                                                $isPassed = $grade['marks_obtained'] >= $grade['passing_marks'];
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($grade['exam_name']) ?></td>
                                                <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                                                <td><?= date('Y-m-d', strtotime($grade['exam_date'])) ?></td>
                                                <td><?= $grade['marks_obtained'] ?> / <?= $grade['total_marks'] ?></td>
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
                        </div>
                    </div>

                    <!-- Performance Graph -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-sm">Performance Trend</h6>
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printReport() {
    window.print();
}

// Initialize performance chart
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Prepare data for chart
    var examData = <?= json_encode(array_map(function($grade) {
        return [
            'date' => $grade['exam_date'],
            'name' => $grade['exam_name'],
            'subject' => $grade['subject_name'],
            'percentage' => ($grade['marks_obtained'] / $grade['total_marks']) * 100
        ];
    }, $grades)) ?>;
    
    // Group by subject
    var subjects = {};
    examData.forEach(function(exam) {
        if (!subjects[exam.subject]) {
            subjects[exam.subject] = {
                label: exam.subject,
                data: [],
                borderColor: getRandomColor(),
                fill: false
            };
        }
        subjects[exam.subject].data.push({
            x: new Date(exam.date),
            y: exam.percentage
        });
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            datasets: Object.values(subjects)
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day'
                    },
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentage'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return examData[context[0].dataIndex].name;
                        }
                    }
                }
            }
        }
    });
});

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
