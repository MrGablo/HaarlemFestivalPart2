<?php

declare(strict_types=1);

use App\Utils\Wysiwyg;
use App\Utils\Media;

/** @var \App\ViewModels\JazzHomePageViewModel $vm */

$hero = $vm->hero;
$intro = $vm->intro;
$dayTicket = $vm->dayTicketPass;
$bg = Media::image($hero['background_image'] ?? null);
?>

<section class="relative min-h-[70vh] bg-cover bg-center"
  style="background-image:url('/<?= htmlspecialchars($bg['src']) ?>')">
  <div class="absolute inset-0 bg-gradient-to-r from-black/75 to-black/15"></div>
  <div class="relative z-[1] max-w-jazz-hero-content max-w-[900px] px-20 pb-10 pt-20 max-[1200px]:px-6">
    <div class="tracking-[0.2em] opacity-75"><?= htmlspecialchars((string)($hero['kicker'] ?? '')) ?></div>
    <h1 class="mb-4 mt-2 text-[64px] leading-none max-[1200px]:text-[44px]"><?= htmlspecialchars((string)($hero['title'] ?? '')) ?></h1>

    <?php
    // NEW (WYSIWYG)
    $subtitleHtml = $hero['subtitle_html'] ?? null;
    // OLD (fallback)
    $subtitleArr = $hero['subtitle'] ?? null;
    ?>

    <?php if (is_string($subtitleHtml) && $subtitleHtml !== ''): ?>
      <div class="mb-4 leading-[1.4] opacity-90 wysiwyg">
        <?= Wysiwyg::render($subtitleHtml) ?>
      </div>
    <?php elseif (is_array($subtitleArr) && !empty($subtitleArr)): ?>
      <div class="mb-4 leading-[1.4] opacity-90">
        <?php foreach ($subtitleArr as $line): ?>
          <div><?= htmlspecialchars((string)$line) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Scroll button (uses JS). href in JSON stays as fallback -->
    <button class="cursor-pointer rounded-lg border-0 bg-jazz-button-dark bg-[#2b2b2b] px-4 py-[10px] text-white" type="button" data-scroll-target="#dayTicket">
      <?= htmlspecialchars((string)($hero['primary_button']['label'] ?? 'Buy ticket')) ?>
    </button>
  </div>
</section>

<section class="max-w-jazz-intro max-w-[1000px] px-20 py-10 max-[1200px]:px-6">
  <div>
    <h2><?= htmlspecialchars((string)($intro['heading'] ?? '')) ?></h2>

    <?php
    // NEW (WYSIWYG)
    $bodyHtml = $intro['body_html'] ?? null;
    // OLD (fallback)
    $paras = $intro['paragraphs'] ?? null;
    ?>

    <?php if (is_string($bodyHtml) && $bodyHtml !== ''): ?>
      <div class="max-w-jazz-text max-w-[820px] opacity-90 wysiwyg">
        <?= Wysiwyg::render($bodyHtml) ?>
      </div>
    <?php elseif (is_array($paras) && !empty($paras)): ?>
      <?php foreach ($paras as $p): ?>
        <p class="max-w-jazz-text max-w-[820px] opacity-90"><?= htmlspecialchars((string)$p) ?></p>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<section id="dayTicket" class="px-20 pb-[10px] pt-[30px] text-center max-[1200px]:px-6">
  <h2><?= htmlspecialchars((string)($dayTicket['title'] ?? 'Day Ticket Pass')) ?></h2>

  <?php if (!empty($dayTicket['buttons']) && is_array($dayTicket['buttons'])): ?>
    <div class="my-3 flex flex-wrap justify-center gap-[10px]">
      <?php foreach ($dayTicket['buttons'] as $b): ?>
        <?php
          $eventId = (int)($b['event_id'] ?? 0);
          $requiresDaySelection = (bool)($b['requires_day_selection'] ?? false);
          $availableDates = is_array($b['available_dates'] ?? null) ? $b['available_dates'] : [];
          $availableDatesJson = json_encode(array_values(array_filter(array_map('strval', $availableDates))), JSON_UNESCAPED_SLASHES);
        ?>
        <?php if ($requiresDaySelection): ?>
          <button
            class="pass-day-picker-btn cursor-pointer rounded-[10px] border-0 bg-jazz-accent bg-[#f7c600] px-[18px] py-[10px] font-bold text-jazz-accent-text text-[#111]"
            type="button"
            data-event-id="<?= $eventId ?>"
            data-pass-label="<?= htmlspecialchars((string)($b['label'] ?? 'Day Pass')) ?>"
            data-available-dates="<?= htmlspecialchars((string)($availableDatesJson ?: '[]')) ?>"
          >
            <?= htmlspecialchars((string)($b['label'] ?? '')) ?>
          </button>
        <?php else: ?>
          <form method="POST" action="/order/item/add" class="ticket-form">
            <input type="hidden" name="event_id" value="<?= $eventId ?>">
            <button class="cursor-pointer rounded-[10px] border-0 bg-jazz-accent bg-[#f7c600] px-[18px] py-[10px] font-bold text-jazz-accent-text text-[#111]" type="submit">
              <?= htmlspecialchars((string)($b['label'] ?? '')) ?>
            </button>
          </form>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <a class="mt-[10px] inline-block text-white no-underline opacity-90" href="#schedule">
    <?= htmlspecialchars($vm->scheduleTitle) ?>
  </a>
</section>

<div id="jazzPassDayModal" class="fixed inset-0 z-[1300] hidden items-center justify-center bg-black/70 px-4">
  <div class="w-full max-w-[540px] rounded-2xl bg-[#111] p-6 text-left text-white ring-1 ring-white/10">
    <div class="mb-4 flex items-start justify-between gap-3">
      <div>
        <h3 class="text-2xl font-bold">Choose Jazz Day</h3>
        <p id="jazzPassDayModalSubtitle" class="mt-1 text-sm opacity-85">Select the date for your day pass.</p>
      </div>
      <button id="jazzPassDayModalClose" type="button" class="rounded-md border border-white/20 px-3 py-1 text-sm hover:bg-white/10">Close</button>
    </div>

    <div id="jazzPassDayModalDates" class="flex flex-wrap gap-2"></div>
    <p id="jazzPassDayModalEmpty" class="hidden text-sm text-amber-200">No available Jazz dates for this pass right now.</p>

    <form id="jazzPassDayForm" method="POST" action="/order/item/add" class="ticket-form hidden">
      <input id="jazzPassDayFormEventId" type="hidden" name="event_id" value="0">
      <input id="jazzPassDayFormDate" type="hidden" name="pass_date" value="">
      <button type="submit" class="hidden">Add</button>
    </form>
  </div>
</div>