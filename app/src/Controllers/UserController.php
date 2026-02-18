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

    public function showManageAccount(): void
    {
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            header('Location: /login', true, 302);
            exit;
        }

        $user = $this->userService->getAccountById($userId);
        if ($user === null) {
            $_SESSION['errors'] = ['general' => 'User not found.'];
            header('Location: /login', true, 302);
            exit;
        }

        $errors = $_SESSION['errors'] ?? [];
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $old = $_SESSION['old'] ?? [];

        unset($_SESSION['errors'], $_SESSION['flash_success'], $_SESSION['old']);

        require __DIR__ . '/../Views/account/manage.php';
    }

    public function updateAccountForm(): void
    {
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            header('Location: /login', true, 302);
            exit;
        }

        $_SESSION['old'] = [
            'firstName' => $_POST['firstName'] ?? '',
            'lastName' => $_POST['lastName'] ?? '',
            'email' => $_POST['email'] ?? '',
            'profilePicturePath' => $_POST['profilePicturePath'] ?? '',
        ];

        try {
            $this->userService->updateAccount($userId, $_POST, $_FILES);
            $updatedUser = $this->userService->getAccountById($userId);
            if ($updatedUser !== null) {
                $_SESSION['profile_picture_path'] = $updatedUser->profilePicturePath ?: '/assets/img/default-user.png';
            }
            $_SESSION['flash_success'] = 'Account updated successfully.';
            unset($_SESSION['old']);
        } catch (\Throwable $e) {
            $_SESSION['errors'] = ['general' => $e->getMessage()];
        }

        header('Location: /account/manage', true, 302);
        exit;
    }

    public function deleteAccountForm(): void
    {
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            header('Location: /login', true, 302);
            exit;
        }

        try {
            $this->userService->deleteAccount($userId, $_POST);

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

            session_start();
            $_SESSION['flash_success'] = 'Account deleted successfully.';
            header('Location: /login', true, 302);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['errors'] = ['general' => $e->getMessage()];
            header('Location: /account/manage', true, 302);
            exit;
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
