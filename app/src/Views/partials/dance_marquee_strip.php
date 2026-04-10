<?php

declare(strict_types=1);

// Scrolling text bar. Home reads strip from $vm->hero; artist/location can set $danceMarqueeStripText.
$defaultStrip = 'HAARLEM FESTIVAL DANCE';
$stripRaw = '';
if (isset($danceMarqueeStripText) && is_string($danceMarqueeStripText) && trim($danceMarqueeStripText) !== '') {
    $stripRaw = trim($danceMarqueeStripText);
} elseif (isset($vm) && is_object($vm) && property_exists($vm, 'hero')) {
    // hero may be an array with stripText / strip_text.
    $hero = $vm->hero;
    if (is_array($hero)) {
        $fromHero = trim((string) ($hero['stripText'] ?? $hero['strip_text'] ?? ''));
        if ($fromHero !== '') {
            $stripRaw = $fromHero;
        }
    }
}
if ($stripRaw === '') {
    $stripRaw = $defaultStrip;
}
$s = htmlspecialchars($stripRaw);
?>
<div class="flex min-h-dance-strip items-center overflow-hidden bg-dance-strip-bg py-3.5">
  <div class="dance-strip-track flex w-max" aria-hidden="true">
    <div class="dance-strip-text flex shrink-0 text-dance-strip-text font-bold tracking-wide text-dance-text"><?php for ($k = 0; $k < 5; $k++): ?><span class="whitespace-nowrap"><?= $s ?></span><?php endfor; ?></div>
    <div class="dance-strip-text flex shrink-0 text-dance-strip-text font-bold tracking-wide text-dance-text"><?php for ($k = 0; $k < 5; $k++): ?><span class="whitespace-nowrap"><?= $s ?></span><?php endfor; ?></div>
  </div>
</div>
