<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Artist;
use App\Repositories\ArtistRepository;
use App\Repositories\JazzEventRepository;
use App\Repositories\PageRepository;
use App\Services\UploadService;
use App\Utils\Wysiwyg;
use App\Utils\AdminGuard;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSJazzArtistController
{
    private const PAGE_TYPE = 'Jazz_Detail_Page';

    private ArtistRepository $artists;
    private JazzEventRepository $events;
    private PageRepository $pages;
    private UploadService $uploads;

    public function __construct()
    {
        $this->artists = new ArtistRepository();
        $this->events = new JazzEventRepository();
        $this->pages = new PageRepository();
        $this->uploads = new UploadService();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $artists = $this->artists->getAllArtists();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = $this->csrfToken();

        require __DIR__ . '/../Views/cms/jazz_artists_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $availableEvents = $this->events->getAllJazzEvents();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = $this->csrfToken();

        require __DIR__ . '/../Views/cms/jazz_artist_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $this->assertCsrf();

            $artist = new Artist(['artist_id' => 0, 'name' => '', 'page_id' => null]);
            $artist->name = $this->requestText('artist_name', 'Artist name');

            $content = $this->buildArtistContent($artist->name, null);
            $pageId = $this->pages->createPage($this->pageTitle($artist->name), self::PAGE_TYPE, $content);

            $artist->page_id = $pageId;
            $newId = $this->artists->createArtist($artist);

            Flash::setSuccess('Jazz artist created successfully.');
            header('Location: /cms/jazz/artists/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/jazz/artists/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $artist = $this->getArtistOrRedirect($id);
        $content = $artist->page_id !== null ? $this->pages->getPageContentById($artist->page_id) : [];
        $old = Flash::getOld();
        $availableEvents = $this->events->getAllJazzEvents();
        $linkedEventIds = $this->selectedEventIds($content, $artist->artist_id);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = $this->csrfToken();

        require __DIR__ . '/../Views/cms/jazz_artist_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $artist = $this->getArtistOrRedirect($id);

        try {
            $this->assertCsrf();

            $artist->name = $this->requestText('artist_name', 'Artist name');
            $existingContent = $artist->page_id !== null ? $this->pages->getPageContentById($artist->page_id) : [];
            $content = $this->buildArtistContent($artist->name, $existingContent);

            if ($artist->page_id === null) {
                $artist->page_id = $this->pages->createPage($this->pageTitle($artist->name), self::PAGE_TYPE, $content);
            } else {
                $this->pages->savePageContentById($artist->page_id, $content, $this->pageTitle($artist->name));
            }

            $this->artists->updateArtist($artist);

            Flash::setSuccess('Jazz artist updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
        }

        header('Location: /cms/jazz/artists/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $this->assertCsrf();

            $artist = $this->getArtistOrRedirect($id);
            $linkedEvents = $this->events->getJazzEventsByArtistId($artist->artist_id);
            if (count($linkedEvents) > 0) {
                throw new \RuntimeException('This artist is still linked to jazz events. Reassign or delete those events first.');
            }

            $content = $artist->page_id !== null ? $this->pages->getPageContentById($artist->page_id) : [];

            if (!$this->artists->deleteArtistById($artist->artist_id)) {
                throw new \RuntimeException('Jazz artist could not be deleted.');
            }

            if ($artist->page_id !== null) {
                $this->pages->deletePageById($artist->page_id);
            }

            $this->deleteArtistImages($content);
            Flash::setSuccess('Jazz artist deleted successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/jazz/artists', true, 302);
        exit;
    }

    private function getArtistOrRedirect(int $id): Artist
    {
        $artist = $this->artists->findArtistById($id);
        if ($artist !== null) {
            return $artist;
        }

        Flash::setErrors(['general' => 'Jazz artist not found.']);
        header('Location: /cms/jazz/artists', true, 302);
        exit;
    }

    private function buildArtistContent(string $artistName, ?array $existingContent): array
    {
        $existingContent = is_array($existingContent) ? $existingContent : [];

        $coverImage = $this->storeOptionalImage(
            'cover_image_file',
            'detail',
            $this->nestedString($existingContent, ['artist', 'cover_image'])
        );
        $heroMainImage = $this->storeOptionalImage(
            'hero_main_image_file',
            'detail',
            $this->nestedString($existingContent, ['artist', 'hero_media', 'main', 'image'])
        );

        $secondary = [];
        for ($index = 1; $index <= 2; $index++) {
            $existingItem = $existingContent['artist']['hero_media']['secondary'][$index - 1] ?? [];
            $image = $this->storeOptionalImage(
                'hero_secondary_' . $index . '_image_file',
                'detail',
                is_array($existingItem) ? (string)($existingItem['image'] ?? '') : ''
            );
            $caption = $this->nullableText('hero_secondary_' . $index . '_caption');

            if ($image !== null && $image !== '') {
                $secondary[] = [
                    'image' => $image,
                    'caption' => $caption,
                ];
            }
        }

        $albums = [];
        for ($index = 1; $index <= 3; $index++) {
            $existingAlbum = $existingContent['albums'][$index - 1] ?? [];
            $image = $this->storeOptionalImage(
                'album_' . $index . '_image_file',
                'album',
                is_array($existingAlbum) ? (string)($existingAlbum['image'] ?? '') : ''
            );
            $title = trim((string)($_POST['album_' . $index . '_title'] ?? ''));
            $artist = trim((string)($_POST['album_' . $index . '_artist'] ?? ''));
            $description = trim((string)($_POST['album_' . $index . '_description_html'] ?? ''));

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
                    'album' => $this->requestWithDefault('tabs_album_label', 'Album'),
                    'career' => $this->requestWithDefault('tabs_career_label', 'Career Highlights'),
                    'events' => $this->requestWithDefault('tabs_events_label', 'Events'),
                ],
                'default' => $this->validDefaultTab((string)($_POST['tabs_default'] ?? 'events')),
            ],
            'about' => [
                'html' => $this->normalizeHtmlField((string)($_POST['about_html'] ?? '')),
                'title' => $this->requestWithDefault('about_title', 'About the Artist:'),
            ],
            'albums' => $albums,
            'artist' => [
                'name' => $artistName,
                'kicker' => trim((string)($_POST['artist_kicker'] ?? '')),
                'breadcrumb' => [
                    'current' => $this->requestWithDefault('breadcrumb_current', $artistName),
                    'back_href' => $this->requestWithDefault('breadcrumb_back_href', '/jazz'),
                    'back_label' => $this->requestWithDefault('breadcrumb_back_label', 'Jazz Event'),
                ],
                'hero_media' => [
                    'main' => [
                        'image' => $heroMainImage ?? '',
                        'caption' => $this->nullableText('hero_main_caption'),
                    ],
                    'secondary' => $secondary,
                ],
                'hero_title' => $this->requestWithDefault('hero_title', $artistName),
                'cover_image' => $coverImage ?? '',
                'hero_subtitle' => trim((string)($_POST['hero_subtitle'] ?? '')),
            ],
            'events' => [
                'event_ids' => $this->eventIdsFromRequest(),
                'ticket_button_label' => $this->requestWithDefault('ticket_button_label', 'Tickets'),
            ],
            'band_members' => [
                'items' => $this->linesFromTextarea((string)($_POST['band_members_items'] ?? '')),
                'title' => $this->requestWithDefault('band_members_title', 'Band Members:'),
            ],
            'career_highlights' => [
                'left_html' => $this->normalizeHtmlField((string)($_POST['career_left_html'] ?? '')),
                'right_html' => $this->normalizeHtmlField((string)($_POST['career_right_html'] ?? '')),
            ],
        ];
    }

    private function eventIdsFromRequest(): array
    {
        $ids = $_POST['event_ids'] ?? [];
        if (!is_array($ids)) {
            return [];
        }

        $values = array_values(array_unique(array_filter(array_map(
            static fn($value) => (string)(int)$value,
            $ids
        ), static fn(string $value) => (int)$value > 0)));

        return $values;
    }

    private function selectedEventIds(array $content, int $artistId): array
    {
        $configured = $content['events']['event_ids'] ?? [];
        if (is_array($configured) && count($configured) > 0) {
            return array_values(array_map('strval', $configured));
        }

        return array_map(
            static fn($event) => (string)$event->event_id,
            $this->events->getJazzEventsByArtistId($artistId)
        );
    }

    private function linesFromTextarea(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        $lines = array_map(static fn(string $line) => trim($line), $lines);
        return array_values(array_filter($lines, static fn(string $line) => $line !== ''));
    }

    private function requestText(string $key, string $label): string
    {
        $value = trim((string)($_POST[$key] ?? ''));
        if ($value === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $value;
    }

    private function requestWithDefault(string $key, string $default): string
    {
        $value = trim((string)($_POST[$key] ?? ''));
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

    private function nullableText(string $key): ?string
    {
        $value = trim((string)($_POST[$key] ?? ''));
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

    private function storeOptionalImage(string $field, string $folder, ?string $existingPath): ?string
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return $existingPath;
        }

        $file = $_FILES[$field];
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

    private function pageTitle(string $artistName): string
    {
        return trim($artistName) . ' Jazz Detail';
    }

    private function deleteArtistImages(array $content): void
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

    private function csrfToken(): string
    {
        Session::ensureStarted();

        $token = (string)($_SESSION['cms_csrf_token'] ?? '');
        if ($token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['cms_csrf_token'] = $token;

        return $token;
    }

    private function assertCsrf(): void
    {
        Session::ensureStarted();

        $sessionToken = (string)($_SESSION['cms_csrf_token'] ?? '');
        $postedToken = (string)($_POST['_csrf'] ?? '');

        if ($sessionToken === '' || $postedToken === '' || !hash_equals($sessionToken, $postedToken)) {
            throw new \RuntimeException('Invalid form token. Please refresh and try again.');
        }
    }
}