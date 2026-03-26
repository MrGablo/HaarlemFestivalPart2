<?php

namespace App\Services;

use App\Models\UserModel;
use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $users; //change name to user repository 
    private EmailService $emails;
    private UploadService $uploads;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->emails = new EmailService();
        $this->uploads = new UploadService();
    }

    public function getAccountById(int $userId): ?UserModel
    {
        return $this->users->getUserById($userId);
    }

    public function updateAccount(int $userId, array $data, array $files = []): void
    {
        $existingUser = $this->users->getUserById($userId);
        if ($existingUser === null) {
            throw new \Exception('User not found.');
        }

        $firstName = trim((string)($data['firstName'] ?? ''));
        $lastName = trim((string)($data['lastName'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $newPassword = (string)($data['password'] ?? '');
        $hasPasswordReset = $newPassword !== '';

        if ($firstName === '' || $lastName === '' || $email === '') {
            throw new \Exception('firstName, lastName and email are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format.');
        }

        $emailOwner = $this->users->findByEmail($email);
        if ($emailOwner !== null && $emailOwner->id !== $userId) {
            throw new \Exception('Email already in use.');
        }

        if ($newPassword !== '' && strlen($newPassword) < 8) {
            throw new \Exception('Password must be at least 8 characters.');
        }

        $existingUser->firstName = $firstName;
        $existingUser->lastName = $lastName;
        $existingUser->email = $email;

        if ($hasPasswordReset) {
            $existingUser->password_hash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $existingUser->profilePicturePath = $this->resolveProfilePicturePath(
            $data,
            $files,
            $existingUser->profilePicturePath
        );

        $this->users->updateUser($userId, $existingUser);

        if ($hasPasswordReset) {
            $sent = $this->emails->sendPasswordResetConfirmation($existingUser->email, $existingUser->firstName);
            if (!$sent) {
                error_log('Password reset confirmation email not sent for user id ' . $userId . ' (' . $existingUser->email . ')');
            }
            return;
        }

        $sent = $this->emails->sendAccountUpdateConfirmation($existingUser->email, $existingUser->firstName);
        if (!$sent) {
            error_log('Account update confirmation email not sent for user id ' . $userId . ' (' . $existingUser->email . ')');
        }
    }

    public function deleteAccount(int $userId, array $data): void
    {
        if (!$this->isDeleteConfirmed($data['confirmDelete'] ?? null)) {
            throw new \Exception('Delete confirmation is required.');
        }

        $existingUser = $this->users->getUserById($userId);
        if ($existingUser === null) {
            throw new \Exception('User not found.');
        }

        $this->users->deleteUser($userId);
    }

    private function isDeleteConfirmed(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtoupper(trim((string)$value));
        return in_array($normalized, ['1', 'TRUE', 'YES', 'CONFIRM', 'DELETE'], true);
    }

    private function resolveProfilePicturePath(array $data, array $files, ?string $currentPath): ?string
    {
        if (isset($files['profilePicture']) && is_array($files['profilePicture'])) {
            $file = $files['profilePicture'];

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                return '/' . $this->uploads->storeImage($file, 'profiles', 'users', null, false, $currentPath);
            }
        }

        return $currentPath;
    }
}
