<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\PageBuilder\Builders\DanceLocationPageBuilder;
use App\Repositories\DanceHomeRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\DanceLocationPageViewModel;

final class DanceLocationService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private DanceHomeRepository $danceEvents = new DanceHomeRepository(),
        private DanceLocationPageBuilder $builder = new DanceLocationPageBuilder()
    ) {}

    public function getLocationPageViewModel(int $pageId): ?DanceLocationPageViewModel
    {
        $meta = $this->pageRepo->findPageById($pageId);
        if ($meta === null || ($meta['Page_Type'] ?? '') !== 'Dance_Location_Page') {
            return null;
        }

        // CMS content for this dance venue (location) page.
        $page = $this->builder->buildViewModel($this->pageRepo->getPageContentById($pageId));

        $venue = is_array($page->venue) ? $page->venue : [];
        $story = is_array($page->story) ? $page->story : [];
        $tickets = is_array($page->tickets) ? $page->tickets : [];

        $venueName = trim((string) ($venue['name'] ?? 'Venue'));
        $heroTitle = trim((string) ($venue['hero_title'] ?? ''));
        if ($heroTitle === '') {
            $heroTitle = $venueName;
        }

        $venueId = (int) ($venue['venue_id'] ?? 0);
        $ticketRows = $venueId > 0 ? $this->danceEvents->findDanceLocationEventsByVenueId($venueId) : [];

        $mappedTickets = [];
        foreach ($ticketRows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $priceLabel = isset($row['price']) && is_numeric((string) $row['price'])
                ? ('€' . number_format((float) $row['price'], 2, '.', ''))
                : (string) ($row['price_label'] ?? '');
            $mappedTickets[] = [
                'event_id' => (int) ($row['event_id'] ?? 0),
                'day_label' => $this->formatDayLabel($row),
                'title' => (string) ($row['title'] ?? ''),
                'time_label' => $this->formatTimeLabel($row),
                'location' => (string) ($row['location_name'] ?? $row['location'] ?? ''),
                'price_label' => $priceLabel,
            ];
        }

        $address = trim((string) ($venue['address'] ?? ''));
        $websiteUrl = trim((string) ($venue['website_url'] ?? ''));
        $mapsUrl = trim((string) ($venue['google_maps_url'] ?? ''));
        if ($mapsUrl === '' && $address !== '') {
            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
        }

        $websiteLabel = $websiteUrl;
        if ($websiteLabel !== '' && strlen($websiteLabel) > 48) {
            $websiteLabel = substr($websiteLabel, 0, 45) . '…';
        }

        return new DanceLocationPageViewModel(
            $pageId,
            $heroTitle,
            $venueName,
            (string) ($venue['back_href'] ?? '/dance'),
            (string) ($venue['back_label'] ?? 'Dance event'),
            (string) ($venue['kicker'] ?? 'Haarlem Dance'),
            $heroTitle,
            (string) ($venue['cover_image'] ?? ''),
            $address,
            trim((string) ($venue['phone'] ?? '')),
            $websiteUrl,
            $websiteLabel,
            $mapsUrl,
            isset($story['intro_html']) ? (string) $story['intro_html'] : null,
            (string) ($tickets['title'] ?? 'TOTAL EVENTS'),
            (string) ($tickets['ticket_button_label'] ?? 'ADD'),
            $mappedTickets,
        );
    }

    // Human-readable day line for one session row.
    private function formatDayLabel(array $row): string
    {
        $raw = trim((string) ($row['day_display_label'] ?? $row['day_label'] ?? ''));
        $dt = $this->parseDateTimeValue($row['session_start'] ?? null)
            ?? $this->parseDateTimeValue($raw);
        if ($dt instanceof \DateTimeImmutable) {
            return $dt->format('l F jS');
        }

        return $raw;
    }

    // "Start - end" time string for one session row.
    private function formatTimeLabel(array $row): string
    {
        $start = $this->formatTimeValue($row['session_start'] ?? null);
        $end = $this->formatTimeValue($row['session_end'] ?? null);
        if ($start !== '' && $end !== '') {
            return $start . ' - ' . $end;
        }

        return $start !== '' ? $start : $end;
    }

    private function formatTimeValue(mixed $value): string
    {
        $dt = $this->parseDateTimeValue($value);
        if ($dt instanceof \DateTimeImmutable) {
            return $dt->format('H:i');
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $raw) === 1) {
            return substr($raw, 0, 5);
        }

        return $raw;
    }

    private function parseDateTimeValue(mixed $value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }
        if (!is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($text);
        } catch (\Throwable) {
            // Unparseable date/time from DB — show raw label instead.
            return null;
        }
    }
}
