<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class DanceLocationPageContentViewModel
{
    public function __construct(
        public array $venue,
        public array $story,
        public array $tickets,
    ) {}
}
