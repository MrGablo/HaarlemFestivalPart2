<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserRole;
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

    public function searchUsers(string $search = '', string $roleFilter = '', string $sortColumn = 'name', string $sortDirection = 'ASC'): array
    {
        $users = $this->users->getAllUsers();

        $users = $this->filterUsers($users, $search, $roleFilter);
        $users = $this->sortUsers($users, $sortColumn, $sortDirection);

        return $users;
    }

    public function findUser(int $id): ?UserModel
    {
        return $this->users->getUserById($id);
    }

    public function createUser(UserModel $user): int
    {
        $existingEmail = $this->users->findByEmail($user->email);
        if ($existingEmail !== null) {
            throw new \RuntimeException('A user with this email already exists.');
        }

        $existingUsername = $this->users->findByUserName($user->userName);
        if ($existingUsername !== null) {
            throw new \RuntimeException('This username is already taken. Please choose another one.');
        }

        return $this->users->createUser($user);
    }

    public function updateUser(int $id, UserModel $user): bool
    {
        $existingEmail = $this->users->findByEmail($user->email);
        if ($existingEmail !== null && $existingEmail->id !== $id) {
            throw new \RuntimeException('A user with this email already exists.');
        }

        return $this->users->updateUser($id, $user);
    }

    public function deleteUser(int $id): bool
    {
        return $this->users->deleteUser($id);
    }

    public function getAllRoles(): array
    {
        return array_map(fn(UserRole $role) => $role->value, UserRole::cases());
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

    private function filterUsers(array $users, string $search, string $roleFilter): array
    {
        if ($search === '' && $roleFilter === '') {
            return $users;
        }

        $search = strtolower($search);
        return array_filter($users, function (UserModel $user) use ($search, $roleFilter) {
            if ($roleFilter !== '' && $user->role->value !== $roleFilter) {
                return false;
            }

            if ($search !== '') {
                $matchSearch = str_contains(strtolower($user->firstName), $search)
                    || str_contains(strtolower($user->lastName), $search)
                    || str_contains(strtolower($user->userName), $search)
                    || str_contains(strtolower($user->email), $search);

                if (!$matchSearch) {
                    return false;
                }
            }

            return true;
        });
    }

    private function sortUsers(array $users, string $sortColumn, string $sortDirection): array
    {
        $directionMult = strtoupper($sortDirection) === 'DESC' ? -1 : 1;

        usort($users, function (UserModel $a, UserModel $b) use ($sortColumn, $directionMult) {
            $valA = match ($sortColumn) {
                'id' => $a->id,
                'created_at' => $a->created_at ?? '',
                'email' => strtolower($a->email),
                'first_name' => strtolower($a->firstName),
                'last_name' => strtolower($a->lastName),
                'user_name' => strtolower($a->userName),
                default => strtolower($a->firstName . ' ' . $a->lastName),
            };

            $valB = match ($sortColumn) {
                'id' => $b->id,
                'created_at' => $b->created_at ?? '',
                'email' => strtolower($b->email),
                'first_name' => strtolower($b->firstName),
                'last_name' => strtolower($b->lastName),
                'user_name' => strtolower($b->userName),
                default => strtolower($b->firstName . ' ' . $b->lastName),
            };

            return ($valA <=> $valB) * $directionMult;
        });

        return $users;
    }
}
