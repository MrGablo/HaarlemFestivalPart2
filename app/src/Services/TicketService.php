<?php

namespace App\Services;

use App\Models\PassEvent;
use App\Repositories\PassRepository;
use App\Repositories\TicketRepository;

class TicketService
{
    private TicketRepository $ticketRepository;
    private PassService $passService;

    public function __construct(?TicketRepository $ticketRepository = null, ?PassService $passService = null)
    {
        $this->ticketRepository = $ticketRepository ?? new TicketRepository();
        $this->passService = $passService ?? new PassService(new PassRepository());
    }

    public function createTicketsForOrderItem(int $orderItemId, int $userId, int $eventId, int $quantity, ?string $passDate): void
    {
        if ($orderItemId <= 0 || $userId <= 0 || $eventId <= 0 || $quantity <= 0) {
            return;
        }

        $coveredEventIds = $this->resolveCoveredEventIds($eventId, $passDate);
        if ($coveredEventIds === []) {
            $coveredEventIds = [$eventId];
        }

        for ($i = 0; $i < $quantity; $i++) {
            foreach ($coveredEventIds as $coveredEventId) {
                try {
                    $this->ticketRepository->createTicket(
                        $orderItemId,
                        $userId,
                        $coveredEventId,
                        'TICKET_' . uniqid('', true)
                    );
                } catch (\Throwable $e) {
                    error_log('Ticket creation failed: ' . $e->getMessage());
                }
            }
        }
    }

    public function getPaidTicketsForUser(int $userId): array
    {
        return $this->ticketRepository->getPaidTicketsForUser($userId);
    }

    private function resolveCoveredEventIds(int $eventId, ?string $passDate): array
    {
        $pass = $this->passService->findActivePassProductByEventId($eventId);
        if (!$pass instanceof PassEvent) {
            return [$eventId];
        }

        $festivalType = strtolower(trim((string)$pass->festival_type));
        $scope = strtolower(trim((string)$pass->pass_scope));

        if ($scope === 'day') {
            if ($festivalType === 'jazz') {
                if ($passDate === null || !$this->passService->isValidJazzPassDate($passDate)) {
                    return [];
                }

                return $this->passService->getJazzEventIdsByDate($passDate);
            }

            if ($festivalType === 'dance') {
                return $this->passService->getDanceSessionEventIdsByPassEvent($eventId);
            }
        }

        if ($scope === 'all_days') {
            if ($festivalType === 'jazz') {
                return $this->passService->getAllJazzEventIds();
            }

            if ($festivalType === 'dance') {
                return $this->passService->getAllDanceSessionEventIds();
            }
        }

        return [$eventId];
    }
}
