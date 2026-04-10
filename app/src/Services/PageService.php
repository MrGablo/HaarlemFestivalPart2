<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\PageRepository;

class PageService
{
    public function __construct(
        private IPageRepository $pages = new PageRepository()
    ) {}

    public function allPages(): array
    {
        return $this->pages->getAllPages();
    }

    public function getPagesByType(string $pageType): array
    {
        return $this->pages->getPagesByType($pageType);
    }

    public function createPage(string $pageTitle, string $pageType, array $content): int
    {
        return $this->pages->createPage($pageTitle, $pageType, $content);
    }

    public function findPageById(int $pageId): ?array
    {
        return $this->pages->findPageById($pageId);
    }

    public function getPageContentById(int $pageId): array
    {
        return $this->pages->getPageContentById($pageId);
    }

    public function savePageContentById(int $pageId, array $content): void
    {
        $this->pages->savePageContentById($pageId, $content);
    }

    public function deletePageById(int $pageId): bool
    {
        return $this->pages->deletePageById($pageId);
    }
}
