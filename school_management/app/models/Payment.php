<?php
namespace App\Models;

class Payment {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function createPayment($data) {
        $this->db->beginTransaction();
        
        try {
            // Créer la facture
            $stmt = $this->db->prepare("
                INSERT INTO invoices (
                    student_id, amount, due_date, 
                    academic_year, semester_id, description,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['student_id'],
                $data['amount'],
                $data['due_date'],
                $data['academic_year'],
                $data['semester_id'],
                $data['description'],
                'pending'
            ]);
            
            $invoiceId = $this->db->lastInsertId();
            
            // Créer les éléments de facture
            if (!empty($data['items'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO invoice_items (
                        invoice_id, description, amount
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($data['items'] as $item) {
                    $stmt->execute([
                        $invoiceId,
                        $item['description'],
                        $item['amount']
                    ]);
                }
            }
            
            $this->db->commit();
            return $invoiceId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function recordPayment($data) {
        $this->db->beginTransaction();
        
        try {
            // Enregistrer le paiement
            $stmt = $this->db->prepare("
                INSERT INTO payments (
                    invoice_id, amount, payment_method,
                    transaction_id, payment_date, notes
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['invoice_id'],
                $data['amount'],
                $data['payment_method'],
                $data['transaction_id'],
                $data['payment_date'],
                $data['notes'] ?? null
            ]);
            
            // Mettre à jour le statut de la facture
            $this->updateInvoiceStatus($data['invoice_id']);
            
            $this->db->commit();
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function updateInvoiceStatus($invoiceId) {
        // Calculer le montant total payé
        $stmt = $this->db->prepare("
            SELECT 
                i.amount as total_amount,
                COALESCE(SUM(p.amount), 0) as paid_amount
            FROM invoices i
            LEFT JOIN payments p ON i.id = p.invoice_id
            WHERE i.id = ?
            GROUP BY i.id, i.amount
        ");
        $stmt->execute([$invoiceId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Mettre à jour le statut
        $status = 'pending';
        if ($result['paid_amount'] >= $result['total_amount']) {
            $status = 'paid';
        } elseif ($result['paid_amount'] > 0) {
            $status = 'partial';
        }
        
        $stmt = $this->db->prepare("
            UPDATE invoices 
            SET status = ?, 
                paid_amount = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $result['paid_amount'], $invoiceId]);
    }
    
    public function getStudentInvoices($studentId) {
        $stmt = $this->db->prepare("
            SELECT i.*, 
                   COALESCE(SUM(p.amount), 0) as paid_amount,
                   (i.amount - COALESCE(SUM(p.amount), 0)) as remaining_amount
            FROM invoices i
            LEFT JOIN payments p ON i.id = p.invoice_id
            WHERE i.student_id = ?
            GROUP BY i.id
            ORDER BY i.due_date DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getInvoice($invoiceId) {
        // Récupérer la facture
        $stmt = $this->db->prepare("
            SELECT i.*, 
                   s.name as student_name,
                   c.name as class_name,
                   COALESCE(SUM(p.amount), 0) as paid_amount,
                   (i.amount - COALESCE(SUM(p.amount), 0)) as remaining_amount
            FROM invoices i
            JOIN users s ON i.student_id = s.id
            LEFT JOIN student_classes sc ON s.id = sc.student_id
            LEFT JOIN classes c ON sc.class_id = c.id
            LEFT JOIN payments p ON i.id = p.invoice_id
            WHERE i.id = ?
            GROUP BY i.id
        ");
        $stmt->execute([$invoiceId]);
        $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($invoice) {
            // Récupérer les éléments de la facture
            $stmt = $this->db->prepare("
                SELECT * FROM invoice_items 
                WHERE invoice_id = ?
            ");
            $stmt->execute([$invoiceId]);
            $invoice['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Récupérer l'historique des paiements
            $stmt = $this->db->prepare("
                SELECT * FROM payments 
                WHERE invoice_id = ?
                ORDER BY payment_date DESC
            ");
            $stmt->execute([$invoiceId]);
            $invoice['payments'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $invoice;
    }
    
    public function getPaymentStats($filters = []) {
        $query = "
            SELECT 
                COUNT(DISTINCT i.id) as total_invoices,
                SUM(i.amount) as total_amount,
                SUM(COALESCE(p.amount, 0)) as total_paid,
                COUNT(DISTINCT CASE WHEN i.status = 'paid' THEN i.id END) as paid_invoices,
                COUNT(DISTINCT CASE WHEN i.status = 'pending' THEN i.id END) as pending_invoices
            FROM invoices i
            LEFT JOIN payments p ON i.id = p.invoice_id
            WHERE 1=1
        ";
        $params = [];
        
        if (isset($filters['start_date'])) {
            $query .= " AND i.created_at >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (isset($filters['end_date'])) {
            $query .= " AND i.created_at <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (isset($filters['academic_year'])) {
            $query .= " AND i.academic_year = ?";
            $params[] = $filters['academic_year'];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
?>
