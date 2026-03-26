<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\Venue;
use App\Cms\Services\CmsVenueService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSVenueController
{
    private CmsVenueService $service;

    public function __construct()
    {
        $this->service = new CmsVenueService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $venues = $this->service->allVenues();
        $usageByVenueId = $this->service->getUsageByVenueId();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/venues_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/venue_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $name = trim((string)($_POST['name'] ?? ''));
            $newId = $this->service->createVenue($name);

            Flash::setSuccess('Venue created successfully.');
            header('Location: /cms/venues/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/venues/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $venue = $this->getVenueOrRedirect($id);
        $inUseCount = $this->service->countVenueUsage((int)$venue->venue_id);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/venue_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $name = trim((string)($_POST['name'] ?? ''));
            $this->service->updateVenue($id, $name);

            Flash::setSuccess('Venue updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/venues/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->service->deleteVenue($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Venue could not be deleted.']);
            } else {
                Flash::setSuccess('Venue deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/venues', true, 302);
        exit;
    }

    private function getVenueOrRedirect(int $id): Venue
    {
        $venue = $this->service->findVenue($id);
        if ($venue !== null) {
            return $venue;
        }

        Flash::setErrors(['general' => 'Venue not found.']);
        header('Location: /cms/venues', true, 302);
        exit;
    }

}
