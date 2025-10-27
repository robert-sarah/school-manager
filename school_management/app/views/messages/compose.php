<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <a href="/messages/compose" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-pen"></i> Compose
                                    </a>
                                    
                                    <div class="list-group">
                                        <a href="/messages/inbox" class="list-group-item list-group-item-action <?= $activeTab == 'inbox' ? 'active' : '' ?>">
                                            <i class="fas fa-inbox"></i> Inbox
                                        </a>
                                        <a href="/messages/sent" class="list-group-item list-group-item-action <?= $activeTab == 'sent' ? 'active' : '' ?>">
                                            <i class="fas fa-paper-plane"></i> Sent
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Compose Message</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/messages/compose" id="composeForm">
                                        <?php if ($reply_to): ?>
                                            <input type="hidden" name="parent_id" value="<?= $reply_to ?>">
                                        <?php endif; ?>
                                        
                                        <div class="form-group mb-3">
                                            <label class="form-control-label">To Users</label>
                                            <select class="form-select" name="users[]" multiple>
                                                <?php foreach ($users as $user): ?>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <option value="<?= $user['id'] ?>">
                                                            <?= htmlspecialchars($user['name']) ?> 
                                                            (<?= htmlspecialchars($user['role']) ?>)
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label class="form-control-label">To Classes</label>
                                            <select class="form-select" name="classes[]" multiple>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?= $class['id'] ?>">
                                                        <?= htmlspecialchars($class['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label class="form-control-label">Subject</label>
                                            <input type="text" class="form-control" name="subject" required>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label class="form-control-label">Message</label>
                                            <textarea class="form-control" name="content" rows="6" required></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Send Message</button>
                                            <a href="/messages/inbox" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
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
$(document).ready(function() {
    $('select[name="users[]"], select[name="classes[]"]').select2({
        placeholder: 'Select recipients',
        allowClear: true
    });
    
    $('#composeForm').on('submit', function(e) {
        var usersSelected = $('select[name="users[]"]').val();
        var classesSelected = $('select[name="classes[]"]').val();
        
        if (!usersSelected?.length && !classesSelected?.length) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'No Recipients Selected',
                text: 'Please select at least one recipient (user or class)'
            });
        }
    });
});
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
