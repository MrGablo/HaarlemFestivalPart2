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
    public static function requireAdmin(): void
    {
        Session::ensureStarted();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        $repo = new UserRepository();
        $user = $repo->getUserById($userId);

        if ($user === null) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        // Adjust this to match YOUR enum values / property name.
        // Examples: $user->role, $user->roleId, $user->userRole, etc.
        $role = strtolower((string)($user->role ?? ''));

        if ($role !== 'admin') {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}