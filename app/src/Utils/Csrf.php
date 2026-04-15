<?php

namespace App\Utils;

final class Csrf
{
    public static function token(string $sessionKey = 'cms_csrf_token'): string
    {
        Session::ensureStarted();
        $token = (string)($_SESSION[$sessionKey] ?? '');
        if ($token !== '') return $token;
        // Convert to a hexadecimal string using bin2hex
        $token = bin2hex(random_bytes(32));
        $_SESSION[$sessionKey] = $token;
        return $token;
    }

    public static function assertPost(string $sessionKey = 'cms_csrf_token', string $field = '_csrf'): void
    {
        Session::ensureStarted();
        $sessionToken = (string)($_SESSION[$sessionKey] ?? '');
        $postedToken = (string)($_POST[$field] ?? '');

        if ($sessionToken === '' || $postedToken === '' || !hash_equals($sessionToken, $postedToken)) {
            throw new CsrfException('Invalid form token. Please refresh and try again.');
        }
    }
}
