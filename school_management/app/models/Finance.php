<?php
namespace App\Models;

class Finance {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    // Gestion des frais de scolarité
    public function recordTuitionPayment($data) {
        $this->db->beginTransaction();

        try {
            // Enregistrer le paiement
            $sql = "INSERT INTO payments (
                student_id, amount, payment_date, 
                payment_method, reference_number,
                semester, academic_year, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['student_id'],
                $data['amount'],
                $data['payment_date'],
                $data['payment_method'],
                $data['reference_number'],
                $data['semester'],
                $data['academic_year']
            ]);

            // Mettre à jour le solde de l'étudiant
            $sql = "UPDATE student_accounts 
                    SET balance = balance - ?,
                        updated_at = NOW()
                    WHERE student_id = ? 
                    AND academic_year = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['amount'],
                $data['student_id'],
                $data['academic_year']
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Gestion des salaires
    public function recordSalaryPayment($data) {
        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO salary_payments (
                teacher_id, amount, payment_date,
                payment_method, month, year,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['teacher_id'],
                $data['amount'],
                $data['payment_date'],
                $data['payment_method'],
                $data['month'],
                $data['year']
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Génération de factures
    public function generateInvoice($student_id, $semester, $academic_year) {
        $sql = "SELECT u.name, u.email, u.address,
                   c.name as class_name,
                   sa.tuition_fees,
                   sa.balance
                FROM users u
                JOIN class_students cs ON u.id = cs.student_id
                JOIN classes c ON cs.class_id = c.id
                JOIN student_accounts sa ON u.id = sa.student_id
                WHERE u.id = ?
                AND sa.academic_year = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$student_id, $academic_year]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            // Générer le numéro de facture
            $invoice_number = 'INV-' . date('Y') . sprintf('%06d', $student_id);

            // Créer la facture dans la base
            $sql = "INSERT INTO invoices (
                invoice_number, student_id,
                amount, balance, due_date,
                semester, academic_year,
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $invoice_number,
                $student_id,
                $data['tuition_fees'],
                $data['balance'],
                date('Y-m-d', strtotime('+30 days')),
                $semester,
                $academic_year
            ]);

            // Envoyer la facture par email
            $notification = new \App\Services\NotificationService();
            $notification->sendEmail(
                $data['email'],
                "Facture de scolarité - $semester $academic_year",
                $this->generateInvoiceTemplate($data, $invoice_number)
            );

            return $invoice_number;
        }

        return false;
    }

    // Rappels de paiement
    public function sendPaymentReminders() {
        $sql = "SELECT i.*, 
                   u.name, u.email,
                   DATEDIFF(i.due_date, CURRENT_DATE) as days_remaining
                FROM invoices i
                JOIN users u ON i.student_id = u.id
                WHERE i.status = 'pending'
                AND i.due_date > CURRENT_DATE
                AND i.due_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pending_payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $notification = new \App\Services\NotificationService();
        
        foreach ($pending_payments as $payment) {
            $notification->sendEmail(
                $payment['email'],
                "Rappel de paiement - Échéance dans {$payment['days_remaining']} jours",
                $this->generateReminderTemplate($payment)
            );
        }
    }

    // Rapports financiers
    public function generateFinancialReport($start_date, $end_date) {
        $report = [
            'income' => [
                'tuition' => $this->getTotalTuitionIncome($start_date, $end_date),
                'other' => $this->getOtherIncome($start_date, $end_date)
            ],
            'expenses' => [
                'salaries' => $this->getTotalSalaries($start_date, $end_date),
                'operational' => $this->getOperationalExpenses($start_date, $end_date)
            ],
            'outstanding' => [
                'tuition' => $this->getOutstandingTuition(),
                'invoices' => $this->getUnpaidInvoices()
            ],
            'statistics' => [
                'payment_rate' => $this->getPaymentRate($start_date, $end_date),
                'average_delay' => $this->getAveragePaymentDelay($start_date, $end_date)
            ]
        ];

        return $report;
    }

    private function getTotalTuitionIncome($start_date, $end_date) {
        $sql = "SELECT SUM(amount) FROM payments
                WHERE payment_date BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function getTotalSalaries($start_date, $end_date) {
        $sql = "SELECT SUM(amount) FROM salary_payments
                WHERE payment_date BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function getOperationalExpenses($start_date, $end_date) {
        $sql = "SELECT SUM(amount) FROM expenses
                WHERE expense_date BETWEEN ? AND ?
                AND category = 'operational'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function getOutstandingTuition() {
        $sql = "SELECT SUM(balance) FROM student_accounts
                WHERE balance > 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    private function getPaymentRate($start_date, $end_date) {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM payments 
                 WHERE payment_date BETWEEN ? AND ?) /
                (SELECT COUNT(*) FROM invoices 
                 WHERE created_at BETWEEN ? AND ?) * 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start_date, $end_date, $start_date, $end_date]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function generateInvoiceTemplate($data, $invoice_number) {
        // Template HTML de la facture
        return "
            <html>
                <body>
                    <h1>Facture N° $invoice_number</h1>
                    <p>Élève : {$data['name']}</p>
                    <p>Classe : {$data['class_name']}</p>
                    <p>Montant dû : {$data['tuition_fees']} €</p>
                    <p>Solde actuel : {$data['balance']} €</p>
                </body>
            </html>
        ";
    }

    private function generateReminderTemplate($payment) {
        return "
            Cher(e) {$payment['name']},

            Nous vous rappelons que le paiement de la facture N° {$payment['invoice_number']}
            d'un montant de {$payment['amount']} € arrive à échéance dans {$payment['days_remaining']} jours.

            Merci de procéder au règlement dès que possible.

            Cordialement,
            L'administration
        ";
    }
}
?>
