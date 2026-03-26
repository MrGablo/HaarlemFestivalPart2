<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class HomePageContentViewModel
{
    /** @param array<int, mixed> $categories */
    public function __construct(
        public array $content,
        public array $categories = []
    ) {}
}