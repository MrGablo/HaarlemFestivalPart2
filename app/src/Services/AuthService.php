<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserRole;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $users;
    private PasswordResetRepository $passwordResets;
    private EmailService $emails;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->passwordResets = new PasswordResetRepository();
        $this->emails = new EmailService();
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

        $userId = $this->users->createUser($user);
        $sent = $this->emails->sendRegistrationConfirmation($user->email, $user->firstName);
        if (!$sent) {
            error_log('Registration email not sent for user id ' . $userId . ' (' . $user->email . ')');
        }

        return $userId;
    }

    public function login(array $data): UserModel
    {
        $identity = trim((string)($data['userName'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if ($identity === '' || $password === '') {
            throw new \Exception('Username and password are required.');
        }

        $user = $this->users->findByUserName($identity);

        if (!$user || !password_verify($password, $user->password_hash)) {
            throw new \Exception('Invalid username/email or password.');
        }

        return $user;
    }

    public function requestPasswordReset(string $email): void
    {
        $normalizedEmail = trim($email);
        if ($normalizedEmail === '' || !filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $user = $this->users->findByEmail($normalizedEmail);
        if ($user === null) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->passwordResets->createResetToken((int)$user->id, $tokenHash, $expiresAt);

        $resetUrl = rtrim($this->resolveAppUrl(), '/') . '/reset-password?token=' . urlencode($token);
        $sent = $this->emails->sendPasswordResetLink($user->email, $user->firstName, $resetUrl);
        if (!$sent) {
            error_log('Password reset email not sent for user id ' . $user->id . ' (' . $user->email . ')');
        }
    }

    public function resetPasswordWithToken(array $data): void
    {
        $token = trim((string)($data['token'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $confirmPassword = (string)($data['confirmPassword'] ?? '');

        if ($token === '') {
            throw new \Exception('Invalid reset link.');
        }

        if ($password === '' || $confirmPassword === '') {
            throw new \Exception('Password and confirmation are required.');
        }

        if (strlen($password) < 8) {
            throw new \Exception('Password must be at least 8 characters.');
        }

        if (!hash_equals($password, $confirmPassword)) {
            throw new \Exception('Password confirmation does not match.');
        }

        $tokenHash = hash('sha256', $token);
        $resetRow = $this->passwordResets->findValidToken($tokenHash);
        if ($resetRow === null) {
            throw new \Exception('This reset link is invalid or has expired.');
        }

        $userId = (int)$resetRow['user_id'];
        $user = $this->users->getUserById($userId);
        if ($user === null) {
            throw new \Exception('Unable to reset password for this account.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $affectedRows = $this->users->updatePasswordHash($userId, $passwordHash);
        if ($affectedRows < 1) {
            throw new \Exception('Unable to update password at this time.');
        }

        $this->passwordResets->markTokenUsed((int)$resetRow['id']);
        $this->passwordResets->deleteActiveTokensByUser($userId);

        $sent = $this->emails->sendPasswordResetConfirmation($user->email, $user->firstName);
        if (!$sent) {
            error_log('Password reset confirmation email not sent for user id ' . $user->id . ' (' . $user->email . ')');
        }
    }

    private function resolveAppUrl(): string
    {
        $configured = trim((string)(getenv('APP_URL') ?: ''));
        if ($configured !== '') {
            return $configured;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = trim((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));

        return $scheme . '://' . $host;
    }
}
