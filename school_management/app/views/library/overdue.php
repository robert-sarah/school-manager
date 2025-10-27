<?php $this->layout('layout/default', ['title' => 'Emprunts en retard']) ?>

<?php $this->start('content') ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Emprunts en retard</h1>
        
        <a href="/library" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la bibliothèque
        </a>
    </div>
    
    <?php if (empty($loans)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> 
        Aucun emprunt en retard.
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Livre</th>
                        <th>Exemplaire</th>
                        <th>Emprunteur</th>
                        <th>Date d'emprunt</th>
                        <th>Date de retour prévue</th>
                        <th>Retard</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): 
                        $dueDate = new DateTime($loan['due_date']);
                        $today = new DateTime();
                        $delay = $today->diff($dueDate)->days;
                    ?>
                    <tr>
                        <td>
                            <a href="/library/view/<?= $loan['book_id'] ?>">
                                <?= htmlspecialchars($loan['title']) ?>
                            </a>
                        </td>
                        <td><?= $loan['copy_id'] ?></td>
                        <td><?= htmlspecialchars($loan['borrower_name']) ?></td>
                        <td><?= (new DateTime($loan['loan_date']))->format('d/m/Y') ?></td>
                        <td><?= $dueDate->format('d/m/Y') ?></td>
                        <td>
                            <span class="badge bg-danger">
                                <?= $delay ?> jour<?= $delay > 1 ? 's' : '' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/notifications/send?loan_id=<?= $loan['id'] ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-envelope"></i> 
                                    Notifier
                                </a>
                                
                                <form method="POST" 
                                      action="/library/return/<?= $loan['id'] ?>"
                                      class="d-inline">
                                    <button type="submit" class="btn btn-outline-success">
                                        <i class="fas fa-check"></i> 
                                        Retourné
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif ?>
</div>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmation avant envoi de notification
    document.querySelectorAll('a[href^="/notifications/send"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Envoyer une notification de rappel ?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
<?php $this->stop() ?>
