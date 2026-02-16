<?php
namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
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
        if (!empty($data['profilePicturePath'])) {
            return trim((string)$data['profilePicturePath']);
        }

        if (!isset($files['profilePicture']) || !is_array($files['profilePicture'])) {
            return $currentPath;
        }

        $file = $files['profilePicture'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $currentPath;
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        $originalName = (string)($file['name'] ?? 'profile-picture');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            throw new \Exception('Invalid profile picture file type.');
        }

        $fileName = 'profile_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetDir = __DIR__ . '/../../public/assets/img/profiles';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new \Exception('Unable to create profile image directory.');
        }

        $targetPath = $targetDir . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new \Exception('Failed to upload profile picture.');
        }

        return '/assets/img/profiles/' . $fileName;
    }

    private function sendAccountUpdateEmail(string $email, string $firstName): void
    {
        $subject = 'Account updated';
        $message = "Hi {$firstName},\n\nYour account details were updated successfully.";
        $headers = 'From: no-reply@haarlemfestival.local';

        @mail($email, $subject, $message, $headers);
    }
}
