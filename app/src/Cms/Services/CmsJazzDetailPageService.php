<?php

declare(strict_types=1);

namespace App\Cms\Services;

final class CmsJazzDetailPageService
{
    public const PAGE_TYPE = 'Jazz_Detail_Page';

    public function isJazzDetailPageType(string $pageType): bool
    {
        return $pageType === self::PAGE_TYPE;
    }

    /** @return array<string, mixed> */
    public function defaultTemplate(?string $artistName): array
    {
        $artistName = trim((string)$artistName);

        return [
            'artist' => [
                'name' => $artistName,
                'cover_image' => '',
                'breadcrumb' => [
                    'back_href' => '/jazz',
                    'back_label' => 'Jazz Event',
                    'current' => $artistName,
                ],
                'kicker' => 'Haarlem Jazz',
                'hero_title' => $artistName,
                'hero_subtitle' => 'Haarlem Jazz',
                'hero_media' => [
                    'main' => ['image' => ''],
                    'secondary' => [],
                ],
            ],
            'tabs' => [
                'default' => 'events',
                'labels' => [
                    'events' => 'Events',
                    'career' => 'Career Highlights',
                    'album' => 'Album',
                ],
            ],
            'events' => [
                'ticket_button_label' => 'Tickets',
            ],
            'career_highlights' => [
                'left_html' => '',
                'right_html' => '',
                'left' => [],
                'right' => [],
            ],
            'albums' => [],
            'about' => [
                'title' => 'About',
                'html' => '',
                'text' => '',
            ],
            'band_members' => [
                'title' => 'Band Members',
                'items' => [],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $contentInput
     * @return array<string, mixed>
     */
    public function applyArtistSelection(array $contentInput, ?string $artistName): array
    {
        $artistName = trim((string)$artistName);
        if ($artistName === '') {
            return $contentInput;
        }

        $contentInput['artist'] = is_array($contentInput['artist'] ?? null) ? $contentInput['artist'] : [];
        $contentInput['artist']['name'] = $artistName;
        $contentInput['artist']['hero_title'] = $artistName;

        $contentInput['artist']['breadcrumb'] = is_array($contentInput['artist']['breadcrumb'] ?? null)
            ? $contentInput['artist']['breadcrumb']
            : [];
        $contentInput['artist']['breadcrumb']['back_href'] = (string)($contentInput['artist']['breadcrumb']['back_href'] ?? '/jazz');
        $contentInput['artist']['breadcrumb']['back_label'] = (string)($contentInput['artist']['breadcrumb']['back_label'] ?? 'Jazz Event');
        $contentInput['artist']['breadcrumb']['current'] = $artistName;

        return $contentInput;
    }

    public function pageTypeLabel(string $pageType): ?string
    {
        return $this->isJazzDetailPageType($pageType) ? 'Jazz Artist Detail Page' : null;
    }
}
