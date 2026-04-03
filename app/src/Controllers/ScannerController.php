<?php

namespace App\Controllers;

use App\Services\TicketService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;

class ScannerController
{
    private TicketService $ticketService;

    public function __construct()
    {
        // Require user to be admin/staff to scan tickets (optional but recommended)
        AdminGuard::requireEmployee();
        $this->ticketService = new TicketService();
    }

    public function index(): void
    {
        $success = Flash::getSuccess();
        $errors = Flash::getErrors();
        $old = Flash::getOld();

        if ($success) {
            $status = 'success';
            $message = $success;
            $eventName = $old['eventName'] ?? null;
            $eventTime = $old['eventTime'] ?? null;
        } elseif (!empty($errors)) {
            $status = $errors['status'] ?? 'error';
            $message = $errors['message'] ?? 'An error occurred';
        }

        $csrfToken = Csrf::token();
        require __DIR__ . '/../Views/scanner.php';
    }

    public function processScan(): void
    {
        Csrf::assertPost();
        $qrHash = $_POST['qr_hash'] ?? '';

        if (empty($qrHash)) {
            Flash::setErrors(['status' => 'error', 'message' => 'No QR Code provided']);
        } else {
            $ticketInfo = $this->ticketService->getTicketInfoByQr($qrHash);

            if (!$ticketInfo) {
                Flash::setErrors(['status' => 'error', 'message' => 'Invalid QR Code']);
            } elseif ($ticketInfo['is_scanned'] == 1) {
                Flash::setErrors(['status' => 'warning', 'message' => 'Ticket already scanned!']);
            } else {
                $this->ticketService->markAsScanned($ticketInfo['ticket_id']);
                Flash::setSuccess('Ticket verified and checked in!');
                Flash::setOld([
                    'eventName' => $ticketInfo['event_name'],
                    'eventTime' => $ticketInfo['event_start_time']
                ]);
            }
        }

        header('Location: /scanner');
        exit;
    }
}
