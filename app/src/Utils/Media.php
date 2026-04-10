<?php

declare(strict_types=1);

namespace App\Utils;

final class Media
{
    /**
     * Accepts either:
     *  - "assets/img/..." (string)
     *  - ["src" => "assets/img/...", "alt" => "..."] (array)
     *
     * Returns: ['src' => string, 'alt' => string]
     */
    public static function image(mixed $img, string $fallbackAlt = ''): array
    {
        $src = '';
        $alt = $fallbackAlt;

        if (is_string($img)) {
            $src = $img;
        } elseif (is_array($img)) {
            $src = (string)($img['src'] ?? '');
            $alt = (string)($img['alt'] ?? $alt);
        }

        return ['src' => $src, 'alt' => $alt];
    }
}