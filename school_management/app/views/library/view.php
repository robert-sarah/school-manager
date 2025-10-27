<?php $this->layout('layout/default', ['title' => $book['title']]) ?>

<?php $this->start('content') ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/library">Bibliothèque</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($book['title']) ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Détails du livre -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="card-title h2 mb-1">
                                <?= htmlspecialchars($book['title']) ?>
                            </h1>
                            <h2 class="h5 text-muted">
                                par <?= htmlspecialchars($book['author']) ?>
                            </h2>
                        </div>
                        
                        <?php if ($this->auth->hasRole('librarian')): ?>
                        <a href="/library/edit/<?= $book['id'] ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <?php endif ?>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">ISBN</dt>
                                <dd class="col-sm-8">
                                    <?= $book['isbn'] ?: '<em>Non renseigné</em>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Catégorie</dt>
                                <dd class="col-sm-8">
                                    <?= htmlspecialchars($book['category_name']) ?>
                                </dd>
                                
                                <dt class="col-sm-4">Publication</dt>
                                <dd class="col-sm-8">
                                    <?= $book['publication_year'] ?: '<em>Non renseigné</em>' ?>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Éditeur</dt>
                                <dd class="col-sm-8">
                                    <?= $book['publisher'] ?: '<em>Non renseigné</em>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Édition</dt>
                                <dd class="col-sm-8">
                                    <?= $book['edition'] ?: '<em>Non renseigné</em>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Emplacement</dt>
                                <dd class="col-sm-8">
                                    <?= $book['shelf_location'] ?: '<em>Non renseigné</em>' ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <?php if ($book['description']): ?>
                    <div class="mb-4">
                        <h3 class="h5">Description</h3>
                        <p class="card-text">
                            <?= nl2br(htmlspecialchars($book['description'])) ?>
                        </p>
                    </div>
                    <?php endif ?>
                    
                    <!-- État des exemplaires -->
                    <div class="mb-4">
                        <h3 class="h5">État des exemplaires</h3>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2">Total</h6>
                                        <p class="h3 mb-0"><?= $book['total_copies'] ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-4">
                                <div class="card <?= $book['available_copies'] > 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2">Disponibles</h6>
                                        <p class="h3 mb-0"><?= $book['available_copies'] ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2">Empruntés</h6>
                                        <p class="h3 mb-0"><?= $book['borrowed_copies'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des exemplaires -->
            <?php if ($this->auth->hasRole('librarian')): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Exemplaires</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Statut</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($book['copies'] as $copy): ?>
                            <tr>
                                <td><?= $copy['copy_number'] ?></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'available' => 'success',
                                        'borrowed' => 'warning',
                                        'maintenance' => 'info',
                                        'lost' => 'danger'
                                    ][$copy['status']];
                                    
                                    $statusText = [
                                        'available' => 'Disponible',
                                        'borrowed' => 'Emprunté',
                                        'maintenance' => 'En maintenance',
                                        'lost' => 'Perdu'
                                    ][$copy['status']];
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td><?= $copy['notes'] ?: '-' ?></td>
                                <td>
                                    <?php if ($copy['status'] === 'available'): ?>
                                    <button type="button" class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#loanModal"
                                            data-copy-id="<?= $copy['id'] ?>">
                                        Prêter
                                    </button>
                                    <?php endif ?>
                                </td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif ?>
            
            <!-- Historique des emprunts -->
            <?php if ($this->auth->hasRole('librarian') && !empty($book['loans'])): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="h5 mb-0">Historique des emprunts</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Exemplaire</th>
                                <th>Emprunteur</th>
                                <th>Date d'emprunt</th>
                                <th>Date de retour prévue</th>
                                <th>Date de retour effective</th>
                                <th>État</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($book['loans'] as $loan): ?>
                            <tr>
                                <td><?= $loan['copy_id'] ?></td>
                                <td><?= htmlspecialchars($loan['borrower_name']) ?></td>
                                <td><?= (new DateTime($loan['loan_date']))->format('d/m/Y') ?></td>
                                <td><?= (new DateTime($loan['due_date']))->format('d/m/Y') ?></td>
                                <td>
                                    <?php if ($loan['return_date']): ?>
                                        <?= (new DateTime($loan['return_date']))->format('d/m/Y') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif ?>
                                </td>
                                <td>
                                    <?php
                                    $isOverdue = !$loan['return_date'] && 
                                                new DateTime($loan['due_date']) < new DateTime();
                                    
                                    if ($loan['return_date']): ?>
                                        <span class="badge bg-success">Retourné</span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge bg-danger">En retard</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">En cours</span>
                                    <?php endif ?>
                                </td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif ?>
        </div>
        
        <!-- Actions -->
        <div class="col-md-4">
            <?php if ($book['available_copies'] > 0): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">Emprunter ce livre</h3>
                    
                    <?php if ($this->auth->isLoggedIn()): ?>
                    <button type="button" class="btn btn-primary w-100"
                            data-bs-toggle="modal" 
                            data-bs-target="#loanModal">
                        <i class="fas fa-book"></i> Emprunter
                    </button>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        Vous devez vous connecter pour emprunter un livre.
                    </div>
                    <?php endif ?>
                </div>
            </div>
            <?php endif ?>
            
            <?php if ($this->auth->hasRole('librarian')): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="h5 mb-0">Actions administrateur</h3>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/library/edit/<?= $book['id'] ?>" 
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-edit"></i> Modifier le livre
                    </a>
                    
                    <a href="#" class="list-group-item list-group-item-action"
                       data-bs-toggle="modal" 
                       data-bs-target="#addCopiesModal">
                        <i class="fas fa-plus"></i> Ajouter des exemplaires
                    </a>
                    
                    <?php if ($book['available_copies'] === $book['total_copies']): ?>
                    <button type="button" 
                            class="list-group-item list-group-item-action text-danger"
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> Supprimer le livre
                    </button>
                    <?php endif ?>
                </div>
            </div>
            <?php endif ?>
        </div>
    </div>
