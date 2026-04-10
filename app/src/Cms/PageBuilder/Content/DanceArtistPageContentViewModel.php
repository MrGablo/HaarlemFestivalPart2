<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class DanceArtistPageContentViewModel
{
    public function __construct(
        public array $artist,
        public array $story,
        public array $feature,
        public array $tickets,
        public array $tracks,
        public array $gallery
    ) {}
}
