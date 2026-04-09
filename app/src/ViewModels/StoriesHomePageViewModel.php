<?php

namespace App\ViewModels;

class StoriesHomePageViewModel
{
    public array $hero;
    public array $introduction;
    public array $days;

    public function __construct(array $hero, array $introduction, array $days)
    {
        $this->hero = $hero;
        $this->introduction = $introduction;
        $this->days = $days;
    }
}