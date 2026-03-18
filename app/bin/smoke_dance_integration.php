#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Integration: DanceHomeRepository rows + OrderRepository dance event + EventModelBuilder.
 * Run in Docker: docker compose run --rm --no-deps -w /app php php bin/smoke_dance_integration.php
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use App\Models\DanceEvent;
use App\Repositories\DanceHomeRepository;
use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Utils\Env;

Env::load();

$errors = [];
$ok = [];

try {
    $danceRepo = new DanceHomeRepository();
    $rows = $danceRepo->findDanceTimetableRows();
} catch (Throwable $e) {
    fwrite(STDERR, "[FAIL] Repository: " . $e->getMessage() . "\n");
    exit(1);
}

if ($rows === []) {
    fwrite(STDERR, "[FAIL] findDanceTimetableRows returned empty (check DB / migrations)\n");
    exit(1);
}
$ok[] = 'timetable rows=' . count($rows);

$sessionEventId = 0;
foreach ($rows as $r) {
    if (($r['row_kind'] ?? '') === 'session' && (int) ($r['event_id'] ?? 0) > 0) {
        $sessionEventId = (int) $r['event_id'];
        $title = (string) ($r['title'] ?? '');
        $loc = (string) ($r['location_name'] ?? '');
        if ($title === '') {
            $errors[] = 'session row missing title';
        } else {
            $ok[] = 'session title from Event: ' . substr($title, 0, 40);
        }
        if ($loc === '' && !empty($r['venue_id'])) {
            $errors[] = 'venue_id set but location_name empty';
        } elseif ($loc !== '') {
            $ok[] = 'venue label: ' . substr($loc, 0, 30);
        }
        break;
    }
}

if ($sessionEventId === 0) {
    $errors[] = 'no session row with event_id';
} else {
    try {
        $orderRepo = new OrderRepository();
        $eventRow = $orderRepo->findEventById($sessionEventId);
    } catch (Throwable $e) {
        fwrite(STDERR, "[FAIL] findEventById: " . $e->getMessage() . "\n");
        exit(1);
    }

    if (!is_array($eventRow) || ($eventRow['event_type'] ?? '') !== 'dance') {
        $errors[] = 'findEventById not dance for session event_id=' . $sessionEventId;
    } else {
        $ok[] = 'findEventById dance OK';
        $price = (float) ($eventRow['price'] ?? 0);
        if ($price <= 0) {
            $errors[] = 'dance event price is 0';
        } else {
            $ok[] = 'dance price=' . $price;
        }

        $builder = new EventModelBuilderService();
        $model = $builder->buildEventModel($eventRow);
        if (!$model instanceof DanceEvent) {
            $errors[] = 'EventModelBuilder did not return DanceEvent';
        } else {
            $ok[] = 'DanceEvent model OK (location len=' . strlen($model->location) . ')';
        }
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

echo "\nDance integration smoke: ALL PASSED\n";
exit(0);
