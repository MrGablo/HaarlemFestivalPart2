<?php

declare(strict_types=1);

/** @var \App\ViewModels\JazzHomePageViewModel $vm */
/** @var string|null $flashSuccess */
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($vm->pageTitle) ?></title>
    <script src="/assets/js/jazz/tailwind.config.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 overflow-x-hidden bg-jazz-dark bg-[#0b0b0b] font-[system-ui,Arial] text-white">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <div class="mx-auto w-full max-w-jazz-container max-w-[1200px] px-5">
        <?php require __DIR__ . '/../partials/flash_success.php'; ?>
    </div>
    <?php require __DIR__ . '/../partials/jazz_home_content.php'; ?>

    <section id="schedule" class="px-20 pb-20 pt-10 max-[1200px]:px-6 max-[768px]:px-4 max-[768px]:pb-14 max-[768px]:pt-8">
        <h2 class="mb-3 text-5xl max-[1200px]:text-[40px] max-[768px]:text-[36px]"><?= htmlspecialchars($vm->scheduleVenueTitle) ?></h2>

        <div class="my-[10px] mb-[18px]">
            <div class="flex flex-wrap items-center gap-[14px] max-[768px]:gap-[8px]">
                <?php foreach ($vm->hallTabs as $i => $tab): ?>
                    <button type="button" class="hall-chip cursor-pointer border-0 bg-transparent px-[6px] py-1 text-lg max-[768px]:text-base <?= $i === 0 ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                        data-active-class="opacity-100 underline underline-offset-[6px]"
                        data-inactive-class="opacity-75"
                        data-hall="<?= htmlspecialchars((string)$tab) ?>">
                        <?= htmlspecialchars((string)$tab) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-[14px] max-[768px]:gap-[8px]">
                <?php foreach ($vm->dayTabs as $i => $d): ?>
                    <?php
                        $dayValue = is_array($d) ? (string)($d['value'] ?? '') : (string)$d;
                        $dayLabel = is_array($d) ? (string)($d['label'] ?? $dayValue) : (string)$d;
                    ?>
                    <button type="button" class="day-chip cursor-pointer border-0 bg-transparent px-[6px] py-1 text-lg max-[768px]:text-base <?= $i === 0 ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                        data-active-class="opacity-100 underline underline-offset-[6px]"
                        data-inactive-class="opacity-75"
                        data-day="<?= htmlspecialchars($dayValue) ?>">
                        <?= htmlspecialchars($dayLabel) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-[18px] grid grid-cols-4 gap-[22px] max-[1200px]:grid-cols-2 max-[768px]:grid-cols-1 max-[768px]:gap-4" id="eventGrid">
            <?php foreach ($vm->events as $ev): ?>
                <article class="event-card"
                    data-hall="<?= htmlspecialchars((string)($ev['hall'] ?? '')) ?>"
                    data-day="<?= htmlspecialchars((string)($ev['event_date'] ?? $ev['day_key'] ?? '')) ?>"
                    data-day-name="<?= htmlspecialchars((string)($ev['day_name'] ?? $ev['day_key'] ?? '')) ?>"
                    data-start-ts="<?= (int)($ev['start_ts'] ?? 0) ?>">

                    <a class="relative block overflow-hidden rounded-2xl text-white no-underline"
                        href="<?= !empty($ev['page_id'])
                                    ? '/jazz/artist?page_id=' . (int)$ev['page_id'] . '&tab=events'
                                    : '#' ?>">
                        <img class="block h-[140px] w-full object-cover" src="/<?= htmlspecialchars((string)($ev['img_background'] ?? '')) ?>"
                            alt="<?= htmlspecialchars((string)($ev['title'] ?? '')) ?>"
                            class="block h-[140px] w-full object-cover"
                            loading="lazy">

                        <div class="absolute bottom-3 left-[14px] right-[14px] [text-shadow:0_2px_12px_rgba(0,0,0,.8)]">
                            <div class="text-[20px] font-extrabold max-[768px]:text-[24px] max-[768px]:leading-tight"><?= htmlspecialchars((string)($ev['title'] ?? '')) ?></div>
                            <div class="opacity-90">
                                <?= htmlspecialchars((string)($ev['display_date'] ?? '')) ?>
                                <?= htmlspecialchars((string)($ev['display_time'] ?? '')) ?>
                            </div>
                            <div class="text-sm font-semibold opacity-95 max-[768px]:text-base">
                                <?= htmlspecialchars((string)($ev['location'] ?? '')) ?>
                            </div>
                        </div>
                    </a>

                    <form method="POST" action="/order/item/add" class="ticket-form">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('cart_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="event_id" value="<?= (int)($ev['event_id'] ?? 0) ?>">
                        <button class="mt-[10px] w-full cursor-pointer rounded-[10px] border-0 bg-jazz-accent bg-[#f7c600] px-[14px] py-3 text-sm font-extrabold text-jazz-accent-text text-[#111] max-[768px]:py-4 max-[768px]:text-lg" type="submit">
                            <?php $price = isset($ev['price']) ? (float)$ev['price'] : 0.0; ?>
                            Ticket: <?= htmlspecialchars(rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.')) ?>€ p.p
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 flex items-center justify-center gap-[18px] max-[768px]:flex-col max-[768px]:items-stretch">
            <button id="toggleMoreBtn" class="cursor-pointer rounded-[10px] border-0 bg-jazz-accent bg-[#f7c600] px-[18px] py-3 font-extrabold text-jazz-accent-text text-[#111] max-[768px]:w-full" type="button">Show more</button>

            <?php if ($vm->showAllEventsButton): ?>
                <button id="allEventsBtn" class="cursor-pointer rounded-[10px] border-0 bg-jazz-accent bg-[#f7c600] px-[18px] py-3 font-extrabold text-jazz-accent-text text-[#111] max-[768px]:w-full" type="button">
                    <?= htmlspecialchars($vm->allEventsButtonLabel) ?>
                </button>
            <?php endif; ?>
        </div>
    </section>

    <button
        id="cartToast"
        type="button"
        class="hidden fixed bottom-6 right-6 z-[1200] rounded-xl bg-zinc-900 px-4 py-3 text-left text-sm text-white shadow-xl ring-1 ring-white/15 transition hover:bg-zinc-800 max-[768px]:bottom-4 max-[768px]:left-4 max-[768px]:right-4"
        aria-live="polite"
    >
        <span class="block font-semibold">Ticket added to cart</span>
        <span class="block text-xs text-zinc-300 max-[768px]:text-sm">Click to open shopping cart</span>
    </button>

    <?php $jazzHomeJsPath = __DIR__ . '/../../../public/assets/js/jazz/jazz_home.js'; ?>
    <script src="/assets/js/jazz/jazz_home.js?v=<?= file_exists($jazzHomeJsPath) ? (string)filemtime($jazzHomeJsPath) : '1' ?>"></script>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>