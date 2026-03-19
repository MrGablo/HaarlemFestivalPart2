<?php

declare(strict_types=1);

/** @var \App\ViewModels\JazzArtistPageViewModel $vm */
/** @var string|null $flashSuccess */

use App\Utils\Wysiwyg;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($vm->pageTitle) ?></title>
    <script src="/assets/js/jazz/tailwind.config.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-jazz-dark bg-[#0b0b0b] font-[system-ui,Arial] text-white">

    <div class="mx-auto w-full max-w-jazz-container max-w-[1200px] px-5">
        <?php require __DIR__ . '/../partials/flash_success.php'; ?>
    </div>

    <!-- HERO -->
    <section class="relative min-h-[62vh] bg-cover bg-center"
        style="background-image:url('/<?= htmlspecialchars($vm->coverImage) ?>')">
        <div class="absolute inset-0 bg-gradient-to-r from-black/75 to-black/15"></div>

        <div class="absolute left-20 top-7 z-[4] flex items-center gap-[10px] max-[1200px]:left-6">
            <a class="font-extrabold text-white no-underline opacity-90" href="<?= htmlspecialchars((string)$vm->breadcrumb['back_href']) ?>">
                ← <?= htmlspecialchars((string)$vm->breadcrumb['back_label']) ?>
            </a>
            <?php if (!empty($vm->breadcrumb['current'])): ?>
                <span class="font-bold opacity-75">› <?= htmlspecialchars((string)$vm->breadcrumb['current']) ?></span>
            <?php endif; ?>
        </div>

        <div class="relative z-[1] max-w-jazz-hero max-w-[980px] px-20 pb-[10px] pt-20 max-[1200px]:px-6">
            <div class="tracking-[0.2em] opacity-75"><?= htmlspecialchars($vm->kicker) ?></div>
            <h1 class="mb-4 mt-2 text-[64px] leading-none max-[1200px]:text-[44px]"><?= htmlspecialchars($vm->heroTitle) ?></h1>
            <div class="mb-4 leading-[1.4] opacity-90"><?= htmlspecialchars($vm->heroSubtitle) ?></div>
        </div>

        <div class="absolute right-[70px] top-[70px] z-[3] grid w-jazz-media w-[360px] gap-[14px] max-[1200px]:static max-[1200px]:mt-[14px] max-[1200px]:w-full">
            <?php if (is_array($vm->mainMedia) && !empty($vm->mainMedia['image'])): ?>
                <div class="overflow-hidden rounded-[14px] bg-white/5 shadow-[0_10px_28px_rgba(0,0,0,.45)]">
                    <img class="block h-[170px] w-full object-cover max-[1200px]:h-[160px]" src="/<?= htmlspecialchars((string)$vm->mainMedia['image']) ?>" alt="">
                </div>
            <?php endif; ?>

            <?php if ($vm->secondaryMedia !== []): ?>
                <div class="grid grid-cols-2 gap-[14px]">
                    <?php foreach ($vm->secondaryMedia as $s): ?>
                        <div class="w-full overflow-hidden rounded-[14px] bg-white/5 shadow-[0_10px_28px_rgba(0,0,0,.45)]">
                            <img class="block h-[140px] w-full object-cover" src="/<?= htmlspecialchars((string)($s['image'] ?? '')) ?>" alt="">
                            <?php if (!empty($s['caption'])): ?>
                                <div class="px-3 py-[10px] text-xs opacity-85"><?= htmlspecialchars((string)$s['caption']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="px-20 pb-20 pt-6 max-[1200px]:px-6">

        <!-- TABS -->
        <div class="mb-[10px] mt-[18px] flex flex-wrap items-center gap-[18px]">
            <a class="inline-block border-0 bg-transparent px-[6px] py-1 text-lg text-white no-underline <?= $vm->activeTab === 'events' ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                data-active-class="opacity-100 underline underline-offset-[6px]"
                data-inactive-class="opacity-75"
                data-artist-tab="events"
                href="<?= htmlspecialchars((string)$vm->tabLinks['events']) ?>">
                <?= htmlspecialchars((string)$vm->tabLabels['events']) ?>
            </a>

            <a class="inline-block border-0 bg-transparent px-[6px] py-1 text-lg text-white no-underline <?= $vm->activeTab === 'career' ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                data-active-class="opacity-100 underline underline-offset-[6px]"
                data-inactive-class="opacity-75"
                data-artist-tab="career"
                href="<?= htmlspecialchars((string)$vm->tabLinks['career']) ?>">
                <?= htmlspecialchars((string)$vm->tabLabels['career']) ?>
            </a>

            <a class="inline-block border-0 bg-transparent px-[6px] py-1 text-lg text-white no-underline <?= $vm->activeTab === 'album' ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                data-active-class="opacity-100 underline underline-offset-[6px]"
                data-inactive-class="opacity-75"
                data-artist-tab="album"
                href="<?= htmlspecialchars((string)$vm->tabLinks['album']) ?>">
                <?= htmlspecialchars((string)$vm->tabLabels['album']) ?>
            </a>
        </div>

        <!-- EVENTS -->
        <div class="<?= $vm->activeTab !== 'events' ? 'hidden' : '' ?> mt-[18px]" data-artist-panel="events">
            <div class="mt-[14px] grid grid-cols-jazz-events-rail grid-cols-[26px_1fr] gap-[22px] max-[1200px]:grid-cols-jazz-events-rail-sm max-[1200px]:grid-cols-[12px_1fr]">
                <div class="rounded-xl bg-jazz-accent-rail bg-[linear-gradient(180deg,_#f7c600,_rgba(247,198,0,.35))] shadow-[0_10px_28px_rgba(0,0,0,.35)]"></div>

                <div>
                    <?php foreach ($vm->events as $ev): ?>
                        <div class="my-[22px] grid grid-cols-jazz-event-row grid-cols-[360px_1fr_220px] items-center gap-7 max-[1200px]:grid-cols-1">
                            <div class="overflow-hidden rounded-2xl bg-white/5">
                                <img class="block h-[170px] w-full object-cover" src="/<?= htmlspecialchars((string)($ev['img_background'] ?? '')) ?>"
                                    alt="<?= htmlspecialchars((string)($ev['title'] ?? '')) ?>"
                                    class="block h-[170px] w-full object-cover"
                                    loading="lazy">
                            </div>

                            <div>
                                <div class="mb-[6px] font-extrabold opacity-95"><?= htmlspecialchars((string)($ev['start_label'] ?? '')) ?></div>
                                <div class="text-[26px] font-black leading-[1.1]"><?= htmlspecialchars((string)($ev['title'] ?? '')) ?></div>
                                <div class="mt-[6px] font-extrabold opacity-90"><?= htmlspecialchars((string)($ev['location'] ?? '')) ?></div>
                            </div>

                            <div class="flex justify-end max-[1200px]:justify-start">
                                <button class="min-w-40 cursor-pointer rounded-xl border-0 bg-jazz-accent bg-[#f7c600] px-[22px] py-3 font-black text-jazz-accent-text text-[#111]" type="button">
                                    <?= htmlspecialchars($vm->ticketButtonLabel) ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="<?= $vm->activeTab !== 'career' ? 'hidden' : '' ?> mt-[18px]" data-artist-panel="career">
            <div class="grid grid-cols-2 gap-[60px] max-[1200px]:grid-cols-1 max-[1200px]:gap-[22px]">
                <div class="wysiwyg">
                    <?php if (is_string($vm->careerLeftHtml) && $vm->careerLeftHtml !== ''): ?>
                        <?= Wysiwyg::render($vm->careerLeftHtml) ?>
                    <?php else: ?>
                        <?php foreach ($vm->careerLeftItems as $line): ?>
                            <p class="mb-[18px] leading-[1.45] opacity-90">• <?= htmlspecialchars((string)$line) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="wysiwyg">
                    <?php if (is_string($vm->careerRightHtml) && $vm->careerRightHtml !== ''): ?>
                        <?= Wysiwyg::render($vm->careerRightHtml) ?>
                    <?php else: ?>
                        <?php foreach ($vm->careerRightItems as $line): ?>
                            <p class="mb-[18px] leading-[1.45] opacity-90">• <?= htmlspecialchars((string)$line) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="<?= $vm->activeTab !== 'album' ? 'hidden' : '' ?> mt-[18px]" data-artist-panel="album">
            <?php foreach ($vm->albums as $alb): ?>
                <div class="grid grid-cols-jazz-album grid-cols-[560px_1fr] items-start gap-7 max-[1200px]:grid-cols-1">
                    <div class="overflow-hidden rounded-2xl">
                        <img class="block w-full" src="/<?= htmlspecialchars((string)$alb['image_src']) ?>"
                            alt="<?= htmlspecialchars((string)$alb['image_alt']) ?>">

                        <?php if (!empty($alb['image_caption'])): ?>
                            <div class="px-3 py-[10px] text-xs opacity-85"><?= htmlspecialchars((string)$alb['image_caption']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="mb-[6px] font-extrabold opacity-80">Album</div>
                        <div class="mb-[6px] text-[40px] font-black"><?= htmlspecialchars((string)($alb['artist'] ?? '')) ?></div>
                        <div class="mb-[10px] text-[28px] font-extrabold opacity-95"><?= htmlspecialchars((string)($alb['title'] ?? '')) ?></div>

                        <?php if (!empty($alb['description_html']) && is_string($alb['description_html'])): ?>
                            <div class="max-w-jazz-album-text max-w-[860px] leading-[1.6] opacity-90 wysiwyg"><?= Wysiwyg::render($alb['description_html']) ?></div>
                        <?php else: ?>
                            <p class="max-w-jazz-album-text max-w-[860px] leading-[1.6] opacity-90"><?= htmlspecialchars((string)($alb['description'] ?? '')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-10 grid grid-cols-[1.3fr_1fr] gap-10 max-[1200px]:grid-cols-1">
            <div>
                <h3 class="mb-[10px] mt-0"><?= htmlspecialchars($vm->aboutTitle) ?></h3>

                <?php if (!empty($vm->aboutHtml)): ?>
                    <div class="leading-[1.6] opacity-90 whitespace-pre-line wysiwyg"><?= Wysiwyg::render($vm->aboutHtml) ?></div>
                <?php else: ?>
                    <p class="leading-[1.6] opacity-90 whitespace-pre-line"><?= htmlspecialchars($vm->aboutText) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <h3 class="mb-[10px] mt-0"><?= htmlspecialchars($vm->bandTitle) ?></h3>
                <ul class="m-0 list-disc pl-[18px] leading-[1.6] opacity-90">
                    <?php foreach ($vm->bandItems as $m): ?>
                        <li><?= htmlspecialchars((string)$m) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </section>

    <script src="/assets/js/jazz/jazz_artist.js"></script>
</body>

</html>