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
        <form method="POST" action="/order/item/add" class="ticket-form">
          <input type="hidden" name="event_id" value="<?= (int)($b['event_id'] ?? 0) ?>">
          <button class="cursor-pointer rounded-[10px] border-0 bg-jazz-accent bg-[#f7c600] px-[18px] py-[10px] font-bold text-jazz-accent-text text-[#111]" type="submit">
            <?= htmlspecialchars((string)($b['label'] ?? '')) ?>
          </button>
        </form>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <a class="mt-[10px] inline-block text-white no-underline opacity-90" href="#schedule">
    <?= htmlspecialchars($vm->scheduleTitle) ?>
  </a>
</section>