<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\PageBuilder\Builders\HistoryDetailPageBuilder;
use App\Cms\PageBuilder\Content\HistoryDetailPageContentViewModel;
use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\HistoryDetailPageViewModel;

class HistoryDetailService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private HistoryDetailPageBuilder $builder = new HistoryDetailPageBuilder()
    ) {}

    public function getHistoryDetailPageViewModel(int $pageId): HistoryDetailPageViewModel
    {
        $page = $this->pageRepo->findPageById($pageId);
        if ($page === null || (string)($page['Page_Type'] ?? '') !== $this->builder->pageType()) {
            throw new \RuntimeException('History detail page not found.');
        }

        /** @var HistoryDetailPageContentViewModel $content */
        $content = $this->builder->buildViewModel($this->pageRepo->getPageContentById($pageId));
        $meta = $content->meta;
        $navigation = is_array($meta['navigation'] ?? null) ? $meta['navigation'] : [];

        return new HistoryDetailPageViewModel(
            $pageId,
            (string)($content->hero['title'] ?? ($page['Page_Title'] ?? 'History detail')),
            [
                'back_href' => (string)($navigation['back_href'] ?? '/history'),
                'back_label' => (string)($navigation['back_label'] ?? 'Back'),
            ],
            $content->hero,
            $content->storyBlocks,
            $content->mapCard,
            $meta
        );
    }
}