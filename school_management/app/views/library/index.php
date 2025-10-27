<?php $this->layout('layout/default', ['title' => 'Bibliothèque']) ?>

<?php $this->start('content') ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bibliothèque</h1>
        
        <?php if ($this->auth->hasRole('librarian')): ?>
        <a href="/library/add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un livre
        </a>
        <?php endif ?>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/library" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Rechercher par titre, auteur ou ISBN"
                               value="<?= htmlspecialchars($filters['search']) ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" 
                                <?= $filters['category_id'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="checkbox" name="available" class="form-check-input" 
                               id="available" <?= $filters['available'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="available">
                            Disponibles uniquement
                        </label>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <a href="/library" class="btn btn-outline-secondary w-100">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Liste des livres -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($books as $book): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="/library/view/<?= $book['id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($book['title']) ?>
                        </a>
                    </h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        par <?= htmlspecialchars($book['author']) ?>
                    </h6>
                    
                    <p class="card-text">
                        <small class="text-muted">
                            <?= htmlspecialchars($book['category_name']) ?>
                            &bull; <?= $book['publication_year'] ?>
                        </small>
                    </p>
                    
                    <p class="card-text">
                        <?php if ($book['available_copies'] > 0): ?>
                        <span class="badge bg-success">
                            <?= $book['available_copies'] ?> exemplaire(s) disponible(s)
                        </span>
                        <?php else: ?>
                        <span class="badge bg-danger">
                            Aucun exemplaire disponible
                        </span>
                        <?php endif ?>
                    </p>
                    
                    <div class="mt-3">
                        <a href="/library/view/<?= $book['id'] ?>" 
                           class="btn btn-outline-primary btn-sm">
                            Détails
                        </a>
                        
                        <?php if ($this->auth->hasRole('librarian')): ?>
                        <a href="/library/edit/<?= $book['id'] ?>" 
                           class="btn btn-outline-secondary btn-sm">
                            Modifier
                        </a>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach ?>
    </div>
    
    <?php if (empty($books)): ?>
    <div class="alert alert-info mt-4">
        Aucun livre ne correspond à vos critères de recherche.
    </div>
    <?php endif ?>
</div>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Soumission automatique du formulaire lors du changement de filtre
    document.querySelectorAll('select[name="category"], input[name="available"]')
        .forEach(function(element) {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        });
});
</script>
<?php $this->stop() ?>
