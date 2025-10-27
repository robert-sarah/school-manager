<?php
namespace App\Models;

class Subject {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM subjects ORDER BY name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO subjects (name, code, description)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['code'],
            $data['description'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE subjects 
            SET name = ?, code = ?, description = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['code'],
            $data['description'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        // Vérifier d'abord si la matière est utilisée
        $check = $this->db->prepare("
            SELECT COUNT(*) 
            FROM teacher_subjects 
            WHERE subject_id = ?
        ");
        $check->execute([$id]);
        
        if ($check->fetchColumn() > 0) {
            throw new \Exception("Cette matière est assignée à des enseignants et ne peut pas être supprimée.");
        }
        
        $stmt = $this->db->prepare("DELETE FROM subjects WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
?>
