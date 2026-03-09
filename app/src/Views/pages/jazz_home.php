<?php

declare(strict_types=1);

/** @var array $content */
/** @var array $events */

use App\Utils\Wysiwyg; // not strictly needed here, but fine

$filters = $content['schedule']['filters'] ?? [];
$days = $filters['days'] ?? ['All Days', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars((string)($content['hero']['title'] ?? 'Jazz')) ?></title>
    <link rel="stylesheet" href="/assets/css/jazz/stylesheet.css">
</head>

<body class="jazz-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/jazz_home_content.php'; ?>

    <section id="schedule" class="schedule">
        <h2 class="schedule__venue"><?= htmlspecialchars((string)($content['schedule']['venue_title'] ?? 'PATRONAAT')) ?></h2>

        <div class="schedule__filters">
            <div class="schedule__tabs">
                <?php
                // Build hall tabs but prepend "By date" which means ALL halls
                $hallTabs = $filters['tabs'] ?? ['Main Hall', 'Second Hall', 'Third Hall', 'Free'];
                array_unshift($hallTabs, (string)($filters['group_label'] ?? 'By date'));
                ?>

                <?php foreach ($hallTabs as $i => $tab): ?>
                    <button type="button" class="chip hall-chip <?= $i === 0 ? 'is-active' : '' ?>"
                        data-hall="<?= htmlspecialchars((string)$tab) ?>">
                        <?= htmlspecialchars((string)$tab) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="schedule__days">
                <?php foreach ($days as $i => $d): ?>
                    <button type="button" class="chip day-chip <?= $i === 0 ? 'is-active' : '' ?>"
                        data-day="<?= htmlspecialchars((string)$d) ?>">
                        <?= htmlspecialchars((string)$d) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="event-grid" id="eventGrid">
            <?php foreach ($events as $ev): ?>
                <article class="event-card"
                    data-hall="<?= htmlspecialchars((string)($ev['hall'] ?? '')) ?>"
                    data-day="<?= htmlspecialchars((string)($ev['day_key'] ?? '')) ?>">

                    <a class="event-card__media"
                        href="<?= !empty($ev['page_id'])
                                    ? '/jazz/artist?page_id=' . (int)$ev['page_id'] . '&tab=events'
                                    : '#' ?>">
                        <img src="/<?= htmlspecialchars((string)($ev['img_background'] ?? '')) ?>"
                            alt="<?= htmlspecialchars((string)($ev['title'] ?? '')) ?>"
                            loading="lazy">

                        <div class="event-card__overlay">
                            <div class="event-card__title"><?= htmlspecialchars((string)($ev['title'] ?? '')) ?></div>
                            <div class="event-card__meta">
                                <?= htmlspecialchars((string)($ev['display_date'] ?? '')) ?>
                                <?= htmlspecialchars((string)($ev['display_time'] ?? '')) ?>
                            </div>
                        </div>
                    </a>

                    <form method="POST" action="/order/item/add" class="ticket-form">
                        <input type="hidden" name="event_id" value="<?= (int)($ev['event_id'] ?? 0) ?>">
                        <button class="ticket-btn" type="submit">
                            Ticket: <?= htmlspecialchars((string)($ev['price'] ?? '')) ?> p.p
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="schedule__footer">
            <button id="toggleMoreBtn" class="show-more" type="button">Show more</button>

            <?php $allBtn = $content['schedule']['all_events_button'] ?? null; ?>
            <?php if (is_array($allBtn) && !empty($allBtn['href'])): ?>
                <button id="allEventsBtn" class="all-events" type="button">
                    <?= htmlspecialchars((string)($allBtn['label'] ?? 'All Events')) ?>
                </button>
            <?php endif; ?>
        </div>
    </section>

    <script src="/assets/js/jazz/jazz_home.js"></script>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>