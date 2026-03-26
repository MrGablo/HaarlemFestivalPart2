<?php

declare(strict_types=1);

/** @var \App\ViewModels\DanceHomePageViewModel $vm */
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $vm->pageTitle) ?> – Haarlem Festival</title>
    <?php /* Tailwind CDN must load first — then theme (otherwise tailwind is undefined and design breaks). */ ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/dance/tailwind.config.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Quattrocento:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .dance-strip-track { animation: dance-strip-scroll 52s linear infinite; }
        @keyframes dance-strip-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    </style>
</head>

<body class="dance-open-cart-on-add min-h-screen bg-dance-bg text-dance-text text-sm font-['Montserrat',sans-serif]">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/dance_home_content.php'; ?>

    <button
        id="cartToast"
        type="button"
        class="fixed bottom-6 right-6 z-dance-toast hidden rounded-xl bg-dance-toast-bg px-4 py-3 text-left text-sm text-dance-on-dark shadow-xl ring-1 ring-dance-toast-border transition hover:brightness-110"
        aria-live="polite"
    >
        <span class="block font-semibold">Ticket added to cart</span>
        <span class="block text-xs text-dance-toast-subtle">Click to open shopping cart</span>
    </button>

    <?php $danceFooter = true; include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
