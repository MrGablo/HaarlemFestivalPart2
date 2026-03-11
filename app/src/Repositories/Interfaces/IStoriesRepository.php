<?php

namespace App\Repositories\Interfaces;

interface IStoriesRepository
{
    /**
     * Fetches all story events joined with their specific details.
     * * @return array
     */
    public function getAllStoriesEvents(): array;
}