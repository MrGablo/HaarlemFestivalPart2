<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ReseedHistoryEventTourCapacity extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "DELETE h, e
             FROM `HistoryEvent` h
             INNER JOIN `Event` e ON e.`event_id` = h.`event_id`
             WHERE e.`event_type` = 'history'
               AND e.`title` = 'A Stroll Through History'
               AND h.`location` = 'Historic city centre'"
        );

        /** @var PDO $pdo */
        $pdo = $this->getAdapter()->getConnection();

        $insertEvent = $pdo->prepare(
            "INSERT INTO `Event` (`title`, `event_type`, `availability`) VALUES (:title, 'history', :availability)"
        );
        $insertHistory = $pdo->prepare(
            "INSERT INTO `HistoryEvent` (`event_id`, `language`, `start_date`, `location`, `price`) VALUES (:event_id, :language, :start_date, :location, :price)"
        );

        foreach ($this->scheduleRows() as $row) {
            foreach (['NL', 'EN', 'CH'] as $language) {
                $tourCountKey = strtolower($language);
                $tourCount = (int)($row[$tourCountKey] ?? 0);

                for ($tourNumber = 0; $tourNumber < $tourCount; $tourNumber++) {
                    $insertEvent->execute([
                        ':title' => 'A Stroll Through History',
                        ':availability' => 12,
                    ]);

                    $eventId = (int)$pdo->lastInsertId();
                    $insertHistory->execute([
                        ':event_id' => $eventId,
                        ':language' => $language,
                        ':start_date' => $row['start_date'],
                        ':location' => 'Historic city centre',
                        ':price' => 17.50,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $this->execute(
            "DELETE h, e
             FROM `HistoryEvent` h
             INNER JOIN `Event` e ON e.`event_id` = h.`event_id`
             WHERE e.`event_type` = 'history'
               AND e.`title` = 'A Stroll Through History'
               AND h.`location` = 'Historic city centre'"
        );
    }

    /** @return array<int, array{start_date:string,nl:int,en:int,ch:int}> */
    private function scheduleRows(): array
    {
        return [
            ['start_date' => '2025-07-24 10:00:00', 'nl' => 1, 'en' => 1, 'ch' => 0],
            ['start_date' => '2025-07-24 13:00:00', 'nl' => 1, 'en' => 1, 'ch' => 0],
            ['start_date' => '2025-07-24 16:00:00', 'nl' => 1, 'en' => 1, 'ch' => 0],
            ['start_date' => '2025-07-25 10:00:00', 'nl' => 1, 'en' => 1, 'ch' => 0],
            ['start_date' => '2025-07-25 13:00:00', 'nl' => 1, 'en' => 1, 'ch' => 1],
            ['start_date' => '2025-07-25 16:00:00', 'nl' => 2, 'en' => 2, 'ch' => 0],
            ['start_date' => '2025-07-26 13:00:00', 'nl' => 2, 'en' => 2, 'ch' => 1],
            ['start_date' => '2025-07-26 16:00:00', 'nl' => 1, 'en' => 1, 'ch' => 1],
            ['start_date' => '2025-07-27 10:00:00', 'nl' => 2, 'en' => 2, 'ch' => 1],
            ['start_date' => '2025-07-27 13:00:00', 'nl' => 3, 'en' => 3, 'ch' => 2],
            ['start_date' => '2025-07-27 16:00:00', 'nl' => 1, 'en' => 1, 'ch' => 0],
        ];
    }
}