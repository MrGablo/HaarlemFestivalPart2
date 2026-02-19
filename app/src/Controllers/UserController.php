<?php

namespace App\Controllers;

use App\Config;
use App\Services\UserService;
use App\Utils\AuthSessionData;
use App\Utils\Flash;
use App\Utils\Session;

class UserController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();

        Session::ensureStarted();
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

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $old = Flash::getOld();

        require __DIR__ . '/../Views/account/manage.php';
    }

    public function updateAccountForm(): void //rename tp upadte user, smae validation as register, no old session handleing - use same routes for post and get methods
    {
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            header('Location: /login', true, 302);
            exit;
        }

        Flash::setOld([
            'firstName' => $_POST['firstName'] ?? '',
            'lastName' => $_POST['lastName'] ?? '',
            'email' => $_POST['email'] ?? '',
            'profilePicturePath' => $_POST['profilePicturePath'] ?? '',
        ]);
        error_log('FILES profilePicture: ' . json_encode($_FILES['profilePicture'] ?? null));
        try {
            $this->userService->updateAccount($userId, $_POST, $_FILES);
            $updatedUser = $this->userService->getAccountById($userId);
            if ($updatedUser !== null) {
                AuthSessionData::store($updatedUser);
            }
            Flash::setSuccess('Account updated successfully.');
            // clear old explicitly
            Flash::setOld([]);
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
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

            Session::ensureStarted();
            Flash::setSuccess('Account deleted successfully.');
            header('Location: /login', true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
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
        $auth = AuthSessionData::read();
        if ($auth === null) {
            return null;
        }

        return (int)$auth['userId'];
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
