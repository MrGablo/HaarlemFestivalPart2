<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\Artist;
use App\Services\ArtistService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSArtistController
{
    private ArtistService $service;

    public function __construct()
    {
        $this->service = new ArtistService();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $artists = $this->service->allArtists();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/artists_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $pages = $this->service->allPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/artist_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $artist = new Artist([]);
            $this->fillArtistFromPost($artist);

            $newId = $this->service->createArtist($artist);
            Flash::setSuccess('Artist created successfully.');
            header('Location: /cms/artists/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/artists/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $artist = $this->getArtistOrRedirect($id);

        $pages = $this->service->allPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/artist_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $artist = $this->getArtistOrRedirect($id);

        try {
            Csrf::assertPost();

            $this->fillArtistFromPost($artist);
            $this->service->updateArtist($artist);

            Flash::setSuccess('Artist updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/artists/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->service->deleteArtist($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Artist could not be deleted.']);
            } else {
                Flash::setSuccess('Artist deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/artists', true, 302);
        exit;
    }

    private function getArtistOrRedirect(int $id): Artist
    {
        $artist = $this->service->findArtist($id);
        if ($artist !== null) {
            return $artist;
        }

        Flash::setErrors(['general' => 'Artist not found.']);
        header('Location: /cms/artists', true, 302);
        exit;
    }

    private function fillArtistFromPost(Artist $artist): void
    {
        $artist->name = $this->requestText('name', 'Name');
        $artist->page_id = $this->parseOptionalPositiveInt((string)($_POST['page_id'] ?? ''), 'Page ID');
    }

    private function requestText(string $key, string $label): string
    {
        $raw = trim((string)($_POST[$key] ?? ''));
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $raw;
    }

    private function parseOptionalPositiveInt(string $raw, string $label): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $value = (int)$raw;
        if ($value <= 0) {
            throw new \RuntimeException($label . ' must be a positive integer.');
        }

        return $value;
    }

}
