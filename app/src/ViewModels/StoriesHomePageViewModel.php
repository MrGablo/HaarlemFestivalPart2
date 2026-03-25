<?php

namespace App\ViewModels;

class StoriesHomePageViewModel
{
    public array $pageContent;
    public array $days;

    public function __construct(array $pageContent, array $days)
    {
        $this->pageContent = $pageContent;
        $this->days = $days;
    }
}