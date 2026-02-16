<?php
namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();

        // Ensure session is available for errors/flash/user_id
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showLogin(): void
    {
        // Pull errors/flash from session (one-time)
        $errors = $_SESSION['errors'] ?? [];
        $flashSuccess = $_SESSION['flash_success'] ?? null;

        unset($_SESSION['errors'], $_SESSION['flash_success']);

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function showRegister(): void
    {
        // Pull errors/old values from session (one-time)
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old']);

        require __DIR__ . '/../Views/auth/register.php';
    }

    public function register(): void
    {
        // Keep old input (except password) so you can re-fill the form on error
        $_SESSION['old'] = [
            'firstName' => $_POST['firstName'] ?? '',
            'lastName'  => $_POST['lastName'] ?? '',
            'userName'  => $_POST['userName'] ?? '',
            'email'     => $_POST['email'] ?? '',
            'phoneNumber' => $_POST['phoneNumber'] ?? '',
        ];

        try {
            // Your existing service signature: register($_POST)
            $userId = $this->authService->register($_POST);

            // Option A: after registering, go to login page with success message
            $_SESSION['flash_success'] = 'Account created. Please log in.';
            unset($_SESSION['old']);

            header('Location: /login', true, 302);
            exit;
        } catch (\Throwable $e) {
            // Store error(s) and redirect back to form (PRG pattern)
            $_SESSION['errors'] = [
                'general' => $e->getMessage()
            ];

            header('Location: /register', true, 302);
            exit;
        }
    }

    public function login(): void
    {
        // Placeholder (until you implement real login in AuthService)
        $_SESSION['errors'] = [
            'general' => 'Login not implemented yet.'
        ];

        header('Location: /login', true, 302);
        exit;
    }

    public function logout(): void
    {
        // Minimal logout without needing to change other code
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        header('Location: /login', true, 302);
        exit;
    }
}
