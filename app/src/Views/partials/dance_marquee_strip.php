<?php

declare(strict_types=1);

/** @var \App\ViewModels\DanceHomePageViewModel $vm */

$s = htmlspecialchars((string) ($vm->hero['stripText'] ?? ''));
?>
<div class="flex min-h-dance-strip items-center overflow-hidden bg-dance-strip-bg py-3.5">
  <div class="dance-strip-track flex w-max" aria-hidden="true">
    <div class="dance-strip-text flex shrink-0 text-dance-strip-text font-bold tracking-wide text-dance-text"><?php for ($k = 0; $k < 5; $k++): ?><span class="whitespace-nowrap"><?= $s ?></span><?php endfor; ?></div>
    <div class="dance-strip-text flex shrink-0 text-dance-strip-text font-bold tracking-wide text-dance-text"><?php for ($k = 0; $k < 5; $k++): ?><span class="whitespace-nowrap"><?= $s ?></span><?php endfor; ?></div>
  </div>
</div>
