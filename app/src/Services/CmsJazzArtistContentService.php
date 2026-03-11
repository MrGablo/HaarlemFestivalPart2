<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Wysiwyg;

final class CmsJazzArtistContentService
{
    public function __construct(private UploadService $uploads) {}

    public function buildContent(string $artistName, ?array $existingContent, array $post, array $files): array
    {
        $existingContent = is_array($existingContent) ? $existingContent : [];

        $coverImage = $this->storeOptionalImage(
            $files,
            'cover_image_file',
            'detail',
            $this->nestedString($existingContent, ['artist', 'cover_image'])
        );
        $heroMainImage = $this->storeOptionalImage(
            $files,
            'hero_main_image_file',
            'detail',
            $this->nestedString($existingContent, ['artist', 'hero_media', 'main', 'image'])
        );

        $secondary = [];
        for ($index = 1; $index <= 2; $index++) {
            $existingItem = $existingContent['artist']['hero_media']['secondary'][$index - 1] ?? [];
            $image = $this->storeOptionalImage(
                $files,
                'hero_secondary_' . $index . '_image_file',
                'detail',
                is_array($existingItem) ? (string)($existingItem['image'] ?? '') : ''
            );

            if ($image === null || $image === '') {
                continue;
            }

            $secondary[] = [
                'image' => $image,
                'caption' => $this->nullableText($post, 'hero_secondary_' . $index . '_caption'),
            ];
        }

        $albums = [];
        for ($index = 1; $index <= 3; $index++) {
            $existingAlbum = $existingContent['albums'][$index - 1] ?? [];
            $image = $this->storeOptionalImage(
                $files,
                'album_' . $index . '_image_file',
                'album',
                is_array($existingAlbum) ? (string)($existingAlbum['image'] ?? '') : ''
            );

            $title = trim((string)($post['album_' . $index . '_title'] ?? ''));
            $artist = trim((string)($post['album_' . $index . '_artist'] ?? ''));
            $description = trim((string)($post['album_' . $index . '_description_html'] ?? ''));

            if ($title === '' && $artist === '' && $description === '' && ($image === null || $image === '')) {
                continue;
            }

            $albums[] = [
                'image' => $image ?? '',
                'title' => $title,
                'artist' => $artist,
                'description_html' => $this->normalizeHtmlField($description),
            ];
        }

        return [
            'tabs' => [
                'labels' => [
                    'album' => $this->requestWithDefault($post, 'tabs_album_label', 'Album'),
                    'career' => $this->requestWithDefault($post, 'tabs_career_label', 'Career Highlights'),
                    'events' => $this->requestWithDefault($post, 'tabs_events_label', 'Events'),
                ],
                'default' => $this->validDefaultTab((string)($post['tabs_default'] ?? 'events')),
            ],
            'about' => [
                'html' => $this->normalizeHtmlField((string)($post['about_html'] ?? '')),
                'title' => $this->requestWithDefault($post, 'about_title', 'About the Artist:'),
            ],
            'albums' => $albums,
            'artist' => [
                'name' => $artistName,
                'kicker' => trim((string)($post['artist_kicker'] ?? '')),
                'breadcrumb' => [
                    'current' => $this->requestWithDefault($post, 'breadcrumb_current', $artistName),
                    'back_href' => $this->requestWithDefault($post, 'breadcrumb_back_href', '/jazz'),
                    'back_label' => $this->requestWithDefault($post, 'breadcrumb_back_label', 'Jazz Event'),
                ],
                'hero_media' => [
                    'main' => [
                        'image' => $heroMainImage ?? '',
                        'caption' => $this->nullableText($post, 'hero_main_caption'),
                    ],
                    'secondary' => $secondary,
                ],
                'hero_title' => $this->requestWithDefault($post, 'hero_title', $artistName),
                'cover_image' => $coverImage ?? '',
                'hero_subtitle' => trim((string)($post['hero_subtitle'] ?? '')),
            ],
            'events' => [
                'event_ids' => $this->eventIdsFromRequest($post),
                'ticket_button_label' => $this->requestWithDefault($post, 'ticket_button_label', 'Tickets'),
            ],
            'band_members' => [
                'items' => $this->linesFromTextarea((string)($post['band_members_items'] ?? '')),
                'title' => $this->requestWithDefault($post, 'band_members_title', 'Band Members:'),
            ],
            'career_highlights' => [
                'left_html' => $this->normalizeHtmlField((string)($post['career_left_html'] ?? '')),
                'right_html' => $this->normalizeHtmlField((string)($post['career_right_html'] ?? '')),
            ],
        ];
    }

