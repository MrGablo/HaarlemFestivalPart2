<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Content;

final class JazzHomePageContentViewModel
{
    public function __construct(
        public array $hero,
        public array $intro,
        public array $dayTicketPass,
        public array $schedule
    ) {}
}