<?php

declare(strict_types=1);

use App\Utils\Wysiwyg;
use App\Utils\Media;

$hero = $content['hero'] ?? [];
$intro = $content['intro'] ?? [];
$lineup = $content['lineup'] ?? [];
$timetable = $content['timetable'] ?? [];

$bg = Media::image($hero['background_image'] ?? null);
$heroBgSrc = $bg['src'] !== '' ? $bg['src'] : 'assets/img/dance-assets/dance-hero-bg.png';

$introImg = Media::image($intro['side_image'] ?? null);
$introImgSrc = $introImg['src'] !== '' ? $introImg['src'] : 'assets/img/dance-assets/dance-intro-side.png';

$stripText = (string)($hero['strip_text'] ?? 'HAARLEM FESTIVAL DANCE');


$normaliseAsset = function (string $path, string $fallback): string {
  $path = trim($path);
  if ($path === '') return $fallback;
  if (strpos($path, 'assets/img/dance-assets/') === 0) return $path;
  return $fallback;
};

$heroBgSrc = $normaliseAsset($heroBgSrc, 'assets/img/dance-assets/dance-hero-bg.png');
$introImgSrc = $normaliseAsset($introImgSrc, 'assets/img/dance-assets/dance-intro-side.png');
$assetRoot = $danceAssetRoot ?? (($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/dance');
?>

<!-- Hero: background + overlay + title + subtitle + Buy ticket -->
<section class="dance-hero" style="background-image: url('<?= htmlspecialchars($assetRoot) ?>/<?= htmlspecialchars($heroBgSrc) ?>');">
  <div class="dance-hero-overlay"></div>
  <div class="dance-hero-inner">
    <?php
    $heroTitle = (string)($hero['title'] ?? 'HAARLEM DANCE EVENT');
    $heroTitleParts = preg_match('/^(.+?)\s+(.+)$/', $heroTitle, $m) ? [$m[1], $m[2]] : [$heroTitle, ''];
    ?>
    <h1 class="dance-hero-title">
      <?php if ($heroTitleParts[1] !== ''): ?>
        <span class="dance-hero-title-line"><?= htmlspecialchars($heroTitleParts[0]) ?></span>
        <span class="dance-hero-title-line dance-hero-title-line--main"><?= htmlspecialchars($heroTitleParts[1]) ?></span>
      <?php else: ?>
        <?= htmlspecialchars($heroTitle) ?>
      <?php endif; ?>
    </h1>
    <?php
    $subtitleHtml = $hero['subtitle_html'] ?? null;
    $subtitleLines = $hero['subtitle'] ?? null;
    ?>
    <?php if (is_string($subtitleHtml) && $subtitleHtml !== ''): ?>
      <div class="dance-hero-subtitle wysiwyg"><?= Wysiwyg::render($subtitleHtml) ?></div>
    <?php elseif (is_array($subtitleLines) && !empty($subtitleLines)): ?>
      <div class="dance-hero-subtitle"><?= implode("\n", array_map('htmlspecialchars', array_map('strval', $subtitleLines))) ?></div>
    <?php else: ?>
      <div class="dance-hero-subtitle">Discover Haarlem's vibrant nightlife
Experience top international DJs
Celebrate dance culture in the heart of the city</div>
    <?php endif; ?>
    <a href="#dance-timetable" class="dance-hero-cta"><?= htmlspecialchars((string)($hero['primary_button']['label'] ?? 'Buy ticket')) ?></a>
  </div>
</section>

<!-- Repeating strip -->
<div class="dance-strip">
  <div class="dance-strip-text">
    <span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span>
  </div>
</div>

<!-- Intro: Let Haarlem's music welcome you in + body + right image + stats -->
<section class="dance-intro">
  <div>
    <h2 class="dance-intro-heading"><?= htmlspecialchars((string)($intro['kicker'] ?? "Let Haarlem's music welcome you in")) ?></h2>
    <?php
    $bodyHtml = $intro['body_html'] ?? null;
    $paras = $intro['paragraphs'] ?? null;
    ?>
    <?php if (is_string($bodyHtml) && $bodyHtml !== ''): ?>
      <div class="dance-intro-body wysiwyg"><?= Wysiwyg::render($bodyHtml) ?></div>
    <?php elseif (is_array($paras) && !empty($paras)): ?>
      <div class="dance-intro-body">
        <?php foreach ($paras as $p): ?>
          <p><?= htmlspecialchars((string)$p) ?></p>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="dance-intro-body">
        <p>The Dance Event is where Haarlem truly comes alive. As the sun goes down, the city switches into a completely different mode — neon lights, deep bass, and a crowd that's ready to move. World-class DJs, immersive light shows, and the city's vibrant nightlife all come together to create nights that feel electric.</p>
        <p>Here, it doesn't matter if you're a die-hard rave lover or someone who's just curious about the scene. Maybe you come for the heavy drops, maybe for the atmosphere, or maybe you just want to dance with friends until your legs can't keep up — either way, you'll fit right in.</p>
        <p>Across the festival's 3 days, Haarlem transforms into a playground for rhythmic energy: back-to-back DJ sets, intimate experimental sessions, and massive stages that pull you in with sound you can feel straight in your chest.</p>
        <p>So dive into the lights, join the crowd, and let yourself get carried by the rhythm. This is Dance — where excitement, connection, and pure nightlife energy meet.</p>
      </div>
    <?php endif; ?>
  </div>
  <div class="dance-intro-side">
    <img src="<?= htmlspecialchars($assetRoot) ?>/<?= htmlspecialchars($introImgSrc) ?>" alt="<?= htmlspecialchars((string)($introImg['alt'] ?? 'Dance event')) ?>" class="dance-intro-image" loading="lazy">
    <?php $stats = $intro['stats'] ?? $hero['stats'] ?? ['3 days', '6 DJs', '2490 min']; $stats = is_array($stats) ? $stats : []; ?>
    <div class="dance-intro-stats"><?= htmlspecialchars(implode('  ', array_map('strval', $stats))) ?></div>
  </div>
</section>

<!-- TOP TIER LINEUP -->
<?php $artists = is_array($lineup['artists'] ?? null) ? $lineup['artists'] : []; ?>
<section class="dance-lineup">
  <div class="dance-lineup-inner">
    <h2 class="dance-lineup-title"><?= htmlspecialchars((string)($lineup['title'] ?? 'TOP TIER LINEUP ...')) ?></h2>
    <div class="dance-lineup-grid">
      <?php
      $defaultDjs = [
        ['name' => 'MARTIN GARRIX', 'image' => 'assets/img/dance-assets/dj-martin.png'],
        ['name' => 'ARMIN VAN BUUREN', 'image' => 'assets/img/dance-assets/dj-armin.png'],
        ['name' => 'TIËSTO', 'image' => 'assets/img/dance-assets/dj-tiesto.png'],
        ['name' => 'HARDWELL', 'image' => 'assets/img/dance-assets/dj-hardwell.png'],
        ['name' => 'AFROJACK', 'image' => 'assets/img/dance-assets/dance-extra-1.png'],
        ['name' => 'NICKY ROMERO', 'image' => 'assets/img/dance-assets/dance-extra-2.png'],
      ];
      $defaultLineupImages = [
        'assets/img/dance-assets/dj-martin.png',
        'assets/img/dance-assets/dj-armin.png',
        'assets/img/dance-assets/dj-tiesto.png',
        'assets/img/dance-assets/dj-hardwell.png',
        'assets/img/dance-assets/dance-extra-1.png',
        'assets/img/dance-assets/dance-extra-2.png',
      ];
      $artists = !empty($artists) ? $artists : $defaultDjs;
      $artistIndex = 0;
      foreach (array_slice($artists, 0, 6) as $artist):
        $img = Media::image($artist['image'] ?? null);
        $rawSrc = $img['src'] !== '' ? $img['src'] : '';
        $imgSrc = $normaliseAsset($rawSrc, $defaultLineupImages[$artistIndex] ?? 'assets/img/dance-assets/dance-intro-side.png');
        $artistIndex++;
      ?>
        <div class="dance-lineup-card">
          <div class="dance-lineup-card-logo">
            <div class="dance-lineup-card-image-wrap">
              <img src="<?= htmlspecialchars($assetRoot) ?>/<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars((string)($artist['name'] ?? 'DJ')) ?>" class="dance-lineup-card-image" loading="lazy">
            </div>
          </div>
          <div class="dance-lineup-card-name"><?= htmlspecialchars((string)($artist['name'] ?? 'DJ')) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Repeating strip -->
<div class="dance-strip">
  <div class="dance-strip-text">
    <span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span>
  </div>
</div>

<!-- TIME TABLE -->
<?php
$passes = is_array($timetable['passes'] ?? null) ? $timetable['passes'] : [];
$rows = is_array($timetable['rows'] ?? null) ? $timetable['rows'] : [];
$dateRange = (string)($timetable['date_range'] ?? 'Friday July 25th -> Sunday July 27th');

$allAccess = null;
$dayPassesByDay = [];
foreach ($passes as $p) {
  $label = (string)($p['label'] ?? '');
  if (stripos($label, 'ALL-ACCESS') !== false) {
    $allAccess = $p;
  } else {
    $dayPassesByDay[] = $p;
  }
}

$rowsByDay = [];
foreach ($rows as $r) {
  $d = (string)($r['day_label'] ?? '');
  if ($d !== '') {
    if (!isset($rowsByDay[$d])) $rowsByDay[$d] = [];
    $rowsByDay[$d][] = $r;
  }
}

$defaultDays = [
  ['label' => 'Friday July 25th', 'pass_label' => 'DAY PASS FOR FRIDAY', 'pass_price' => '€125.00', 'events' => [
    ['artist' => 'NICKY ROMERO / AFROJACK', 'tag' => 'B2B', 'start' => '20:00', 'end' => '02:00', 'venue' => 'Lichtfabriek', 'price' => '€75.00', 'time_tick' => '08PM'],
  ]],
  ['label' => 'Saturday July 26th', 'pass_label' => 'DAY PASS FOR SATURDAY', 'pass_price' => '€125.00', 'events' => []],
  ['label' => 'Sunday July 27th', 'pass_label' => 'DAY PASS FOR SUNDAY', 'pass_price' => '€125.00', 'events' => []],
];
$useDefault = empty($passes) && empty($rows);
?>
<section class="dance-timetable" id="dance-timetable">
  <div class="timetable-container">
    <h2 class="main-title"><?= htmlspecialchars((string)($timetable['title'] ?? 'time table')) ?></h2>
    <p class="daterange"><?= htmlspecialchars($dateRange) ?></p>

    <?php if ($useDefault || $allAccess !== null): ?>
    <div class="pass-section">
      <div class="pass-row highlight">
        <span class="pass-label"><?= htmlspecialchars($allAccess ? (string)($allAccess['label'] ?? '') : 'ALL-ACCESS PASS 3 DAYS') ?></span>
        <div class="pass-info">
          <span class="info-badge"><?= htmlspecialchars((string)($allAccess['note'] ?? 'NO garanteed')) ?></span>
        </div>
        <span class="pass-price"><?= htmlspecialchars($allAccess ? (string)($allAccess['price'] ?? '') : '€250.00') ?></span>
        <?php if ($allAccess && !empty($allAccess['event_id'])): ?>
          <form method="POST" action="/order/item/add" style="display:inline;"><input type="hidden" name="event_id" value="<?= (int)$allAccess['event_id'] ?>"><button type="submit" class="add-button"><span class="cart-icon" aria-hidden="true"></span> ADD</button></form>
        <?php else: ?>
          <button type="button" class="add-button" disabled><span class="cart-icon" aria-hidden="true"></span> ADD</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($useDefault): ?>
      <?php foreach ($defaultDays as $day): ?>
    <div class="day-section">
      <div class="day-header"><?= htmlspecialchars($day['label']) ?></div>
      <div class="pass-row day-pass">
        <span class="pass-label"><?= htmlspecialchars($day['pass_label']) ?></span>
        <span class="pass-price"><?= htmlspecialchars($day['pass_price']) ?></span>
        <button type="button" class="add-button" disabled><span class="cart-icon" aria-hidden="true"></span> ADD</button>
      </div>
        <?php if (!empty($day['events'])): ?>
      <div class="timeline">
          <?php foreach ($day['events'] as $ev): ?>
        <div class="event-item">
          <span class="timeline-marker"><?= htmlspecialchars($ev['time_tick'] ?? '') ?></span>
          <span class="timeline-line" aria-hidden="true"></span>
          <div class="event-row">
            <div class="artist-info">
              <div class="artist-names"><?= htmlspecialchars($ev['artist']) ?></div>
              <?php if (!empty($ev['tag'])): ?><span class="artist-tag"><?= htmlspecialchars($ev['tag']) ?></span><?php endif; ?>
            </div>
            <span class="event-time"><?= htmlspecialchars($ev['start'] . ' - ' . $ev['end']) ?></span>
            <span class="event-venue"><?= htmlspecialchars($ev['venue']) ?></span>
            <span class="event-price"><?= htmlspecialchars($ev['price']) ?></span>
            <button type="button" class="add-button" disabled><span class="cart-icon" aria-hidden="true"></span> ADD</button>
          </div>
        </div>
          <?php endforeach; ?>
      </div>
        <?php endif; ?>
    </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?php
      $dayOrder = [];
      foreach ($dayPassesByDay as $p) {
        $l = (string)($p['label'] ?? '');
        if (stripos($l, 'FRIDAY') !== false) $dayOrder['Friday July 25th'] = $p;
        elseif (stripos($l, 'SATURDAY') !== false) $dayOrder['Saturday July 26th'] = $p;
        elseif (stripos($l, 'SUNDAY') !== false) $dayOrder['Sunday July 27th'] = $p;
      }
      foreach (array_keys($rowsByDay) as $d) {
        if (!isset($dayOrder[$d])) $dayOrder[$d] = ['label' => $d . ' Pass', 'price' => '', 'event_id' => null];
      }
      foreach ($dayOrder as $dayLabel => $dayPass):
        $dayRows = $rowsByDay[$dayLabel] ?? [];
        $passLabel = is_array($dayPass) ? (string)($dayPass['label'] ?? $dayLabel) : $dayLabel;
        $passPrice = is_array($dayPass) ? (string)($dayPass['price'] ?? '') : '';
        $passEventId = is_array($dayPass) && isset($dayPass['event_id']) ? (int)$dayPass['event_id'] : 0;
      ?>
    <div class="day-section">
      <div class="day-header"><?= htmlspecialchars($dayLabel) ?></div>
      <div class="pass-row day-pass">
        <span class="pass-label"><?= htmlspecialchars($passLabel) ?></span>
        <span class="pass-price"><?= htmlspecialchars($passPrice) ?></span>
        <?php if ($passEventId > 0): ?>
          <form method="POST" action="/order/item/add" style="display:inline;"><input type="hidden" name="event_id" value="<?= $passEventId ?>"><button type="submit" class="add-button"><span class="cart-icon" aria-hidden="true"></span> ADD</button></form>
        <?php else: ?>
          <button type="button" class="add-button" disabled><span class="cart-icon" aria-hidden="true"></span> ADD</button>
        <?php endif; ?>
      </div>
        <?php if (!empty($dayRows)): ?>
      <div class="timeline">
          <?php foreach ($dayRows as $row):
            $artist = (string)($row['artist'] ?? '');
            $tag = isset($row['tag']) ? (string)$row['tag'] : '';
            $tagSpecial = !empty($row['tag_special']);
            $start = (string)($row['start'] ?? '');
            $end = (string)($row['end'] ?? '');
            $venue = (string)($row['venue'] ?? '');
            $priceLabel = (string)($row['price_label'] ?? '');
            $eventId = isset($row['event_id']) ? (int)$row['event_id'] : 0;
            $timeTick = (string)($row['time_tick'] ?? '');
            if ($timeTick === '' && $start !== '') {
              $h = (int)substr($start, 0, 2);
              $timeTick = sprintf('%02d%s', $h <= 12 ? $h : $h - 12, $h < 12 ? 'AM' : 'PM');
            }
          ?>
        <div class="event-item">
          <span class="timeline-marker"><?= htmlspecialchars($timeTick) ?></span>
          <span class="timeline-line" aria-hidden="true"></span>
          <div class="event-row<?= !empty($row['tall']) ? ' tall' : '' ?>">
            <div class="artist-info">
              <div class="artist-names"><?= htmlspecialchars($artist) ?></div>
              <?php if ($tag !== ''): ?><span class="artist-tag<?= $tagSpecial ? ' special' : '' ?>"><?php if ($tagSpecial): ?><span class="star-icon" aria-hidden="true"></span><?php endif; ?><?= htmlspecialchars($tag) ?></span><?php endif; ?>
            </div>
            <span class="event-time"><?= htmlspecialchars($start . ' - ' . $end) ?></span>
            <span class="event-venue"><?= htmlspecialchars($venue) ?></span>
            <span class="event-price"><?= htmlspecialchars($priceLabel) ?></span>
            <?php if ($eventId > 0): ?>
              <form method="POST" action="/order/item/add" style="display:inline;"><input type="hidden" name="event_id" value="<?= $eventId ?>"><button type="submit" class="add-button"><span class="cart-icon" aria-hidden="true"></span> ADD</button></form>
            <?php else: ?>
              <button type="button" class="add-button" disabled><span class="cart-icon" aria-hidden="true"></span> ADD</button>
            <?php endif; ?>
          </div>
        </div>
          <?php endforeach; ?>
      </div>
        <?php endif; ?>
    </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- Repeating strip -->
<div class="dance-strip">
  <div class="dance-strip-text">
    <span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span><span><?= htmlspecialchars($stripText) ?></span>
  </div>
</div>
