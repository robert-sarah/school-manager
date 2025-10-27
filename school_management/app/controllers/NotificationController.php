<?php
namespace App\Controllers;

use App\Core\BaseController;

class NotificationController extends BaseController {
    private $notificationModel;
    
    public function __construct() {
        parent::__construct();
        $this->notificationModel = new \App\Models\Notification();
    }
    
    public function getNotifications() {
        $notifications = $this->notificationModel->getUserNotifications($_SESSION['user_id']);
        $unreadCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);
        
        echo json_encode([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    public function markAsRead($id) {
        $success = $this->notificationModel->markAsRead($id, $_SESSION['user_id']);
        echo json_encode(['success' => $success]);
    }
    
    public function markAllAsRead() {
        $success = $this->notificationModel->markAllAsRead($_SESSION['user_id']);
        echo json_encode(['success' => $success]);
    }
    
    public function create() {
        $this->requirePermission('send_notifications');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recipients = [];
            
            // Gérer les destinataires
            if (!empty($_POST['users'])) {
                foreach ($_POST['users'] as $userId) {
                    $recipients[] = [
                        'id' => $userId,
                        'type' => 'user'
                    ];
                }
            }
            
            if (!empty($_POST['roles'])) {
                foreach ($_POST['roles'] as $roleId) {
                    $recipients[] = [
                        'id' => $roleId,
                        'type' => 'role'
                    ];
                }
            }
            
            if (!empty($_POST['classes'])) {
                foreach ($_POST['classes'] as $classId) {
                    $recipients[] = [
                        'id' => $classId,
                        'type' => 'class'
                    ];
                }
            }
            
            $notificationData = [
                'title' => $_POST['title'],
                'message' => $_POST['message'],
                'type' => $_POST['type'],
                'link' => $_POST['link'] ?? null,
                'created_by' => $_SESSION['user_id'],
                'is_broadcast' => isset($_POST['is_broadcast']),
                'recipients' => $recipients
            ];
            
            try {
                $notificationId = $this->notificationModel->createNotification($notificationData);
                $this->setFlash('success', 'Notification sent successfully');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to send notification');
            }
            
            redirect('/notifications/create');
        }
        
        $users = (new \App\Models\User())->getAllUsers();
        $roles = (new \App\Models\Role())->getAllRoles();
        $classes = (new \App\Models\Class_())->getAllClasses();
        
        $this->view('notifications/create', [
            'users' => $users,
            'roles' => $roles,
            'classes' => $classes
        ]);
    }
    
    public function delete($id) {
        $this->requirePermission('manage_notifications');
        
        if ($this->notificationModel->deleteNotification($id)) {
            $this->setFlash('success', 'Notification deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete notification');
        }
        
        redirect('/notifications');
    }
}
?>
