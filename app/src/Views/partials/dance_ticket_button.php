<?php

declare(strict_types=1);

/** @var int $eventId */
/** @var ?string $passDate */
$cartCsrfToken = \App\Utils\Csrf::token('cart_csrf_token');
$resolvedPassDate = isset($passDate) ? trim((string)$passDate) : '';

?>
<?php if ($eventId > 0): ?>
  <form method="POST" action="/order/item/add" class="ticket-form shrink-0">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($cartCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="event_id" value="<?= $eventId ?>">
    <?php if ($resolvedPassDate !== ''): ?>
      <input type="hidden" name="pass_date" value="<?= htmlspecialchars($resolvedPassDate, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <button
      type="submit"
      class="flex h-dance-btn w-dance-btn shrink-0 items-center justify-center gap-2 rounded border border-dance-accent bg-dance-accent-soft text-base font-bold text-dance-on-dark transition hover:bg-dance-accent-hover hover:-translate-y-px disabled:cursor-default disabled:opacity-75"
    >
      <span class="inline-block h-dance-icon w-dance-icon" aria-hidden="true"></span> ADD
    </button>
  </form>
<?php else: ?>
  <button
    type="button"
    disabled
    class="flex h-dance-btn w-dance-btn shrink-0 cursor-not-allowed items-center justify-center gap-2 rounded border border-dance-accent bg-dance-accent-soft text-base font-bold text-dance-on-dark opacity-75"
    aria-label="Add to cart (unavailable)"
    aria-disabled="true"
  >
    <span class="inline-block h-dance-icon w-dance-icon" aria-hidden="true"></span> ADD
  </button>
<?php endif; ?>
