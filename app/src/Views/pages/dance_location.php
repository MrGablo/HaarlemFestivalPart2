<?php

declare(strict_types=1);

// $vm: one dance venue / location page (DanceLocationPageViewModel).
use App\Utils\Wysiwyg;

$websiteHref = $vm->websiteUrl;
if ($websiteHref !== '' && !preg_match('#^https?://#i', $websiteHref)) {
    $websiteHref = 'https://' . $websiteHref;
}
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
        .dance-location-bg {
            background:
                radial-gradient(90% 60% at 55% 48%, rgba(132, 27, 27, 0.28), rgba(16, 14, 24, 0.95) 62%),
                radial-gradient(120% 80% at 50% 100%, rgba(178, 32, 32, 0.3), rgba(10, 10, 14, 0.98) 58%),
                #121116;
        }
        .dance-strip-track { animation: dance-strip-scroll 52s linear infinite; }
        @keyframes dance-strip-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    </style>
</head>

<body class="bg-dance-bg text-dance-text font-['Montserrat',sans-serif]">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="dance-location-bg">
        <section class="mx-auto max-w-[1100px] px-6 pb-8 pt-7">
            <nav class="text-xs font-semibold text-dance-text-subtle" aria-label="Breadcrumb">
                <a href="<?= htmlspecialchars($vm->backHref) ?>" class="inline-flex items-center gap-1 no-underline hover:text-white">
                    <span aria-hidden="true">←</span> <?= htmlspecialchars($vm->backLabel) ?>
                </a>
                <span class="mx-2 text-dance-text-subtle/70">›</span>
                <span class="text-white"><?= htmlspecialchars($vm->venueName) ?></span>
            </nav>

            <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,430px)_minmax(0,1fr)] lg:items-start">
                <div class="relative overflow-hidden border border-white/10">
                    <?php if ($vm->coverImage !== ''): ?>
                        <img src="/<?= htmlspecialchars($vm->coverImage) ?>" alt="<?= htmlspecialchars($vm->venueName) ?>" class="block h-[220px] w-full object-cover md:h-[280px]">
                    <?php else: ?>
                        <div class="flex h-[220px] w-full items-center justify-center bg-white/5 text-sm text-dance-text-subtle md:h-[280px]">Add a venue photo in CMS</div>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-[0.2em] text-dance-text-subtle"><?= htmlspecialchars($vm->kicker) ?></div>
                    <h1 class="mt-2 text-[clamp(2rem,6vw,3.25rem)] font-bold uppercase leading-tight text-white">
                        <?= nl2br(htmlspecialchars($vm->heroTitle)) ?>
                    </h1>

                    <ul class="mt-6 space-y-3 text-base text-dance-text-subtle">
                        <?php if ($vm->address !== ''): ?>
                            <li class="flex gap-3">
                                <span class="mt-0.5 shrink-0 text-dance-accent" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.125-7.5 11.25-7.5 11.25S4.5 17.625 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                </span>
                                <span class="leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($vm->address)) ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if ($vm->phone !== ''): ?>
                            <li class="flex gap-3">
                                <span class="mt-0.5 shrink-0 text-dance-accent" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                </span>
                                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $vm->phone)) ?>" class="text-inherit underline decoration-white/30 hover:text-white"><?= htmlspecialchars($vm->phone) ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($vm->websiteUrl !== ''): ?>
                            <li class="flex gap-3">
                                <span class="mt-0.5 shrink-0 text-dance-accent" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                </span>
                                <a href="<?= htmlspecialchars($websiteHref) ?>" class="break-all underline decoration-white/40 hover:text-white" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($vm->websiteLabel) ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($vm->googleMapsHref !== ''): ?>
                        <a
                            href="<?= htmlspecialchars($vm->googleMapsHref) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-6 inline-flex w-full max-w-md items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold uppercase tracking-wide text-dance-bg no-underline transition hover:bg-white/90 lg:w-auto"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg>
                            Show on Google Maps
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($vm->introHtml !== null && trim($vm->introHtml) !== ''): ?>
                <div class="mt-8 max-w-[980px] text-base leading-8 text-dance-text-subtle [&_p]:mb-2">
                    <?= Wysiwyg::render($vm->introHtml) ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="mx-auto max-w-[1100px] px-6 pb-16 pt-4">
            <h2 class="mb-5 text-[11px] font-semibold uppercase tracking-[0.25em] text-dance-text-subtle"><?= htmlspecialchars($vm->eventsSectionTitle) ?></h2>
            <div class="grid grid-cols-1 gap-3">
                <?php foreach ($vm->ticketEvents as $ev): ?>
                    <article class="grid grid-cols-1 items-center gap-3 border border-white/10 bg-white/10 px-3 py-3 text-sm md:grid-cols-[minmax(0,9rem)_minmax(0,1fr)_minmax(0,6rem)_minmax(0,7rem)_minmax(0,5rem)_auto] md:gap-4">
                        <div class="text-[11px] text-dance-text-subtle md:pl-1"><?= htmlspecialchars((string) ($ev['day_label'] ?? '')) ?></div>
                        <div class="min-w-0 font-bold uppercase leading-snug text-white"><?= htmlspecialchars((string) ($ev['title'] ?? '')) ?></div>
                        <div class="text-dance-text-subtle md:text-center"><?= htmlspecialchars((string) ($ev['time_label'] ?? '')) ?></div>
                        <div class="truncate font-semibold text-dance-text underline decoration-white/30 md:text-right"><?= htmlspecialchars((string) ($ev['location'] ?? '')) ?></div>
                        <div class="font-semibold text-white md:text-right"><?= htmlspecialchars((string) ($ev['price_label'] ?? '')) ?></div>
                        <div class="justify-self-start md:justify-self-end">
                            <?php $eventId = (int) ($ev['event_id'] ?? 0); ?>
                            <?php $passDate = null; ?>
                            <?php include __DIR__ . '/../partials/dance_ticket_button.php'; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($vm->ticketEvents === []): ?>
                    <p class="py-4 text-sm text-dance-text-subtle">No sessions at this venue yet. Set the correct Venue ID in CMS and ensure Dance events use that venue.</p>
                <?php endif; ?>
            </div>
        </section>
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
