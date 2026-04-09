<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Repositories\TicketRepository;

final class CmsTicketService
{
    public function __construct(
        private TicketRepository $tickets = new TicketRepository()
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function searchTickets(
        string $search = '',
        string $scanFilter = '',
        string $sortColumn = 'ticket_id',
        string $sortDirection = 'DESC'
    ): array {
        $rows = $this->tickets->getAllTicketsWithSummary();
        $normalized = array_map(fn(array $row): array => $this->normalizeRow($row), $rows);

        if ($search !== '' || $scanFilter !== '') {
            $normalized = array_values(array_filter(
                $normalized,
                fn(array $row): bool => $this->matchesFilters($row, $search, $scanFilter)
            ));
        }

        $sortBy = $this->normalizeSortColumn($sortColumn);
        $sortDir = strtoupper($sortDirection) === 'ASC' ? 1 : -1;

        usort($normalized, function (array $a, array $b) use ($sortBy, $sortDir): int {
            $left = $a[$sortBy] ?? null;
            $right = $b[$sortBy] ?? null;

            if (is_string($left)) {
                $left = strtolower($left);
            }
            if (is_string($right)) {
                $right = strtolower($right);
            }

            return ($left <=> $right) * $sortDir;
        });

        return $normalized;
    }

    /** @return array<string, mixed>|null */
    public function findTicket(int $ticketId): ?array
    {
        if ($ticketId <= 0) {
            return null;
        }

        $row = $this->tickets->findTicketById($ticketId);
        if ($row === null) {
            return null;
        }

        return $this->normalizeRow($row);
    }

    public function updateTicket(int $ticketId, array $input): void
    {
        $ticket = $this->findTicket($ticketId);
        if ($ticket === null) {
            throw new \RuntimeException('Ticket not found.');
        }

        $qr = trim((string)($input['qr'] ?? ''));
        if ($qr === '') {
            throw new \RuntimeException('QR is required.');
        }

        $isScanned = isset($input['is_scanned']) && (string)$input['is_scanned'] === '1' ? 1 : 0;

        $updated = $this->tickets->updateTicketCms($ticketId, $qr, $isScanned);
        if (!$updated) {
            throw new \RuntimeException('Ticket could not be updated.');
        }
    }

    public function deleteTicket(int $ticketId): bool
    {
        if ($ticketId <= 0) {
            throw new \RuntimeException('Invalid ticket id.');
        }

        if ($this->findTicket($ticketId) === null) {
            return false;
        }

        return $this->tickets->deleteTicketById($ticketId);
    }

    /** @param array<string, mixed> $row */
    private function normalizeRow(array $row): array
    {
        $firstName = trim((string)($row['first_name'] ?? ''));
        $lastName = trim((string)($row['last_name'] ?? ''));

        return [
            'ticket_id' => (int)($row['ticket_id'] ?? 0),
            'order_item_id' => (int)($row['order_item_id'] ?? 0),
            'order_id' => (int)($row['order_id'] ?? 0),
            'user_id' => (int)($row['user_id'] ?? 0),
            'event_id' => (int)($row['event_id'] ?? 0),
            'qr' => (string)($row['qr'] ?? ''),
            'is_scanned' => (int)($row['is_scanned'] ?? 0),
            'order_status' => (string)($row['order_status'] ?? ''),
            'event_title' => (string)($row['event_title'] ?? 'Unknown event'),
            'event_type' => (string)($row['event_type'] ?? ''),
            'customer_name' => trim($firstName . ' ' . $lastName) !== '' ? trim($firstName . ' ' . $lastName) : 'Unknown user',
            'customer_email' => (string)($row['email'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? ''),
        ];
    }

    /** @param array<string, mixed> $row */
    private function matchesFilters(array $row, string $search, string $scanFilter): bool
    {
        $scanFilter = strtolower(trim($scanFilter));
        if ($scanFilter !== '') {
            $wanted = $scanFilter === 'scanned' ? 1 : ($scanFilter === 'not_scanned' ? 0 : null);
            if ($wanted !== null && (int)($row['is_scanned'] ?? 0) !== $wanted) {
                return false;
            }
        }

        $search = strtolower(trim($search));
        if ($search === '') {
            return true;
        }

        return str_contains((string)($row['ticket_id'] ?? ''), $search)
            || str_contains((string)($row['order_id'] ?? ''), $search)
            || str_contains((string)($row['user_id'] ?? ''), $search)
            || str_contains((string)($row['event_id'] ?? ''), $search)
            || str_contains(strtolower((string)($row['event_title'] ?? '')), $search)
            || str_contains(strtolower((string)($row['customer_name'] ?? '')), $search)
            || str_contains(strtolower((string)($row['customer_email'] ?? '')), $search)
            || str_contains(strtolower((string)($row['qr'] ?? '')), $search);
    }

    private function normalizeSortColumn(string $sortColumn): string
    {
        $allowed = [
            'ticket_id',
            'order_id',
            'user_id',
            'event_id',
            'event_title',
            'customer_name',
            'is_scanned',
            'created_at',
        ];

        return in_array($sortColumn, $allowed, true) ? $sortColumn : 'ticket_id';
    }
}