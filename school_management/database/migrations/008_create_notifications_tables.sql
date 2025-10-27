-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    link VARCHAR(255),
    created_by INT NOT NULL,
    is_broadcast BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create notification_recipients table
CREATE TABLE IF NOT EXISTS notification_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    recipient_id INT NOT NULL,
    recipient_type ENUM('user', 'role', 'class') NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_notification_created ON notifications(created_at);
CREATE INDEX idx_notification_broadcast ON notifications(is_broadcast);
CREATE INDEX idx_notification_recipient ON notification_recipients(recipient_id, recipient_type);
CREATE INDEX idx_notification_read ON notification_recipients(read_at);
