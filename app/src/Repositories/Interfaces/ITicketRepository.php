<?php

namespace App\Repositories\Interfaces;

use PDO;

interface ITicketRepository
{
    public function createTicket(int $orderItemId, int $userId, int $eventId, string $qr): int;
    public function createTicketUsingConnection(PDO $connection, int $orderItemId, int $userId, int $eventId, string $qr): int;
    public function executeInTransaction(callable $callback): void;
    public function getPaidTicketsForUser(int $userId): array;
    public function getTicketInfoByQr(string $qr): ?array;
    public function markAsScanned(int $ticketId): void;
    public function getAllTicketsWithSummary(): array;
    public function findTicketById(int $ticketId): ?array;
    public function updateTicketCms(int $ticketId, string $qr, int $isScanned): bool;
    public function deleteTicketById(int $ticketId): bool;
}
