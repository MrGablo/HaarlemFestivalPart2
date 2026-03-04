<?php

namespace App\Repositories\Interfaces;

interface IJazzEventRepository
{
    public function getAllJazzEvents(): array; // returns array of rows (assoc arrays)
}