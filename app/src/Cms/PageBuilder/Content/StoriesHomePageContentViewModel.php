<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class StoriesHomePageContentViewModel
{
    public function __construct(
        public array $hero,
        public array $introduction
    ) {}
}
