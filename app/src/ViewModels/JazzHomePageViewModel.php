<?php

namespace App\ViewModels;

class JazzHomePageViewModel
{
    public function __construct(
        public string $pageTitle,
        public array $hero,
        public array $intro,
        public array $dayTicketPass,
        public string $scheduleTitle,
        public string $scheduleVenueTitle,
        public array $hallTabs,
        public array $dayTabs,
        public bool $showAllEventsButton,
        public string $allEventsButtonLabel,
        public array $events
    ) {}
}