<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IEventRepository;

class EventRepository extends Repository implements IEventRepository
{
    public function getAllEvents(?string $eventType = null): array
    {
        $pdo = $this->getConnection();

        $sql = '
            SELECT
                event_id,
                title,
                event_type,
                availability
            FROM Event
        ';

        $params = [];
        if ($eventType !== null && $eventType !== '') {
            $sql .= ' WHERE event_type = :event_type';
            $params[':event_type'] = $eventType;
        }

        $sql .= ' ORDER BY title ASC, event_id ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    public function findEventById(int $eventId): ?array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('
            SELECT
                event_id,
                title,
                event_type,
                availability
            FROM Event
            WHERE event_id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $eventId]);

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function updateEvent(int $eventId, string $title, int $availability): bool
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('
            UPDATE Event
            SET title = :title,
                availability = :availability
            WHERE event_id = :id
        ');
        $stmt->execute([
            ':title' => $title,
            ':availability' => $availability,
            ':id' => $eventId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function decrementAvailabilityByOne(int $eventId): bool
    {
        return $this->decrementAvailabilityByOneUsingConnection($this->getConnection(), $eventId);
    }

    public function decrementAvailabilityByOneUsingConnection(\PDO $pdo, int $eventId): bool
    {
        if ($eventId <= 0) {
            return false;
        }

        $stmt = $pdo->prepare('
            UPDATE Event
            SET availability = availability - 1
            WHERE event_id = :id
              AND availability > 0
        ');
        $stmt->execute([':id' => $eventId]);

        return $stmt->rowCount() > 0;
    }
}
