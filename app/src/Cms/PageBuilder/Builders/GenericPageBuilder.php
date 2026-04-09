<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\PageViewModelBuilderInterface;

final class GenericPageBuilder implements PageViewModelBuilderInterface
{
    public function pageType(): string
    {
        return '*';
    }

    public function buildViewModel(array $content): object
    {
        return (object)['content' => $content];
    }

    public function editorSchema(): array
    {
        return [];
    }

    public function normalizeInput(array $input): array
    {
        return $input;
    }
}