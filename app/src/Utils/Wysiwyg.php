<?php

declare(strict_types=1);

namespace App\Utils;

final class Wysiwyg
{
    public static function render(?string $html): string
    {
        $html = (string)$html;

        // Allowlist tags you want WYSIWYG to output
        $allowed = '<p><br><b><strong><i><em><u><a><ul><ol><li><span><div><h2><h3><h4>';
        $clean = strip_tags($html, $allowed);

        // Kill javascript: links
        $clean = preg_replace('~href\s*=\s*([\'"])\s*javascript:.*?\1~i', 'href="#"', $clean) ?? $clean;

        // Remove inline event handlers like onclick="", onload=""
        $clean = preg_replace('~<a([^>]*?)target\s*=\s*([\'"])_blank\2([^>]*)>~i', '<a$1target="_blank" rel="noopener noreferrer"$3>', $clean) ?? $clean;

        return $clean;
    }
}