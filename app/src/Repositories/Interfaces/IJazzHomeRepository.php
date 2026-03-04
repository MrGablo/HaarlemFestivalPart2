<?php

namespace App\Repositories\Interfaces;

interface IJazzHomeRepository
{
    public function getJazzHomePageContent(): array; // returns decoded JSON array
}