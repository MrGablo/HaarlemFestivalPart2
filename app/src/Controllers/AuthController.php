<?php
namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }
    
    public function showLogin(): void
    {
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function showRegister(): void
    {
        // simplest: include a PHP view
        require __DIR__ . '/../Views/register.php';
    }

    public function register(): void
    {
        try {
            $result = $this->authService->register($_POST);

            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode([
                'message' => 'User created',
                'user_id' => $result,
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }
    public function login(): void
    {
        // We’ll implement real login later; placeholder
        $error = "Login not implemented yet.";
        require __DIR__ . '/../Views/auth/login.php';
    }
}
