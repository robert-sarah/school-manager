<?php $this->layout('layout/default', ['title' => 'Tableau de bord']) ?>

<?php $this->start('content') ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="/dashboard">
                            <i class="fas fa-home"></i> Tableau de bord
                        </a>
                    </li>
                    
                    <?php if ($this->security->checkPermission('users.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/users">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('classes.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/classes">
                            <i class="fas fa-chalkboard"></i> Classes
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('courses.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/courses">
                            <i class="fas fa-book"></i> Cours
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('attendance.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/attendance">
                            <i class="fas fa-calendar-check"></i> Présences
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('grades.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/grades">
                            <i class="fas fa-star"></i> Notes
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('schedule.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/schedule">
                            <i class="fas fa-calendar-alt"></i> Emploi du temps
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('library.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/library">
                            <i class="fas fa-book-reader"></i> Bibliothèque
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('events.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/events">
                            <i class="fas fa-calendar-day"></i> Événements
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('payments.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/payments">
                            <i class="fas fa-money-bill"></i> Paiements
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('reports.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/reports">
                            <i class="fas fa-chart-bar"></i> Rapports
                        </a>
                    </li>
                    <?php endif ?>
                    
                    <?php if ($this->security->checkPermission('settings.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/settings">
                            <i class="fas fa-cogs"></i> Paramètres
                        </a>
                    </li>
                    <?php endif ?>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Tableau de bord</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <i class="fas fa-calendar"></i> Cette semaine
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row row-cols-1 row-cols-md-4 g-4 mb-4">
                <div class="col">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Étudiants</h6>
                                    <h2 class="my-2"><?= $stats['students'] ?></h2>
                                    <p class="card-text mb-0">
                                        <small>+<?= $stats['new_students'] ?> cette semaine</small>
                                    </p>
                                </div>
                                <i class="fas fa-user-graduate fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Classes</h6>
                                    <h2 class="my-2"><?= $stats['classes'] ?></h2>
                                    <p class="card-text mb-0">
                                        <small><?= $stats['active_classes'] ?> actives</small>
                                    </p>
                                </div>
                                <i class="fas fa-chalkboard-teacher fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Enseignants</h6>
                                    <h2 class="my-2"><?= $stats['teachers'] ?></h2>
                                    <p class="card-text mb-0">
                                        <small><?= $stats['subjects'] ?> matières</small>
                                    </p>
                                </div>
                                <i class="fas fa-chalkboard fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Revenus</h6>
                                    <h2 class="my-2"><?= number_format($stats['revenue'], 2) ?> €</h2>
                                    <p class="card-text mb-0">
                                        <small>Ce mois</small>
                                    </p>
                                </div>
                                <i class="fas fa-euro-sign fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Recent Activities -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Activités récentes</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php foreach ($activities as $activity): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">
                                            <?= htmlspecialchars($activity['title']) ?>
                                        </h6>
                                        <p class="timeline-text">
                                            <?= htmlspecialchars($activity['description']) ?>
                                        </p>
                                        <small class="text-muted">
                                            <?= $activity['created_at'] ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Événements à venir</h5>
                            <a href="/events" class="btn btn-sm btn-primary">
                                <i class="fas fa-calendar-plus"></i> Ajouter
                            </a>
                        </div>
                        <div class="card-body">
                            <?php foreach ($events as $event): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded p-2 me-3 text-center" style="width: 60px">
                                    <small class="text-muted d-block">
                                        <?= date('M', strtotime($event['start_date'])) ?>
                                    </small>
                                    <strong>
                                        <?= date('d', strtotime($event['start_date'])) ?>
                                    </strong>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?= $event['location'] ?>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Quick Actions -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Actions rapides</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php if ($this->security->checkPermission('students.add')): ?>
                                <div class="col-6">
                                    <a href="/students/add" class="btn btn-light w-100 text-start">
                                        <i class="fas fa-user-plus text-primary"></i>
                                        Nouvel étudiant
                                    </a>
                                </div>
                                <?php endif ?>

                                <?php if ($this->security->checkPermission('attendance.mark')): ?>
                                <div class="col-6">
                                    <a href="/attendance/mark" class="btn btn-light w-100 text-start">
                                        <i class="fas fa-check-square text-success"></i>
                                        Marquer présences
                                    </a>
                                </div>
                                <?php endif ?>

                                <?php if ($this->security->checkPermission('grades.add')): ?>
                                <div class="col-6">
                                    <a href="/grades/add" class="btn btn-light w-100 text-start">
                                        <i class="fas fa-star text-warning"></i>
                                        Saisir notes
                                    </a>
                                </div>
                                <?php endif ?>

                                <?php if ($this->security->checkPermission('events.add')): ?>
                                <div class="col-6">
                                    <a href="/events/add" class="btn btn-light w-100 text-start">
                                        <i class="fas fa-calendar-plus text-info"></i>
                                        Nouvel événement
                                    </a>
                                </div>
                                <?php endif ?>

                                <?php if ($this->security->checkPermission('payments.add')): ?>
                                <div class="col-6">
                                    <a href="/payments/add" class="btn btn-light w-100 text-start">
                                        <i class="fas fa-money-bill text-danger"></i>
                                        Nouveau paiement
                                    </a>
                                </div>
                                <?php endif ?>

                                <?php if ($this->security->checkPermission('reports.generate')): ?>
                                <div class="col-6">
                                    <a href="/reports" class="btn btn-light w-100 text-start">
                                        <i class="fas fa-chart-line text-secondary"></i>
                                        Générer rapport
                                    </a>
                                </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">État du système</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6>Espace disque</h6>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?= $system['disk_usage'] ?>%"
                                         aria-valuenow="<?= $system['disk_usage'] ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $system['disk_usage'] ?>%
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6>Sessions actives</h6>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $system['active_sessions'] ?>%"
                                         aria-valuenow="<?= $system['active_sessions'] ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $system['active_sessions'] ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6>Cache</h6>
                                <div class="progress">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?= $system['cache_usage'] ?>%"
                                         aria-valuenow="<?= $system['cache_usage'] ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $system['cache_usage'] ?>%
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h6>Version du système</h6>
                                <p class="mb-0">
                                    <span class="badge bg-primary"><?= $system['version'] ?></span>
                                    <?php if ($system['update_available']): ?>
                                    <span class="badge bg-warning">Mise à jour disponible</span>
                                    <?php endif ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php $this->stop() ?>

<?php $this->start('styles') ?>
<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
}

.sidebar .nav-link.active {
    color: #2470dc;
}

.sidebar .nav-link:hover {
    color: #2470dc;
}

.sidebar .nav-link i {
    margin-right: 10px;
}

.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #2470dc;
}

.timeline-marker::before {
    content: '';
    position: absolute;
    left: 50%;
    bottom: 0;
    width: 2px;
    height: calc(100% + 20px);
    background: #e9ecef;
    transform: translateX(-50%);
    z-index: -1;
}

.timeline-item:last-child .timeline-marker::before {
    display: none;
}
</style>
<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les graphiques si nécessaire
});
</script>
<?php $this->stop() ?>
