<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Models\UserModel;
use App\Services\UserService;

class CmsUserService
{
    public function __construct(
        private UserService $service = new UserService()
    ) {}

    public function searchUsers(string $search = '', string $roleFilter = '', string $sortColumn = 'name', string $sortDirection = 'ASC'): array
    {
        return $this->service->searchUsers($search, $roleFilter, $sortColumn, $sortDirection);
    }

    public function findUser(int $id): ?UserModel
    {
        return $this->service->findUser($id);
    }

    public function createUser(UserModel $user): int
    {
        return $this->service->createUser($user);
    }

    public function updateUser(int $id, UserModel $user): bool
    {
        return $this->service->updateUser($id, $user);
    }

    public function deleteUser(int $id): bool
    {
        return $this->service->deleteUser($id);
    }

    public function getAllRoles(): array
    {
        return $this->service->getAllRoles();
    }
}
