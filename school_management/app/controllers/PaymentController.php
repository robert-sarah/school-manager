<?php
namespace App\Controllers;

use App\Core\BaseController;

class PaymentController extends BaseController {
    private $paymentModel;
    
    public function __construct() {
        parent::__construct();
        $this->paymentModel = new \App\Models\Payment();
    }
    
    public function index() {
        $this->requirePermission('view_payments');
        
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'academic_year' => $_GET['academic_year'] ?? getCurrentAcademicYear()
        ];
        
        $stats = $this->paymentModel->getPaymentStats($filters);
        $students = (new \App\Models\Student())->getAllStudents();
        
        $this->view('payments/index', [
            'stats' => $stats,
            'students' => $students,
            'filters' => $filters
        ]);
    }
    
    public function createInvoice() {
        $this->requirePermission('manage_payments');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $items = [];
            foreach ($_POST['item_description'] as $key => $description) {
                if (!empty($description) && !empty($_POST['item_amount'][$key])) {
                    $items[] = [
                        'description' => $description,
                        'amount' => $_POST['item_amount'][$key]
                    ];
                }
            }
            
            $invoiceData = [
                'student_id' => $_POST['student_id'],
                'amount' => array_sum(array_column($items, 'amount')),
                'due_date' => $_POST['due_date'],
                'academic_year' => $_POST['academic_year'],
                'semester_id' => $_POST['semester_id'],
                'description' => $_POST['description'],
                'items' => $items
            ];
            
            try {
                $invoiceId = $this->paymentModel->createPayment($invoiceData);
                
                // Créer une notification pour l'étudiant
                $notificationModel = new \App\Models\Notification();
                $notificationModel->createNotification([
                    'title' => 'New Invoice Generated',
                    'message' => "A new invoice of {$invoiceData['amount']} has been generated for you.",
                    'type' => 'invoice',
                    'link' => "/payments/invoice/$invoiceId",
                    'created_by' => $_SESSION['user_id'],
                    'is_broadcast' => false,
                    'recipients' => [[
                        'id' => $invoiceData['student_id'],
                        'type' => 'user'
                    ]]
                ]);
                
                $this->setFlash('success', 'Invoice created successfully');
                redirect("/payments/invoice/$invoiceId");
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to create invoice');
                redirect('/payments/create-invoice');
            }
        }
        
        $students = (new \App\Models\Student())->getAllStudents();
        $semesters = (new \App\Models\Semester())->getAllSemesters();
        
        $this->view('payments/create_invoice', [
            'students' => $students,
            'semesters' => $semesters,
            'currentYear' => getCurrentAcademicYear()
        ]);
    }
    
    public function recordPayment() {
        $this->requirePermission('manage_payments');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentData = [
                'invoice_id' => $_POST['invoice_id'],
                'amount' => $_POST['amount'],
                'payment_method' => $_POST['payment_method'],
                'transaction_id' => $_POST['transaction_id'],
                'payment_date' => $_POST['payment_date'],
                'notes' => $_POST['notes']
            ];
            
            try {
                $this->paymentModel->recordPayment($paymentData);
                
                // Créer une notification
                $invoice = $this->paymentModel->getInvoice($paymentData['invoice_id']);
                $notificationModel = new \App\Models\Notification();
                $notificationModel->createNotification([
                    'title' => 'Payment Recorded',
                    'message' => "A payment of {$paymentData['amount']} has been recorded for your invoice.",
                    'type' => 'payment',
                    'link' => "/payments/invoice/{$paymentData['invoice_id']}",
                    'created_by' => $_SESSION['user_id'],
                    'is_broadcast' => false,
                    'recipients' => [[
                        'id' => $invoice['student_id'],
                        'type' => 'user'
                    ]]
                ]);
                
                $this->setFlash('success', 'Payment recorded successfully');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to record payment');
            }
            
            redirect("/payments/invoice/{$paymentData['invoice_id']}");
        }
    }
    
    public function viewInvoice($id) {
        $this->requirePermission('view_payments');
        
        $invoice = $this->paymentModel->getInvoice($id);
        
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            redirect('/payments');
        }
        
        $this->view('payments/invoice', [
            'invoice' => $invoice
        ]);
    }
    
    public function studentInvoices($studentId) {
        $this->requirePermission('view_payments');
        
        $student = (new \App\Models\Student())->getStudentById($studentId);
        if (!$student) {
            $this->setFlash('error', 'Student not found');
            redirect('/payments');
        }
        
        $invoices = $this->paymentModel->getStudentInvoices($studentId);
        
        $this->view('payments/student_invoices', [
            'student' => $student,
            'invoices' => $invoices
        ]);
    }
    
    public function downloadInvoice($id) {
        $this->requirePermission('view_payments');
        
        $invoice = $this->paymentModel->getInvoice($id);
        
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            redirect('/payments');
        }
        
        // Générer le PDF
        $pdf = new \FPDF();
        $pdf->AddPage();
        
        // En-tête
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
        
        // Informations de l'école
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'School Name', 0, 1, 'C');
        $pdf->Cell(0, 10, 'School Address', 0, 1, 'C');
        
        // Informations de la facture
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Invoice #: ' . $invoice['id'], 0, 1);
        $pdf->Cell(0, 10, 'Date: ' . date('Y-m-d', strtotime($invoice['created_at'])), 0, 1);
        $pdf->Cell(0, 10, 'Due Date: ' . date('Y-m-d', strtotime($invoice['due_date'])), 0, 1);
        
        // Informations de l'étudiant
        $pdf->Ln(5);
        $pdf->Cell(0, 10, 'Student Information:', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Name: ' . $invoice['student_name'], 0, 1);
        $pdf->Cell(0, 10, 'Class: ' . $invoice['class_name'], 0, 1);
        
        // Éléments de la facture
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 10, 'Description', 1);
        $pdf->Cell(90, 10, 'Amount', 1);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 12);
        foreach ($invoice['items'] as $item) {
            $pdf->Cell(100, 10, $item['description'], 1);
            $pdf->Cell(90, 10, number_format($item['amount'], 2), 1);
            $pdf->Ln();
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 10, 'Total', 1);
        $pdf->Cell(90, 10, number_format($invoice['amount'], 2), 1);
        
        // Statut du paiement
        $pdf->Ln(20);
        $pdf->Cell(0, 10, 'Payment Status: ' . ucfirst($invoice['status']), 0, 1);
        $pdf->Cell(0, 10, 'Amount Paid: ' . number_format($invoice['paid_amount'], 2), 0, 1);
        $pdf->Cell(0, 10, 'Amount Due: ' . number_format($invoice['remaining_amount'], 2), 0, 1);
        
        // Générer le PDF
        $pdf->Output('Invoice_' . $invoice['id'] . '.pdf', 'D');
    }
}
?>
