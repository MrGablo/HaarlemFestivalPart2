<?php

namespace App\Repositories\Interfaces;

interface IPasswordResetRepository
{
    public function createResetToken(int $userId, string $tokenHash, string $expiresAt): void;
    public function findValidToken(string $tokenHash): ?array;
    public function markTokenUsed(int $id): void;
    public function deleteActiveTokensByUser(int $userId): void;
}
