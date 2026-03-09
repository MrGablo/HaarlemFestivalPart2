<?php

declare(strict_types=1);

/** @var array $content */
/** @var array $events */
/** @var string $activeTab */

use App\Utils\Wysiwyg;

$artist = $content['artist'] ?? [];
$labels = $content['tabs']['labels'] ?? ['events' => 'Events', 'career' => 'Career Highlights', 'album' => 'Album'];

$bc = $artist['breadcrumb'] ?? [];
$media = $artist['hero_media'] ?? [];
$mainMedia = is_array($media) ? ($media['main'] ?? null) : null;
$secondaryMedia = is_array($media) ? ($media['secondary'] ?? []) : [];

$career = $content['career_highlights'] ?? [];
$albums = $content['albums'] ?? [];

$kickerText = trim((string)($artist['kicker'] ?? ''));
$heroTitleText = trim((string)($artist['hero_title'] ?? ($artist['name'] ?? '')));
$heroSubtitleText = trim((string)($artist['hero_subtitle'] ?? ''));

if (strcasecmp($kickerText, $heroTitleText) === 0) {
    $kickerText = '';
}

if (
    strcasecmp($heroSubtitleText, $heroTitleText) === 0 ||
    ($kickerText !== '' && strcasecmp($heroSubtitleText, $kickerText) === 0)
) {
    $heroSubtitleText = '';
}

$pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;

function tabLink(int $pageId, string $tab): string
{
    return "/jazz/artist?page_id=" . $pageId . "&tab=" . urlencode($tab);
}

function safeTab(string $t): string
{
    $allowed = ['events', 'career', 'album'];
    return in_array($t, $allowed, true) ? $t : 'events';
}

