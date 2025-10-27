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
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6><?= htmlspecialchars($message['subject']) ?></h6>
                                        <div>
                                            <a href="/messages/compose?reply_to=<?= $message['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-reply"></i> Reply
                                            </a>
                                            <button onclick="deleteMessage(<?= $message['id'] ?>)" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Message original -->
                                    <div class="message-container mb-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <strong>From:</strong> <?= htmlspecialchars($message['sender_name']) ?><br>
                                                <strong>To:</strong> <?= htmlspecialchars($message['recipient_names']) ?><br>
                                                <strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($message['created_at'])) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="message-content p-3 bg-light rounded">
                                            <?= nl2br(htmlspecialchars($message['content'])) ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Réponses -->
                                    <?php if (!empty($message['replies'])): ?>
                                        <h6 class="mb-3">Replies</h6>
                                        <?php foreach ($message['replies'] as $reply): ?>
                                            <div class="message-container mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div>
                                                        <strong>From:</strong> <?= htmlspecialchars($reply['sender_name']) ?><br>
                                                        <strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($reply['created_at'])) ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="message-content p-3 bg-light rounded">
                                                    <?= nl2br(htmlspecialchars($reply['content'])) ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
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
