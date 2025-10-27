CREATE TABLE IF NOT EXISTS `book_categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `books` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `author` VARCHAR(255) NOT NULL,
    `isbn` VARCHAR(13),
    `category_id` INT,
    `publication_year` INT,
    `publisher` VARCHAR(255),
    `edition` VARCHAR(50),
    `total_copies` INT NOT NULL DEFAULT 1,
    `available_copies` INT NOT NULL DEFAULT 1,
    `shelf_location` VARCHAR(50),
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES book_categories(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `book_copies` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `book_id` INT NOT NULL,
    `copy_number` INT NOT NULL,
    `status` ENUM('available', 'borrowed', 'maintenance', 'lost') DEFAULT 'available',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    UNIQUE KEY `unique_copy` (book_id, copy_number)
);

CREATE TABLE IF NOT EXISTS `book_loans` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `book_id` INT NOT NULL,
    `copy_id` INT NOT NULL,
    `borrower_id` INT NOT NULL,
    `loan_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `return_date` DATE,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE RESTRICT,
    FOREIGN KEY (copy_id) REFERENCES book_copies(id)
        ON DELETE RESTRICT,
    FOREIGN KEY (borrower_id) REFERENCES users(id)
        ON DELETE RESTRICT
);

-- Création des index pour améliorer les performances
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_books_isbn ON books(isbn);
CREATE INDEX idx_book_loans_dates ON book_loans(loan_date, due_date, return_date);
CREATE INDEX idx_book_loans_borrower ON book_loans(borrower_id);

-- Insertion de quelques catégories de base
INSERT INTO book_categories (name, description) VALUES
('Fiction', 'Romans et histoires fictives'),
('Non-Fiction', 'Livres documentaires et factuels'),
('Manuels Scolaires', 'Manuels et livres d\'étude'),
('Sciences', 'Livres scientifiques et techniques'),
('Histoire', 'Livres d\'histoire et biographies'),
('Littérature', 'Œuvres littéraires classiques');
