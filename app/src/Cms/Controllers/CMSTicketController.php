<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Cms\Services\CmsTicketService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSTicketController
{
    private CmsTicketService $service;

    public function __construct()
    {
        $this->service = new CmsTicketService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $search = trim((string)($_GET['search'] ?? ''));
        $scanFilter = trim((string)($_GET['scan'] ?? ''));
        $sortColumn = trim((string)($_GET['sort'] ?? 'ticket_id'));
        $sortDirection = trim((string)($_GET['dir'] ?? 'DESC'));

        $tickets = $this->service->searchTickets($search, $scanFilter, $sortColumn, $sortDirection);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/tickets_index.php';
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $ticket = $this->service->findTicket($id);
        if ($ticket === null) {
            Flash::setErrors(['general' => 'Ticket not found.']);
            header('Location: /cms/tickets', true, 302);
            exit;
        }

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/ticket_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();
            $this->service->updateTicket($id, $_POST);
            Flash::setSuccess('Ticket updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/tickets/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->service->deleteTicket($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Ticket not found or could not be deleted.']);
            } else {
                Flash::setSuccess('Ticket deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/tickets', true, 302);
        exit;
    }
}