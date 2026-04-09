<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class HistoryHomePageContentViewModel
{
    public function __construct(
        public array $hero,
        public array $overview,
        public array $booking,
        public array $map
    ) {}
}