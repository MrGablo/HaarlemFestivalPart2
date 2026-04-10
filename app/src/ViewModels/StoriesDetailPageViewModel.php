<?php

declare(strict_types=1);

namespace App\ViewModels;

final class StoriesDetailPageViewModel
{
    public function __construct(
        public int $pageId,
        public int $eventId,
        public string $name,
        public array $breadcrumb,
        public string $kicker,
        public string $heroTitle,
        public string $heroSubtitle,
        public ?string $heroBodyHtml,
        public ?string $coverImage,
        public ?array $mainMedia,
        public array $secondaryMedia,
        public array $eventMeta,
        public array $eventCard,
        public array $intro,
        public array $origin,
        public array $video,
        public float $price,
        public int $availability
    ) {}
}