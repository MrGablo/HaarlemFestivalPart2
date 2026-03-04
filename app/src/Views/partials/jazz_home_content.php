<?php
$hero = $content['hero'] ?? [];
$intro = $content['intro'] ?? [];
$dayTicket = $content['day_ticket_pass'] ?? [];
?>
<section class="hero"
  style="background-image:url('/<?= htmlspecialchars((string)($hero['background_image'] ?? '')) ?>')">
  <div class="hero__inner">
    <div class="hero__kicker"><?= htmlspecialchars((string)($hero['kicker'] ?? '')) ?></div>
    <h1 class="hero__title"><?= htmlspecialchars((string)($hero['title'] ?? '')) ?></h1>

    <?php if (!empty($hero['subtitle']) && is_array($hero['subtitle'])): ?>
      <div class="hero__subtitle">
        <?php foreach ($hero['subtitle'] as $line): ?>
          <div><?= htmlspecialchars((string)$line) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Scroll button (uses JS). href in JSON stays as fallback -->
    <button class="btn buy-ticket" type="button" data-scroll-target="#dayTicket">
      <?= htmlspecialchars((string)($hero['primary_button']['label'] ?? 'Buy ticket')) ?>
    </button>
  </div>
</section>

<section class="intro">
  <div class="intro__inner">
    <h2><?= htmlspecialchars((string)($intro['heading'] ?? '')) ?></h2>
    <?php if (!empty($intro['paragraphs']) && is_array($intro['paragraphs'])): ?>
      <?php foreach ($intro['paragraphs'] as $p): ?>
        <p><?= htmlspecialchars((string)$p) ?></p>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<section id="dayTicket" class="day-ticket">
  <h2><?= htmlspecialchars((string)($dayTicket['title'] ?? 'Day Ticket Pass')) ?></h2>

  <?php if (!empty($dayTicket['buttons']) && is_array($dayTicket['buttons'])): ?>
    <div class="day-ticket__buttons">
      <?php foreach ($dayTicket['buttons'] as $b): ?>
        <button class="pass-btn" type="button"
                data-pass="<?= htmlspecialchars((string)($b['value'] ?? '')) ?>">
          <?= htmlspecialchars((string)($b['label'] ?? '')) ?>
        </button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <a class="jump-schedule" href="#schedule">
    <?= htmlspecialchars((string)($content['schedule']['title'] ?? 'SCHEDULE')) ?>
  </a>
</section>