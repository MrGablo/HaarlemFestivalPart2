<?php

declare(strict_types=1);

use App\Utils\Wysiwyg;
use App\Utils\Media;

$hero = $content['hero'] ?? [];
$intro = $content['intro'] ?? [];
$dayTicket = $content['day_ticket_pass'] ?? [];
$bg = Media::image($hero['background_image'] ?? null);
?>

<section class="hero relative min-h-[70vh] bg-cover bg-center"
  style="background-image:url('/<?= htmlspecialchars($bg['src']) ?>')">
  <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.75),rgba(0,0,0,.15))]"></div>

  <div class="hero__inner relative z-[1] max-w-[900px] px-20 pb-10 pt-20 max-[1200px]:px-6">
    <div class="hero__kicker tracking-[0.2em] opacity-75"><?= htmlspecialchars((string)($hero['kicker'] ?? '')) ?></div>
    <h1 class="hero__title my-2 mb-4 text-[64px] leading-none max-[1200px]:text-[44px]"><?= htmlspecialchars((string)($hero['title'] ?? '')) ?></h1>

    <?php
    // NEW (WYSIWYG)
    $subtitleHtml = $hero['subtitle_html'] ?? null;
    // OLD (fallback)
    $subtitleArr = $hero['subtitle'] ?? null;
    ?>

    <?php if (is_string($subtitleHtml) && $subtitleHtml !== ''): ?>
      <div class="hero__subtitle wysiwyg mb-4 leading-[1.4] opacity-90">
        <?= Wysiwyg::render($subtitleHtml) ?>
      </div>
    <?php elseif (is_array($subtitleArr) && !empty($subtitleArr)): ?>
      <div class="hero__subtitle mb-4 leading-[1.4] opacity-90">
        <?php foreach ($subtitleArr as $line): ?>
          <div><?= htmlspecialchars((string)$line) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Scroll button (uses JS). href in JSON stays as fallback -->
    <button class="btn buy-ticket cursor-pointer rounded-lg border-0 bg-[#2b2b2b] px-4 py-2.5 text-white" type="button" data-scroll-target="#dayTicket">
      <?= htmlspecialchars((string)($hero['primary_button']['label'] ?? 'Buy ticket')) ?>
    </button>
  </div>
</section>

<section class="intro max-w-[1000px] px-20 py-10 max-[1200px]:px-6">
  <div class="intro__inner">
    <h2><?= htmlspecialchars((string)($intro['heading'] ?? '')) ?></h2>

    <?php
    // NEW (WYSIWYG)
    $bodyHtml = $intro['body_html'] ?? null;
    // OLD (fallback)
    $paras = $intro['paragraphs'] ?? null;
    ?>

    <?php if (is_string($bodyHtml) && $bodyHtml !== ''): ?>
      <div class="intro__body wysiwyg [&>p]:max-w-[820px] [&>p]:opacity-90">
        <?= Wysiwyg::render($bodyHtml) ?>
      </div>
    <?php elseif (is_array($paras) && !empty($paras)): ?>
      <?php foreach ($paras as $p): ?>
        <p class="max-w-[820px] opacity-90"><?= htmlspecialchars((string)$p) ?></p>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<section id="dayTicket" class="day-ticket px-20 pb-2.5 pt-[30px] text-center max-[1200px]:px-6">
  <h2><?= htmlspecialchars((string)($dayTicket['title'] ?? 'Day Ticket Pass')) ?></h2>

  <?php if (!empty($dayTicket['buttons']) && is_array($dayTicket['buttons'])): ?>
    <div class="day-ticket__buttons my-3 mb-2 flex flex-wrap justify-center gap-2.5">
      <?php foreach ($dayTicket['buttons'] as $b): ?>
        <button class="pass-btn cursor-pointer rounded-[10px] border-0 bg-[#f7c600] px-[18px] py-2.5 font-bold text-[#111]" type="button"
          data-pass="<?= htmlspecialchars((string)($b['value'] ?? '')) ?>">
          <?= htmlspecialchars((string)($b['label'] ?? '')) ?>
        </button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <a class="jump-schedule mt-2.5 inline-block text-white no-underline opacity-90" href="#schedule">
    <?= htmlspecialchars((string)($content['schedule']['title'] ?? 'SCHEDULE')) ?>
  </a>
</section>