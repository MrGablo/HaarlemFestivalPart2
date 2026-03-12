<?php

declare(strict_types=1);

/** @var array $content */

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $danceAssetRoot = $scheme . '://' . $host . '/dance';
    ?>
    <title><?= htmlspecialchars((string)($content['hero']['title'] ?? 'Dance')) ?> – Haarlem Festival</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Quattrocento:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .dance-strip-track { animation: dance-strip-scroll 52s linear infinite; }
        @keyframes dance-strip-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    </style>
</head>

<body class="min-h-screen bg-[#191717] text-[#F9F9F9] text-sm font-['Montserrat',sans-serif]">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/dance_home_content.php'; ?>

    <button
        id="cartToast"
        type="button"
        class="hidden fixed bottom-6 right-6 z-[1200] rounded-xl bg-zinc-900 px-4 py-3 text-left text-sm text-white shadow-xl ring-1 ring-white/15 transition hover:bg-zinc-800"
        aria-live="polite"
    >
        <span class="block font-semibold">Ticket added to cart</span>
        <span class="block text-xs text-zinc-300">Click to open shopping cart</span>
    </button>

    <script src="<?= htmlspecialchars($danceAssetRoot) ?>/assets/js/dance/dance_home.js"></script>
    <?php $danceFooter = true; include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
