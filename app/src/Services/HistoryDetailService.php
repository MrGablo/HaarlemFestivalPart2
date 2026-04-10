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

        return $this->buildViewModelFromPage($pageId, $page);
    }

    public function getHistoryDetailPageViewModelBySlug(string $slug): HistoryDetailPageViewModel
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '') {
            throw new \RuntimeException('History detail page not found.');
        }

        foreach ($this->pageRepo->getPagesByType($this->builder->pageType()) as $page) {
            $pageId = (int)($page['Page_ID'] ?? 0);
            if ($pageId <= 0) {
                continue;
            }

            $content = $this->pageRepo->getPageContentById($pageId);
            $meta = is_array($content['meta'] ?? null) ? $content['meta'] : [];
            $candidate = $this->normalizeSlug((string)($meta['slug'] ?? ''));
            if ($candidate === '' && isset($page['Page_Title'])) {
                $candidate = $this->normalizeSlug((string)$page['Page_Title']);
            }

            if ($candidate === $slug) {
                return $this->buildViewModelFromPage($pageId, $page);
            }
        }

        throw new \RuntimeException('History detail page not found.');
    }

    private function buildViewModelFromPage(int $pageId, array $page): HistoryDetailPageViewModel
    {
        if ((string)($page['Page_Type'] ?? '') !== $this->builder->pageType()) {
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

    private function normalizeSlug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}