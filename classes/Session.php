<?php

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public static function setFlash(string $type, string $message): void {
        self::start();
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public static function getFlash(): ?array {
        self::start();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function hasFlash(): bool {
        self::start();
        return isset($_SESSION['flash']);
    }
}
