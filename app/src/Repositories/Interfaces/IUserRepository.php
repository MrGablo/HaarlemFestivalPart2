<?php

namespace App\Repositories\Interfaces;

use App\Models\PresentationModel;

interface IPresentationRepository
{
    public function create(PresentationModel $presentation): int;
    public function getById(int $id): ?PresentationModel;
    public function update(PresentationModel $presentation): void;
    public function delete(int $id): void;

    // targets (presentation_targets)
    public function setTargets(int $presentationId, array $roleIds): void;
    public function getTargetRoleIds(int $presentationId): array;

    // feed (filtered by role)
    public function getFeedForRole(int $roleId, int $offset, int $limit): array; // assoc rows including view status if you want later
    public function searchFeedForRole(int $roleId, string $query): array;
    public function getAllForAdmin(): array;
}