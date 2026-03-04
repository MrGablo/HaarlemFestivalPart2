<?php

namespace App\ViewModels;

class JazzHomePageViewModel
{
    public array $content;
    public array $events;

    public function __construct(array $content, array $events)
    {
        $this->content = $content;
        $this->events = $events;
    }
}