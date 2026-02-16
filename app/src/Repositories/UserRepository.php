<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\UserModel;
use App\Models\UserRole;
use App\Repositories\Interfaces\IUserRepository;
use PDO;

class UserRepository extends Repository implements IUserRepository
{
    public function createUser(UserModel $user): int
    {
        $sql = "INSERT INTO users
                (first_name, last_name, user_name, email, password_hash, phone_number, role, profile_picture_path)
                VALUES
                (:first_name, :last_name, :user_name, :email, :password_hash, :phone_number, :role, :profile_picture_path)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':first_name' => $user->firstName,
            ':last_name' => $user->lastName,
            ':user_name' => $user->userName,
            ':email' => $user->email,
            ':password_hash' => $user->password_hash,
            ':phone_number' => $user->phoneNumber,
            ':role' => $user->role->value,
            ':profile_picture_path' => $user->profilePicturePath,
        ]);

        return (int)$this->getConnection()->lastInsertId();
    }

    public function findByEmail(string $email): ?UserModel
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByUserName(string $userName): ?UserModel
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM users WHERE user_name = :userName");
        $stmt->execute([':userName' => $userName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function getUserById(int $id): ?UserModel
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function getAllUsers(): array
    {
        $stmt = $this->getConnection()->query("SELECT * FROM users");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapRowToUser($row), $rows);
    }

    public function updateUser(int $id, UserModel $user): bool
    {
        $sql = "UPDATE users
                SET first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    password_hash = :password_hash,
                    profile_picture_path = :profile_picture_path,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->getConnection()->prepare($sql);

        return $stmt->execute([
            ':first_name' => $user->firstName,
            ':last_name' => $user->lastName,
            ':email' => $user->email,
            ':password_hash' => $user->password_hash,
            ':profile_picture_path' => $user->profilePicturePath,
            ':id' => $id,
        ]);
    }

    public function deleteUser(int $id): bool
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function mapRowToUser(array $row): UserModel
    {
        $user = new UserModel();
        $user->id = (int)$row['id'];
        $user->firstName = $row['first_name'];
        $user->lastName = $row['last_name'];
        $user->userName = $row['user_name'];
        $user->email = $row['email'];
        $user->password_hash = $row['password_hash'];
        $user->phoneNumber = $row['phone_number'] ?? '';
        $user->role = UserRole::from($row['role']);
        $user->created_at = $row['created_at'] ?? null;
        $user->updated_at = $row['updated_at'] ?? null;
        $user->profilePicturePath = $row['profile_picture_path'] ?? null;
        return $user;
    }
}
