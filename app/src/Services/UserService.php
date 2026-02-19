<?php

namespace App\Services;

use App\Models\UserModel;
use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $users; //change name to user repository 

    public function __construct()
    {
        $this->users = new UserRepository();
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

        if ($newPassword !== '') {
            $existingUser->password_hash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $existingUser->profilePicturePath = $this->resolveProfilePicturePath(
            $data,
            $files,
            $existingUser->profilePicturePath
        );

        $this->users->updateUser($userId, $existingUser);

        $this->sendAccountUpdateEmail($existingUser->email, $existingUser->firstName);
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
        // 1) Prefer uploaded file if present
        if (isset($files['profilePicture']) && is_array($files['profilePicture'])) {
            $file = $files['profilePicture'];

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $tmpName = (string)($file['tmp_name'] ?? '');

                // Extra safety: ensure it is a real uploaded file
                if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                    throw new \Exception('Upload failed (invalid temp file).');
                }

                $originalName = (string)($file['name'] ?? 'profile-picture');
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    throw new \Exception('Invalid profile picture file type.');
                }

                // Ensure target directory exists
                $targetDir = __DIR__ . '/../../public/assets/img/profiles';
                if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                    throw new \Exception('Unable to create profile image directory.');
                }

                // 2) Deterministic filename based on file contents (prevents duplicates)
                $hash = hash_file('sha256', $tmpName);
                $fileName = $hash . '.' . $extension;

                $targetPath = $targetDir . '/' . $fileName;

                // If this exact image already exists, do not store a duplicate
                if (!file_exists($targetPath)) {
                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        throw new \Exception('Failed to upload profile picture.');
                    }
                }

                // Store path for DB / rendering
                return '/assets/img/profiles/' . $fileName;
            }
        }

        // 3) Otherwise allow URL/path override if user typed one
        if (!empty($data['profilePicturePath'])) {
            return trim((string)$data['profilePicturePath']);
        }

        // 4) Otherwise keep current
        return $currentPath;
    }



    private function sendAccountUpdateEmail(string $email, string $firstName): void
    {
        $subject = 'Account updated';
        $message = "Hi {$firstName},\n\nYour account details were updated successfully.";
        $headers = 'From: no-reply@haarlemfestival.local';

        @mail($email, $subject, $message, $headers);
    }
}
