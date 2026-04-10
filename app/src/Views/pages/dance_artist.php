<?php

declare(strict_types=1);

// $vm: one dance artist page (DanceArtistPageViewModel).
use App\Utils\Wysiwyg;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($vm->pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script src="/assets/js/dance/tailwind.config.js"></script>
    <style>
        .dance-artist-bg {
            background:
                radial-gradient(90% 60% at 55% 48%, rgba(132, 27, 27, 0.28), rgba(16, 14, 24, 0.95) 62%),
                radial-gradient(120% 80% at 50% 100%, rgba(178, 32, 32, 0.3), rgba(10, 10, 14, 0.98) 58%),
                #121116;
        }
        .dance-plus-strip {
            background-image: radial-gradient(circle at center, rgba(255,255,255,0.18) 0 1.5px, transparent 2px);
            background-size: 24px 24px;
            opacity: 0.25;
        }
        .dance-strip-track { animation: dance-strip-scroll 52s linear infinite; }
        @keyframes dance-strip-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    </style>
</head>

<body class="bg-dance-bg text-dance-text font-['Montserrat',sans-serif]">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="dance-artist-bg">
        <section class="mx-auto max-w-[1100px] px-6 pb-10 pt-7">
            <a href="<?= htmlspecialchars($vm->backHref) ?>" class="inline-block text-xs font-semibold text-dance-text-subtle no-underline hover:text-white">
                ← <?= htmlspecialchars($vm->backLabel) ?> · <?= htmlspecialchars($vm->artistName) ?>
            </a>

            <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-[430px_minmax(0,1fr)] lg:items-start">
                <div class="relative">
                    <?php if ($vm->coverImage !== ''): ?>
                        <img src="/<?= htmlspecialchars($vm->coverImage) ?>" alt="<?= htmlspecialchars($vm->artistName) ?>" class="block h-[210px] w-full border border-white/10 object-cover md:h-[240px]">
                    <?php endif; ?>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-[0.2em] text-dance-text-subtle"><?= htmlspecialchars($vm->kicker) ?></div>
                    <h1 class="mt-2 max-w-[11ch] text-[clamp(2.3rem,7vw,4.5rem)] font-bold uppercase leading-[0.94] text-white">
                        <?= nl2br(htmlspecialchars($vm->heroTitle)) ?>
                    </h1>
                    <div class="mt-4 max-w-[520px] text-base leading-8 text-dance-text-subtle">
                        <?php if ($vm->heroBullets !== []): ?>
                            <ul class="space-y-1.5">
                                <?php foreach ($vm->heroBullets as $line): ?>
                                    <li>• <?= htmlspecialchars((string)$line) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif ($vm->highlightsHtml !== null && trim($vm->highlightsHtml) !== ''): ?>
                            <div class="wysiwyg [&_p]:mb-2 [&_p]:leading-8 [&_li]:leading-8"><?= Wysiwyg::render($vm->highlightsHtml) ?></div>
                        <?php else: ?>
                            <p><?= htmlspecialchars($vm->heroSubtitle) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($vm->introHtml !== null && trim($vm->introHtml) !== ''): ?>
                <div class="mt-6 max-w-[980px] text-base leading-8 text-dance-text-subtle [&_p]:mb-2 [&_p]:leading-8 [&_li]:leading-8">
                    <?= Wysiwyg::render($vm->introHtml) ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="relative">
            <div class="dance-plus-strip absolute inset-0"></div>
            <div class="relative mx-auto grid max-w-[1100px] grid-cols-1 gap-8 px-6 py-8 lg:grid-cols-[430px_minmax(0,1fr)]">
                <div class="relative mb-12 overflow-visible">
                    <?php if ($vm->featureMainImage !== ''): ?>
                        <img src="/<?= htmlspecialchars($vm->featureMainImage) ?>" alt="<?= htmlspecialchars($vm->artistName) ?>" class="block h-[340px] w-full object-cover">
                    <?php elseif ($vm->portraitImage !== ''): ?>
                        <img src="/<?= htmlspecialchars($vm->portraitImage) ?>" alt="<?= htmlspecialchars($vm->artistName) ?>" class="block h-[340px] w-full object-cover">
                    <?php endif; ?>
                    <?php if ($vm->featureOverlayImage !== ''): ?>
                        <img src="/<?= htmlspecialchars($vm->featureOverlayImage) ?>" alt="" class="absolute bottom-0 right-0 z-20 h-[132px] w-[144px] translate-x-[18%] border border-white/10 object-cover shadow-lg">
                    <?php endif; ?>
                </div>
                <div class="max-w-[430px] text-base leading-8 text-dance-text-subtle">
                    <?php if ($vm->featureTextHtml !== null && trim($vm->featureTextHtml) !== ''): ?>
                        <div class="wysiwyg [&_p]:mb-3 [&_p]:leading-8 [&_li]:leading-8"><?= Wysiwyg::render($vm->featureTextHtml) ?></div>
                    <?php elseif ($vm->gallery !== [] && isset($vm->gallery[0]['caption']) && trim((string)$vm->gallery[0]['caption']) !== ''): ?>
                        <p class="mb-4"><?= htmlspecialchars((string)$vm->gallery[0]['caption']) ?></p>
                        <p><?= htmlspecialchars($vm->heroSubtitle) ?></p>
                    <?php elseif ($vm->highlightsHtml !== null && trim($vm->highlightsHtml) !== ''): ?>
                        <div class="wysiwyg [&_p]:mb-3 [&_p]:leading-8 [&_li]:leading-8"><?= Wysiwyg::render($vm->highlightsHtml) ?></div>
                    <?php else: ?>
                        <p><?= htmlspecialchars($vm->heroSubtitle) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[1100px] px-6 pb-8 pt-3">
            <div class="grid grid-cols-1 gap-2">
                <?php foreach ($vm->ticketEvents as $ev): ?>
                    <article class="grid grid-cols-[140px_1fr_110px_100px_auto] items-center gap-3 border border-white/10 bg-white/10 px-3 py-2 text-sm max-md:grid-cols-1">
                        <div class="text-[11px] text-dance-text-subtle"><?= htmlspecialchars((string)($ev['day_label'] ?? '')) ?></div>
                        <div class="font-bold uppercase"><?= htmlspecialchars((string)($ev['title'] ?? '')) ?></div>
                        <div class="text-center text-dance-text-subtle"><?= htmlspecialchars((string)($ev['time_label'] ?? '')) ?></div>
                        <div class="text-right font-semibold"><?= htmlspecialchars((string)($ev['price_label'] ?? '')) ?></div>
                        <div class="justify-self-end max-md:justify-self-start">
                            <?php $eventId = (int)($ev['event_id'] ?? 0); ?>
                            <?php $passDate = null; ?>
                            <?php include __DIR__ . '/../partials/dance_ticket_button.php'; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($vm->ticketEvents === []): ?>
                    <div class="py-3 text-sm text-dance-text-subtle">No linked dance events found for this page. Create/edit events in CMS Events and set Linked page to this artist page.</div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($vm->tracks !== [] || !empty($vm->ep['name']) || (string)($vm->ep['cover_image'] ?? '') !== '' || $vm->gallery !== []): ?>
        <section class="mx-auto max-w-[1100px] px-6 pb-16 pt-6">
            <h2 class="mb-6 text-[34px] font-bold leading-tight text-white"><?= htmlspecialchars($vm->tracksTitle) ?></h2>
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[320px_minmax(0,1fr)]">
                <div class="space-y-4">
                    <?php foreach (array_slice($vm->tracks, 0, 3) as $item): ?>
                        <div class="flex items-center gap-3">
                            <img src="/<?= htmlspecialchars((string)($item['cover_image'] ?? '')) ?>" alt="" class="h-24 w-24 border border-white/10 object-cover">
                            <div class="text-sm font-semibold text-white"><?= htmlspecialchars((string)($item['name'] ?? 'Track')) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ((string)($vm->ep['cover_image'] ?? '') !== ''): ?>
                        <div class="pt-4 text-sm text-dance-text-subtle"><?= htmlspecialchars((string)($vm->ep['label'] ?? 'EP')) ?></div>
                        <div class="flex items-center gap-3">
                            <img src="/<?= htmlspecialchars((string)($vm->ep['cover_image'] ?? '')) ?>" alt="" class="h-24 w-24 border border-white/10 object-cover">
                            <div class="text-sm font-semibold text-white"><?= htmlspecialchars((string)($vm->ep['name'] ?? '')) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="space-y-5 text-base leading-8 text-dance-text-subtle">
                    <?php foreach (array_slice($vm->tracks, 0, 3) as $item): ?>
                        <div>
                            <h3 class="text-lg font-semibold text-white">• <?= htmlspecialchars((string)($item['name'] ?? 'Track')) ?></h3>
                            <?php if (!empty($item['description'])): ?>
                                <div class="wysiwyg [&_p]:mb-2 [&_p]:leading-8 [&_li]:leading-8"><?= Wysiwyg::render((string)$item['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!empty($vm->ep['name'])): ?>
                        <div>
                            <h3 class="text-lg font-semibold text-white">• <?= htmlspecialchars((string)$vm->ep['name']) ?></h3>
                            <?php if (!empty($vm->ep['description'])): ?>
                                <div class="wysiwyg [&_p]:mb-2 [&_p]:leading-8 [&_li]:leading-8"><?= Wysiwyg::render((string)$vm->ep['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <button
        id="cartToast"
        type="button"
        class="fixed bottom-6 right-6 z-dance-toast hidden rounded-xl bg-dance-toast-bg px-4 py-3 text-left text-sm text-dance-on-dark shadow-xl ring-1 ring-dance-toast-border transition hover:brightness-110"
        aria-live="polite"
    >
        <span class="block font-semibold">Ticket added to cart</span>
        <span class="block text-xs text-dance-toast-subtle">Click to open shopping cart</span>
    </button>

    <?php require __DIR__ . '/../partials/dance_marquee_strip.php'; ?>
    <?php $danceFooter = true; include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
