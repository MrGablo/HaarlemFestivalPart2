<?php

namespace App\Controllers;

use App\Config;
use App\Services\AuthService;
use App\Utils\AuthSessionData;
use App\Utils\Flash;
use App\Utils\Session;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(): void
    {
        // Pull errors/flash/old from Flash util (one-time)
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $old = Flash::getOld();

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function showRegister(): void
    {
        // Pull errors/old values from Flash util (one-time)
        $errors = Flash::getErrors();
        $old = Flash::getOld();

        require __DIR__ . '/../Views/auth/register.php';
    }

    public function showForgotPassword(): void
    {
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $old = Flash::getOld();

        require __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function requestPasswordReset(): void
    {
        Flash::setOld([
            'email' => $_POST['email'] ?? '',
        ]);

        try {
            $this->authService->requestPasswordReset((string)($_POST['email'] ?? ''));
            Flash::setOld([]);
        } catch (\Throwable $e) {
            // Keep this generic on purpose, to avoid account enumeration.
        }

        Flash::setSuccess('If an account exists for that email, you will receive a password reset link shortly.');
        header('Location: /forgot-password', true, 302);
        exit;
    }

    public function showResetPassword(): void
    {
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $old = Flash::getOld();

        $token = trim((string)($old['token'] ?? ($_GET['token'] ?? '')));

        require __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function resetPassword(): void
    {
        $token = trim((string)($_POST['token'] ?? ''));

        Flash::setOld([
            'token' => $token,
        ]);

        try {
            $this->authService->resetPasswordWithToken($_POST);
            Flash::setOld([]);
            Flash::setSuccess('Password reset successful. You can now sign in.');
            header('Location: /login', true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors([
                'general' => $e->getMessage(),
            ]);

            header('Location: /reset-password?token=' . urlencode($token), true, 302);
            exit;
        }
    }

    public function register(): void
    {
        // Do not store old input in session; if validation fails we'll render
        // the register view directly using the POST data so the form can
        // be re-filled without using Flash::setOld.

        try {
            // Your existing service signature: register($_POST)
            $userId = $this->authService->register($_POST);

            //after registering, go to login page with success message
            Flash::setSuccess('Account created. Please log in.');
            // clear old explicitly
            Flash::setOld([]);

            header('Location: /login', true, 302);
            exit;
        } catch (\Throwable $e) {
            // On error, render the register form in the same request using
            // the POST data so the form can be re-filled without a redirect.
            $errors = ['general' => $e->getMessage()];
            $old = [
                'firstName' => $_POST['firstName'] ?? '',
                'lastName'  => $_POST['lastName'] ?? '',
                'userName'  => $_POST['userName'] ?? '',
                'email'     => $_POST['email'] ?? '',
                'phoneNumber' => $_POST['phoneNumber'] ?? '',
            ];

            require __DIR__ . '/../Views/auth/register.php';
            return;
        }
    }

    public function login(): void
    {
        Flash::setOld([
            'userName' => $_POST['userName'] ?? '',
        ]);

        try {
            $user = $this->authService->login($_POST);

            //generate new session ID to prevent session fixation
            session_regenerate_id(true);

            AuthSessionData::store($user);

            // clear any previous errors/old inputs
            Flash::setErrors([]);
            Flash::setOld([]);

            header('Location: /', true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors([
                'general' => $e->getMessage()
            ]);

            header('Location: /login', true, 302);
            exit;
        }
    }

    public function logout(): void
    {
        AuthSessionData::clear();
        session_destroy();
        header('Location: /', true, 302);
        exit;
    }
}
