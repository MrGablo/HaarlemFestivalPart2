<?php
declare(strict_types=1);

use App\Utils\AuthSessionData;

$auth = AuthSessionData::read();
$userName = $auth['userName'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Haarlem Festival</title>
    <style>
        body { font-family: Arial, sans-serif; background: #111; color: #fff; text-align: center; padding: 80px 20px; }
        .container { max-width: 500px; margin: 0 auto; }
        .checkmark { font-size: 64px; margin-bottom: 20px; }
        h1 { color: #4CAF50; margin-bottom: 10px; }
        p { opacity: 0.85; line-height: 1.6; }
        .btn { display: inline-block; margin-top: 30px; padding: 12px 28px; background: #f7c600; color: #111; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .btn:hover { background: #e0b300; }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkmark">&#10003;</div>
        <h1>Payment Successful!</h1>
        <p>Thank you, <?= htmlspecialchars($userName) ?>! Your ticket has been booked.</p>
        <p>You will receive your ticket details shortly. Check your Personal Program for your tickets.</p>
        <a class="btn" href="/jazz">Back to Events</a>
    </div>
</body>
</html>