$activeTab = safeTab((string)$activeTab);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars((string)($artist['name'] ?? 'Artist')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-[#0b0b0b] font-[system-ui,Arial] text-white">

    <!-- HERO -->
    <section class="relative min-h-[62vh] bg-cover bg-center"
        style="background-image:url('/<?= htmlspecialchars((string)($artist['cover_image'] ?? '')) ?>')">
        <div class="absolute inset-0 bg-gradient-to-r from-black/75 to-black/15"></div>

        <div class="absolute left-20 top-7 z-[4] flex items-center gap-[10px] max-[1200px]:left-6">
            <a class="font-extrabold text-white no-underline opacity-90" href="<?= htmlspecialchars((string)($bc['back_href'] ?? '/jazz')) ?>">
                ← <?= htmlspecialchars((string)($bc['back_label'] ?? 'Back')) ?>
            </a>
            <?php if (!empty($bc['current'])): ?>
                <span class="font-bold opacity-75">› <?= htmlspecialchars((string)$bc['current']) ?></span>
            <?php endif; ?>
        </div>

        <div class="relative z-[1] max-w-[980px] px-20 pb-[10px] pt-20 max-[1200px]:px-6">
            <div class="tracking-[0.2em] opacity-75"><?= htmlspecialchars((string)($artist['kicker'] ?? '')) ?></div>
            <h1 class="mb-4 mt-2 text-[64px] leading-none max-[1200px]:text-[44px]"><?= htmlspecialchars((string)($artist['hero_title'] ?? ($artist['name'] ?? ''))) ?></h1>
            <div class="mb-4 leading-[1.4] opacity-90"><?= htmlspecialchars((string)($artist['hero_subtitle'] ?? '')) ?></div>
        </div>

        <div class="absolute right-[70px] top-[70px] z-[3] grid w-[360px] gap-[14px] max-[1200px]:static max-[1200px]:mt-[14px] max-[1200px]:w-full">
            <?php if (is_array($mainMedia) && !empty($mainMedia['image'])): ?>
                <div class="overflow-hidden rounded-[14px] bg-white/5 shadow-[0_10px_28px_rgba(0,0,0,.45)]">
                    <img class="block h-[170px] w-full object-cover max-[1200px]:h-[160px]" src="/<?= htmlspecialchars((string)$mainMedia['image']) ?>" alt="">
                </div>
            <?php endif; ?>

            <?php if (is_array($secondaryMedia) && count($secondaryMedia) > 0): ?>
                <div class="grid grid-cols-2 gap-[14px]">
                    <?php foreach ($secondaryMedia as $s): ?>
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
            <a class="inline-block border-0 bg-transparent px-[6px] py-1 text-lg text-white no-underline <?= $activeTab === 'events' ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                data-active-class="opacity-100 underline underline-offset-[6px]"
                data-inactive-class="opacity-75"
                data-artist-tab="events"
                href="<?= htmlspecialchars(tabLink($pageId, 'events')) ?>">
                <?= htmlspecialchars((string)($labels['events'] ?? 'Events')) ?>
            </a>

            <a class="inline-block border-0 bg-transparent px-[6px] py-1 text-lg text-white no-underline <?= $activeTab === 'career' ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                data-active-class="opacity-100 underline underline-offset-[6px]"
                data-inactive-class="opacity-75"
                data-artist-tab="career"
                href="<?= htmlspecialchars(tabLink($pageId, 'career')) ?>">
                <?= htmlspecialchars((string)($labels['career'] ?? 'Career Highlights')) ?>
            </a>

            <a class="inline-block border-0 bg-transparent px-[6px] py-1 text-lg text-white no-underline <?= $activeTab === 'album' ? 'opacity-100 underline underline-offset-[6px]' : 'opacity-75' ?>"
                data-active-class="opacity-100 underline underline-offset-[6px]"
                data-inactive-class="opacity-75"
                data-artist-tab="album"
                href="<?= htmlspecialchars(tabLink($pageId, 'album')) ?>">
                <?= htmlspecialchars((string)($labels['album'] ?? 'Album')) ?>
            </a>
        </div>

        <!-- EVENTS -->
        <div class="<?= $activeTab !== 'events' ? 'hidden' : '' ?> mt-[18px]" data-artist-panel="events">
            <div class="mt-[14px] grid grid-cols-[26px_1fr] gap-[22px] max-[1200px]:grid-cols-[12px_1fr]">
                <div class="rounded-xl bg-[linear-gradient(180deg,_#f7c600,_rgba(247,198,0,.35))] shadow-[0_10px_28px_rgba(0,0,0,.35)]"></div>

                <div>
                    <?php foreach ($events as $ev): ?>
                        <div class="my-[22px] grid grid-cols-[360px_1fr_220px] items-center gap-7 max-[1200px]:grid-cols-1">
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
                                <button class="min-w-40 cursor-pointer rounded-xl border-0 bg-[#f7c600] px-[22px] py-3 font-black text-[#111]" type="button">
                                    <?= htmlspecialchars((string)($content['events']['ticket_button_label'] ?? 'Tickets')) ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- CAREER (WYSIWYG HTML supported) -->
        <?php
        $leftHtml = $career['left_html'] ?? null;
        $rightHtml = $career['right_html'] ?? null;
        $leftArr = $career['left'] ?? [];
        $rightArr = $career['right'] ?? [];
        ?>

        <div class="<?= $activeTab !== 'career' ? 'hidden' : '' ?> mt-[18px]" data-artist-panel="career">
            <div class="grid grid-cols-2 gap-[60px] max-[1200px]:grid-cols-1 max-[1200px]:gap-[22px]">
                <div class="wysiwyg">
                    <?php if (is_string($leftHtml) && $leftHtml !== ''): ?>
                        <?= Wysiwyg::render($leftHtml) ?>
                    <?php else: ?>
                        <?php foreach (($leftArr ?? []) as $line): ?>
                            <p class="mb-[18px] leading-[1.45] opacity-90">• <?= htmlspecialchars((string)$line) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="wysiwyg">
                    <?php if (is_string($rightHtml) && $rightHtml !== ''): ?>
                        <?= Wysiwyg::render($rightHtml) ?>
                    <?php else: ?>
                        <?php foreach (($rightArr ?? []) as $line): ?>
                            <p class="mb-[18px] leading-[1.45] opacity-90">• <?= htmlspecialchars((string)$line) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ALBUM (WYSIWYG description supported) -->
        <div class="<?= $activeTab !== 'album' ? 'hidden' : '' ?> mt-[18px]" data-artist-panel="album">
            <?php foreach ($albums as $alb): ?>
                <div class="grid grid-cols-[560px_1fr] items-start gap-7 max-[1200px]:grid-cols-1">
                    <div class="overflow-hidden rounded-2xl">
                        <?php
                        $img = $alb['image'] ?? null;

                        // supports BOTH formats:
                        // old: "image": "path.jpg"
                        // new: "image": {"src":"path.jpg","alt":"...","caption":"..."}
                        $imgSrc = '';
                        $imgAlt = (string)($alb['title'] ?? 'Album');
                        $imgCap = null;

                        if (is_string($img)) {
                            $imgSrc = $img;
                        } elseif (is_array($img)) {
                            $imgSrc = (string)($img['src'] ?? '');
                            $imgAlt = (string)($img['alt'] ?? $imgAlt);
                            $imgCap = $img['caption'] ?? null;
                        }
                        ?>

                        <img class="block w-full" src="/<?= htmlspecialchars($imgSrc) ?>"
                            alt="<?= htmlspecialchars($imgAlt) ?>">

                        <?php if (is_string($imgCap) && $imgCap !== ''): ?>
                            <div class="px-3 py-[10px] text-xs opacity-85"><?= htmlspecialchars($imgCap) ?></div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="mb-[6px] font-extrabold opacity-80">Album</div>
                        <div class="mb-[6px] text-[40px] font-black"><?= htmlspecialchars((string)($alb['artist'] ?? '')) ?></div>
                        <div class="mb-[10px] text-[28px] font-extrabold opacity-95"><?= htmlspecialchars((string)($alb['title'] ?? '')) ?></div>

                        <?php if (!empty($alb['description_html']) && is_string($alb['description_html'])): ?>
                            <div class="max-w-[860px] leading-[1.6] opacity-90 wysiwyg"><?= Wysiwyg::render($alb['description_html']) ?></div>
                        <?php else: ?>
                            <p class="max-w-[860px] leading-[1.6] opacity-90"><?= htmlspecialchars((string)($alb['description'] ?? '')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ABOUT + BAND -->
        <?php
        $about = $content['about'] ?? [];
        $band = $content['band_members'] ?? [];
        ?>

        <div class="mt-10 grid grid-cols-[1.3fr_1fr] gap-10 max-[1200px]:grid-cols-1">
            <div>
                <h3 class="mb-[10px] mt-0"><?= htmlspecialchars((string)($about['title'] ?? 'About')) ?></h3>

                <?php if (!empty($about['html']) && is_string($about['html'])): ?>
                    <div class="leading-[1.6] opacity-90 whitespace-pre-line wysiwyg"><?= Wysiwyg::render($about['html']) ?></div>
                <?php else: ?>
                    <p class="leading-[1.6] opacity-90 whitespace-pre-line"><?= htmlspecialchars((string)($about['text'] ?? '')) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <h3 class="mb-[10px] mt-0"><?= htmlspecialchars((string)($band['title'] ?? 'Band Members')) ?></h3>
                <ul class="m-0 list-disc pl-[18px] leading-[1.6] opacity-90">
                    <?php foreach (($band['items'] ?? []) as $m): ?>
                        <li><?= htmlspecialchars((string)$m) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </section>

    <script src="/assets/js/jazz/jazz_artist.js"></script>
</body>

</html>