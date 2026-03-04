<?php
/** @var array $content */
/** @var array $events */
/** @var string $activeTab */

$artist = $content['artist'] ?? [];
$labels = $content['tabs']['labels'] ?? ['events'=>'Events','career'=>'Career Highlights','album'=>'Album'];
$thumbs = $artist['thumbs'] ?? [];

$career = $content['career_highlights'] ?? ['left'=>[],'right'=>[]];
$albums = $content['albums'] ?? [];

$pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;

function tabLink(int $pageId, string $tab): string {
    return "/jazz/artist?page_id=" . $pageId . "&tab=" . urlencode($tab);
}

function safeTab(string $t): string {
    $allowed = ['events','career','album'];
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
        <div class="hero__inner">
            <div class="hero__kicker"><?= htmlspecialchars((string)($artist['kicker'] ?? '')) ?></div>
            <h1 class="hero__title"><?= htmlspecialchars((string)($artist['hero_title'] ?? ($artist['name'] ?? ''))) ?></h1>
            <div class="hero__subtitle"><?= htmlspecialchars((string)($artist['hero_subtitle'] ?? '')) ?></div>
        </div>

        <?php if (is_array($thumbs) && count($thumbs) > 0): ?>
            <div class="artist-hero__thumbs">
                <?php foreach ($thumbs as $t): ?>
                    <div class="artist-hero__thumb">
                        <img src="/<?= htmlspecialchars((string)($t['image'] ?? '')) ?>" alt="">
                        <?php if (!empty($t['caption'])): ?>
                            <div class="artist-hero__thumb-cap"><?= htmlspecialchars((string)$t['caption']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="schedule" style="padding-top:24px;">

        <!-- TABS (no reload when JS enabled; normal links still work when JS disabled) -->
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

        <!-- PANELS -->

        <!-- EVENTS -->
        <div class="artist-panel <?= $activeTab !== 'events' ? 'is-hidden' : '' ?>" data-artist-panel="events">
            <div class="artist-events-grid">
                <?php foreach ($events as $ev): ?>
                    <div class="artist-event">
                        <div class="artist-event__media">
                            <img src="/<?= htmlspecialchars((string)$ev['img_background']) ?>"
                                 alt="<?= htmlspecialchars((string)$ev['title']) ?>"
                                 loading="lazy">
                        </div>

                        <div class="artist-event__info">
                            <div class="artist-event__date"><?= htmlspecialchars((string)($ev['start_label'] ?? '')) ?></div>
                            <div class="artist-event__title"><?= htmlspecialchars((string)($ev['title'] ?? '')) ?></div>
                            <div class="artist-event__loc"><?= htmlspecialchars((string)($ev['location'] ?? '')) ?></div>

                            <button class="ticket-btn artist-event__cta" type="button">
                                <?= htmlspecialchars((string)($content['events']['ticket_button_label'] ?? 'Tickets')) ?>:
                                <?= htmlspecialchars(number_format((float)($ev['price'] ?? 0), 2)) ?> p.p
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CAREER HIGHLIGHTS -->
        <div class="artist-panel <?= $activeTab !== 'career' ? 'is-hidden' : '' ?>" data-artist-panel="career">
            <div class="artist-career">
                <div>
                    <?php foreach (($career['left'] ?? []) as $line): ?>
                        <p class="artist-bullet">• <?= htmlspecialchars((string)$line) ?></p>
                    <?php endforeach; ?>
                </div>
                <div>
                    <?php foreach (($career['right'] ?? []) as $line): ?>
                        <p class="artist-bullet">• <?= htmlspecialchars((string)$line) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ALBUM -->
        <div class="artist-panel <?= $activeTab !== 'album' ? 'is-hidden' : '' ?>" data-artist-panel="album">
            <?php foreach ($albums as $alb): ?>
                <div class="artist-album">
                    <div class="artist-album__media">
                        <img src="/<?= htmlspecialchars((string)($alb['image'] ?? '')) ?>"
                             alt="<?= htmlspecialchars((string)($alb['title'] ?? 'Album')) ?>">
                    </div>

                    <div>
                        <div class="artist-album__kicker">Album</div>
                        <div class="artist-album__artist"><?= htmlspecialchars((string)($alb['artist'] ?? '')) ?></div>
                        <div class="artist-album__title"><?= htmlspecialchars((string)($alb['title'] ?? '')) ?></div>
                        <p class="artist-album__desc"><?= htmlspecialchars((string)($alb['description'] ?? '')) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ABOUT + BAND (always visible) -->
        <?php $about = $content['about'] ?? []; $band = $content['band_members'] ?? []; ?>
        <div class="artist-bottom">
            <div class="artist-about">
                <h3><?= htmlspecialchars((string)($about['title'] ?? 'About')) ?></h3>
                <p><?= htmlspecialchars((string)($about['text'] ?? '')) ?></p>
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

    <!-- JS enables tab switching without reload -->
    <script src="/assets/js/jazz/jazz_artist.js"></script>
</body>
</html>