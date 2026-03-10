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
}
