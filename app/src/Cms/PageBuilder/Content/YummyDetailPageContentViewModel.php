<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class YummyDetailPageContentViewModel
{
    public function __construct(
        public array $heroSection,
        public array $aboutSection,
        public array $contentSection1,
        public array $chefSection,
        public array $menuSection,
        public string $informationBlock
    ) {}
}
