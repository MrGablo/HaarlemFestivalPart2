<?php

namespace App\Services;

use App\Repositories\DanceHomeRepository;
use App\Repositories\EventRepository;
use App\Repositories\JazzEventRepository;
use App\Models\PassEvent;
use App\Repositories\PassRepository;
use App\Repositories\TicketRepository;

class TicketService
{
    private TicketRepository $ticketRepository;
    private PassService $passService;
    private JazzEventRepository $jazzEventRepository;
    private DanceHomeRepository $danceHomeRepository;
    private EventRepository $eventRepository;

    public function __construct(
        ?TicketRepository $ticketRepository = null,
        ?PassService $passService = null,
        ?JazzEventRepository $jazzEventRepository = null,
        ?DanceHomeRepository $danceHomeRepository = null,
        ?EventRepository $eventRepository = null
    ) {
        $this->ticketRepository = $ticketRepository ?? new TicketRepository();
        $this->passService = $passService ?? new PassService(new PassRepository());
        $this->jazzEventRepository = $jazzEventRepository ?? new JazzEventRepository();
        $this->danceHomeRepository = $danceHomeRepository ?? new DanceHomeRepository();
        $this->eventRepository = $eventRepository ?? new EventRepository();
    }

    public function createTicketsForOrderItem(int $orderItemId, int $userId, int $eventId, int $quantity, ?string $passDate): void
    {
        if ($orderItemId <= 0 || $userId <= 0 || $eventId <= 0 || $quantity <= 0) {
            return;
        }

        $coveredEventIds = $this->resolveCoveredEventIds($eventId, $passDate);
        if ($coveredEventIds === []) {
            // Pass products should never generate a QR for the pass item itself.
            return;
        }

        for ($i = 0; $i < $quantity; $i++) {
            foreach ($coveredEventIds as $coveredEventId) {
                $this->ticketRepository->executeInTransaction(function (\PDO $connection) use ($orderItemId, $userId, $coveredEventId): void {
                    $availabilityUpdated = $this->eventRepository->decrementAvailabilityByOneUsingConnection($connection, $coveredEventId);
                    if (!$availabilityUpdated) {
                        throw new \RuntimeException('No availability left for event_id=' . $coveredEventId);
                    }

                    $this->ticketRepository->createTicketUsingConnection(
                        $connection,
                        $orderItemId,
                        $userId,
                        $coveredEventId,
                        'TICKET_' . bin2hex(random_bytes(16))
                    );
                });
            }
        }
    }

    public function getPaidTicketsForUser(int $userId): array
    {
        return $this->ticketRepository->getPaidTicketsForUser($userId);
    }

    public function getTicketInfoByQr(string $qr): ?array
    {
        return $this->ticketRepository->getTicketInfoByQr($qr);
    }

    public function markAsScanned(int $ticketId): void
    {
        $this->ticketRepository->markAsScanned($ticketId);
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
                if ($passDate === null || trim($passDate) === '') {
                    return [];
                }

                return $this->jazzEventRepository->getJazzEventIdsByDate($passDate);
            }

            if ($festivalType === 'dance') {
                return $this->danceHomeRepository->getDanceSessionEventIdsByPassEvent($eventId);
            }
        }

        if ($scope === 'all_days') {
            if ($festivalType === 'jazz') {
                return $this->jazzEventRepository->getAllJazzEventIds();
            }

            if ($festivalType === 'dance') {
                return $this->danceHomeRepository->getAllDanceSessionEventIds();
            }
        }

        return [$eventId];
    }
}
