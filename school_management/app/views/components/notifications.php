<!-- Notifications Dropdown -->
<li class="nav-item dropdown">
    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span class="notification-count badge bg-danger rounded-pill" style="display: none;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notificationsDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="dropdown-header border-0">Notifications</h6>
            <button type="button" class="btn btn-link btn-sm text-primary mark-all-read">Mark all as read</button>
        </div>
        <div class="notifications-list">
            <!-- Les notifications seront chargées ici dynamiquement -->
        </div>
    </div>
</li>

<script>
function loadNotifications() {
    $.ajax({
        url: '/notifications/get',
        method: 'GET',
        success: function(response) {
            const data = JSON.parse(response);
            updateNotificationBadge(data.unreadCount);
            renderNotifications(data.notifications);
        }
    });
}

function updateNotificationBadge(count) {
    const badge = $('.notification-count');
    if (count > 0) {
        badge.text(count).show();
    } else {
        badge.hide();
    }
}

function renderNotifications(notifications) {
    const container = $('.notifications-list');
    container.empty();
    
    if (notifications.length === 0) {
        container.append('<div class="text-center text-muted">No notifications</div>');
        return;
    }
    
    notifications.forEach(notification => {
        const html = `
            <div class="dropdown-item notification-item ${!notification.read_at ? 'fw-bold' : ''}" 
                 data-id="${notification.id}">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-muted">
                            ${notification.sender_name} · ${moment(notification.created_at).fromNow()}
                        </div>
                        <div>${notification.title}</div>
                        <div class="small">${notification.message}</div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
            </div>
        `;
        container.append(html);
    });
}

$(document).ready(function() {
    // Charger les notifications au démarrage
    loadNotifications();
    
    // Recharger périodiquement
    setInterval(loadNotifications, 60000); // Toutes les minutes
    
    // Marquer une notification comme lue
    $(document).on('click', '.notification-item', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `/notifications/mark-read/${id}`,
            method: 'POST',
            success: function() {
                loadNotifications();
            }
        });
        
        // Si la notification a un lien, rediriger
        const link = $(this).data('link');
        if (link) {
            window.location.href = link;
        }
    });
    
    // Marquer toutes les notifications comme lues
    $('.mark-all-read').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $.ajax({
            url: '/notifications/mark-all-read',
            method: 'POST',
            success: function() {
                loadNotifications();
            }
        });
    });
});
</script>
