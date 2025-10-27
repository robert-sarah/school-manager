<?php
namespace App\Models;

class Forum {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    // Création d'un nouveau sujet
    public function createTopic($data) {
        $sql = "INSERT INTO forum_topics (
            title, content, author_id, category_id,
            class_id, subject_id, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['content'],
            $data['author_id'],
            $data['category_id'],
            $data['class_id'] ?? null,
            $data['subject_id'] ?? null
        ]);
    }

    // Ajout d'une réponse
    public function addReply($data) {
        $sql = "INSERT INTO forum_replies (
            topic_id, content, author_id, parent_id,
            created_at
        ) VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['topic_id'],
            $data['content'],
            $data['author_id'],
            $data['parent_id'] ?? null
        ]);
    }

    // Récupération des sujets
    public function getTopics($filters = [], $page = 1, $perPage = 20) {
        $sql = "SELECT t.*, 
                   u.name as author_name,
                   c.name as class_name,
                   s.name as subject_name,
                   COUNT(DISTINCT r.id) as replies_count,
                   MAX(r.created_at) as last_reply_date
                FROM forum_topics t
                JOIN users u ON t.author_id = u.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN subjects s ON t.subject_id = s.id
                LEFT JOIN forum_replies r ON t.id = r.topic_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['class_id'])) {
            $sql .= " AND t.class_id = ?";
            $params[] = $filters['class_id'];
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND t.subject_id = ?";
            $params[] = $filters['subject_id'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = ?";
            $params[] = $filters['category_id'];
        }

        $sql .= " GROUP BY t.id
                  ORDER BY t.pinned DESC, last_reply_date DESC
                  LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Récupération des réponses d'un sujet
    public function getReplies($topic_id, $page = 1, $perPage = 20) {
        $sql = "SELECT r.*, 
                   u.name as author_name,
                   u.avatar as author_avatar,
                   u.role as author_role
                FROM forum_replies r
                JOIN users u ON r.author_id = u.id
                WHERE r.topic_id = ?
                ORDER BY r.created_at ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $topic_id,
            $perPage,
            ($page - 1) * $perPage
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Marquer comme résolu
    public function markAsSolved($topic_id, $reply_id) {
        $sql = "UPDATE forum_topics 
                SET solved = 1,
                    solution_id = ?,
                    updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reply_id, $topic_id]);
    }

    // Épingler un sujet
    public function pinTopic($topic_id) {
        $sql = "UPDATE forum_topics 
                SET pinned = 1,
                    updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$topic_id]);
    }

    // Recherche dans le forum
    public function search($query, $filters = []) {
        $sql = "SELECT t.*, 
                   u.name as author_name,
                   c.name as class_name,
                   s.name as subject_name
                FROM forum_topics t
                JOIN users u ON t.author_id = u.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN subjects s ON t.subject_id = s.id
                WHERE (t.title LIKE ? OR t.content LIKE ?)";

        $params = [
            "%$query%",
            "%$query%"
        ];

        if (!empty($filters['class_id'])) {
            $sql .= " AND t.class_id = ?";
            $params[] = $filters['class_id'];
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND t.subject_id = ?";
            $params[] = $filters['subject_id'];
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
