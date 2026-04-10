<?php

declare(strict_types=1);

namespace App\ViewModels;

class HistoryHomePageViewModel
{
    public function __construct(
        public array $hero,
        public array $overview,
        public array $booking,
        public array $map,
        public array $locations,
        public array $scheduleRows,
        public array $bookingEvents
    ) {}
}