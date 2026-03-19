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
    <?php if ($h['subtitleMode'] === 'html'): ?>
      <div class="wysiwyg mb-7 max-w-dance-hero-sub text-2xl font-bold leading-snug text-dance-on-dark whitespace-pre-line"><?= Wysiwyg::render($h['subtitleHtml']) ?></div>
    <?php elseif ($h['subtitleMode'] === 'lines'): ?>
      <div class="mb-7 max-w-dance-hero-sub text-2xl font-bold leading-snug text-dance-on-dark"><?php foreach ($h['subtitleLines'] as $subIdx => $line): ?><?= $subIdx > 0 ? '<br>' : '' ?><?= htmlspecialchars((string) $line) ?><?php endforeach; ?></div>
    <?php else: ?>
      <div class="mb-7 max-w-dance-hero-sub text-2xl font-bold leading-snug text-dance-on-dark"><?php foreach ($h['defaultSubtitleLines'] as $subIdx => $line): ?><?= $subIdx > 0 ? '<br>' : '' ?><?= htmlspecialchars((string) $line) ?><?php endforeach; ?></div>
    <?php endif; ?>
    <a href="#dance-timetable" class="inline-block rounded-lg bg-dance-hero-cta-bg px-6 py-2.5 text-xl font-bold text-dance-on-dark shadow-dance-cta transition hover:bg-dance-on-dark hover:text-dance-bg"><?= htmlspecialchars((string) ($h['primaryButtonLabel'] ?? '')) ?></a>
  </div>
</section>

<?php require __DIR__ . '/dance_marquee_strip.php'; ?>

<section class="mx-auto grid max-w-dance-container grid-cols-1 gap-10 px-6 py-16 md:grid-cols-2 md:gap-12 md:py-20">
  <div>
    <h2 class="mb-4 text-2xl font-semibold tracking-wide text-dance-text md:text-3xl"><?= htmlspecialchars((string) ($intro['kicker'] ?? '')) ?></h2>
    <?php if (($intro['bodyMode'] ?? '') === 'html'): ?>
      <div class="wysiwyg text-dance-text [&_p]:mb-4"><?= Wysiwyg::render((string) ($intro['bodyHtml'] ?? '')) ?></div>
    <?php else: ?>
      <div class="text-dance-text [&_p]:mb-4">
        <?php foreach ($intro['paragraphs'] ?? [] as $p): ?>
          <p><?= htmlspecialchars((string) $p) ?></p>
        <?php endforeach; ?>
      </div>
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
        <div class="flex flex-col items-center">
          <div class="relative h-dance-photo-sm w-dance-photo-sm overflow-hidden rounded-none md:h-dance-photo-lg md:w-dance-photo-lg">
            <img src="<?= htmlspecialchars((string) ($artist['imageUrl'] ?? '')) ?>" alt="<?= htmlspecialchars((string) ($artist['alt'] ?? '')) ?>" class="h-full w-full object-cover object-top" loading="lazy">
          </div>
          <div class="mt-3.5 max-w-dance-lineup-name text-center text-sm font-bold uppercase tracking-wide text-dance-text md:text-base"><?= htmlspecialchars((string) ($artist['name'] ?? '')) ?></div>
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
        <?php $aa = $vm->allAccess; ?>
        <div class="mb-12">
          <div class="mb-3 flex min-h-dance-row items-center gap-4 rounded px-4 py-2 bg-dance-row-glass">
            <span class="min-w-0 flex-1 text-xl font-bold uppercase leading-tight text-dance-on-dark md:text-2xl"><?= htmlspecialchars((string) ($aa['label'] ?? '')) ?></span>
            <span class="shrink-0 text-base font-bold uppercase text-dance-on-dark"><?= htmlspecialchars((string) ($aa['note'] ?? '')) ?></span>
            <span class="w-dance-slot-price shrink-0 text-right text-xl font-bold text-dance-on-dark"><?= htmlspecialchars((string) ($aa['priceLabel'] ?? '')) ?></span>
            <?php $eventId = $aa['eventId']; include __DIR__ . '/dance_ticket_button.php'; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php foreach ($vm->timetableDays as $day): ?>
        <div class="mb-14">
          <div class="mb-3 pl-5 text-xs font-normal leading-normal text-dance-text"><?= htmlspecialchars($day['dayLabel']) ?></div>
          <div class="mb-3 flex min-h-dance-row items-center gap-4 rounded bg-dance-accent px-4 py-2">
            <span class="min-w-0 flex-1 text-lg font-bold uppercase leading-tight text-dance-on-dark md:text-2xl"><?= htmlspecialchars($day['passLabel']) ?></span>
            <span class="w-dance-slot-price shrink-0 text-right text-xl font-bold text-dance-on-dark"><?= htmlspecialchars($day['passPriceLabel']) ?></span>
            <?php $eventId = $day['passEventId']; include __DIR__ . '/dance_ticket_button.php'; ?>
          </div>
          <?php foreach ($day['sessions'] as $sess): ?>
            <div class="mb-3">
              <div class="flex min-h-dance-row w-full flex-wrap items-center gap-4 rounded px-4 py-2 md:flex-nowrap bg-dance-row-glass">
                <div class="min-w-0 flex-1 md:basis-0 md:grow">
                  <div class="mb-1 text-xl font-bold uppercase leading-tight text-dance-on-dark md:text-2xl"><?= htmlspecialchars((string) ($sess['title'] ?? '')) ?></div>
                  <?php if (($sess['tag'] ?? '') !== ''): ?>
                    <span class="mt-1 inline-flex items-center gap-1.5 text-dance-tag font-light uppercase leading-snug text-dance-text <?= !empty($sess['tagSpecial']) ? 'font-semibold italic' : '' ?>">
                      <?php if (!empty($sess['tagSpecial'])): ?><span class="inline-block h-dance-icon w-dance-icon" aria-hidden="true">★</span><?php endif; ?>
                      <?= htmlspecialchars((string) ($sess['tag'] ?? '')) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <span class="w-full shrink-0 text-left text-xl font-bold leading-snug text-dance-on-dark md:w-dance-slot-time"><?= htmlspecialchars((string) ($sess['timeRange'] ?? '')) ?></span>
                <span class="w-full shrink-0 truncate text-base font-bold leading-normal text-dance-text-strong underline md:w-dance-slot-venue"><?= htmlspecialchars((string) ($sess['venueName'] ?? '')) ?></span>
                <span class="w-full shrink-0 text-right text-xl font-bold leading-snug text-dance-on-dark md:w-dance-slot-price"><?= htmlspecialchars((string) ($sess['priceLabel'] ?? '')) ?></span>
                <?php $eventId = $sess['eventId']; include __DIR__ . '/dance_ticket_button.php'; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/dance_marquee_strip.php'; ?>
