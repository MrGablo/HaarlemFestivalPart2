<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder;

interface PageViewModelBuilderInterface
{
    public function pageType(): string;

    public function buildViewModel(array $content): object;

    /** @return array<int, array<string, mixed>> */
    public function editorSchema(): array;

    /** @param array<string, mixed> $input */
    public function normalizeInput(array $input): array;
}