<?php
namespace App\Models;

enum UserRole: string {
    case ADMIN = 'admin';
    case USER = 'user';
}
class UserModel
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public string $userName;
    public string $email;
    public string $password_hash;
    public string $phoneNumber;
    public UserRole $role;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?string $profilePicturePath = null;

    public function __construct() {}
}
