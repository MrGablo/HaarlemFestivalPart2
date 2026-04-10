<?php

declare(strict_types=1);

namespace App\ViewModels;

final class DanceLocationPageViewModel
{
    public function __construct(
        public int $pageId,
        public string $pageTitle,
        public string $venueName,
        public string $backHref,
        public string $backLabel,
        public string $kicker,
        public string $heroTitle,
        public string $coverImage,
        public string $address,
        public string $phone,
        public string $websiteUrl,
        public string $websiteLabel,
        public string $googleMapsHref,
        public ?string $introHtml,
        public string $eventsSectionTitle,
        public string $ticketButtonLabel,
        // Events listed on the venue page (id, day, title, time, place, price text).
        public array $ticketEvents,
    ) {}
}
