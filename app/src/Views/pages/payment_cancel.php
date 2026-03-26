<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled – Haarlem Festival</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-white font-[system-ui,'Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-neutral-800 leading-relaxed">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto w-full max-w-[500px] px-5 py-16 text-center">
        <div class="mb-5 text-[64px] leading-none text-red-500" aria-hidden="true">&#10007;</div>
        <h1 class="mb-2 text-3xl font-extrabold text-red-500">Payment Cancelled</h1>
        <p class="mb-1 text-neutral-600">Your payment was not completed. No charges were made.</p>
        <p class="text-neutral-600">You can try again whenever you're ready.</p>
        <a href="/jazz"
            class="mt-8 inline-block rounded-lg border-0 bg-black px-7 py-3 text-sm font-bold uppercase tracking-wide text-white no-underline transition hover:bg-neutral-800">
            Back to Events
        </a>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
