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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Quattrocento:wght@400;700&display=swap" rel="stylesheet">
    <link href="<?= htmlspecialchars($danceAssetRoot) ?>/assets/css/dance.css" rel="stylesheet">
</head>

<body class="dance-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/dance_home_content.php'; ?>
    <?php $danceFooter = true; include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
