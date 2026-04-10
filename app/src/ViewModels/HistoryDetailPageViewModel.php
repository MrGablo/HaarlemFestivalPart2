<?php

declare(strict_types=1);

namespace App\ViewModels;

class HistoryDetailPageViewModel
{
    public function __construct(
        public int $pageId,
        public string $pageTitle,
        public array $navigation,
        public array $hero,
        public array $storyBlocks,
        public array $mapCard,
        public array $meta
    ) {}
}