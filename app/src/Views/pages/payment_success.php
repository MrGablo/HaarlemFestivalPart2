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
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-[#333] leading-[1.6] m-0 p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif]">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="mx-auto max-w-[500px] px-5 py-20 text-center">
        <div class="text-[64px] leading-none mb-5 text-green-500">&#10003;</div>
        <h1 class="text-3xl font-extrabold text-green-500 mb-2">Payment Successful!</h1>
        <p class="text-[#555] mb-1">Thank you, <?= htmlspecialchars($userName) ?>! Your ticket has been booked.</p>
        <p class="text-[#555]">You will receive your ticket details shortly. Check your Personal Program for your tickets.</p>
        <a href="/jazz"
           class="inline-block mt-8 px-7 py-3 bg-black text-white font-bold rounded-lg no-underline hover:bg-[#333] transition-colors duration-200">
            Back to Events
        </a>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
