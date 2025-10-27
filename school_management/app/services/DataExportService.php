<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class DataExportService {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function exportStudents($filters = []) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // En-têtes
        $headers = [
            'ID', 'Nom', 'Email', 'Classe', 'Date de naissance',
            'Téléphone', 'Adresse', 'Parent/Tuteur', 'Date d\'inscription'
        ];
        $sheet->fromArray([$headers], null, 'A1');
        
        // Récupérer les données
        $sql = "
            SELECT u.id, u.name, u.email, c.name as class_name,
                   u.birthdate, u.phone, u.address,
                   p.name as parent_name, u.created_at
            FROM users u
            LEFT JOIN classes c ON u.class_id = c.id
            LEFT JOIN users p ON u.parent_id = p.id
            WHERE u.role = 'student'
        ";
        
        if (!empty($filters['class'])) {
            $sql .= " AND u.class_id = ?";
            $params[] = $filters['class'];
        }
        
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(u.created_at) = ?";
            $params[] = $filters['year'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params ?? []);
        $students = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Données
        $row = 2;
        foreach ($students as $student) {
            $sheet->fromArray([array_values($student)], null, "A{$row}");
            $row++;
        }
        
        // Formatage
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $styleArray = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CCCCCC']
            ]
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($styleArray);
        
        // Créer le fichier
        $writer = new Xlsx($spreadsheet);
        $filename = 'students_export_' . date('Y-m-d_His') . '.xlsx';
        $path = __DIR__ . '/../../storage/exports/' . $filename;
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $writer->save($path);
        
        return $filename;
    }
    
    public function exportGrades($filters = []) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // En-têtes
        $headers = [
            'Étudiant', 'Classe', 'Matière', 'Note', 'Période',
            'Professeur', 'Date', 'Commentaire'
        ];
        $sheet->fromArray([$headers], null, 'A1');
        
        // Données
        $sql = "
            SELECT u.name as student_name, c.name as class_name,
                   s.name as subject_name, g.grade,
                   g.period, t.name as teacher_name,
                   g.created_at, g.comment
            FROM grades g
            JOIN users u ON g.student_id = u.id
            JOIN classes c ON u.class_id = c.id
            JOIN subjects s ON g.subject_id = s.id
            JOIN users t ON g.teacher_id = t.id
        ";
        
        if (!empty($filters['class'])) {
            $sql .= " AND c.id = ?";
            $params[] = $filters['class'];
        }
        
        if (!empty($filters['period'])) {
            $sql .= " AND g.period = ?";
            $params[] = $filters['period'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params ?? []);
        $grades = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $row = 2;
        foreach ($grades as $grade) {
            $sheet->fromArray([array_values($grade)], null, "A{$row}");
            $row++;
        }
        
        // Formatage
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $styleArray = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CCCCCC']
            ]
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($styleArray);
        
        // Créer le fichier
        $writer = new Xlsx($spreadsheet);
        $filename = 'grades_export_' . date('Y-m-d_His') . '.xlsx';
        $path = __DIR__ . '/../../storage/exports/' . $filename;
        
        $writer->save($path);
        
        return $filename;
    }
    
    public function importStudents($file) {
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Enlever les en-têtes
        array_shift($rows);
        
        $this->db->beginTransaction();
        
        try {
            foreach ($rows as $row) {
                // Vérifier si l'étudiant existe déjà
                $stmt = $this->db->prepare("
                    SELECT id FROM users 
                    WHERE email = ? OR (name = ? AND birthdate = ?)
                ");
                $stmt->execute([$row[2], $row[1], $row[4]]);
                
                if ($stmt->rowCount() > 0) {
                    // Mettre à jour
                    $student = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $stmt = $this->db->prepare("
                        UPDATE users SET
                            name = ?, email = ?, birthdate = ?,
                            phone = ?, address = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $row[1], $row[2], $row[4],
                        $row[5], $row[6], $student['id']
                    ]);
                } else {
                    // Créer
                    $stmt = $this->db->prepare("
                        INSERT INTO users (
                            name, email, role, birthdate,
                            phone, address, created_at
                        ) VALUES (?, ?, 'student', ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $row[1], $row[2], $row[4],
                        $row[5], $row[6]
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
    
    public function importGrades($file) {
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Enlever les en-têtes
        array_shift($rows);
        
        $this->db->beginTransaction();
        
        try {
            foreach ($rows as $row) {
                // Trouver l'étudiant
                $stmt = $this->db->prepare("
                    SELECT id FROM users 
                    WHERE name = ? AND role = 'student'
                ");
                $stmt->execute([$row[0]]);
                $student = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$student) continue;
                
                // Trouver la matière
                $stmt = $this->db->prepare("
                    SELECT id FROM subjects WHERE name = ?
                ");
                $stmt->execute([$row[2]]);
                $subject = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$subject) continue;
                
                // Ajouter la note
                $stmt = $this->db->prepare("
                    INSERT INTO grades (
                        student_id, subject_id, grade,
                        period, teacher_id, comment,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $student['id'],
                    $subject['id'],
                    $row[3],
                    $row[4],
                    $this->getCurrentUserId(),
                    $row[7],
                    $row[6]
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}
?>
