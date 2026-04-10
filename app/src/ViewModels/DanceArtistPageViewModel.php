<?php

declare(strict_types=1);

namespace App\ViewModels;

final class DanceArtistPageViewModel
{
    public function __construct(
        public int $pageId,
        public string $pageTitle,
        public string $artistName,
        public string $backHref,
        public string $backLabel,
        public string $kicker,
        public string $heroTitle,
        public string $heroSubtitle,
        public string $coverImage,
        public string $portraitImage,
        public array $heroBullets,
        public ?string $introHtml,
        public ?string $highlightsHtml,
        public string $featureMainImage,
        public string $featureOverlayImage,
        public ?string $featureTextHtml,
        public string $ticketsTitle,
        public string $ticketButtonLabel,
        public array $ticketEvents,
        public string $tracksTitle,
        public array $tracks,
        public array $ep,
        public array $gallery
    ) {}
}
