<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IPageRepository;

class PageRepository extends Repository implements IPageRepository
{
    public function getAllPages(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->query("
            SELECT Page_ID, Page_Title, Page_Type, Updated_At, Created_At
            FROM Page
            ORDER BY Page_Type ASC, Page_Title ASC
        ");

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function getPageContentByType(string $pageType): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("SELECT Content FROM Page WHERE Page_Type = :type LIMIT 1");
        $stmt->execute([':type' => $pageType]);
        $row = $stmt->fetch();

        if (!$row || empty($row['Content'])) {
            return [];
        }

        $decoded = json_decode((string)$row['Content'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function savePageContentByType(string $pageType, array $content, ?string $pageTitle = null): void
    {
        $pdo = $this->getConnection();

        $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode page content to JSON.');
        }

        $stmt = $pdo->prepare("SELECT Page_ID FROM Page WHERE Page_Type = :type LIMIT 1");
        $stmt->execute([':type' => $pageType]);
        $existing = $stmt->fetch();

        if ($existing && isset($existing['Page_ID'])) {
            $update = $pdo->prepare("
                UPDATE Page
                SET Content = :content,
                    Updated_At = NOW()
                WHERE Page_ID = :id
            ");
            $update->execute([
                ':content' => $json,
                ':id' => (int)$existing['Page_ID'],
            ]);
            return;
        }

        $insert = $pdo->prepare("
            INSERT INTO Page (Page_Title, Page_Type, Content, Created_At, Updated_At)
            VALUES (:title, :type, :content, NOW(), NULL)
        ");
        $insert->execute([
            ':title' => $pageTitle ?? $pageType,
            ':type' => $pageType,
            ':content' => $json,
        ]);
    }

    public function findPageByType(string $pageType): ?array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("
            SELECT Page_ID, Page_Title, Page_Type, Content, Updated_At, Created_At
            FROM Page
            WHERE Page_Type = :type
            LIMIT 1
        ");
        $stmt->execute([':type' => $pageType]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getPageContentById(int $pageId): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("SELECT Content FROM Page WHERE Page_ID = :id LIMIT 1");
        $stmt->execute([':id' => $pageId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['Content'])) return [];

        $decoded = json_decode((string)$row['Content'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function findPageById(int $pageId): ?array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("
        SELECT Page_ID, Page_Title, Page_Type, Content, Updated_At, Created_At
        FROM Page
        WHERE Page_ID = :id
        LIMIT 1
    ");
        $stmt->execute([':id' => $pageId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function savePageContentById(int $pageId, array $content): void
    {
        $pdo = $this->getConnection();

        $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode page content to JSON.');
        }

        $update = $pdo->prepare("
        UPDATE Page
        SET Content = :content,
            Updated_At = NOW()
        WHERE Page_ID = :id
        LIMIT 1
    ");

        $update->execute([
            ':content' => $json,
            ':id' => $pageId,
        ]);
    }

    public function createPage(string $pageTitle, string $pageType, array $content): int
    {
        $pdo = $this->getConnection();

        $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode page content to JSON.');
        }

        $insert = $pdo->prepare(
            "
            INSERT INTO Page (Page_Title, Page_Type, Content, Created_At, Updated_At)
            VALUES (:title, :type, :content, NOW(), NULL)
            "
        );

        $insert->execute([
            ':title' => trim($pageTitle),
            ':type' => trim($pageType),
            ':content' => $json,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public function deletePageById(int $pageId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $clearArtist = $pdo->prepare(
                'UPDATE Artist
                 SET page_id = NULL,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE page_id = :id'
            );
            $clearArtist->execute([':id' => $pageId]);

            $clearJazzEvents = $pdo->prepare(
                'UPDATE JazzEvent
                 SET page_id = NULL
                 WHERE page_id = :id'
            );
            $clearJazzEvents->execute([':id' => $pageId]);

            $delete = $pdo->prepare('DELETE FROM Page WHERE Page_ID = :id LIMIT 1');
            $delete->execute([':id' => $pageId]);

            $pdo->commit();
            return $delete->rowCount() > 0;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