</div>

<!-- Modal d'emprunt -->
<div class="modal fade" id="loanModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/library/loan" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Emprunter un livre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <input type="hidden" name="copy_id" id="copyId">
                
                <?php if ($this->auth->hasRole('librarian')): ?>
                <div class="mb-3">
                    <label for="borrower_id" class="form-label">Emprunteur</label>
                    <select class="form-select" name="borrower_id" required>
                        <option value="">Sélectionner un utilisateur</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['name']) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <?php else: ?>
                <input type="hidden" name="borrower_id" 
                       value="<?= $this->auth->getUserId() ?>">
                <?php endif ?>
                
                <div class="mb-3">
                    <label for="loan_date" class="form-label">Date d'emprunt</label>
                    <input type="date" class="form-control" name="loan_date" 
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="due_date" class="form-label">Date de retour prévue</label>
                    <input type="date" class="form-control" name="due_date" 
                           value="<?= date('Y-m-d', strtotime('+14 days')) ?>"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirmer l'emprunt
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($this->auth->hasRole('librarian')): ?>
<!-- Modal d'ajout d'exemplaires -->
<div class="modal fade" id="addCopiesModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/library/copies/add" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter des exemplaires</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                
                <div class="mb-3">
                    <label for="quantity" class="form-label">
                        Nombre d'exemplaires à ajouter
                    </label>
                    <input type="number" class="form-control" name="quantity" 
                           min="1" value="1" required>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/library/delete/<?= $book['id'] ?>" 
              class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce livre ?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Cette action est irréversible !
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif ?>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du modal d'emprunt
    const loanModal = document.getElementById('loanModal');
    if (loanModal) {
        loanModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const copyId = button.dataset.copyId;
            
            if (copyId) {
                document.getElementById('copyId').value = copyId;
            }
        });
    }
});
</script>
<?php $this->stop() ?>
