<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserRole;
use App\Repositories\UserRepository;

class CmsUserService
{
    public function __construct(
        private UserRepository $users = new UserRepository()
    ) {}

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
                $matchSearch = str_contains(strtolower($user->firstName), $search) ||
                    str_contains(strtolower($user->lastName), $search) ||
                    str_contains(strtolower($user->userName), $search) ||
                    str_contains(strtolower($user->email), $search);

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
                'created_at' => $a->created_at ?? '',
                'email' => strtolower($a->email),
                'first_name' => strtolower($a->firstName),
                'last_name' => strtolower($a->lastName),
                'user_name' => strtolower($a->userName),
                default => strtolower($a->firstName . ' ' . $a->lastName), // 'name'
            };

            $valB = match ($sortColumn) {
                'created_at' => $b->created_at ?? '',
                'email' => strtolower($b->email),
                'first_name' => strtolower($b->firstName),
                'last_name' => strtolower($b->lastName),
                'user_name' => strtolower($b->userName),
                default => strtolower($b->firstName . ' ' . $b->lastName), // 'name'
            };

            return ($valA <=> $valB) * $directionMult;
        });

        return $users;
    }
}
