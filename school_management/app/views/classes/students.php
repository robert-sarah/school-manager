<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudiants de la classe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/classes">Classes</a></li>
                <li class="breadcrumb-item active">Étudiants</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Liste des étudiants</h2>
            
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sectionFilter" data-bs-toggle="dropdown">
                        <?php 
                        $currentSection = 'Toutes les sections';
                        if (isset($_GET['section'])) {
                            foreach ($sections as $section) {
                                if ($section['id'] == $_GET['section']) {
                                    $currentSection = $section['name'];
                                    break;
                                }
                            }
                        }
                        echo $currentSection;
                        ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item <?php echo !isset($_GET['section']) ? 'active' : ''; ?>" 
                               href="/classes/<?php echo $id; ?>/students">
                                Toutes les sections
                            </a>
                        </li>
                        <?php foreach ($sections as $section): ?>
                        <li>
                            <a class="dropdown-item <?php echo isset($_GET['section']) && $_GET['section'] == $section['id'] ? 'active' : ''; ?>" 
                               href="/classes/<?php echo $id; ?>/students?section=<?php echo $section['id']; ?>">
                                <?php echo htmlspecialchars($section['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <a href="/students/create?class=<?php echo $id; ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Nouvel étudiant
                </a>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center text-muted my-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Aucun étudiant trouvé dans cette classe</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Section</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
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
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($student['section_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/students/<?php echo $student['id']; ?>/attendance" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-calendar-check"></i> Présence
                                            </a>
                                            <a href="/students/<?php echo $student['id']; ?>/grades" 
                                               class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-star"></i> Notes
                                            </a>
                                            <a href="/students/<?php echo $student['id']; ?>/edit" 
                                               class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
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
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
