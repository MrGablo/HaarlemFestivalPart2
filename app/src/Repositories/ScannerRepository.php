<?php

namespace App\Repositories;

use App\Framework\Repository;

class ScannerRepository extends Repository
{
    private TicketRepository $ticketRepository;

    public function __construct()
    {
        $this->ticketRepository = new TicketRepository();
    }

    public function getTicketInfo(string $qr): ?array
    {
        return $this->ticketRepository->getTicketInfoByQr($qr);
    }

    public function markAsScanned(int $ticketId): void
    {
        $this->ticketRepository->markAsScanned($ticketId);
    }
}
