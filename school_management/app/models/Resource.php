<?php
namespace App\Models;

class Resource {
    private $db;
    private $storage_path;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
        $this->storage_path = __DIR__ . '/../../storage/resources/';
    }

    public function upload($file, $data) {
        // Vérifier et créer le répertoire si nécessaire
        $target_dir = $this->storage_path . date('Y/m/');
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Générer un nom unique pour le fichier
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $target_file = $target_dir . $filename;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Enregistrer dans la base de données
            $sql = "INSERT INTO resources (
                title, description, file_path, file_type,
                file_size, uploader_id, subject_id,
                class_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['description'],
                date('Y/m/') . $filename,
                $file['type'],
                $file['size'],
                $data['uploader_id'],
                $data['subject_id'] ?? null,
                $data['class_id'] ?? null
            ]);
        }

        return false;
    }

    public function getResources($filters = [], $page = 1, $perPage = 20) {
        $sql = "SELECT r.*, 
                   u.name as uploader_name,
                   s.name as subject_name,
                   c.name as class_name
                FROM resources r
                JOIN users u ON r.uploader_id = u.id
                LEFT JOIN subjects s ON r.subject_id = s.id
                LEFT JOIN classes c ON r.class_id = c.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['subject_id'])) {
            $sql .= " AND r.subject_id = ?";
            $params[] = $filters['subject_id'];
        }

        if (!empty($filters['class_id'])) {
            $sql .= " AND r.class_id = ?";
            $params[] = $filters['class_id'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND r.file_type LIKE ?";
            $params[] = "%{$filters['type']}%";
        }

        $sql .= " ORDER BY r.created_at DESC
                  LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function download($id) {
        $sql = "SELECT * FROM resources WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $resource = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($resource) {
            $file_path = $this->storage_path . $resource['file_path'];
            if (file_exists($file_path)) {
                // Mettre à jour le compteur de téléchargements
                $sql = "UPDATE resources 
                        SET downloads = downloads + 1 
                        WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id]);

                return $file_path;
            }
        }

        return false;
    }

    public function search($query) {
        $sql = "SELECT r.*, 
                   u.name as uploader_name,
                   s.name as subject_name,
                   c.name as class_name
                FROM resources r
                JOIN users u ON r.uploader_id = u.id
                LEFT JOIN subjects s ON r.subject_id = s.id
                LEFT JOIN classes c ON r.class_id = c.id
                WHERE r.title LIKE ? 
                   OR r.description LIKE ?
                ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", "%$query%"]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        // Récupérer le chemin du fichier
        $sql = "SELECT file_path FROM resources WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $resource = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($resource) {
            // Supprimer le fichier physique
            $file_path = $this->storage_path . $resource['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Supprimer l'enregistrement
            $sql = "DELETE FROM resources WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        }

        return false;
    }

    public function getMostPopular($limit = 10) {
        $sql = "SELECT r.*, 
                   u.name as uploader_name,
                   s.name as subject_name,
                   c.name as class_name
                FROM resources r
                JOIN users u ON r.uploader_id = u.id
                LEFT JOIN subjects s ON r.subject_id = s.id
                LEFT JOIN classes c ON r.class_id = c.id
                ORDER BY r.downloads DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecentBySubject($subject_id, $limit = 5) {
        $sql = "SELECT r.*, 
                   u.name as uploader_name
                FROM resources r
                JOIN users u ON r.uploader_id = u.id
                WHERE r.subject_id = ?
                ORDER BY r.created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$subject_id, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
