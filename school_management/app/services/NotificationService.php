<?php
namespace App\Services;

class NotificationService {
    private $db;
    private $mailer;
    private $pushService;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $this->pushService = new \App\Services\PushNotificationService();
        
        // Configuration du mailer
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'];
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
        $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['SMTP_PORT'];
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    }

    public function sendEmail($to, $subject, $body, $attachments = []) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            foreach ($attachments as $attachment) {
                $this->mailer->addAttachment(
                    $attachment['path'],
                    $attachment['name'] ?? ''
                );
            }

            $sent = $this->mailer->send();
            
            // Enregistrer la notification
            $this->logNotification([
                'type' => 'email',
                'recipient' => $to,
                'subject' => $subject,
                'content' => $body,
                'status' => $sent ? 'sent' : 'failed'
            ]);

            return $sent;
        } catch (\Exception $e) {
            $this->logNotification([
                'type' => 'email',
                'recipient' => $to,
                'subject' => $subject,
                'content' => $body,
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function sendPushNotification($user_id, $title, $message, $data = []) {
        try {
            // Récupérer les tokens de l'utilisateur
            $sql = "SELECT device_token, platform 
                    FROM user_devices 
                    WHERE user_id = ? 
                    AND active = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);
            $devices = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($devices as $device) {
                $result = $this->pushService->send(
                    $device['device_token'],
                    $device['platform'],
                    $title,
                    $message,
                    $data
                );

                $this->logNotification([
                    'type' => 'push',
                    'recipient' => $user_id,
                    'subject' => $title,
                    'content' => $message,
                    'device_token' => $device['device_token'],
                    'platform' => $device['platform'],
                    'status' => $result ? 'sent' : 'failed'
                ]);
            }

            return true;
        } catch (\Exception $e) {
            $this->logNotification([
                'type' => 'push',
                'recipient' => $user_id,
                'subject' => $title,
                'content' => $message,
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function sendSMS($phone, $message) {
        try {
            // Implémentation avec un service SMS (Twilio, Nexmo, etc.)
            $smsService = new \App\Services\SMSService();
            $result = $smsService->send($phone, $message);

            $this->logNotification([
                'type' => 'sms',
                'recipient' => $phone,
                'content' => $message,
                'status' => $result ? 'sent' : 'failed'
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logNotification([
                'type' => 'sms',
                'recipient' => $phone,
                'content' => $message,
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function broadcastNotification($role, $title, $message, $data = []) {
        $sql = "SELECT id, email, phone 
                FROM users 
                WHERE role = ?
                AND active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role]);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            // Envoyer email
            $this->sendEmail(
                $user['email'],
                $title,
                $message
            );

            // Envoyer notification push
            $this->sendPushNotification(
                $user['id'],
                $title,
                $message,
                $data
            );

            // Envoyer SMS si urgent
            if (!empty($data['urgent']) && $user['phone']) {
                $this->sendSMS(
                    $user['phone'],
                    $message
                );
            }
        }
    }

    private function logNotification($data) {
        $sql = "INSERT INTO notification_logs (
            type, recipient, subject, content,
            status, error, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['type'],
            $data['recipient'],
            $data['subject'] ?? null,
            $data['content'],
            $data['status'],
            $data['error'] ?? null
        ]);
    }

    public function getNotificationHistory($user_id, $limit = 50) {
        $sql = "SELECT * FROM notification_logs
                WHERE recipient = ?
                ORDER BY created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
