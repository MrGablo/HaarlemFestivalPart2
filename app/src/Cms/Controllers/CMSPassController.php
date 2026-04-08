<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Services\PassService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSPassController
{
    private PassService $service;

    public function __construct()
    {
        $this->service = new PassService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $passes = $this->service->allPassProducts();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/passes_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $festivalTypes = $this->service->getFestivalTypes();
        $passScopes = $this->service->getPassScopes();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/pass_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $newId = $this->service->createPassProductFromInput($_POST);
            Flash::setSuccess('Pass created successfully.');
            header('Location: /cms/passes/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/passes/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $pass = $this->service->findPassProduct($id);
        if ($pass === null) {
            Flash::setErrors(['general' => 'Pass not found.']);
            header('Location: /cms/passes', true, 302);
            exit;
        }

        $festivalTypes = $this->service->getFestivalTypes();
        $passScopes = $this->service->getPassScopes();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/pass_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $this->service->updatePassProductFromInput($id, $_POST);
            Flash::setSuccess('Pass updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/passes/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->service->deletePassProduct($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Pass not found or could not be deleted.']);
            } else {
                Flash::setSuccess('Pass deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/passes', true, 302);
        exit;
    }
}
