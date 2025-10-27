<?php
namespace App\Models;

class Library {
    private $db;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function addBook($data) {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO books (
                    title, author, isbn, category_id,
                    publication_year, publisher, edition,
                    total_copies, available_copies,
                    shelf_location, description
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['author'],
                $data['isbn'],
                $data['category_id'],
                $data['publication_year'],
                $data['publisher'],
                $data['edition'],
                $data['total_copies'],
                $data['total_copies'], // Au début, tous les exemplaires sont disponibles
                $data['shelf_location'],
                $data['description']
            ]);
            
            $bookId = $this->db->lastInsertId();
            
            // Ajouter les exemplaires
            $stmt = $this->db->prepare("
                INSERT INTO book_copies (
                    book_id, copy_number, status
                ) VALUES (?, ?, 'available')
            ");
            
            for ($i = 1; $i <= $data['total_copies']; $i++) {
                $stmt->execute([$bookId, $i]);
            }
            
            $this->db->commit();
            return $bookId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function updateBook($bookId, $data) {
        $stmt = $this->db->prepare("
            UPDATE books SET 
                title = ?, author = ?, isbn = ?, 
                category_id = ?, publication_year = ?,
                publisher = ?, edition = ?,
                shelf_location = ?, description = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['author'],
            $data['isbn'],
            $data['category_id'],
            $data['publication_year'],
            $data['publisher'],
            $data['edition'],
            $data['shelf_location'],
            $data['description'],
            $bookId
        ]);
    }
    
    public function getBook($bookId) {
        $stmt = $this->db->prepare("
            SELECT b.*, c.name as category_name,
                   (SELECT COUNT(*) FROM book_loans WHERE book_id = b.id AND return_date IS NULL) as borrowed_copies
            FROM books b
            LEFT JOIN book_categories c ON b.category_id = c.id
            WHERE b.id = ?
        ");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($book) {
            // Récupérer les exemplaires
            $stmt = $this->db->prepare("
                SELECT * FROM book_copies 
                WHERE book_id = ?
            ");
            $stmt->execute([$bookId]);
            $book['copies'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Récupérer l'historique des emprunts
            $stmt = $this->db->prepare("
                SELECT bl.*, u.name as borrower_name
                FROM book_loans bl
                JOIN users u ON bl.borrower_id = u.id
                WHERE bl.book_id = ?
                ORDER BY bl.loan_date DESC
            ");
            $stmt->execute([$bookId]);
            $book['loans'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $book;
    }
    
    public function searchBooks($filters = []) {
        $query = "
            SELECT b.*, c.name as category_name,
                   (SELECT COUNT(*) FROM book_loans WHERE book_id = b.id AND return_date IS NULL) as borrowed_copies
            FROM books b
            LEFT JOIN book_categories c ON b.category_id = c.id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filters['search'])) {
            $query .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
            $search = "%{$filters['search']}%";
            $params = array_merge($params, [$search, $search, $search]);
        }
        
        if (!empty($filters['category_id'])) {
            $query .= " AND b.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (isset($filters['available']) && $filters['available']) {
            $query .= " AND b.available_copies > 0";
        }
        
        $query .= " ORDER BY b.title ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function loanBook($data) {
        $this->db->beginTransaction();
        
        try {
            // Vérifier la disponibilité
            $stmt = $this->db->prepare("
                SELECT available_copies 
                FROM books WHERE id = ?
            ");
            $stmt->execute([$data['book_id']]);
            $book = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($book['available_copies'] <= 0) {
                throw new \Exception('No copies available');
            }
            
            // Trouver un exemplaire disponible
            $stmt = $this->db->prepare("
                SELECT id FROM book_copies
                WHERE book_id = ? AND status = 'available'
                LIMIT 1
            ");
            $stmt->execute([$data['book_id']]);
            $copy = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Créer l'emprunt
            $stmt = $this->db->prepare("
                INSERT INTO book_loans (
                    book_id, copy_id, borrower_id,
                    loan_date, due_date, notes
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['book_id'],
                $copy['id'],
                $data['borrower_id'],
                $data['loan_date'],
                $data['due_date'],
                $data['notes'] ?? null
            ]);
            
            // Mettre à jour le statut de l'exemplaire
            $stmt = $this->db->prepare("
                UPDATE book_copies 
                SET status = 'borrowed'
                WHERE id = ?
            ");
            $stmt->execute([$copy['id']]);
            
            // Mettre à jour le nombre d'exemplaires disponibles
            $stmt = $this->db->prepare("
                UPDATE books 
                SET available_copies = available_copies - 1
                WHERE id = ?
            ");
            $stmt->execute([$data['book_id']]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function returnBook($loanId) {
        $this->db->beginTransaction();
        
        try {
            // Récupérer les informations du prêt
            $stmt = $this->db->prepare("
                SELECT book_id, copy_id 
                FROM book_loans 
                WHERE id = ?
            ");
            $stmt->execute([$loanId]);
            $loan = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Marquer le prêt comme retourné
            $stmt = $this->db->prepare("
                UPDATE book_loans 
                SET return_date = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$loanId]);
            
            // Mettre à jour le statut de l'exemplaire
            $stmt = $this->db->prepare("
                UPDATE book_copies 
                SET status = 'available'
                WHERE id = ?
            ");
            $stmt->execute([$loan['copy_id']]);
            
            // Mettre à jour le nombre d'exemplaires disponibles
            $stmt = $this->db->prepare("
                UPDATE books 
                SET available_copies = available_copies + 1
                WHERE id = ?
            ");
            $stmt->execute([$loan['book_id']]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUserLoans($userId) {
        $stmt = $this->db->prepare("
            SELECT bl.*, b.title, b.author
            FROM book_loans bl
            JOIN books b ON bl.book_id = b.id
            WHERE bl.borrower_id = ?
            ORDER BY bl.loan_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getOverdueLoans() {
        $stmt = $this->db->prepare("
            SELECT bl.*, b.title, u.name as borrower_name
            FROM book_loans bl
            JOIN books b ON bl.book_id = b.id
            JOIN users u ON bl.borrower_id = u.id
            WHERE bl.due_date < CURRENT_DATE
            AND bl.return_date IS NULL
            ORDER BY bl.due_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
