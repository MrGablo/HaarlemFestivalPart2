<?php
namespace App\Utils;

class Flash
{
    public static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setSuccess(string $message): void
    {
        self::ensureSession();
        $_SESSION['flash_success'] = $message;
    }

    public static function getSuccess(): ?string
    {
        self::ensureSession();
        $v = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        return $v;
    }

    public static function setErrors(array $errors): void
    {
        self::ensureSession();
        $_SESSION['errors'] = $errors;
    }

    public static function getErrors(): array
    {
        self::ensureSession();
        $v = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $v;
    }

    public static function setOld(array $old): void
    {
        self::ensureSession();
        $_SESSION['old'] = $old;
    }

    public static function getOld(): array
    {
        self::ensureSession();
        $v = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        return $v;
    }
}
