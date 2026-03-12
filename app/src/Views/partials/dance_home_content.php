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


$knownDanceAssets = [
  'dance-hero-bg.png', 'dance-intro-side.png', 'dance-timetable-texture.png',
  'dj-martin.png', 'dj-armin.png', 'dj-tiesto.png', 'dj-hardwell.png',
  'dj-nicky-romero.png', 'dj-afrojack.png',
];
$normaliseAsset = function (string $path, string $fallback) use ($knownDanceAssets): string {
  $path = trim($path);
  if ($path === '') return $fallback;
  if (strpos($path, 'assets/img/dance-assets/') !== 0) return $fallback;
  $base = basename($path);
  if (in_array($base, $knownDanceAssets, true)) return $path;
  return $fallback;
};

$heroBgSrc = $normaliseAsset($heroBgSrc, 'assets/img/dance-assets/dance-hero-bg.png');
$introImgSrc = $normaliseAsset($introImgSrc, 'assets/img/dance-assets/dance-intro-side.png');
$assetRoot = $danceAssetRoot ?? (($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/dance');
?>

<!-- Hero: background + overlay + title + subtitle + Buy ticket (Tailwind) -->
<section
  class="relative min-h-[90vh] overflow-hidden bg-cover bg-center"
  style="background-image: url('<?= htmlspecialchars($assetRoot) ?>/<?= htmlspecialchars($heroBgSrc) ?>');"
>
  <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/75 pointer-events-none" aria-hidden="true"></div>
  <div class="relative z-10 mx-auto max-w-[1200px] px-6 pb-20 pt-40">
    <?php
    $heroTitle = (string)($hero['title'] ?? 'HAARLEM DANCE EVENT');
    $heroTitleParts = preg_match('/^(.+?)\s+(.+)$/', $heroTitle, $m) ? [$m[1], $m[2]] : [$heroTitle, ''];
    ?>
    <h1 class="mb-6 flex flex-col text-[clamp(48px,10vw,96px)] font-bold leading-tight uppercase text-white drop-shadow-[0_4px_4px_rgba(0,0,0,0.32)]">
      <?php if ($heroTitleParts[1] !== ''): ?>
        <span><?= htmlspecialchars($heroTitleParts[0]) ?></span>
        <span><?= htmlspecialchars($heroTitleParts[1]) ?></span>
      <?php else: ?>
        <?= htmlspecialchars($heroTitle) ?>
      <?php endif; ?>
    </h1>
    <?php
    $subtitleHtml = $hero['subtitle_html'] ?? null;
    $subtitleLines = $hero['subtitle'] ?? null;
    ?>
    <?php if (is_string($subtitleHtml) && $subtitleHtml !== ''): ?>
      <div class="wysiwyg mb-7 max-w-[615px] text-[25px] font-bold leading-snug text-white whitespace-pre-line"><?= Wysiwyg::render($subtitleHtml) ?></div>
    <?php elseif (is_array($subtitleLines) && !empty($subtitleLines)): ?>
      <div class="mb-7 max-w-[615px] text-[25px] font-bold leading-snug text-white whitespace-pre-line"><?= implode("\n", array_map('htmlspecialchars', array_map('strval', $subtitleLines))) ?></div>
    <?php else: ?>
      <div class="mb-7 max-w-[615px] text-[25px] font-bold leading-snug text-white whitespace-pre-line">Discover Haarlem's vibrant nightlife
Experience top international DJs
Celebrate dance culture in the heart of the city</div>
    <?php endif; ?>
    <a href="#dance-timetable" class="inline-block rounded-lg bg-white/20 px-6 py-2.5 font-bold text-xl text-white shadow-[0_0_2.6px_0_#410000] transition hover:bg-white hover:text-[#191717]"><?= htmlspecialchars((string)($hero['primary_button']['label'] ?? 'Buy ticket')) ?></a>
  </div>
</section>

<!-- Repeating strip (marquee): layout Tailwind, animation in page <style> -->
<div class="flex min-h-[67px] items-center overflow-hidden bg-black py-3.5">
  <div class="dance-strip-track flex w-max" aria-hidden="true">
    <div class="dance-strip-text flex flex-shrink-0 text-[clamp(20px,4vw,39px)] font-bold tracking-[0.02em] text-[#F9F9F9]">
      <span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span>
    </div>
    <div class="dance-strip-text flex flex-shrink-0 text-[clamp(20px,4vw,39px)] font-bold tracking-[0.02em] text-[#F9F9F9]">
      <span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span>
    </div>
  </div>
</div>

<!-- Intro (Tailwind) -->
<section class="mx-auto grid max-w-[1200px] grid-cols-1 gap-10 px-6 py-16 md:grid-cols-2 md:gap-12 md:py-20">
  <div>
    <h2 class="mb-4 text-2xl font-semibold tracking-wide text-[#F9F9F9] md:text-3xl"><?= htmlspecialchars((string)($intro['kicker'] ?? "Let Haarlem's music welcome you in")) ?></h2>
    <?php
    $bodyHtml = $intro['body_html'] ?? null;
    $paras = $intro['paragraphs'] ?? null;
    ?>
    <?php if (is_string($bodyHtml) && $bodyHtml !== ''): ?>
      <div class="wysiwyg text-[#F9F9F9] [&_p]:mb-4"><?= Wysiwyg::render($bodyHtml) ?></div>
    <?php elseif (is_array($paras) && !empty($paras)): ?>
      <div class="text-[#F9F9F9] [&_p]:mb-4">
        <?php foreach ($paras as $p): ?>
          <p><?= htmlspecialchars((string)$p) ?></p>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-[#F9F9F9] [&_p]:mb-4">
        <p>The Dance Event is where Haarlem truly comes alive. As the sun goes down, the city switches into a completely different mode — neon lights, deep bass, and a crowd that's ready to move. World-class DJs, immersive light shows, and the city's vibrant nightlife all come together to create nights that feel electric.</p>
        <p>Here, it doesn't matter if you're a die-hard rave lover or someone who's just curious about the scene. Maybe you come for the heavy drops, maybe for the atmosphere, or maybe you just want to dance with friends until your legs can't keep up — either way, you'll fit right in.</p>
        <p>Across the festival's 3 days, Haarlem transforms into a playground for rhythmic energy: back-to-back DJ sets, intimate experimental sessions, and massive stages that pull you in with sound you can feel straight in your chest.</p>
        <p>So dive into the lights, join the crowd, and let yourself get carried by the rhythm. This is Dance — where excitement, connection, and pure nightlife energy meet.</p>
      </div>
    <?php endif; ?>
  </div>
  <div class="flex flex-col items-start">
    <img src="<?= htmlspecialchars($assetRoot) ?>/<?= htmlspecialchars($introImgSrc) ?>" alt="<?= htmlspecialchars((string)($introImg['alt'] ?? 'Dance event')) ?>" class="mb-4 w-full max-w-md rounded object-cover" loading="lazy">
    <?php $stats = $intro['stats'] ?? $hero['stats'] ?? ['3 days', '6 DJs', '2490 min']; $stats = is_array($stats) ? $stats : []; ?>
    <div class="text-sm text-[#F9F9F9]/80"><?= htmlspecialchars(implode('  ', array_map('strval', $stats))) ?></div>
  </div>
</section>

<!-- TOP TIER LINEUP (Tailwind) -->
<?php $artists = is_array($lineup['artists'] ?? null) ? $lineup['artists'] : []; ?>
<section class="bg-[#191717] px-6 py-14 md:py-20">
  <div class="mx-auto max-w-[1200px]">
    <h2 class="mb-10 text-center text-2xl font-bold uppercase tracking-wider text-[#F9F9F9] md:text-3xl"><?= htmlspecialchars((string)($lineup['title'] ?? 'TOP TIER LINEUP ...')) ?></h2>
    <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 md:gap-10">
      <?php
      $defaultDjs = [
        ['name' => 'MARTIN GARRIX', 'image' => 'assets/img/dance-assets/dj-martin.png'],
        ['name' => 'ARMIN VAN BUUREN', 'image' => 'assets/img/dance-assets/dj-armin.png'],
        ['name' => 'TIËSTO', 'image' => 'assets/img/dance-assets/dj-tiesto.png'],
        ['name' => 'HARDWELL', 'image' => 'assets/img/dance-assets/dj-hardwell.png'],
        ['name' => 'AFROJACK', 'image' => 'assets/img/dance-assets/dj-afrojack.png'],
        ['name' => 'NICKY ROMERO', 'image' => 'assets/img/dance-assets/dj-nicky-romero.png'],
      ];
      $defaultLineupImages = [
        'assets/img/dance-assets/dj-martin.png',
        'assets/img/dance-assets/dj-armin.png',
        'assets/img/dance-assets/dj-tiesto.png',
        'assets/img/dance-assets/dj-hardwell.png',
        'assets/img/dance-assets/dj-afrojack.png',
        'assets/img/dance-assets/dj-nicky-romero.png',
      ];
      $artists = !empty($artists) ? $artists : $defaultDjs;
      $artistIndex = 0;
      foreach (array_slice($artists, 0, 6) as $artist):
        $img = Media::image($artist['image'] ?? null);
        $rawSrc = $img['src'] !== '' ? $img['src'] : '';
        $imgSrc = $normaliseAsset($rawSrc, $defaultLineupImages[$artistIndex] ?? 'assets/img/dance-assets/dance-intro-side.png');
        $artistIndex++;
      ?>
        <div class="flex flex-col items-center">
          <div class="relative h-[120px] w-[120px] overflow-hidden rounded-none md:h-[140px] md:w-[140px]">
            <img src="<?= htmlspecialchars($assetRoot) ?>/<?= htmlspecialchars($imgSrc) ?>" alt="" class="h-full w-full object-cover object-top" loading="lazy">
          </div>
          <div class="mt-3.5 max-w-[220px] text-center text-sm font-bold uppercase tracking-wide text-[#F9F9F9] md:text-base"><?= htmlspecialchars((string)($artist['name'] ?? 'DJ')) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Repeating strip (marquee) -->
<div class="flex min-h-[67px] items-center overflow-hidden bg-black py-3.5">
  <div class="dance-strip-track flex w-max" aria-hidden="true">
    <div class="dance-strip-text flex flex-shrink-0 text-[clamp(20px,4vw,39px)] font-bold tracking-[0.02em] text-[#F9F9F9]">
      <span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span>
    </div>
    <div class="dance-strip-text flex flex-shrink-0 text-[clamp(20px,4vw,39px)] font-bold tracking-[0.02em] text-[#F9F9F9]">
      <span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span>
    </div>
  </div>
</div>

<!-- TIME TABLE -->
<?php
$passes = is_array($timetable['passes'] ?? null) ? $timetable['passes'] : [];
$rows = is_array($timetable['rows'] ?? null) ? $timetable['rows'] : [];
$dateRange = (string)($timetable['date_range'] ?? 'Friday July 25th → Sunday July 27th');

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

$normalizeDayLabel = function (string $d): string {
  $d = trim($d);
  if (stripos($d, 'Friday') !== false) return 'Friday July 25th';
  if (stripos($d, 'Saturday') !== false) return 'Saturday July 26th';
  if (stripos($d, 'Sunday') !== false) return 'Sunday July 27th';
  return $d;
};
$rowsByDay = [];
foreach ($rows as $r) {
  $d = $normalizeDayLabel((string)($r['day_label'] ?? ''));
  if ($d !== '') {
    if (!isset($rowsByDay[$d])) $rowsByDay[$d] = [];
    $rowsByDay[$d][] = $r;
  }
}

$defaultDays = [
  ['label' => 'Friday July 25th', 'pass_label' => 'DAY PASS FOR FRIDAY', 'pass_price' => '€125.00', 'events' => [
    ['artist' => 'NICKY ROMERO / AFROJACK', 'tag' => 'B2B', 'tag_special' => false, 'start' => '20:00', 'end' => '02:00', 'venue' => 'Lichtfabriek', 'price' => '€75.00', 'time_tick' => '08PM'],
    ['artist' => 'TIËSTO', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '22:00', 'end' => '23:30', 'venue' => 'Slachthuis', 'price' => '€60.00', 'time_tick' => '10PM'],
    ['artist' => 'ARMIN VAN BUUREN', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '22:00', 'end' => '02:00', 'venue' => 'XO the Club', 'price' => '€60.00', 'time_tick' => '10PM'],
    ['artist' => 'MARTIN GARRIX', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '22:00', 'end' => '02:00', 'venue' => 'Puncher comedy club', 'price' => '€60.00', 'time_tick' => '10PM'],
    ['artist' => 'HARDWELL', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '23:00', 'end' => '00:30', 'venue' => 'Jopenkerk', 'price' => '€60.00', 'time_tick' => '11PM'],
  ]],
  ['label' => 'Saturday July 26th', 'pass_label' => 'DAY PASS FOR SATURDAY', 'pass_price' => '€125.00', 'events' => [
    ['artist' => 'HARDWELL / MARTIN GARRIX / ARMIN VAN BUUREN', 'tag' => 'B2B', 'tag_special' => false, 'start' => '14:00', 'end' => '23:00', 'venue' => 'Caprera Openluchttheater', 'price' => '€110.00', 'time_tick' => '02PM'],
    ['artist' => 'TIËSTO', 'tag' => 'TIËSTOWORLD', 'tag_special' => true, 'start' => '21:00', 'end' => '01:00', 'venue' => 'Lichtfabriek', 'price' => '€75.00', 'time_tick' => '09PM'],
    ['artist' => 'AFROJACK', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '22:00', 'end' => '23:30', 'venue' => 'Opener', 'price' => '€60.00', 'time_tick' => '10PM'],
    ['artist' => 'NICKY ROMERO', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '23:00', 'end' => '00:30', 'venue' => 'Slachthuis', 'price' => '€60.00', 'time_tick' => '11PM'],
  ]],
  ['label' => 'Sunday July 27th', 'pass_label' => 'DAY PASS FOR SUNDAY', 'pass_price' => '€125.00', 'events' => [
    ['artist' => 'AFROJACK / TIËSTO / NICKY ROMERO', 'tag' => 'B2B', 'tag_special' => false, 'start' => '14:00', 'end' => '23:00', 'venue' => 'Caprera Openluchttheater', 'price' => '€110.00', 'time_tick' => '02PM'],
    ['artist' => 'MARTIN GARRIX', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '18:00', 'end' => '19:30', 'venue' => 'Slachthuis', 'price' => '€60.00', 'time_tick' => '06PM'],
    ['artist' => 'ARMIN VAN BUUREN', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '19:00', 'end' => '20:30', 'venue' => 'Jopenkerk', 'price' => '€60.00', 'time_tick' => '07PM'],
    ['artist' => 'HARDWELL', 'tag' => 'CLUB', 'tag_special' => false, 'start' => '21:00', 'end' => '22:30', 'venue' => 'XO the Club', 'price' => '€90.00', 'time_tick' => '09PM'],
  ]],
];
$useDefault = empty($passes) && empty($rows);
?>
<section id="dance-timetable" class="bg-[#0d0d0d] bg-center bg-cover bg-no-repeat py-10 px-5 md:py-10 md:px-5" style="background-image: url('<?= htmlspecialchars($assetRoot) ?>/assets/img/dance-assets/dance-timetable-texture.png');">
  <div class="mx-auto max-w-[1175px] font-['Montserrat',sans-serif]">
    <h2 class="mb-3 text-[28px] font-bold uppercase leading-tight text-[#F9F9F9] md:text-4xl lg:text-[49px] lg:leading-[58.8px]"><?= htmlspecialchars((string)($timetable['title'] ?? 'time table')) ?></h2>
    <p class="mb-10 text-[13px] font-normal leading-[19.5px] text-[#F9F9F9]/85"><?= htmlspecialchars($dateRange) ?></p>

    <?php if ($useDefault || $allAccess !== null): ?>
    <div class="mb-12">
      <div class="mb-3 flex min-h-[57px] items-center gap-4 rounded px-4 py-2" style="background: rgba(255,255,255,0.17);">
        <span class="min-w-0 flex-1 text-2xl font-bold uppercase leading-tight text-white md:text-xl lg:text-2xl"><?= htmlspecialchars($allAccess ? (string)($allAccess['label'] ?? '') : 'ALL-ACCESS PASS 3 DAYS') ?></span>
        <div class="flex shrink-0 items-center gap-2">
          <span class="text-base font-bold uppercase text-white"><?= htmlspecialchars((string)($allAccess['note'] ?? 'NO garanteed')) ?></span>
        </div>
        <span class="w-[90px] shrink-0 text-right text-xl font-bold text-white"><?= htmlspecialchars($allAccess ? (string)($allAccess['price'] ?? '') : '€250.00') ?></span>
        <?php if ($allAccess && !empty($allAccess['event_id'])): ?>
          <form method="POST" action="/order/item/add" class="ticket-form shrink-0"><input type="hidden" name="event_id" value="<?= (int)$allAccess['event_id'] ?>"><button type="submit" class="flex h-[41px] w-[95px] items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px disabled:cursor-default disabled:opacity-75"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button></form>
        <?php else: ?>
          <button type="button" class="dance-add-placeholder flex h-[41px] w-[95px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px" aria-label="Add to cart (not yet available)"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($useDefault): ?>
      <?php foreach ($defaultDays as $day): ?>
    <div class="mb-14">
      <div class="mb-3 pl-5 text-[13px] font-normal leading-normal text-[#F9F9F9]"><?= htmlspecialchars($day['label']) ?></div>
      <div class="mb-3 flex min-h-[57px] items-center gap-4 rounded bg-[#E60000] px-4 py-2">
        <span class="min-w-0 flex-1 text-lg font-bold uppercase leading-tight text-white md:text-2xl"><?= htmlspecialchars($day['pass_label']) ?></span>
        <span class="w-[90px] shrink-0 text-right text-xl font-bold text-white"><?= htmlspecialchars($day['pass_price']) ?></span>
        <button type="button" class="dance-add-placeholder flex h-[41px] w-[95px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px" aria-label="Add to cart (not yet available)"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button>
      </div>
        <?php if (!empty($day['events'])): ?>
      <div class="relative">
          <?php foreach ($day['events'] as $ev):
            $tag = $ev['tag'] ?? '';
            $tagSpecial = !empty($ev['tag_special']);
          ?>
        <div class="mb-3">
          <div class="flex min-h-[57px] w-full items-center gap-4 rounded px-4 py-2" style="background: rgba(255,255,255,0.17);">
            <div class="min-w-0 flex-1">
              <div class="mb-1 text-xl font-bold uppercase leading-tight text-white md:text-2xl"><?= htmlspecialchars($ev['artist']) ?></div>
              <?php if ($tag !== ''): ?><span class="mt-1 inline-flex items-center gap-1.5 text-[10px] font-light uppercase leading-snug text-[#F9F9F9]<?= $tagSpecial ? ' font-semibold italic' : '' ?>"><?php if ($tagSpecial): ?><span class="inline-block h-[18px] w-[18px]" aria-hidden="true">★</span><?php endif; ?><?= htmlspecialchars($tag) ?></span><?php endif; ?>
            </div>
            <span class="w-[140px] shrink-0 text-left text-xl font-bold leading-snug text-white"><?= htmlspecialchars($ev['start'] . ' - ' . $ev['end']) ?></span>
            <span class="w-[140px] shrink-0 truncate text-base font-bold leading-normal text-white/95 underline"><?= htmlspecialchars($ev['venue']) ?></span>
            <span class="w-[90px] shrink-0 text-right text-xl font-bold leading-snug text-white"><?= htmlspecialchars($ev['price']) ?></span>
            <button type="button" class="dance-add-placeholder flex h-[41px] w-[95px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px" aria-label="Add to cart (not yet available)"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button>
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
      $defaultDaysByLabel = [];
      foreach ($defaultDays as $dd) {
        $defaultDaysByLabel[(string)($dd['label'] ?? '')] = $dd;
      }
      foreach ($dayOrder as $dayLabel => $dayPass):
        $dayRows = $rowsByDay[$dayLabel] ?? [];
        if (isset($defaultDaysByLabel[$dayLabel]) && !empty($defaultDaysByLabel[$dayLabel]['events'])) {
          $dayRows = [];
          foreach ($defaultDaysByLabel[$dayLabel]['events'] as $ev) {
            $dayRows[] = [
              'artist' => $ev['artist'] ?? '',
              'tag' => $ev['tag'] ?? '',
              'tag_special' => !empty($ev['tag_special']),
              'start' => $ev['start'] ?? '',
              'end' => $ev['end'] ?? '',
              'venue' => $ev['venue'] ?? '',
              'price_label' => $ev['price'] ?? '',
              'event_id' => 0,
              'time_tick' => $ev['time_tick'] ?? '',
            ];
          }
        }
        $passLabel = is_array($dayPass) ? (string)($dayPass['label'] ?? $dayLabel) : $dayLabel;
        $passPrice = is_array($dayPass) ? (string)($dayPass['price'] ?? '') : '';
        $passEventId = is_array($dayPass) && isset($dayPass['event_id']) ? (int)$dayPass['event_id'] : 0;
      ?>
    <div class="mb-14">
      <div class="mb-3 pl-5 text-[13px] font-normal leading-normal text-[#F9F9F9]"><?= htmlspecialchars($dayLabel) ?></div>
      <div class="mb-3 flex min-h-[57px] items-center gap-4 rounded bg-[#E60000] px-4 py-2">
        <span class="min-w-0 flex-1 text-lg font-bold uppercase leading-tight text-white md:text-2xl"><?= htmlspecialchars($passLabel) ?></span>
        <span class="w-[90px] shrink-0 text-right text-xl font-bold text-white"><?= htmlspecialchars($passPrice) ?></span>
        <?php if ($passEventId > 0): ?>
          <form method="POST" action="/order/item/add" class="ticket-form shrink-0"><input type="hidden" name="event_id" value="<?= $passEventId ?>"><button type="submit" class="flex h-[41px] w-[95px] items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button></form>
        <?php else: ?>
          <button type="button" class="dance-add-placeholder flex h-[41px] w-[95px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px" aria-label="Add to cart (not yet available)"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button>
        <?php endif; ?>
      </div>
        <?php if (!empty($dayRows)): ?>
      <div class="relative">
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
            $rowTall = !empty($row['tall']);
          ?>
        <div class="mb-3">
          <div class="flex min-h-[57px] w-full flex-wrap items-center gap-4 rounded px-4 py-2 md:flex-nowrap<?= $rowTall ? ' min-h-[85px] items-start pt-4' : '' ?>" style="background: rgba(255,255,255,0.17);">
            <div class="min-w-0 flex-1 md:flex-[1_1_0%]">
              <div class="mb-1 text-xl font-bold uppercase leading-tight text-white md:text-2xl"><?= htmlspecialchars($artist) ?></div>
              <?php if ($tag !== ''): ?><span class="mt-1 inline-flex items-center gap-1.5 text-[10px] font-light uppercase leading-snug text-[#F9F9F9]<?= $tagSpecial ? ' font-semibold italic' : '' ?>"><?php if ($tagSpecial): ?><span class="inline-block h-[18px] w-[18px]" aria-hidden="true">★</span><?php endif; ?><?= htmlspecialchars($tag) ?></span><?php endif; ?>
            </div>
            <span class="w-full shrink-0 text-left text-xl font-bold leading-snug text-white md:w-[140px]"><?= htmlspecialchars($start . ' - ' . $end) ?></span>
            <span class="w-full shrink-0 truncate text-base font-bold leading-normal text-white/95 underline md:w-[140px]"><?= htmlspecialchars($venue) ?></span>
            <span class="w-full shrink-0 text-right text-xl font-bold leading-snug text-white md:w-[90px]"><?= htmlspecialchars($priceLabel) ?></span>
            <?php if ($eventId > 0): ?>
              <form method="POST" action="/order/item/add" class="ticket-form shrink-0"><input type="hidden" name="event_id" value="<?= $eventId ?>"><button type="submit" class="flex h-[41px] w-[95px] items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button></form>
            <?php else: ?>
              <button type="button" class="dance-add-placeholder flex h-[41px] w-[95px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded border border-[#E60000] bg-[rgba(230,0,0,0.45)] text-base font-bold text-white transition hover:bg-[rgba(230,0,0,0.65)] hover:-translate-y-px" aria-label="Add to cart (not yet available)"><span class="h-4 w-4" aria-hidden="true"></span> ADD</button>
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

<!-- Repeating strip (marquee) -->
<div class="flex min-h-[67px] items-center overflow-hidden bg-black py-3.5">
  <div class="dance-strip-track flex w-max" aria-hidden="true">
    <div class="dance-strip-text flex flex-shrink-0 text-[clamp(20px,4vw,39px)] font-bold tracking-[0.02em] text-[#F9F9F9]">
      <span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span>
    </div>
    <div class="dance-strip-text flex flex-shrink-0 text-[clamp(20px,4vw,39px)] font-bold tracking-[0.02em] text-[#F9F9F9]">
      <span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span><span class="whitespace-nowrap"><?= htmlspecialchars($stripText) ?></span>
    </div>
  </div>
</div>
