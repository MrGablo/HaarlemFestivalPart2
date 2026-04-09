<?php

namespace App\Repositories\Interfaces;

interface IStoriesRepository
{
    /**
     * Fetches all story events joined with their specific details.
     * * @return array
     */
    public function getAllStoriesEvents(): array;
    public function getStoriesEventById(int $eventId): ?object;
    public function updateStoriesEventCms(int $eventId, array $data): bool;
    public function deleteStoriesEventById(int $eventId): bool;
}