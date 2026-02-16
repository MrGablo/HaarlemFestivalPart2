<?php
namespace App\Repositories\Interfaces;

use App\Models\UserModel;

interface IUserRepository
{
    public function getUserById(int $id): ?UserModel;
    public function getAllUsers(): array;

    public function createUser(UserModel $user): int;

    public function updateUser(int $id, UserModel $user): bool;
    public function deleteUser(int $id): bool;

    public function findByEmail(string $email): ?UserModel;
    public function findByUserName(string $userName): ?UserModel;
}
