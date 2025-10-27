<?php
namespace App\Models;

class Event {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function createEvent($data) {
        $this->db->beginTransaction();
        
        try {
            // Insérer l'événement
            $stmt = $this->db->prepare("
                INSERT INTO events (
                    title, description, event_type, start_date, end_date,
                    location, created_by, is_public
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['event_type'],
                $data['start_date'],
                $data['end_date'],
                $data['location'],
                $data['created_by'],
                $data['is_public']
            ]);
            
            $eventId = $this->db->lastInsertId();
            
            // Ajouter les participants
            if (!empty($data['participants'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO event_participants (
                        event_id, participant_id, participant_type
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($data['participants'] as $participant) {
                    $stmt->execute([
                        $eventId,
                        $participant['id'],
                        $participant['type']
                    ]);
                }
            }
            
            $this->db->commit();
            return $eventId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function updateEvent($eventId, $data) {
        $this->db->beginTransaction();
        
        try {
            // Mettre à jour l'événement
            $stmt = $this->db->prepare("
                UPDATE events SET 
                    title = ?, description = ?, event_type = ?,
                    start_date = ?, end_date = ?, location = ?,
                    is_public = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['event_type'],
                $data['start_date'],
                $data['end_date'],
                $data['location'],
                $data['is_public'],
                $eventId
            ]);
            
            // Mettre à jour les participants
            if (isset($data['participants'])) {
                // Supprimer les anciens participants
                $stmt = $this->db->prepare("DELETE FROM event_participants WHERE event_id = ?");
                $stmt->execute([$eventId]);
                
                // Ajouter les nouveaux participants
                $stmt = $this->db->prepare("
                    INSERT INTO event_participants (
                        event_id, participant_id, participant_type
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($data['participants'] as $participant) {
                    $stmt->execute([
                        $eventId,
                        $participant['id'],
                        $participant['type']
                    ]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getEvent($eventId) {
        $stmt = $this->db->prepare("
            SELECT e.*, u.name as creator_name,
                   GROUP_CONCAT(
                       DISTINCT CASE ep.participant_type
                           WHEN 'user' THEN u2.name
                           WHEN 'class' THEN c.name
                       END
                   ) as participant_names
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            LEFT JOIN event_participants ep ON e.id = ep.event_id
            LEFT JOIN users u2 ON ep.participant_id = u2.id AND ep.participant_type = 'user'
            LEFT JOIN classes c ON ep.participant_id = c.id AND ep.participant_type = 'class'
            WHERE e.id = ?
            GROUP BY e.id
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getAllEvents($filters = []) {
        $query = "
            SELECT e.*, u.name as creator_name
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE 1=1
        ";
        $params = [];
        
        if (isset($filters['start_date'])) {
            $query .= " AND e.start_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (isset($filters['end_date'])) {
            $query .= " AND e.end_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (isset($filters['event_type'])) {
            $query .= " AND e.event_type = ?";
            $params[] = $filters['event_type'];
        }
        
        if (isset($filters['created_by'])) {
            $query .= " AND e.created_by = ?";
            $params[] = $filters['created_by'];
        }
        
        $query .= " ORDER BY e.start_date ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getUserEvents($userId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT e.*, u.name as creator_name
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            LEFT JOIN event_participants ep ON e.id = ep.event_id
            WHERE e.is_public = 1
            OR e.created_by = ?
            OR (ep.participant_type = 'user' AND ep.participant_id = ?)
            OR (ep.participant_type = 'class' AND ep.participant_id IN (
                SELECT class_id FROM student_classes WHERE student_id = ?
            ))
            ORDER BY e.start_date ASC
        ");
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function deleteEvent($eventId) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$eventId]);
    }
    
    public function getEventTypes() {
        return [
            'academic' => 'Academic Event',
            'sports' => 'Sports Event',
            'cultural' => 'Cultural Event',
            'holiday' => 'Holiday',
            'exam' => 'Examination',
            'meeting' => 'Meeting',
            'other' => 'Other'
        ];
    }
}
?>
