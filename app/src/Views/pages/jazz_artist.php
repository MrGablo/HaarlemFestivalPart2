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
    <link rel="stylesheet" href="/assets/css/jazz/stylesheet.css">
</head>

<body class="jazz-page">

    <!-- HERO -->
    <section class="hero artist-hero"
        style="background-image:url('/<?= htmlspecialchars((string)($artist['cover_image'] ?? '')) ?>')">

        <div class="artist-hero__top">
            <a class="artist-back" href="<?= htmlspecialchars((string)($bc['back_href'] ?? '/jazz')) ?>">
                ← <?= htmlspecialchars((string)($bc['back_label'] ?? 'Back')) ?>
            </a>
            <?php if (!empty($bc['current'])): ?>
                <span class="artist-crumb">› <?= htmlspecialchars((string)$bc['current']) ?></span>
            <?php endif; ?>
        </div>

        <div class="hero__inner">
            <div class="hero__kicker"><?= htmlspecialchars((string)($artist['kicker'] ?? '')) ?></div>
            <h1 class="hero__title"><?= htmlspecialchars((string)($artist['hero_title'] ?? ($artist['name'] ?? ''))) ?></h1>
            <div class="hero__subtitle"><?= htmlspecialchars((string)($artist['hero_subtitle'] ?? '')) ?></div>
        </div>

        <div class="artist-hero__media">
            <?php if (is_array($mainMedia) && !empty($mainMedia['image'])): ?>
                <div class="artist-hero__media-main">
                    <img src="/<?= htmlspecialchars((string)$mainMedia['image']) ?>" alt="">
                </div>
            <?php endif; ?>

            <?php if (is_array($secondaryMedia) && count($secondaryMedia) > 0): ?>
                <div class="artist-hero__media-secondary">
                    <?php foreach ($secondaryMedia as $s): ?>
                        <div class="artist-hero__thumb">
                            <img src="/<?= htmlspecialchars((string)($s['image'] ?? '')) ?>" alt="">
                            <?php if (!empty($s['caption'])): ?>
                                <div class="artist-hero__thumb-cap"><?= htmlspecialchars((string)$s['caption']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="schedule artist-shell" style="padding-top:24px;">

        <!-- TABS -->
        <div class="artist-tabs">
            <a class="chip <?= $activeTab === 'events' ? 'is-active' : '' ?>"
                data-artist-tab="events"
                href="<?= htmlspecialchars(tabLink($pageId, 'events')) ?>">
                <?= htmlspecialchars((string)($labels['events'] ?? 'Events')) ?>
            </a>

            <a class="chip <?= $activeTab === 'career' ? 'is-active' : '' ?>"
                data-artist-tab="career"
                href="<?= htmlspecialchars(tabLink($pageId, 'career')) ?>">
                <?= htmlspecialchars((string)($labels['career'] ?? 'Career Highlights')) ?>
            </a>

            <a class="chip <?= $activeTab === 'album' ? 'is-active' : '' ?>"
                data-artist-tab="album"
                href="<?= htmlspecialchars(tabLink($pageId, 'album')) ?>">
                <?= htmlspecialchars((string)($labels['album'] ?? 'Album')) ?>
            </a>
        </div>

        <!-- EVENTS -->
        <div class="artist-panel <?= $activeTab !== 'events' ? 'is-hidden' : '' ?>" data-artist-panel="events">
            <div class="artist-events">
                <div class="artist-events__bar"></div>

                <div class="artist-events__list">
                    <?php foreach ($events as $ev): ?>
                        <div class="artist-row">
                            <div class="artist-row__media">
                                <img src="/<?= htmlspecialchars((string)($ev['img_background'] ?? '')) ?>"
                                    alt="<?= htmlspecialchars((string)($ev['title'] ?? '')) ?>"
                                    loading="lazy">
                            </div>

                            <div class="artist-row__info">
                                <div class="artist-row__date"><?= htmlspecialchars((string)($ev['start_label'] ?? '')) ?></div>
                                <div class="artist-row__title"><?= htmlspecialchars((string)($ev['title'] ?? '')) ?></div>
                                <div class="artist-row__loc"><?= htmlspecialchars((string)($ev['location'] ?? '')) ?></div>
                            </div>

                            <div class="artist-row__cta">
                                <button class="artist-ticket" type="button">
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

        <div class="artist-panel <?= $activeTab !== 'career' ? 'is-hidden' : '' ?>" data-artist-panel="career">
            <div class="artist-career">
                <div class="wysiwyg">
                    <?php if (is_string($leftHtml) && $leftHtml !== ''): ?>
                        <?= Wysiwyg::render($leftHtml) ?>
                    <?php else: ?>
                        <?php foreach (($leftArr ?? []) as $line): ?>
                            <p class="artist-bullet">• <?= htmlspecialchars((string)$line) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="wysiwyg">
                    <?php if (is_string($rightHtml) && $rightHtml !== ''): ?>
                        <?= Wysiwyg::render($rightHtml) ?>
                    <?php else: ?>
                        <?php foreach (($rightArr ?? []) as $line): ?>
                            <p class="artist-bullet">• <?= htmlspecialchars((string)$line) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ALBUM (WYSIWYG description supported) -->
        <div class="artist-panel <?= $activeTab !== 'album' ? 'is-hidden' : '' ?>" data-artist-panel="album">
            <?php foreach ($albums as $alb): ?>
                <div class="artist-album">
                    <div class="artist-album__media">
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

                        <img src="/<?= htmlspecialchars($imgSrc) ?>"
                            alt="<?= htmlspecialchars($imgAlt) ?>">

                        <?php if (is_string($imgCap) && $imgCap !== ''): ?>
                            <div class="artist-album__caption"><?= htmlspecialchars($imgCap) ?></div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="artist-album__kicker">Album</div>
                        <div class="artist-album__artist"><?= htmlspecialchars((string)($alb['artist'] ?? '')) ?></div>
                        <div class="artist-album__title"><?= htmlspecialchars((string)($alb['title'] ?? '')) ?></div>

                        <?php if (!empty($alb['description_html']) && is_string($alb['description_html'])): ?>
                            <div class="artist-album__desc wysiwyg"><?= Wysiwyg::render($alb['description_html']) ?></div>
                        <?php else: ?>
                            <p class="artist-album__desc"><?= htmlspecialchars((string)($alb['description'] ?? '')) ?></p>
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

        <div class="artist-bottom">
            <div class="artist-about">
                <h3><?= htmlspecialchars((string)($about['title'] ?? 'About')) ?></h3>

                <?php if (!empty($about['html']) && is_string($about['html'])): ?>
                    <div class="wysiwyg"><?= Wysiwyg::render($about['html']) ?></div>
                <?php else: ?>
                    <p><?= htmlspecialchars((string)($about['text'] ?? '')) ?></p>
                <?php endif; ?>
            </div>

            <div class="artist-band">
                <h3><?= htmlspecialchars((string)($band['title'] ?? 'Band Members')) ?></h3>
                <ul>
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