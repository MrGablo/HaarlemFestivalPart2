<?php
namespace App\Utils;

class Session
{
    public static function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function regenerateId(bool $deleteOld = true): void
    {
        self::ensureStarted();
        session_regenerate_id($deleteOld);
    }

    public static function destroy(): void
    {
        if (!self::isStarted()) {
            return;
        }

        // Clear session array
        $_SESSION = [];

        // Delete session cookie if used
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function restart(): void
    {
        self::destroy();
        self::ensureStarted();
    }
}
