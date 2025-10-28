<?php
namespace App\Core;

class Flash {
    public function set(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function success(string $message): void {
        $this->set('success', $message);
    }

    public function error(string $message): void {
        $this->set('error', $message);
    }

    public function info(string $message): void {
        $this->set('info', $message);
    }

    public function get(): ?array {
        if (!isset($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}