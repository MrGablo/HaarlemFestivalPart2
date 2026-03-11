<?php

namespace App\ViewModels;

class JazzArtistPageViewModel
{
    public int $artistId;
    public array $content;   // decoded JSON
    public array $events;    // mapped arrays for the view
    public string $activeTab;

    public function __construct(int $artistId, array $content, array $events, string $activeTab)
    {
        $this->artistId = $artistId;
        $this->content = $content;
        $this->events = $events;
        $this->activeTab = $activeTab;
    }
}