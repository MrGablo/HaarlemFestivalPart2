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
        $stmt = $this->getConnection()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByUserName(string $userName): ?UserModel
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM users WHERE user_name = :u LIMIT 1");
        $stmt->execute([':u' => $userName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function getUserById(int $id): ?UserModel { return null; } // implement later
    public function getAllUsers(): array { return []; } // implement later
    public function updateUser(int $id, UserModel $user): bool { return false; } // implement later
    public function deleteUser(int $id): bool { return false; } // implement later

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
        return $u;
    }
}
