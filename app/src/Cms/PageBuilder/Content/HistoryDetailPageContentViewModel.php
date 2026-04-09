<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class HistoryDetailPageContentViewModel
{
    public function __construct(
        public array $meta,
        public array $hero,
        public array $storyBlocks,
        public array $mapCard
    ) {}
}