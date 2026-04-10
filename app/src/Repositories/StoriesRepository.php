<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IStoriesRepository;
use PDO;

class StoriesRepository extends Repository implements IStoriesRepository
{
    public function getAllStoriesEvents(): array
    {
        $pdo = $this->getConnection();

        $sql = "
        SELECT 
            e.event_id,
            e.title,
            s.page_id,
            s.language,
            s.age_group,
            s.story_type,
            s.location,
            s.description,
            s.start_date,
            s.end_date,
            s.price,
            s.img_background
        FROM Event e
        INNER JOIN StoriesEvent s ON e.event_id = s.event_id
        WHERE e.event_type = 'stories'
        ORDER BY s.start_date ASC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function getStoriesEventById(int $eventId): ?object
    {
        $pdo = $this->getConnection();

        $sql = "
        SELECT 
            e.event_id,
            e.title,
            e.event_type,
            s.page_id,
            s.language,
            s.age_group,
            s.story_type,
            s.location,
            s.description,
            s.start_date,
            s.end_date,
            s.price,
            s.img_background
        FROM Event e
        INNER JOIN StoriesEvent s ON e.event_id = s.event_id
        WHERE e.event_type = 'stories'
          AND e.event_id = :event_id
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':event_id' => $eventId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        return $row ?: null;
    }

    public function updateStoriesEventCms(int $eventId, array $data): bool
    {
        $pdo = $this->getConnection();

        $fields = [
            'language = :language',
            'age_group = :age_group',
            'story_type = :story_type',
            'location = :location',
            'description = :description',
            'start_date = :start_date',
            'end_date = :end_date',
            'price = :price',
        ];

        $params = [
            ':event_id' => $eventId,
            ':language' => $data['language'] ?? '',
            ':age_group' => $data['age_group'] ?? '',
            ':story_type' => $data['story_type'] ?? '',
            ':location' => $data['location'] ?? '',
            ':description' => $data['description'] ?? null,
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null,
            ':price' => $data['price'] ?? 0,
        ];

        if (!empty($data['img_background'])) {
            $fields[] = 'img_background = :img_background';
            $params[':img_background'] = $data['img_background'];
        }

        $sql = 'UPDATE StoriesEvent SET ' . implode(', ', $fields) . ' WHERE event_id = :event_id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return true;
    }

    public function deleteStoriesEventById(int $eventId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare("
                SELECT 1
                FROM Event
                WHERE event_id = :id
                  AND event_type = 'stories'
                LIMIT 1
            ");
            $check->execute([':id' => $eventId]);

            if (!$check->fetchColumn()) {
                $pdo->rollBack();
                return false;
            }

            $stmtStories = $pdo->prepare('DELETE FROM StoriesEvent WHERE event_id = :id');
            $stmtStories->execute([':id' => $eventId]);

            $stmtEvent = $pdo->prepare("
                DELETE FROM Event
                WHERE event_id = :id
                  AND event_type = 'stories'
            ");
            $stmtEvent->execute([':id' => $eventId]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getStoriesEventByPageId(int $pageId): ?array
    {
        $pdo = $this->getConnection();

        $sql = "
            SELECT
                e.event_id,
                e.title,
                e.event_type,
                e.availability,
                s.page_id,
                s.language,
                s.age_group,
                s.story_type,
                s.location,
                s.description,
                s.start_date,
                s.end_date,
                s.price,
                s.img_background
            FROM Event e
            INNER JOIN StoriesEvent s ON e.event_id = s.event_id
            WHERE e.event_type = 'stories'
              AND s.page_id = :page_id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':page_id' => $pageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

}