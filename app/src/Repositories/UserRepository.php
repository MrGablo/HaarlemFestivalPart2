<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\PresentationModel;
use App\Repositories\Interfaces\IPresentationRepository;
use PDO;

class PresentationRepository extends Repository implements IPresentationRepository
{
    public function create(PresentationModel $presentation): int
    {
        $sql = "INSERT INTO presentations (title, description, youtube_video_id, created_by_user_id, published_at)
                VALUES (:title, :description, :youtube_video_id, :created_by_user_id, :published_at)";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'title' => $presentation->title,
            'description' => $presentation->description,
            'youtube_video_id' => $presentation->youtube_video_id,
            'created_by_user_id' => $presentation->created_by_user_id,
            'published_at' => $presentation->published_at ?? date('Y-m-d H:i:s')
        ]);

        return (int)$this->getConnection()->lastInsertId();
    }

    public function getById(int $id): ?PresentationModel
    {
        $sql = "SELECT id, title, description, youtube_video_id, created_by_user_id, published_at, created_at
                FROM presentations
                WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, PresentationModel::class);
        $p = $stmt->fetch();

        return $p ?: null;
    }

    public function update(PresentationModel $presentation): void
    {
        $sql = "UPDATE presentations
                SET title = :title,
                    description = :description,
                    youtube_video_id = :youtube_video_id,
                    published_at = :published_at
                WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'title' => $presentation->title,
            'description' => $presentation->description,
            'youtube_video_id' => $presentation->youtube_video_id,
            'published_at' => $presentation->published_at,
            'id' => $presentation->id
        ]);
    }

    public function delete(int $id): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $pdo->prepare("DELETE FROM presentation_targets WHERE presentation_id = :id")->execute(['id' => $id]);
            $pdo->prepare("DELETE FROM presentation_comments WHERE presentation_id = :id")->execute(['id' => $id]);
            $pdo->prepare("DELETE FROM presentation_views WHERE presentation_id = :id")->execute(['id' => $id]);

            $pdo->prepare("DELETE FROM presentations WHERE id = :id")->execute(['id' => $id]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }


    public function setTargets(int $presentationId, array $roleIds): void
    {
        // Reset targets then insert new ones
        $deleteSql = "DELETE FROM presentation_targets WHERE presentation_id = :pid";
        $del = $this->getConnection()->prepare($deleteSql);
        $del->execute(['pid' => $presentationId]);

        if (count($roleIds) === 0) {
            return;
        }

        $insertSql = "INSERT INTO presentation_targets (presentation_id, role_id)
                      VALUES (:pid, :rid)";
        $ins = $this->getConnection()->prepare($insertSql);

        foreach ($roleIds as $rid) {
            $ins->execute([
                'pid' => $presentationId,
                'rid' => (int)$rid
            ]);
        }
    }

    public function getTargetRoleIds(int $presentationId): array
    {
        $sql = "SELECT role_id
                FROM presentation_targets
                WHERE presentation_id = :pid";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['pid' => $presentationId]);

        return array_map(
            fn($row) => (int)$row['role_id'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getFeedForRole(int $roleId, int $offset, int $limit): array
    {
        $sql = "SELECT DISTINCT
                p.id,
                p.title,
                p.description,
                p.youtube_video_id,
                p.created_by_user_id,
                p.published_at
            FROM presentations p
            INNER JOIN presentation_targets pt ON pt.presentation_id = p.id
            WHERE pt.role_id = :role_id
            ORDER BY p.published_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->getConnection()->prepare($sql);

        // IMPORTANT: bindValue for limit/offset as integers
        $stmt->bindValue(':role_id', $roleId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function searchFeedForRole(int $roleId, string $query): array
    {
        $sql = "SELECT DISTINCT
                    p.id,
                    p.title,
                    p.description,
                    p.youtube_video_id,
                    p.created_by_user_id,
                    p.published_at
                FROM presentations p
                INNER JOIN presentation_targets pt ON pt.presentation_id = p.id
                WHERE pt.role_id = :role_id
                  AND (p.title LIKE :q OR p.description LIKE :q)
                ORDER BY p.published_at DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'role_id' => $roleId,
            'q' => '%' . $query . '%'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllForAdmin(): array
    {
        $sql = "SELECT
                p.id,
                p.title,
                p.description,
                p.youtube_video_id,
                p.published_at,
                u.name AS creator_name,
                u.email AS creator_email
            FROM presentations p
            LEFT JOIN users u ON u.id = p.created_by_user_id
            ORDER BY p.published_at DESC";

        $stmt = $this->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}