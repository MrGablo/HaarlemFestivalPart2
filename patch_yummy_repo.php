<?php
$content = file_get_contents('app/src/Repositories/YummyEventRepository.php');

$newMethods = <<< 'METHODS'

    public function getAllCMSYummyEvents(): array
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("
            SELECT e.event_id, e.title, e.event_type, e.availability,
                   y.id as yummy_id, y.start_time, y.end_time, y.page_id, y.thumbnail_path, y.cuisine, y.star_rating, y.price
            FROM Event e
            INNER JOIN YummyEvent y ON e.event_id = y.event_id
            WHERE e.event_type = 'yummy'
            ORDER BY y.start_time ASC
        ");

        $rows = $stmt->fetchAll() ?: [];
        return array_map(fn($r) => $this->modelBuilder->buildEventModel($r), $rows);
    }

    public function findYummyEventById(int $id): ?YummyEvent
    {
        return $this->getEventDetails($id);
    }

    public function createYummyEvent(YummyEvent $event): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
                                                                                     ent                                                                           )
                                             te                        le' =>                                             te                  bilit                                             te                        le' =>                                             tw n                     'Failed to insert parent Event.');
            }

            $stmt            $stmt            $st        I            $stmt            $stmt  ce           st            $stmt            $stmt   tar_            $stmt            $stmt            $st        I            $stmt    me, :end_time            $stmt            $stmt            $st        I 
                                                  '   ent_id'       => $eventId,
                ':price'          => $event-                        ':cuisine'                     ':price'                       time'     => $event->start_time,
                ':end_time'       => $event->end_time,
                ':thumbnail_path' => $event->thumbnail_path,
                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':                ':    v         
                                                                                                                                                                                                                                                                                                                (!$                                                                            ot found or is not a yummy event.');
            }

            $stmt1 = $pdo->prepare("
                UPDATE Event SET title = :title, availability = :availability WHERE event_id = :id
            ");
            $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stm              $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1-            $stmt1-            $stmt1-            $stmt1-                $stmt1->end_time,
                                      event->thumbnail_path,
                ':star_rating'    => $event->star_rating,
                ':page               $event->page_id,
                ':id'             => $event->event_id,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deleteYummyEventById(int $eventId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $del1 = $pdo->prepare("DELETE FROM YummyEvent WHERE event_id = :id");
            $del1->execute([':id' => $eventId]);

            $del2 = $pdo->prepare("DELETE FROM Event WHERE event_id = :id AND event_type = 'yummy'");
            $del2->execute([':id' => $eventId]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }

METHODS;

$newContent = preg_replace('/}\s*$/', $newMethods . "\n}\n", $content);
file_put_contents('app/src/Repositories/YummyEventRepository.php', $newContent);
echo "Patched YummyEventRepository.php\n";
