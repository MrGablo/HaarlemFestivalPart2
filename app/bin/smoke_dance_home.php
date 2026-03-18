#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Smoke test: build Dance ViewModel + render full HTML (same path as browser).
 * Run: php bin/smoke_dance_home.php
 * Or:  docker compose run --rm php php bin/smoke_dance_home.php
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use App\Repositories\DanceHomeRepository;
use App\Repositories\PageRepository;
use App\Services\DanceHomeService;
use App\Utils\Env;
use App\Utils\Session;

$_SERVER['REQUEST_URI'] ??= '/dance';
$_SERVER['REQUEST_METHOD'] ??= 'GET';
$_SERVER['HTTP_HOST'] ??= 'localhost';

Env::load();
Session::ensureStarted();

$errors = [];
$ok = [];

try {
    $service = new DanceHomeService(new PageRepository(), new DanceHomeRepository());
    $vm = $service->buildViewModel();
} catch (Throwable $e) {
    fwrite(STDERR, "[FAIL] ViewModel: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    exit(1);
}

$ok[] = 'DanceHomePageViewModel built';

if ($vm->pageTitle === '') {
    $errors[] = 'pageTitle empty';
} else {
    $ok[] = 'pageTitle OK';
}

if (!is_array($vm->lineupArtists)) {
    $errors[] = 'lineupArtists not array';
} else {
    $ok[] = 'lineupArtists count=' . count($vm->lineupArtists);
}

if (!is_array($vm->timetableDays) && !isset($vm->timetableDays)) {
    $errors[] = 'timetableDays missing';
} else {
    $ok[] = 'timetableDays count=' . count($vm->timetableDays);
}

$html = '';
try {
    ob_start();
    require $root . '/src/Views/pages/dance_home.php';
    $html = (string) ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    fwrite(STDERR, "[FAIL] Render: " . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n");
    exit(1);
}

if ($html === '') {
    fwrite(STDERR, "[FAIL] Empty HTML output\n");
    exit(1);
}

$checks = [
    'DOCTYPE' => stripos($html, '<!doctype html>') !== false,
    'dance-timetable anchor' => str_contains($html, 'id="dance-timetable"'),
    'tailwind + theme script' => str_contains($html, 'tailwind.config.js'),
    'dance_home.js' => str_contains($html, 'dance_home.js'),
    'no PHP fatal string' => stripos($html, 'fatal error') === false && stripos($html, 'parse error') === false,
];

foreach ($checks as $label => $pass) {
    if ($pass) {
        $ok[] = $label;
    } else {
        $errors[] = $label;
    }
}

if ($vm->timetableHasRows) {
    if (!str_contains($html, 'dance-timetable') || (!str_contains($html, '€') && !str_contains($html, '&euro;'))) {
        $errors[] = 'timetable expected rows but page missing price/timetable markers';
    } else {
        $ok[] = 'timetable content present';
    }
}

foreach ($ok as $m) {
    echo "[OK]   {$m}\n";
}
foreach ($errors as $m) {
    echo "[FAIL] {$m}\n";
}

if ($errors !== []) {
    exit(1);
}

echo "\nDance home smoke test: ALL PASSED\n";
exit(0);
