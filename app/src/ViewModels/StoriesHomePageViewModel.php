<?php

namespace App\ViewModels;

class StoriesHomePageViewModel
{
    public array $pageContent;
    public array $events;

    public function __construct(array $pageContent, array $events)
    {
        $this->pageContent = $pageContent;
        $this->events = $events;
    }
}