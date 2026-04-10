<?php

declare(strict_types=1);

use App\Utils\Wysiwyg;

/** @var \App\ViewModels\DanceHomePageViewModel $vm */

$h = $vm->hero;
$intro = $vm->intro;
?>

<section
  class="relative min-h-dance-hero overflow-hidden bg-cover bg-center"
  style="background-image: url('<?= htmlspecialchars((string) $vm->heroBackgroundImageUrl) ?>');"
>
  <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-dance-overlay-from via-dance-overlay-via to-transparent" aria-hidden="true"></div>
  <div class="relative z-10 mx-auto max-w-dance-container px-6 pb-20 pt-40">
    <h1 class="mb-6 flex flex-col text-dance-hero-title uppercase text-dance-on-dark drop-shadow-dance-hero">
      <?php if ($h['titleLine2'] !== ''): ?>
        <span><?= htmlspecialchars((string) ($h['titleLine1'] ?? '')) ?></span>
        <span><?= htmlspecialchars((string) ($h['titleLine2'] ?? '')) ?></span>
      <?php else: ?>
        <?= htmlspecialchars((string) ($h['titleLine1'] ?? '')) ?>
      <?php endif; ?>
    </h1>
    <?php if (($h['subtitleHtml'] ?? '') !== ''): ?>
      <div class="wysiwyg mb-7 max-w-dance-hero-sub text-2xl font-bold leading-snug text-dance-on-dark whitespace-pre-line"><?= Wysiwyg::render($h['subtitleHtml']) ?></div>
    <?php endif; ?>
    <a href="#dance-timetable" class="inline-block rounded-lg bg-dance-hero-cta-bg px-6 py-2.5 text-xl font-bold text-dance-on-dark shadow-dance-cta transition hover:bg-dance-on-dark hover:text-dance-bg"><?= htmlspecialchars((string) ($h['primaryButtonLabel'] ?? '')) ?></a>
  </div>
</section>

<?php require __DIR__ . '/dance_marquee_strip.php'; ?>

<section class="mx-auto grid max-w-dance-container grid-cols-1 gap-10 px-6 py-16 md:grid-cols-2 md:gap-12 md:py-20">
  <div>
    <h2 class="mb-4 text-2xl font-semibold tracking-wide text-dance-text md:text-3xl"><?= htmlspecialchars((string) ($intro['kicker'] ?? '')) ?></h2>
    <?php if (($intro['bodyHtml'] ?? '') !== ''): ?>
      <div class="wysiwyg text-dance-text [&_p]:mb-4"><?= Wysiwyg::render((string) ($intro['bodyHtml'] ?? '')) ?></div>
    <?php endif; ?>
  </div>
  <div class="flex flex-col items-start">
    <img src="<?= htmlspecialchars((string) $vm->introSideImageUrl) ?>" alt="<?= htmlspecialchars((string) ($intro['sideImageAlt'] ?? '')) ?>" class="mb-4 w-full max-w-dance-intro-img rounded object-cover" loading="lazy">
    <div class="text-sm text-dance-text-subtle"><?= htmlspecialchars((string) ($intro['statsLine'] ?? '')) ?></div>
  </div>
</section>

<section class="bg-dance-bg px-6 py-14 md:py-20">
  <div class="mx-auto max-w-dance-container">
    <h2 class="mb-10 text-center text-2xl font-bold uppercase tracking-wider text-dance-text md:text-3xl"><?= htmlspecialchars((string) $vm->lineupTitle) ?></h2>
    <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 md:gap-10">
      <?php foreach ($vm->lineupArtists as $artist): ?>
        <?php $artistPageUrl = trim((string)($artist['pageUrl'] ?? '')); ?>
        <div class="flex flex-col items-center">
          <?php if ($artistPageUrl !== ''): ?>
            <a href="<?= htmlspecialchars($artistPageUrl) ?>" class="group text-inherit no-underline">
          <?php endif; ?>
              <div class="relative h-dance-photo-sm w-dance-photo-sm overflow-hidden rounded-none md:h-dance-photo-lg md:w-dance-photo-lg">
                <img
                  src="/assets/img/dance-assets/artistBG.svg"
                  alt=""
                  aria-hidden="true"
                  class="absolute inset-0 z-0 h-full w-full scale-110 object-contain object-center pointer-events-none select-none"
                  loading="lazy"
                >
                <img
                  src="<?= htmlspecialchars((string) ($artist['imageUrl'] ?? '')) ?>"
                  alt="<?= htmlspecialchars((string) ($artist['alt'] ?? '')) ?>"
                  class="relative z-10 h-full w-full object-cover object-top transition duration-150 group-hover:brightness-110"
                  loading="lazy"
                >
              </div>
              <div class="mt-3.5 max-w-dance-lineup-name text-center text-sm font-bold uppercase tracking-wide text-dance-text md:text-base"><?= htmlspecialchars((string) ($artist['name'] ?? '')) ?></div>
          <?php if ($artistPageUrl !== ''): ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/dance_marquee_strip.php'; ?>

