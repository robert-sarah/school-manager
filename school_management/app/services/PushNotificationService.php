<?php
namespace App\Services;

class PushNotificationService {
    private $fcm_key;
    private $apns_key;
    
    public function __construct() {
        $this->fcm_key = $_ENV['FCM_SERVER_KEY'];
        $this->apns_key = $_ENV['APNS_KEY_PATH'];
    }
    
    public function send($device_token, $platform, $title, $message, $data = []) {
        return $platform === 'android' ? 
            $this->sendFCM($device_token, $title, $message, $data) :
            $this->sendAPNS($device_token, $title, $message, $data);
    }
    
    private function sendFCM($token, $title, $message, $data) {
        $fields = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default'
            ],
            'data' => $data
        ];

        $headers = [
            'Authorization: key=' . $this->fcm_key,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result !== false;
    }
    
    private function sendAPNS($token, $title, $message, $data) {
        $payload = [
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body' => $message
                ],
                'sound' => 'default'
            ]
        ];

        if (!empty($data)) {
            $payload['extra'] = $data;
        }

        $ctx = stream_context_create([
            'ssl' => [
                'local_cert' => $this->apns_key,
                'passphrase' => $_ENV['APNS_PASSPHRASE']
            ]
        ]);

        $fp = stream_socket_client(
            'ssl://gateway.push.apple.com:2195',
            $err,
            $errstr,
            60,
            STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
            $ctx
        );

        if (!$fp) {
            return false;
        }

        $msg = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', $token))
             . pack('n', strlen(json_encode($payload))) . json_encode($payload);

        $result = fwrite($fp, $msg, strlen($msg));
        fclose($fp);

        return $result !== false;
    }
    
    public function registerDevice($user_id, $device_token, $platform) {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO user_devices (
            user_id, device_token, platform, 
            active, created_at
        ) VALUES (?, ?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE 
            active = 1,
            updated_at = NOW()";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $user_id,
            $device_token,
            $platform
        ]);
    }
    
    public function unregisterDevice($device_token) {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $sql = "UPDATE user_devices 
                SET active = 0,
                    updated_at = NOW()
                WHERE device_token = ?";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([$device_token]);
    }
}
?>
