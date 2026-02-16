<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\UserRole;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function register(array $data): int
    {
        $firstName = trim($data['firstName'] ?? '');
        $lastName  = trim($data['lastName'] ?? '');
        $userName  = trim($data['userName'] ?? '');
        $email     = trim($data['email'] ?? '');
        $phone     = trim($data['phoneNumber'] ?? '');
        $password  = (string)($data['password'] ?? '');

        if ($firstName === '' || $lastName === '' || $userName === '' || $email === '' || $password === '') {
            throw new \Exception('Missing required fields.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format.');
        }

        if (strlen($password) < 8) {
            throw new \Exception('Password must be at least 8 characters.');
        }

        if ($this->users->findByEmail($email)) {
            throw new \Exception('Email already in use.');
        }

        if ($this->users->findByUserName($userName)) {
            throw new \Exception('Username already in use.');
        }

        $user = new UserModel();
        $user->firstName = $firstName;
        $user->lastName = $lastName;
        $user->userName = $userName;
        $user->email = $email;
        $user->phoneNumber = $phone;
        $user->role = UserRole::USER;
        $user->password_hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->users->createUser($user);
    }
}
