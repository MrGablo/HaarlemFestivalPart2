<?php

namespace App\Controllers;

use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\PageRepository;
use App\Repositories\JazzEventRepository;
use App\Services\CmsContentService;
use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\Flash;
use App\Utils\Session;

class CMSController
{
    private IPageRepository $pages;
    private CmsContentService $contentService;

    private JazzEventRepository $jazzEvents;
    private UploadService $uploads;

    public function __construct()
    {
        $this->pages = new PageRepository();
        $this->contentService = new CmsContentService();

        $this->jazzEvents = new JazzEventRepository();
        $this->uploads = new UploadService();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $pages = $this->pages->getAllPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/index.php';
    }

    public function generalIndex(): void
    {
        AdminGuard::requireAdmin(true);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/generalIndex.php';
    }

    // ✅ NEW: list all jazz events
    public function jazzIndex(): void
    {
        AdminGuard::requireAdmin(true);

        $events = $this->jazzEvents->getAllJazzEvents();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/jazz_events_index.php';
    }

    // ✅ NEW: edit one jazz event
    public function jazzEdit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->jazzEvents->findJazzEventById($id);
        if ($event === null) {
            Flash::setErrors(['general' => 'Jazz event not found.']);
            header('Location: /cms/events/jazz', true, 302);
            exit;
        }

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/jazz_event_edit.php';
    }

    // ✅ NEW: update one jazz event (with optional image upload)
    public function jazzUpdate(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->jazzEvents->findJazzEventById($id);
        if ($event === null) {
            Flash::setErrors(['general' => 'Jazz event not found.']);
            header('Location: /cms/events/jazz', true, 302);
            exit;
        }

        try {
            // Parent + child fields
            $event->title       = trim((string)($_POST['title'] ?? $event->title));
            $event->start_date  = $this->normalizeDateTime((string)($_POST['start_date'] ?? ''), $event->start_date);
            $event->end_date    = $this->normalizeDateTime((string)($_POST['end_date'] ?? ''), $event->end_date);
            $event->location    = trim((string)($_POST['location'] ?? $event->location));
            $event->artist_name = trim((string)($_POST['artist_name'] ?? $event->artist_name));

            if (isset($_POST['price']) && $_POST['price'] !== '') {
                $event->price = (float)$_POST['price'];
            }

            $pageIdRaw = trim((string)($_POST['page_id'] ?? ''));
            $event->page_id = ($pageIdRaw === '') ? null : (int)$pageIdRaw;

            // Image upload: file input name MUST be img_background_file
            if (isset($_FILES['img_background_file']) && is_array($_FILES['img_background_file'])) {
                $file = $_FILES['img_background_file'];

                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    // store new file with random name, and delete the previous one
                    $event->img_background = $this->uploads->storeImage(
                        $file,
                        'jazz',
                        'event',
                        null,
                        false,
                        $event->img_background // treat as "old path to delete"
                    );
                }
            }

            $this->jazzEvents->updateJazzEvent($event);

            Flash::setSuccess('Jazz event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/jazz/' . $id, true, 302);
        exit;
    }

    // --- existing page edit/update ----

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $page = $this->pages->findPageById($id);
        if ($page === null) {
            http_response_code(404);
            echo 'Page not found.';
            return;
        }

        $content = $this->pages->getPageContentById($id);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $page = $this->pages->findPageById($id);
        if ($page === null) {
            Flash::setErrors(['general' => 'Page not found.']);
            header('Location: /cms', true, 302);
            exit;
        }

        try {
            $contentInput = $_POST['content'] ?? [];
            $typeMap = $_POST['types'] ?? [];

            if (!is_array($contentInput)) {
                throw new \RuntimeException('Invalid content payload.');
            }

            $normalized = $this->contentService->normalizeContent($contentInput, is_array($typeMap) ? $typeMap : []);
            $this->pages->savePageContentById($id, $normalized);

            Flash::setSuccess('Page content updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/page/' . $id, true, 302);
        exit;
    }

    private function normalizeDateTime(string $input, $fallback)
    {
        $input = trim($input);
        if ($input === '') return $fallback;

        // datetime-local: YYYY-MM-DDTHH:MM
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $input)) {
            return str_replace('T', ' ', $input) . ':00';
        }

        try {
            $dt = new \DateTime($input);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return $fallback;
        }
    }
}
