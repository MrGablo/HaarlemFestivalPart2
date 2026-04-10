<?php

namespace App\ViewModels;

class JazzArtistPageViewModel
{
    public function __construct(
        public int $pageId,
        public string $pageTitle,
        public string $coverImage,
        public array $breadcrumb,
        public string $kicker,
        public string $heroTitle,
        public string $heroSubtitle,
        public ?array $mainMedia,
        public array $secondaryMedia,
        public array $tabLabels,
        public array $tabLinks,
        public string $activeTab,
        public array $events,
        public string $ticketButtonLabel,
        public ?string $careerLeftHtml,
        public ?string $careerRightHtml,
        public array $careerLeftItems,
        public array $careerRightItems,
        public array $albums,
        public string $aboutTitle,
        public ?string $aboutHtml,
        public string $aboutText,
        public string $bandTitle,
        public array $bandItems
    ) {}
}