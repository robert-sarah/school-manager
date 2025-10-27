<?php $this->layout('layout/default', ['title' => 'Ajouter un livre']) ?>

<?php $this->start('content') ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1 class="mb-4">Ajouter un livre</h1>
            
            <form method="POST" action="/library/add" class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label">Auteur *</label>
                        <input type="text" class="form-control" id="author" name="author" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" 
                                   pattern="[0-9]{10}|[0-9]{13}" 
                                   title="ISBN à 10 ou 13 chiffres">
                            <div class="form-text">Format: 10 ou 13 chiffres</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Catégorie *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Choisir une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="publication_year" class="form-label">Année de publication</label>
                            <input type="number" class="form-control" id="publication_year" 
                                   name="publication_year" min="1800" max="<?= date('Y') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="publisher" class="form-label">Éditeur</label>
                            <input type="text" class="form-control" id="publisher" name="publisher">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="edition" class="form-label">Édition</label>
                            <input type="text" class="form-control" id="edition" name="edition">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="total_copies" class="form-label">Nombre d'exemplaires *</label>
                            <input type="number" class="form-control" id="total_copies" 
                                   name="total_copies" min="1" value="1" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="shelf_location" class="form-label">Emplacement</label>
                            <input type="text" class="form-control" id="shelf_location" 
                                   name="shelf_location" placeholder="Ex: A-12">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="4"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="/library" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation de l'ISBN
    const isbnInput = document.getElementById('isbn');
    isbnInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 13) {
            this.value = this.value.slice(0, 13);
        }
    });
    
    // Validation de l'année
    const yearInput = document.getElementById('publication_year');
    yearInput.max = new Date().getFullYear();
});
</script>
<?php $this->stop() ?>
