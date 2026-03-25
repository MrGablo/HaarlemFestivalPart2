<?php

namespace App\ViewModels;

class HomePageViewModel
{
    public function __construct(
        public array $content,
        public array $categories = []
    ) {}
}