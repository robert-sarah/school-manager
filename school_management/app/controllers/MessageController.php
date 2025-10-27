<?php
namespace App\Controllers;

use App\Core\BaseController;

class MessageController extends BaseController {
    private $messageModel;
    
    public function __construct() {
        parent::__construct();
        $this->messageModel = new \App\Models\Message();
    }
    
    public function inbox() {
        $page = $_GET['page'] ?? 1;
        $messages = $this->messageModel->getInbox($_SESSION['user_id'], $page);
        $unreadCount = $this->messageModel->getUnreadCount($_SESSION['user_id']);
        
        $this->view('messages/inbox', [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'page' => $page,
            'activeTab' => 'inbox'
        ]);
    }
    
    public function sent() {
        $page = $_GET['page'] ?? 1;
        $messages = $this->messageModel->getSent($_SESSION['user_id'], $page);
        
        $this->view('messages/sent', [
            'messages' => $messages,
            'page' => $page,
            'activeTab' => 'sent'
        ]);
    }
    
    public function compose() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recipients = [];
            
            // Traiter les destinataires
            if (!empty($_POST['users'])) {
                foreach ($_POST['users'] as $userId) {
                    $recipients[] = [
                        'id' => $userId,
                        'type' => 'user'
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
            
            if (empty($recipients)) {
                $this->setFlash('error', 'Please select at least one recipient');
                redirect('/messages/compose');
            }
            
            $messageData = [
                'sender_id' => $_SESSION['user_id'],
                'subject' => $_POST['subject'],
                'content' => $_POST['content'],
                'recipients' => $recipients,
                'parent_id' => $_POST['parent_id'] ?? null
            ];
            
            try {
                $messageId = $this->messageModel->sendMessage($messageData);
                $this->setFlash('success', 'Message sent successfully');
                
                if (isset($_POST['parent_id'])) {
                    redirect("/messages/view/{$_POST['parent_id']}");
                } else {
                    redirect('/messages/sent');
                }
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to send message');
                redirect('/messages/compose');
            }
        }
        
        $users = (new \App\Models\User())->getAllUsers();
        $classes = (new \App\Models\Class_())->getAllClasses();
        
        $this->view('messages/compose', [
            'users' => $users,
            'classes' => $classes,
            'activeTab' => 'compose',
            'reply_to' => $_GET['reply_to'] ?? null
        ]);
    }
    
    public function view($id) {
        $message = $this->messageModel->getMessage($id, $_SESSION['user_id']);
        
        if (!$message) {
            $this->setFlash('error', 'Message not found');
            redirect('/messages/inbox');
        }
        
        $this->view('messages/view', [
            'message' => $message,
            'activeTab' => $message['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'inbox'
        ]);
    }
    
    public function delete($id) {
        if ($this->messageModel->deleteMessage($id, $_SESSION['user_id'])) {
            $this->setFlash('success', 'Message deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete message');
        }
        
        redirect('/messages/inbox');
    }
    
    public function markAsRead($id) {
        if ($this->messageModel->markAsRead($id, $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
?>
