<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class StoriesDetailPageContentViewModel
{
    public function __construct(
        public array $story,
        public array $eventCard,
        public array $intro,
        public array $origin,
        public array $video
    ) {}
}