<section id="dance-timetable" class="bg-dance-surface bg-cover bg-center bg-no-repeat px-5 py-10 md:px-5 md:py-10" style="background-image: url('<?= htmlspecialchars($vm->timetableSectionBackgroundUrl) ?>');">
  <div class="mx-auto max-w-dance-timetable font-['Montserrat',sans-serif]">
    <h2 class="mb-3 text-3xl font-bold uppercase leading-tight text-dance-text md:text-4xl lg:text-5xl lg:leading-tight"><?= htmlspecialchars($vm->timetableTitle) ?></h2>
    <p class="mb-10 text-xs font-normal leading-snug text-dance-muted"><?= htmlspecialchars($vm->timetableDateRange) ?></p>

    <?php if (!$vm->timetableHasRows): ?>
      <p class="text-dance-muted">Timetable will be published soon.</p>
    <?php else: ?>
      <?php if ($vm->allAccess !== null): ?>
        <?php /** @var array{label: string, note: string, priceLabel: string, eventId: int} $aa */ ?>
        <?php $aa = $vm->allAccess; ?>
        <div class="mb-12">
          <div class="mb-3 flex min-h-[3.5rem] min-h-dance-row items-center gap-4 rounded px-4 py-2 bg-dance-row-glass">
            <span class="min-w-0 flex-1 text-xl font-bold uppercase leading-tight text-dance-on-dark md:text-2xl"><?= htmlspecialchars((string) ($aa['label'] ?? '')) ?></span>
            <span class="shrink-0 text-base font-bold uppercase text-dance-on-dark"><?= htmlspecialchars((string) ($aa['note'] ?? '')) ?></span>
            <span class="w-[6rem] shrink-0 text-right text-xl font-bold text-dance-on-dark"><?= htmlspecialchars((string) ($aa['priceLabel'] ?? '')) ?></span>
            <?php $passDate = null; ?>
            <?php $eventId = $aa['eventId']; include __DIR__ . '/dance_ticket_button.php'; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php foreach ($vm->timetableDays as $day): ?>
        <?php /** @var array{dayKey: string, dayLabel: string, passLabel: string, passPriceLabel: string, passEventId: int, sessions: list<array{title: string, tag: string, tagSpecial: bool, timeRange: string, venueName: string, priceLabel: string, eventId: int}>} $day */ ?>
        <div class="mb-14">
          <div class="mb-3 pl-5 text-xs font-normal leading-normal text-dance-text"><?= htmlspecialchars($day['dayLabel']) ?></div>
          <div class="mb-3 flex min-h-[3.5rem] min-h-dance-row items-center gap-4 rounded bg-dance-accent px-4 py-2">
            <span class="min-w-0 flex-1 text-lg font-bold uppercase leading-tight text-dance-on-dark md:text-2xl"><?= htmlspecialchars($day['passLabel']) ?></span>
            <span class="w-[6rem] shrink-0 text-right text-xl font-bold text-dance-on-dark"><?= htmlspecialchars($day['passPriceLabel']) ?></span>
            <?php $passDate = (string)$day['dayKey']; ?>
            <?php $eventId = $day['passEventId']; include __DIR__ . '/dance_ticket_button.php'; ?>
          </div>
          <?php foreach ($day['sessions'] as $sess): ?>
            <?php
            $sessionTag = trim((string) ($sess['tag'] ?? ''));
            $showSessionTag = $sessionTag !== '' && $sessionTag !== '/';
            ?>
            <div class="mb-3 rounded bg-dance-row-glass px-4 py-3 md:py-2">
              <div class="grid min-h-[3.5rem] grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_9rem_9rem_6rem_auto] md:items-center md:gap-x-4 md:gap-y-0">
                <div class="min-w-0">
                  <div class="text-xl font-bold uppercase leading-tight text-dance-on-dark md:text-2xl"><?= htmlspecialchars((string) ($sess['title'] ?? '')) ?></div>
                  <?php if ($showSessionTag): ?>
                    <span class="mt-1 inline-flex items-center gap-1.5 text-dance-tag font-light uppercase leading-snug text-dance-text <?= !empty($sess['tagSpecial']) ? 'font-semibold italic' : '' ?>">
                      <?php if (!empty($sess['tagSpecial'])): ?><span class="inline-block h-dance-icon w-dance-icon" aria-hidden="true">★</span><?php endif; ?>
                      <?= htmlspecialchars($sessionTag) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="text-xl font-bold leading-snug text-dance-on-dark md:text-right"><?= htmlspecialchars((string) ($sess['timeRange'] ?? '')) ?></div>
                <div class="truncate text-base font-bold leading-normal text-dance-text-strong underline md:text-right"><?= htmlspecialchars((string) ($sess['venueName'] ?? '')) ?></div>
                <div class="text-xl font-bold leading-snug text-dance-on-dark md:text-right"><?= htmlspecialchars((string) ($sess['priceLabel'] ?? '')) ?></div>
                <div class="flex items-center md:justify-end">
                  <?php $passDate = null; ?>
                  <?php $eventId = $sess['eventId']; include __DIR__ . '/dance_ticket_button.php'; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/dance_marquee_strip.php'; ?>
