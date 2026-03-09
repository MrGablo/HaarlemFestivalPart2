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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/jazz/stylesheet.css">
</head>

<body class="jazz-page m-0 bg-[#0b0b0b] font-[system-ui,Arial] text-white">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/jazz_home_content.php'; ?>

    <section id="schedule" class="schedule px-20 pt-10 pb-20 max-[1200px]:px-6">
        <h2 class="schedule__venue mb-3 mt-0 text-5xl"><?= htmlspecialchars((string)($content['schedule']['venue_title'] ?? 'PATRONAAT')) ?></h2>

        <div class="schedule__filters my-[10px] mb-[18px]">
            <div class="schedule__tabs flex flex-wrap items-center gap-[14px]">
                <?php
                // Build hall tabs but prepend "By date" which means ALL halls
                $hallTabs = $filters['tabs'] ?? ['Main Hall', 'Second Hall', 'Third Hall', 'Free'];
                array_unshift($hallTabs, (string)($filters['group_label'] ?? 'By date'));
                ?>

                <?php foreach ($hallTabs as $i => $tab): ?>
                    <button type="button" class="chip hall-chip cursor-pointer border-0 bg-transparent px-1.5 py-1 text-[18px] leading-none text-slate-300 transition-colors hover:text-[#f7c600] <?= $i === 0 ? 'is-active text-[#f7c600] underline underline-offset-[6px]' : '' ?>"
                        data-hall="<?= htmlspecialchars((string)$tab) ?>">
                        <?= htmlspecialchars((string)$tab) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="schedule__days mt-2 flex flex-wrap items-center gap-[14px]">
                <?php foreach ($days as $i => $d): ?>
                    <button type="button" class="chip day-chip cursor-pointer border-0 bg-transparent px-1.5 py-1 text-[18px] leading-none text-slate-300 transition-colors hover:text-[#f7c600] <?= $i === 0 ? 'is-active text-[#f7c600] underline underline-offset-[6px]' : '' ?>"
                        data-day="<?= htmlspecialchars((string)$d) ?>">
                        <?= htmlspecialchars((string)$d) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="event-grid mt-[18px] grid gap-[22px] [grid-template-columns:repeat(4,minmax(220px,1fr))] max-[1200px]:[grid-template-columns:repeat(2,minmax(220px,1fr))]" id="eventGrid">
            <?php foreach ($events as $ev): ?>
                <article class="event-card"
                    data-hall="<?= htmlspecialchars((string)($ev['hall'] ?? '')) ?>"
                    data-day="<?= htmlspecialchars((string)($ev['day_key'] ?? '')) ?>">

                    <a class="event-card__media relative block overflow-hidden rounded-2xl text-white no-underline"
                        href="<?= !empty($ev['page_id'])
                                    ? '/jazz/artist?page_id=' . (int)$ev['page_id'] . '&tab=events'
                                    : '#' ?>">
                        <img src="/<?= htmlspecialchars((string)($ev['img_background'] ?? '')) ?>"
                            alt="<?= htmlspecialchars((string)($ev['title'] ?? '')) ?>"
                            class="block h-[140px] w-full object-cover"
                            loading="lazy">

                        <div class="event-card__overlay absolute bottom-3 left-[14px] right-[14px] [text-shadow:0_2px_12px_rgba(0,0,0,.8)]">
                            <div class="event-card__title text-xl font-extrabold"><?= htmlspecialchars((string)($ev['title'] ?? '')) ?></div>
                            <div class="event-card__meta opacity-90">
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

        <div class="schedule__footer mt-5 flex items-center justify-center gap-[18px]">
            <button id="toggleMoreBtn" class="show-more cursor-pointer rounded-[10px] border-0 bg-[#f7c600] px-[18px] py-3 font-extrabold text-[#111]" type="button">Show more</button>

            <?php $allBtn = $content['schedule']['all_events_button'] ?? null; ?>
            <?php if (is_array($allBtn) && !empty($allBtn['href'])): ?>
                <button id="allEventsBtn" class="all-events cursor-pointer rounded-[10px] border-0 bg-[#f7c600] px-[18px] py-3 font-extrabold text-[#111]" type="button">
                    <?= htmlspecialchars((string)($allBtn['label'] ?? 'All Events')) ?>
                </button>
            <?php endif; ?>
        </div>
    </section>

    <button
        id="cartToast"
        type="button"
        class="hidden fixed bottom-6 right-6 z-[1200] rounded-xl bg-zinc-900 px-4 py-3 text-left text-sm text-white shadow-xl ring-1 ring-white/15 transition hover:bg-zinc-800"
        aria-live="polite"
    >
        <span class="block font-semibold">Ticket added to cart</span>
        <span class="block text-xs text-zinc-300">Click to open shopping cart</span>
    </button>

    <script src="/assets/js/jazz/jazz_home.js"></script>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>