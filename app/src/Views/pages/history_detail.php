<?php

declare(strict_types=1);

/** @var \App\ViewModels\HistoryDetailPageViewModel $vm */

$hero = $vm->hero;
$storyBlocks = $vm->storyBlocks;
$mapCard = $vm->mapCard;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($vm->pageTitle) ?></title>
    <script>
        tailwind = { config: { corePlugins: { preflight: false } } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-[#faf7ef] text-[#171717] font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif]">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-[1200px] px-8 py-12">
        <a href="<?= htmlspecialchars((string)($vm->navigation['back_href'] ?? '/history')) ?>" class="inline-flex items-center gap-2 text-sm font-bold text-[#171717] no-underline">
            <span aria-hidden="true">←</span>
            <?= htmlspecialchars((string)($vm->navigation['back_label'] ?? 'Back')) ?>
        </a>

        <section class="mt-8 grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div>
                <?php if (!empty($hero['kicker'])): ?>
                    <div class="text-sm font-bold uppercase tracking-[0.3em] text-[#8d7e63]">
                        <?= htmlspecialchars((string)$hero['kicker']) ?>
                    </div>
                <?php endif; ?>
                <h1 class="mt-4 text-5xl font-black leading-none md:text-7xl">
                    <?= htmlspecialchars((string)($hero['title'] ?? $vm->pageTitle)) ?>
                </h1>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <?php if (!empty($hero['main_image'])): ?>
                    <div class="sm:col-span-2 overflow-hidden rounded-[24px] shadow-[0_18px_60px_rgba(0,0,0,0.10)]">
                        <img src="/<?= htmlspecialchars((string)$hero['main_image']) ?>" alt="<?= htmlspecialchars($vm->pageTitle) ?>" class="block h-72 w-full object-cover">
                    </div>
                <?php endif; ?>
                <?php foreach (($hero['gallery'] ?? []) as $galleryItem): ?>
                    <div class="overflow-hidden rounded-[24px] bg-white shadow-[0_18px_60px_rgba(0,0,0,0.08)]">
                        <img src="/<?= htmlspecialchars((string)($galleryItem['image'] ?? '')) ?>" alt="<?= htmlspecialchars((string)($galleryItem['caption'] ?? $vm->pageTitle)) ?>" class="block h-44 w-full object-cover">
                        <?php if (!empty($galleryItem['caption'])): ?>
                            <div class="px-4 py-3 text-sm text-[#555]">
                                <?= htmlspecialchars((string)$galleryItem['caption']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="mt-12 space-y-8">
            <?php foreach ($storyBlocks as $index => $block): ?>
                <?php $isRight = (string)($block['image_position'] ?? 'left') === 'right'; ?>
                <article class="grid gap-6 rounded-[28px] bg-white p-8 shadow-[0_18px_60px_rgba(0,0,0,0.08)] md:grid-cols-2">
                    <?php if (!$isRight): ?>
                        <div class="overflow-hidden rounded-[24px]">
                            <img src="/<?= htmlspecialchars((string)($block['image'] ?? '')) ?>" alt="<?= htmlspecialchars((string)($block['title'] ?? $vm->pageTitle)) ?>" class="block h-full min-h-64 w-full object-cover">
                        </div>
                    <?php endif; ?>
                    <div class="<?= $isRight ? 'md:order-1' : '' ?>">
                        <?php if (!empty($block['title'])): ?>
                            <h2 class="text-2xl font-black"><?= htmlspecialchars((string)$block['title']) ?></h2>
                        <?php endif; ?>
                        <div class="mt-4 text-[15px] leading-7 text-[#444]">
                            <?= $block['body_html'] ?? '' ?>
                        </div>
                    </div>
                    <?php if ($isRight): ?>
                        <div class="overflow-hidden rounded-[24px] md:order-2">
                            <img src="/<?= htmlspecialchars((string)($block['image'] ?? '')) ?>" alt="<?= htmlspecialchars((string)($block['title'] ?? $vm->pageTitle)) ?>" class="block h-full min-h-64 w-full object-cover">
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="mt-12 rounded-[32px] bg-[#fffaf1] p-8 shadow-[0_18px_60px_rgba(0,0,0,0.06)]">
            <h2 class="text-2xl font-black"><?= htmlspecialchars((string)($mapCard['title'] ?? $vm->pageTitle)) ?></h2>
            <?php if (!empty($mapCard['summary'])): ?>
                <p class="mt-4 max-w-[760px] text-[15px] leading-7 text-[#555]">
                    <?= htmlspecialchars((string)$mapCard['summary']) ?>
                </p>
            <?php endif; ?>
            <a href="/history" class="mt-6 inline-block rounded-full bg-[#121212] px-5 py-3 text-sm font-bold text-white no-underline">
                <?= htmlspecialchars((string)($mapCard['button_label'] ?? 'Bekijk locatie')) ?>
            </a>
        </section>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>