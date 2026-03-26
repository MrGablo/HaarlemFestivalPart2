<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\UserModel;
use App\Models\UserRole;
use App\Cms\Services\CmsUserService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSUserController
{
    private CmsUserService $service;

    public function __construct()
    {
        $this->service = new CmsUserService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $search = trim((string)($_GET['search'] ?? ''));
        $roleFilter = trim((string)($_GET['role'] ?? ''));
        $sortColumn = trim((string)($_GET['sort'] ?? 'name'));
        $sortDirection = trim((string)($_GET['dir'] ?? 'ASC'));

        $users = $this->service->searchUsers($search, $roleFilter, $sortColumn, $sortDirection);
        $roles = $this->service->getAllRoles();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/users_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $roles = $this->service->getAllRoles();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/user_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $user = new UserModel();
            $this->fillUserFromPost($user);

            $this->service->createUser($user);
            Flash::setSuccess('User created successfully.');

            header('Location: /cms/users', true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);

            // Strip passwords and tokens before flashing old input for security reasons
            $old = $_POST;
            unset($old['password'], $old['password_confirmation'], $old['confirm_password'], $old['_csrf'], $old['csrf_token']);
            Flash::setOld($old);

            header('Location: /cms/users/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $user = $this->getUserOrRedirect($id);
        $roles = $this->service->getAllRoles();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/user_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $user = $this->getUserOrRedirect($id);

        try {
            Csrf::assertPost();

            $this->fillUserFromPost($user, true);
            $this->service->updateUser($id, $user);

            Flash::setSuccess('User updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/users', true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->service->deleteUser($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'User could not be deleted.']);
            } else {
                Flash::setSuccess('User deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/users', true, 302);
        exit;
    }

    private function getUserOrRedirect(int $id): UserModel
    {
        $user = $this->service->findUser($id);
        if ($user !== null) {
            return $user;
        }

        Flash::setErrors(['general' => 'User not found.']);
        header('Location: /cms/users', true, 302);
        exit;
    }

    private function fillUserFromPost(UserModel $user, bool $isUpdate = false): void
    {
        $user->firstName = $this->requestText('first_name', 'First name');
        $user->lastName = $this->requestText('last_name', 'Last name');
        $user->email = $this->requestEmail('email', 'Email');
        $user->phoneNumber = trim((string)($_POST['phone_number'] ?? ''));
        $user->role = UserRole::from($this->requestRole('role', 'Role'));

        if (!$isUpdate) {
            $user->userName = $this->requestText('user_name', 'Username');
            $password = $this->requestText('password', 'Password');
            if (strlen($password) < 8) {
                throw new \RuntimeException('Password must be at least 8 characters long.');
            }
            $user->password_hash = password_hash($password, PASSWORD_BCRYPT);
        } else {
            $password = trim((string)($_POST['password'] ?? ''));
            if ($password !== '') {
                if (strlen($password) < 8) {
                    throw new \RuntimeException('New password must be at least 8 characters long.');
                }
                $user->password_hash = password_hash($password, PASSWORD_BCRYPT);
            }
        }
    }

    private function requestText(string $key, string $label): string
    {
        $raw = trim((string)($_POST[$key] ?? ''));
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $raw;
    }

    private function requestEmail(string $key, string $label): string
    {
        $email = trim((string)($_POST[$key] ?? ''));
        if ($email === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException($label . ' is not valid.');
        }

        return $email;
    }

    private function requestRole(string $key, string $label): string
    {
        $role = trim((string)($_POST[$key] ?? ''));
        if ($role === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        try {
            UserRole::from($role);
            return $role;
        } catch (\Throwable $e) {
            throw new \RuntimeException($label . ' is not valid.');
        }
    }
}