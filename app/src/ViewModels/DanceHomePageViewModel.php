<?php

declare(strict_types=1);

namespace App\ViewModels;

class DanceHomePageViewModel
{
    public array $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }
}

