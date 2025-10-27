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
                                            <?php if ($unreadCount > 0): ?>
                                                <span class="badge bg-danger float-end"><?= $unreadCount ?></span>
                                            <?php endif; ?>
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
                                    <h6>Inbox</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($messages)): ?>
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>From</th>
                                                        <th>Subject</th>
                                                        <th>Replies</th>
                                                        <th>Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($messages as $message): ?>
                                                        <tr class="<?= $message['read_at'] ? '' : 'fw-bold' ?>">
                                                            <td>
                                                                <?php if (!$message['read_at']): ?>
                                                                    <i class="fas fa-circle text-primary"></i>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($message['sender_name']) ?></td>
                                                            <td>
                                                                <a href="/messages/view/<?= $message['id'] ?>">
                                                                    <?= htmlspecialchars($message['subject']) ?>
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <?php if ($message['replies_count'] > 0): ?>
                                                                    <span class="badge bg-info">
                                                                        <?= $message['replies_count'] ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= date('Y-m-d H:i', strtotime($message['created_at'])) ?></td>
                                                            <td>
                                                                <button onclick="deleteMessage(<?= $message['id'] ?>)" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center">No messages found</p>
                                    <?php endif; ?>
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
function deleteMessage(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This message will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/messages/delete/${id}`;
        }
    });
}
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
