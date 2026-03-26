<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class JazzArtistPageContentViewModel
{
    public function __construct(
        public array $artist,
        public array $tabs,
        public array $events,
        public array $careerHighlights,
        public array $albums,
        public array $about,
        public array $bandMembers
    ) {}
}