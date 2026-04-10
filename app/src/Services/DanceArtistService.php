<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\PageBuilder\Builders\DanceArtistPageBuilder;
use App\Cms\PageBuilder\Content\DanceArtistPageContentViewModel;
use App\Repositories\DanceHomeRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\DanceArtistPageViewModel;

final class DanceArtistService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private DanceHomeRepository $danceEvents = new DanceHomeRepository(),
        private DanceArtistPageBuilder $builder = new DanceArtistPageBuilder()
    ) {}

    public function getArtistPageViewModel(int $pageId): DanceArtistPageViewModel
    {
        /** @var DanceArtistPageContentViewModel $page */
        $page = $this->builder->buildViewModel($this->pageRepo->getPageContentById($pageId));

        $artist = is_array($page->artist) ? $page->artist : [];
        $story = is_array($page->story) ? $page->story : [];
        $feature = is_array($page->feature) ? $page->feature : [];
        $tickets = is_array($page->tickets) ? $page->tickets : [];
        $tracks = is_array($page->tracks) ? $page->tracks : [];
        $galleryRaw = is_array($page->gallery) ? $page->gallery : [];

        $artistName = trim((string)($artist['name'] ?? 'Dance Artist'));
        $heroTitle = trim((string)($artist['hero_title'] ?? ''));
        $heroSubtitle = trim((string)($artist['hero_subtitle'] ?? ''));

        if ($heroTitle === '') {
            $heroTitle = $artistName;
        }
        if ($heroSubtitle === '') {
            $heroSubtitle = 'Live in Haarlem';
        }

        $heroBullets = is_array($story['hero_bullets'] ?? null) ? array_values(array_filter($story['hero_bullets'], static fn($v) => is_string($v) && trim($v) !== '')) : [];

        $ticketRows = $this->danceEvents->findDanceArtistEventsByPageId($pageId);
        if ($ticketRows === [] && $artistName !== '') {
            $ticketRows = $this->danceEvents->findDanceArtistEventsByArtistName($artistName);
        }
        $mappedTickets = [];
        foreach ($ticketRows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $dayLabel = $this->formatDayLabel($row);
            $timeLabel = $this->formatTimeLabel($row);
            $priceLabel = isset($row['price']) && is_numeric((string)$row['price'])
                ? ('€' . number_format((float)$row['price'], 2, '.', ''))
                : (string)($row['price_label'] ?? '');
            $mappedTickets[] = [
                'event_id' => (int)($row['event_id'] ?? 0),
                'day_label' => $dayLabel,
                'title' => (string)($row['title'] ?? ''),
                'time_label' => $timeLabel,
                'location' => (string)($row['location_name'] ?? $row['location'] ?? ''),
                'price_label' => $priceLabel,
            ];
        }

        $trackRows = is_array($tracks['tracks'] ?? null) ? $tracks['tracks'] : [];
        $mappedTracks = [];
        foreach ($trackRows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $cover = trim((string)($row['cover_image'] ?? ''));
            $name = trim((string)($row['name'] ?? ''));
            $desc = isset($row['description']) ? (string)$row['description'] : '';
            if ($cover === '' && $name === '' && trim($desc) === '') {
                continue;
            }
            $mappedTracks[] = [
                'name' => $name,
                'description' => $desc,
                'cover_image' => $cover,
            ];
        }

        $ep = is_array($tracks['ep'] ?? null) ? $tracks['ep'] : [];
        $mappedEp = [
            'label' => (string)($ep['label'] ?? 'EP'),
            'name' => (string)($ep['name'] ?? ''),
            'description' => isset($ep['description']) ? (string)$ep['description'] : '',
            'cover_image' => (string)($ep['cover_image'] ?? ''),
        ];

        $gallery = [];
        foreach ($galleryRaw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $src = trim((string)($item['image'] ?? ''));
            if ($src === '') {
                continue;
            }
            $gallery[] = [
                'image' => $src,
                'caption' => (string)($item['caption'] ?? ''),
            ];
        }

        if ($mappedTracks === [] && $gallery !== []) {
            foreach (array_slice($gallery, 0, 3) as $item) {
                $mappedTracks[] = [
                    'name' => (string)($item['caption'] ?? 'Track'),
                    'description' => '',
                    'cover_image' => (string)($item['image'] ?? ''),
                ];
            }
        }

        return new DanceArtistPageViewModel(
            $pageId,
            $heroTitle,
            $artistName,
            (string)($artist['back_href'] ?? '/dance'),
            (string)($artist['back_label'] ?? 'Dance Event'),
            (string)($artist['kicker'] ?? 'Haarlem Dance'),
            $heroTitle,
            $heroSubtitle,
            (string)($artist['cover_image'] ?? ''),
            (string)($artist['portrait_image'] ?? ''),
            $heroBullets,
            isset($story['intro_html']) ? (string)$story['intro_html'] : null,
            isset($story['highlights_html']) ? (string)$story['highlights_html'] : null,
            (string)($feature['main_image'] ?? ''),
            (string)($feature['overlay_image'] ?? ''),
            isset($feature['text_html']) ? (string)$feature['text_html'] : null,
            (string)($tickets['title'] ?? 'Available Dance Sets'),
            (string)($tickets['ticket_button_label'] ?? 'ADD'),
            $mappedTickets,
            (string)($tracks['title'] ?? 'Important Tracks / Albums'),
            $mappedTracks,
            $mappedEp,
            $gallery
        );
    }

    private function formatDayLabel(array $row): string
    {
        $raw = trim((string)($row['day_display_label'] ?? $row['day_label'] ?? ''));
        $dt = $this->parseDateTimeValue($row['session_start'] ?? null)
            ?? $this->parseDateTimeValue($raw);
        if ($dt instanceof \DateTimeImmutable) {
            return $dt->format('l F jS');
        }

        return $raw;
    }

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

        $raw = trim((string)$value);
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

        $text = trim((string)$value);
        if ($text === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($text);
        } catch (\Throwable) {
            return null;
        }
    }
}
