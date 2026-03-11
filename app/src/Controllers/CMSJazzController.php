<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\JazzEvent;
use App\Repositories\ArtistRepository;
use App\Repositories\JazzEventRepository;
use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSJazzController
{
    private ArtistRepository $artists;
    private JazzEventRepository $jazzEvents;
    private UploadService $uploads;

    public function __construct()
    {
        $this->artists = new ArtistRepository();
        $this->jazzEvents = new JazzEventRepository();
        $this->uploads = new UploadService();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $events = $this->jazzEvents->getAllJazzEvents();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = $this->csrfToken();

        require __DIR__ . '/../Views/cms/jazz_events_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $artists = $this->artists->getAllArtists();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = $this->csrfToken();

        require __DIR__ . '/../Views/cms/jazz_event_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $this->assertCsrf();

            $event = new JazzEvent([
                'event_id' => 0,
                'event_type' => 'jazz',
                'artist_id' => null,
                'img_background' => null,
            ]);
            $this->fillEventFromPost($event);
            $this->handleImageUpload($event, false);

            $newId = $this->jazzEvents->createJazzEvent($event);
            Flash::setSuccess('Jazz event created successfully.');
            header('Location: /cms/events/jazz/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/events/jazz/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);
        $artists = $this->artists->getAllArtists();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = $this->csrfToken();

        require __DIR__ . '/../Views/cms/jazz_event_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);

        try {
            $this->assertCsrf();

            $this->fillEventFromPost($event);
            $this->handleImageUpload($event, true);

            $this->jazzEvents->updateJazzEvent($event);
            Flash::setSuccess('Jazz event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/jazz/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $this->assertCsrf();

            $event = $this->getEventOrRedirect($id);

            $deleted = $this->jazzEvents->deleteJazzEventById($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Jazz event could not be deleted.']);
                header('Location: /cms/events/jazz', true, 302);
                exit;
            }

            $this->deleteJazzImageIfExists($event->img_background);
            Flash::setSuccess('Jazz event deleted successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/jazz', true, 302);
        exit;
    }

    private function getEventOrRedirect(int $id): JazzEvent
    {
        $event = $this->jazzEvents->findJazzEventById($id);
        if ($event !== null) {
            return $event;
        }

        Flash::setErrors(['general' => 'Jazz event not found.']);
        header('Location: /cms/events/jazz', true, 302);
        exit;
    }

    private function fillEventFromPost(JazzEvent $event): void
    {
        $event->title = $this->requestText('title', 'Title');
        $event->start_date = $this->normalizeDateTime((string)($_POST['start_date'] ?? ''));
        $event->end_date = $this->normalizeDateTime((string)($_POST['end_date'] ?? ''));
        $event->location = $this->requestText('location', 'Location');
        $event->artist_id = $this->parsePositiveInt((string)($_POST['artist_id'] ?? ''), 'Artist');
        $event->price = $this->parsePrice((string)($_POST['price'] ?? ''));

        $artist = $this->artists->findArtistById((int)$event->artist_id);
        if ($artist === null) {
            throw new \RuntimeException('Selected artist does not exist.');
        }

        $event->artist_name = $artist->name;
        $event->page_id = $artist->page_id;
    }

    private function handleImageUpload(JazzEvent $event, bool $replaceOld): void
    {
        if (!isset($_FILES['img_background_file']) || !is_array($_FILES['img_background_file'])) {
            return;
        }

        $file = $_FILES['img_background_file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return;
        }

        $event->img_background = $this->uploads->storeImage(
            $file,
            'jazz',
            'event',
            null,
            false,
            $replaceOld ? $event->img_background : null
        );
    }

    private function requestText(string $key, string $label): string
    {
        $raw = trim((string)($_POST[$key] ?? ''));
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $raw;
    }

    private function parsePrice(string $raw): float
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw new \RuntimeException('Price is required.');
        }

        if (!is_numeric($raw)) {
            throw new \RuntimeException('Price must be numeric.');
        }

        $price = (float)$raw;
        if ($price < 0) {
            throw new \RuntimeException('Price cannot be negative.');
        }

        return $price;
    }

    private function parsePositiveInt(string $raw, string $label): int
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        $value = (int)$raw;
        if ($value <= 0) {
            throw new \RuntimeException($label . ' must be a positive integer.');
        }

        return $value;
    }

    private function normalizeDateTime(string $input): string
    {
        $input = trim($input);

        if ($input === '') {
            throw new \RuntimeException('Date/time fields are required.');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $input)) {
            return str_replace('T', ' ', $input) . ':00';
        }

        try {
            $dt = new \DateTime($input);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Invalid date/time format.');
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

    private function deleteJazzImageIfExists(?string $path): void
    {
        if ($path === null || trim($path) === '') {
            return;
        }

        $relative = ltrim($path, '/');
        if (!str_starts_with($relative, 'assets/img/jazz/event/')) {
            return;
        }

        $absolute = __DIR__ . '/../../public/' . $relative;
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }
}