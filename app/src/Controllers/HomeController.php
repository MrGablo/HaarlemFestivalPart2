<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;

/**
 * Home controller
 */
class HomeController
{
    /**
     * @param array $vars
     */
    public function home(array $vars = []): void
    {
        $isAuthenticated = AuthHelper::isAuthenticated();
        $user = AuthHelper::getCurrentUser();
        $this->renderHomePage($isAuthenticated, $user);
    }

    /**
     * @param bool $isAuthenticated
     * @param \App\Models\User|null $user
     */
    private function renderHomePage(bool $isAuthenticated, $user): void
    {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haarlem Festival</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
        .header h1 {
            margin: 0;
        }
        .auth-section {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .content {
            margin-top: 30px;
        }
        .welcome-message {
            padding: 20px 0;
        }
        .welcome-message h2 {
            margin: 0 0 10px 0;
        }
        .welcome-message p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Haarlem Festival</h1>
            <div class="auth-section">
                <?php if ($isAuthenticated && $user): ?>
                    <div>
                        Welcome, <?= htmlspecialchars($user->getFullName(), ENT_QUOTES, 'UTF-8') ?>!
                        <?php if ($user->getRole()): ?>
                            (<?= htmlspecialchars($user->getRole(), ENT_QUOTES, 'UTF-8') ?>)
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="/logout" style="display: inline;">
                        <button type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="/login">Login</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="content">
            <div class="welcome-message">
                <h2>Welcome to Haarlem Festival</h2>
                <?php if ($isAuthenticated): ?>
                    <p>You are successfully logged in! Enjoy exploring the festival.</p>
                <?php else: ?>
                    <p>Please log in to access all features of the Haarlem Festival website.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
        <?php
    }
}
