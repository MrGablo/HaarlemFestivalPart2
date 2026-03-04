<?php

declare(strict_types=1);

namespace App\ViewModel;

final class JazzHomePageViewModel
{
    public function __construct(
        public readonly array $content,  // decoded JSON from Page.Content
        public readonly array $events    // normalized jazz events
    ) {}
}