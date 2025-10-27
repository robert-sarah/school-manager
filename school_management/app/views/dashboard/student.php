<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <h1 class="mb-4">Mon tableau de bord</h1>
        
        <div class="row">
            <!-- Statistiques de présence -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ma présence</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalDays = count($data['attendance']);
                        $presentDays = count(array_filter($data['attendance'], function($a) {
                            return $a['status'] === 'present';
                        }));
                        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;
                        ?>
                        <div class="text-center mb-4">
                            <div class="h1"><?php echo $percentage; ?>%</div>
                            <div class="text-muted">Taux de présence ce mois</div>
                        </div>
                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%">
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4"><?php echo $presentDays; ?></div>
                                <div class="text-muted">Présent</div>
                            </div>
                            <div class="col-4">
                                <div class="h4"><?php echo $totalDays - $presentDays; ?></div>
                                <div class="text-muted">Absent</div>
                            </div>
                            <div class="col-4">
                                <div class="h4"><?php echo $totalDays; ?></div>
                                <div class="text-muted">Total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes récentes -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Mes dernières notes</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Matière</th>
                                        <th>Type d'examen</th>
                                        <th>Note</th>
                                        <th>Remarques</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($data['grades'], 0, 5) as $grade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['exam_type']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['remarks'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Emploi du temps aujourd'hui -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Mon emploi du temps aujourd'hui</h6>
                    </div>
                    <div class="card-body">
                        <!-- À implémenter: Emploi du temps -->
                        <div class="text-center text-muted">
                            <i class="fas fa-calendar fa-3x mb-3"></i>
                            <p>L'emploi du temps sera bientôt disponible</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devoirs à rendre -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Devoirs à rendre</h6>
                    </div>
                    <div class="card-body">
                        <!-- À implémenter: Liste des devoirs -->
                        <div class="text-center text-muted">
                            <i class="fas fa-tasks fa-3x mb-3"></i>
                            <p>Aucun devoir en attente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
