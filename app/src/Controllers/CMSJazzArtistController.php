<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Artist;
use App\Repositories\ArtistRepository;
use App\Repositories\JazzEventRepository;
use App\Repositories\PageRepository;
use App\Services\CmsJazzArtistContentService;
use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSJazzArtistController
{
    private const PAGE_TYPE = 'Jazz_Detail_Page';

    private ArtistRepository $artists;
    private JazzEventRepository $events;
    private PageRepository $pages;
    private CmsJazzArtistContentService $contentService;

    public function __construct()
    {
        $this->artists = new ArtistRepository();
        $this->events = new JazzEventRepository();
        $this->pages = new PageRepository();
        $this->contentService = new CmsJazzArtistContentService(new UploadService());

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

            $content = $this->contentService->buildContent($artist->name, null, $_POST, $_FILES);
            $pageId = $this->pages->createPage($this->contentService->pageTitle($artist->name), self::PAGE_TYPE, $content);

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
        $linkedEventIds = $this->contentService->selectedEventIds(
            $content,
            $this->events->getJazzEventsByArtistId($artist->artist_id)
        );
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
            $content = $this->contentService->buildContent($artist->name, $existingContent, $_POST, $_FILES);

            if ($artist->page_id === null) {
                $artist->page_id = $this->pages->createPage($this->contentService->pageTitle($artist->name), self::PAGE_TYPE, $content);
            } else {
                $this->pages->savePageContentById($artist->page_id, $content, $this->contentService->pageTitle($artist->name));
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
            if (count($this->events->getJazzEventsByArtistId($artist->artist_id)) > 0) {
                throw new \RuntimeException('This artist is still linked to jazz events. Reassign or delete those events first.');
            }

            $content = $artist->page_id !== null ? $this->pages->getPageContentById($artist->page_id) : [];
            if (!$this->artists->deleteArtistById($artist->artist_id)) {
                throw new \RuntimeException('Jazz artist could not be deleted.');
            }

            if ($artist->page_id !== null) {
                $this->pages->deletePageById($artist->page_id);
            }

            $this->contentService->deleteManagedImages($content);
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

    private function requestText(string $key, string $label): string
    {
        $value = trim((string)($_POST[$key] ?? ''));
        if ($value === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $value;
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