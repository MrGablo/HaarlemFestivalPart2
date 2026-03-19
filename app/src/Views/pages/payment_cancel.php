<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - Haarlem Festival</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-[#333] leading-[1.6] m-0 p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif]">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="mx-auto max-w-[500px] px-5 py-20 text-center">
        <div class="text-[64px] leading-none mb-5 text-red-500">&#10007;</div>
        <h1 class="text-3xl font-extrabold text-red-500 mb-2">Payment Cancelled</h1>
        <p class="text-[#555] mb-1">Your payment was not completed. No charges were made.</p>
        <p class="text-[#555]">You can try again whenever you're ready.</p>
        <a href="/jazz"
           class="inline-block mt-8 px-7 py-3 bg-black text-white font-bold rounded-lg no-underline hover:bg-[#333] transition-colors duration-200">
            Back to Events
        </a>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
