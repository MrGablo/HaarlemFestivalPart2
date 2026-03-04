<?php

namespace App\ViewModels;

class JazzArtistPageViewModel
{
    public array $content;   // decoded JSON
    public array $events;    // mapped arrays for the view
    public string $activeTab;

    public function __construct(array $content, array $events, string $activeTab)
    {
        $this->content = $content;
        $this->events = $events;
        $this->activeTab = $activeTab;
    }
}