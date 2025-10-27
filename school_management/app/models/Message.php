<?php
namespace App\Models;

class Message {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function sendMessage($data) {
        $this->db->beginTransaction();
        
        try {
            // Insérer le message principal
            $stmt = $this->db->prepare("
                INSERT INTO messages (sender_id, subject, content, parent_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['sender_id'],
                $data['subject'],
                $data['content'],
                $data['parent_id'] ?? null
            ]);
            
            $messageId = $this->db->lastInsertId();
            
            // Insérer les destinataires
            $stmt = $this->db->prepare("
                INSERT INTO message_recipients (message_id, recipient_id, recipient_type)
                VALUES (?, ?, ?)
            ");
            
            foreach ($data['recipients'] as $recipient) {
                $stmt->execute([
                    $messageId,
                    $recipient['id'],
                    $recipient['type']
                ]);
            }
            
            $this->db->commit();
            return $messageId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getInbox($userId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->prepare("
            SELECT m.*, u.name as sender_name,
                   GROUP_CONCAT(DISTINCT mr2.recipient_id) as recipient_ids,
                   (SELECT COUNT(*) FROM messages WHERE parent_id = m.id) as replies_count,
                   mr.read_at
            FROM messages m
            JOIN message_recipients mr ON m.id = mr.message_id
            JOIN users u ON m.sender_id = u.id
            LEFT JOIN message_recipients mr2 ON m.id = mr2.message_id
            WHERE mr.recipient_id = ?
            AND m.parent_id IS NULL
            GROUP BY m.id
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $perPage, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getSent($userId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   GROUP_CONCAT(DISTINCT u.name) as recipient_names,
                   (SELECT COUNT(*) FROM messages WHERE parent_id = m.id) as replies_count
            FROM messages m
            JOIN message_recipients mr ON m.id = mr.message_id
            JOIN users u ON mr.recipient_id = u.id
            WHERE m.sender_id = ?
            AND m.parent_id IS NULL
            GROUP BY m.id
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $perPage, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getMessage($messageId, $userId) {
        // Vérifier que l'utilisateur a accès au message
        $stmt = $this->db->prepare("
            SELECT m.*, u.name as sender_name,
                   GROUP_CONCAT(DISTINCT u2.name) as recipient_names,
                   mr.read_at
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            JOIN message_recipients mr ON m.id = mr.message_id
            LEFT JOIN users u2 ON mr.recipient_id = u2.id
            WHERE m.id = ?
            AND (m.sender_id = ? OR mr.recipient_id = ?)
            GROUP BY m.id
        ");
        $stmt->execute([$messageId, $userId, $userId]);
        $message = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($message) {
            // Marquer comme lu si pas encore lu
            if (!$message['read_at']) {
                $this->markAsRead($messageId, $userId);
            }
            
            // Récupérer les réponses
            $stmt = $this->db->prepare("
                SELECT m.*, u.name as sender_name
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.parent_id = ?
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$messageId]);
            $message['replies'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $message;
    }
    
    public function markAsRead($messageId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE message_recipients 
            SET read_at = CURRENT_TIMESTAMP
            WHERE message_id = ? AND recipient_id = ? AND read_at IS NULL
        ");
        return $stmt->execute([$messageId, $userId]);
    }
    
    public function deleteMessage($messageId, $userId) {
        // Vérifier que l'utilisateur est l'expéditeur ou le destinataire
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM messages m
            LEFT JOIN message_recipients mr ON m.id = mr.message_id
            WHERE m.id = ?
            AND (m.sender_id = ? OR mr.recipient_id = ?)
        ");
        $stmt->execute([$messageId, $userId, $userId]);
        
        if ($stmt->fetch()) {
            $stmt = $this->db->prepare("
                UPDATE messages 
                SET deleted_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            return $stmt->execute([$messageId]);
        }
        return false;
    }
    
    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM message_recipients mr
            JOIN messages m ON mr.message_id = m.id
            WHERE mr.recipient_id = ?
            AND mr.read_at IS NULL
            AND m.deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>
