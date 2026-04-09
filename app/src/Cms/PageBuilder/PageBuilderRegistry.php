<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder;

use App\Cms\PageBuilder\Builders\DanceHomePageBuilder;
use App\Cms\PageBuilder\Builders\GenericPageBuilder;
use App\Cms\PageBuilder\Builders\HomePageBuilder;
use App\Cms\PageBuilder\Builders\JazzArtistPageBuilder;
use App\Cms\PageBuilder\Builders\JazzHomePageBuilder;
use App\Cms\PageBuilder\Builders\StoriesHomePageBuilder;
use App\Cms\PageBuilder\Builders\YummyDetailPageBuilder;

final class PageBuilderRegistry
{
    /** @var array<string, PageViewModelBuilderInterface> */
    private array $buildersByPageType = [];

    private GenericPageBuilder $genericBuilder;

    /** @param array<int, PageViewModelBuilderInterface>|null $builders */
    public function __construct(?array $builders = null)
    {
        $builders ??= [
            new HomePageBuilder(),
            new JazzHomePageBuilder(),
            new DanceHomePageBuilder(),
            new JazzArtistPageBuilder(),
            new StoriesHomePageBuilder(),
            new YummyDetailPageBuilder(),
        ];

        foreach ($builders as $builder) {
            $this->buildersByPageType[$builder->pageType()] = $builder;
        }

        $this->genericBuilder = new GenericPageBuilder();
    }

    public function resolveForPageType(string $pageType): PageViewModelBuilderInterface
    {
        return $this->buildersByPageType[$pageType] ?? $this->genericBuilder;
    }

    public function generic(): GenericPageBuilder
    {
        return $this->genericBuilder;
    }

    /** @return array<string, PageViewModelBuilderInterface> */
    public function all(): array
    {
        return $this->buildersByPageType;
    }
}