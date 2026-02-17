<?php
namespace App\Controllers;

use App\Services\UserService;

class UserController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function updateAccount(): void
    {
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $data = $this->getRequestData();
            $this->userService->updateAccount($userId, $data, $_FILES);
            $this->json(['message' => 'Account updated successfully.'], 200);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function deleteAccount(): void
    {
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $data = $this->getRequestData();
            $this->userService->deleteAccount($userId, $data);

            $_SESSION = [];
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

            $this->json(['message' => 'Account deleted successfully.'], 200);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    private function getAuthenticatedUserId(): ?int
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return (int)$_SESSION['user_id'];
    }

    private function getRequestData(): array
    {
        $data = $_POST;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $rawBody = file_get_contents('php://input');
            $json = json_decode($rawBody ?: '{}', true);
            if (is_array($json)) {
                $data = $json;
            }
        }

        return $data;
    }

    private function json(array $payload, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
    }
}
