<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IPasswordResetRepository;
use PDO;

class PasswordResetRepository extends Repository implements IPasswordResetRepository
{
    public function createResetToken(int $userId, string $tokenHash, string $expiresAt): void
    {
        $this->ensureTableExists();
        $this->deleteActiveTokensByUser($userId);

        $sql = "INSERT INTO password_resets (user_id, token_hash, expires_at)
                VALUES (:user_id, :token_hash, :expires_at)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt,
        ]);
    }

    public function findValidToken(string $tokenHash): ?array
    {
        $this->ensureTableExists();

        $sql = "SELECT id, user_id, token_hash, expires_at, used_at
                FROM password_resets
                WHERE token_hash = :token_hash
                  AND used_at IS NULL
                  AND expires_at > NOW()
                LIMIT 1";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':token_hash' => $tokenHash]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markTokenUsed(int $id): void
    {
        $this->ensureTableExists();

        $sql = "UPDATE password_resets
                SET used_at = NOW()
                WHERE id = :id";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function deleteActiveTokensByUser(int $userId): void
    {
        $this->ensureTableExists();

        $sql = "DELETE FROM password_resets
                WHERE user_id = :user_id
                  AND used_at IS NULL";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
    }

    private function ensureTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS password_resets (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token_hash CHAR(64) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    used_at DATETIME NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_password_resets_token_hash (token_hash),
                    KEY idx_password_resets_user_id (user_id),
                    KEY idx_password_resets_expires_at (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->getConnection()->exec($sql);
    }
}
