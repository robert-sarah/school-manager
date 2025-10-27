<?php
namespace App\Models;

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function createNotification($data) {
        $this->db->beginTransaction();
        
        try {
            // Insérer la notification
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    title, message, type, link,
                    created_by, is_broadcast
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['message'],
                $data['type'],
                $data['link'] ?? null,
                $data['created_by'],
                $data['is_broadcast']
            ]);
            
            $notificationId = $this->db->lastInsertId();
            
            // Créer les destinataires si ce n'est pas une diffusion
            if (!$data['is_broadcast'] && !empty($data['recipients'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO notification_recipients (
                        notification_id, recipient_id, recipient_type
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($data['recipients'] as $recipient) {
                    $stmt->execute([
                        $notificationId,
                        $recipient['id'],
                        $recipient['type']
                    ]);
                }
            }
            
            $this->db->commit();
            return $notificationId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUserNotifications($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT n.*, u.name as sender_name,
                   nr.read_at
            FROM notifications n
            LEFT JOIN users u ON n.created_by = u.id
            LEFT JOIN notification_recipients nr ON n.id = nr.notification_id
            WHERE n.is_broadcast = 1
            OR (nr.recipient_type = 'user' AND nr.recipient_id = ?)
            OR (nr.recipient_type = 'role' AND nr.recipient_id = (
                SELECT role_id FROM users WHERE id = ?
            ))
            OR (nr.recipient_type = 'class' AND nr.recipient_id IN (
                SELECT class_id FROM student_classes WHERE student_id = ?
            ))
            ORDER BY n.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $userId, $userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT n.id) as count
            FROM notifications n
            LEFT JOIN notification_recipients nr ON n.id = nr.notification_id
            WHERE nr.read_at IS NULL
            AND (
                n.is_broadcast = 1
                OR (nr.recipient_type = 'user' AND nr.recipient_id = ?)
                OR (nr.recipient_type = 'role' AND nr.recipient_id = (
                    SELECT role_id FROM users WHERE id = ?
                ))
                OR (nr.recipient_type = 'class' AND nr.recipient_id IN (
                    SELECT class_id FROM student_classes WHERE student_id = ?
                ))
            )
        ");
        
        $stmt->execute([$userId, $userId, $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    public function markAsRead($notificationId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE notification_recipients
            SET read_at = CURRENT_TIMESTAMP
            WHERE notification_id = ?
            AND recipient_id = ?
            AND read_at IS NULL
        ");
        return $stmt->execute([$notificationId, $userId]);
    }
    
    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("
            UPDATE notification_recipients nr
            JOIN notifications n ON nr.notification_id = n.id
            SET nr.read_at = CURRENT_TIMESTAMP
            WHERE nr.recipient_id = ?
            AND nr.read_at IS NULL
        ");
        return $stmt->execute([$userId]);
    }
    
    public function deleteNotification($notificationId) {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }
}
?>
