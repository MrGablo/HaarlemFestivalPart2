<?php

/** @var array $content */
/** @var array $events */

$filters = $content['schedule']['filters'] ?? [];
$hallTabs = $filters['tabs'] ?? ['Main Hall', 'Second Hall', 'Third Hall', 'Free'];
$days = $filters['days'] ?? ['All Days', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($content['hero']['title'] ?? 'Jazz') ?></title>
    <link rel="stylesheet" href="/assets/css/jazz/stylesheet.css">
</head>

<body class="jazz-page">

    <?php require __DIR__ . '/../partials/jazz_home_content.php'; ?>

    <section id="schedule" class="schedule">
        <h2 class="schedule__venue"><?= htmlspecialchars($content['schedule']['venue_title'] ?? 'PATRONAAT') ?></h2>

        <div class="schedule__filters">
            <div class="schedule__tabs">
                <?php
                // Build hall tabs but prepend "By date" which means ALL halls
                $hallTabs = $filters['tabs'] ?? ['Main Hall', 'Second Hall', 'Third Hall', 'Free'];
                array_unshift($hallTabs, $filters['group_label'] ?? 'By date'); // first tab = By date
                ?>

                <?php foreach ($hallTabs as $i => $tab): ?>
                    <button type="button" class="chip hall-chip <?= $i === 0 ? 'is-active' : '' ?>"
                        data-hall="<?= htmlspecialchars($tab) ?>"><?= htmlspecialchars($tab) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="schedule__days">
                <?php foreach ($days as $i => $d): ?>
                    <button type="button" class="chip day-chip <?= $i === 0 ? 'is-active' : '' ?>"
                        data-day="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="event-grid" id="eventGrid">
            <?php foreach ($events as $ev): ?>
                <article class="event-card" data-hall="<?= htmlspecialchars($ev['hall']) ?>"
                    data-day="<?= htmlspecialchars($ev['day_key']) ?>">
                    <a class="event-card__media"
                        href="<?= $ev['page_id'] ? '/jazz/artist?page_id=' . (int)$ev['page_id'] . '&tab=events' : '#' ?>">
                        <img src="/<?= htmlspecialchars($ev['img_background']) ?>"
                            alt="<?= htmlspecialchars($ev['title']) ?>" loading="lazy">
                        <div class="event-card__overlay">
                            <div class="event-card__title"><?= htmlspecialchars($ev['title']) ?></div>
                            <div class="event-card__meta"><?= htmlspecialchars($ev['display_date']) ?>
                                <?= htmlspecialchars($ev['display_time']) ?></div>
                        </div>
                    </a>

                    <button class="ticket-btn" type="button">
                        Ticket: <?= htmlspecialchars((string)$ev['price']) ?> p.p
                    </button>
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
</body>

</html>