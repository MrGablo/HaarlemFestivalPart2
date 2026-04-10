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

        $plan = $this->buildFulfillmentPlan([
            [
                'order_item_id' => $orderItemId,
                'event_id' => $eventId,
                'quantity' => $quantity,
                'pass_date' => $passDate,
            ],
        ]);

        $this->ticketRepository->executeInTransaction(function (\PDO $connection) use ($plan, $userId): void {
            $this->validateAvailabilityForRequirementsUsingConnection($connection, $plan['requirements']);
            $this->decrementAvailabilityForRequirementsUsingConnection($connection, $plan['requirements']);
            $this->createTicketsForPlansUsingConnection($connection, $userId, $plan['items']);
        });
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

    /**
     * @param array<int, array<string, mixed>> $orderItems
     * @return array{items: array<int, array{order_item_id:int, quantity:int, covered_event_ids:array<int, int>}>, requirements: array<int, int>}
     */
    public function buildFulfillmentPlan(array $orderItems): array
    {
        $items = [];
        $requirements = [];

        foreach ($orderItems as $item) {
            $orderItemId = (int)($item['order_item_id'] ?? 0);
            $eventId = (int)($item['event_id'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);
            $passDate = isset($item['pass_date']) ? (string)$item['pass_date'] : null;

            if ($orderItemId <= 0 || $eventId <= 0 || $quantity <= 0) {
                throw new \RuntimeException('Order contains an invalid item and cannot be fulfilled.');
            }

            $coveredEventIds = array_values(array_unique(array_filter(
                array_map('intval', $this->resolveCoveredEventIds($eventId, $passDate)),
                static fn (int $coveredEventId): bool => $coveredEventId > 0
            )));
            sort($coveredEventIds);

            if ($coveredEventIds === []) {
                throw new \RuntimeException('Order item ' . $orderItemId . ' cannot be fulfilled because no covered events were resolved.');
            }

            $items[] = [
                'order_item_id' => $orderItemId,
                'quantity' => $quantity,
                'covered_event_ids' => $coveredEventIds,
            ];

            foreach ($coveredEventIds as $coveredEventId) {
                $requirements[$coveredEventId] = ($requirements[$coveredEventId] ?? 0) + $quantity;
            }
        }

        ksort($requirements);

        return [
            'items' => $items,
            'requirements' => $requirements,
        ];
    }

    /** @param array<int, int> $requirements */
    public function validateAvailabilityForRequirementsUsingConnection(\PDO $connection, array $requirements): void
    {
        if ($requirements === []) {
            throw new \RuntimeException('No event availability requirements were generated for fulfilment.');
        }

        $lockedEvents = $this->eventRepository->lockEventsByIdsUsingConnection($connection, array_keys($requirements));

        foreach ($requirements as $eventId => $requiredQuantity) {
            $eventRow = $lockedEvents[$eventId] ?? null;
            if (!is_array($eventRow)) {
                throw new \RuntimeException('Event ' . $eventId . ' is missing and cannot be fulfilled.');
            }

            $availability = (int)($eventRow['availability'] ?? 0);
            if ($availability < $requiredQuantity) {
                throw new \RuntimeException('Not enough seats available for event ' . $eventId . '.');
            }
        }
    }

    /** @param array<int, int> $requirements */
    public function decrementAvailabilityForRequirementsUsingConnection(\PDO $connection, array $requirements): void
    {
        foreach ($requirements as $eventId => $requiredQuantity) {
            $updated = $this->eventRepository->decrementAvailabilityByQuantityUsingConnection($connection, (int)$eventId, (int)$requiredQuantity);
            if (!$updated) {
                throw new \RuntimeException('Unable to reserve seats for event ' . $eventId . '.');
            }
        }
    }

    /** @param array<int, array{order_item_id:int, quantity:int, covered_event_ids:array<int, int>}> $plans */
    public function createTicketsForPlansUsingConnection(\PDO $connection, int $userId, array $plans): void
    {
        foreach ($plans as $plan) {
            $orderItemId = (int)($plan['order_item_id'] ?? 0);
            $quantity = (int)($plan['quantity'] ?? 0);
            $coveredEventIds = $plan['covered_event_ids'] ?? [];

            if ($orderItemId <= 0 || $quantity <= 0 || !is_array($coveredEventIds) || $coveredEventIds === []) {
                throw new \RuntimeException('Ticket creation plan is invalid.');
            }

            for ($i = 0; $i < $quantity; $i++) {
                foreach ($coveredEventIds as $coveredEventId) {
                    $this->ticketRepository->createTicketUsingConnection(
                        $connection,
                        $orderItemId,
                        $userId,
                        (int)$coveredEventId,
                        'TICKET_' . bin2hex(random_bytes(16))
                    );
                }
            }
        }
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
