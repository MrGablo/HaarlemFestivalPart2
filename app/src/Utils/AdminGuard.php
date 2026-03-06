<?php

declare(strict_types=1);

namespace App\Utils;

use App\Repositories\UserRepository;

final class AdminGuard
{
    /**
     * Ensures:
     *  - session started
     *  - user logged in
     *  - user role is ADMIN (or whatever your enum stores)
     *
     * If not, it sends 403 and exits.
     */
    public static function requireAdmin(bool $redirectToLogin = false): void
    {
        Session::ensureStarted();

        $auth = AuthSessionData::read();
        if ($auth !== null && !empty($auth['userId'])) {
            $role = strtolower((string)($auth['userRole'] ?? ''));
            if ($role === 'admin') {
                return;
            }

            self::deny($redirectToLogin);
        }

        // Legacy fallback for older session format that stores user_id only.
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            self::deny($redirectToLogin);
        }

        $repo = new UserRepository();
        $user = $repo->getUserById($userId);

        if ($user === null) {
            self::deny($redirectToLogin);
        }

        // Adjust this to match YOUR enum values / property name.
        // Examples: $user->role, $user->roleId, $user->userRole, etc.
        $role = strtolower((string)($user->role ?? ''));

        if ($role !== 'admin') {
            self::deny($redirectToLogin);
        }
    }

    private static function deny(bool $redirectToLogin): void
    {
        if ($redirectToLogin) {
            header('Location: /login', true, 302);
            exit;
        }

            http_response_code(403);
            echo 'Forbidden';
            exit;
    }
}