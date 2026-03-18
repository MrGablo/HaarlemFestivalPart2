<?php

declare(strict_types=1);

/** @var string $orderItemAddPath */
/** @var int $eventId */

?>
<?php if ($eventId > 0): ?>
  <form method="POST" action="<?= htmlspecialchars($orderItemAddPath) ?>" class="ticket-form shrink-0">
    <input type="hidden" name="event_id" value="<?= $eventId ?>">
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
    class="dance-add-placeholder flex h-dance-btn w-dance-btn shrink-0 cursor-pointer items-center justify-center gap-2 rounded border border-dance-accent bg-dance-accent-soft text-base font-bold text-dance-on-dark transition hover:bg-dance-accent-hover hover:-translate-y-px"
    aria-label="Add to cart (not yet available)"
  >
    <span class="inline-block h-dance-icon w-dance-icon" aria-hidden="true"></span> ADD
  </button>
<?php endif; ?>