    public function selectedEventIds(array $content, array $fallbackEvents): array
    {
        $configured = $content['events']['event_ids'] ?? [];
        if (is_array($configured) && count($configured) > 0) {
            return array_values(array_map('strval', $configured));
        }

        return array_map(
            static fn($event) => (string)$event->event_id,
            $fallbackEvents
        );
    }

    public function deleteManagedImages(array $content): void
    {
        $paths = [];

        $cover = $content['artist']['cover_image'] ?? null;
        if (is_string($cover) && $cover !== '') {
            $paths[] = $cover;
        }

        $main = $content['artist']['hero_media']['main']['image'] ?? null;
        if (is_string($main) && $main !== '') {
            $paths[] = $main;
        }

        $secondary = $content['artist']['hero_media']['secondary'] ?? [];
        if (is_array($secondary)) {
            foreach ($secondary as $item) {
                if (is_array($item) && !empty($item['image']) && is_string($item['image'])) {
                    $paths[] = $item['image'];
                }
            }
        }

        $albums = $content['albums'] ?? [];
        if (is_array($albums)) {
            foreach ($albums as $album) {
                if (is_array($album) && !empty($album['image']) && is_string($album['image'])) {
                    $paths[] = $album['image'];
                }
            }
        }

        foreach (array_unique($paths) as $path) {
            $this->deleteManagedImageIfExists($path);
        }
    }

    public function pageTitle(string $artistName): string
    {
        return trim($artistName) . ' Jazz Detail';
    }

    private function eventIdsFromRequest(array $post): array
    {
        $ids = $post['event_ids'] ?? [];
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn($value) => (string)(int)$value,
            $ids
        ), static fn(string $value) => (int)$value > 0)));
    }

    private function linesFromTextarea(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        $lines = array_map(static fn(string $line) => trim($line), $lines);
        return array_values(array_filter($lines, static fn(string $line) => $line !== ''));
    }

    private function requestWithDefault(array $post, string $key, string $default): string
    {
        $value = trim((string)($post[$key] ?? ''));
        return $value !== '' ? $value : $default;
    }

    private function normalizeHtmlField(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/<[^>]+>/', $value) !== 1) {
            $safeText = nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            return Wysiwyg::render('<p>' . $safeText . '</p>');
        }

        return Wysiwyg::render($value);
    }

    private function nullableText(array $post, string $key): ?string
    {
        $value = trim((string)($post[$key] ?? ''));
        return $value !== '' ? $value : null;
    }

    private function nestedString(array $data, array $path): string
    {
        $cursor = $data;
        foreach ($path as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return '';
            }

            $cursor = $cursor[$segment];
        }

        return is_string($cursor) ? $cursor : '';
    }

    private function storeOptionalImage(array $files, string $field, string $folder, ?string $existingPath): ?string
    {
        if (!isset($files[$field]) || !is_array($files[$field])) {
            return $existingPath;
        }

        $file = $files[$field];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $existingPath;
        }

        return $this->uploads->storeImage(
            $file,
            'jazz',
            $folder,
            null,
            false,
            $existingPath
        );
    }

    private function validDefaultTab(string $value): string
    {
        $value = trim($value);
        return in_array($value, ['events', 'career', 'album'], true) ? $value : 'events';
    }

    private function deleteManagedImageIfExists(string $path): void
    {
        $relative = ltrim($path, '/');
        $allowedPrefixes = [
            'assets/img/jazz/detail/',
            'assets/img/jazz/album/',
        ];

        $matchesPrefix = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($relative, $prefix)) {
                $matchesPrefix = true;
                break;
            }
        }

        if (!$matchesPrefix) {
            return;
        }

        $absolute = __DIR__ . '/../../public/' . $relative;
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }
}