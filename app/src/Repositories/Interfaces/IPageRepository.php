<?php

namespace App\Repositories\Interfaces;

interface IPageRepository
{
    public function getPageContentByType(string $pageType): array;

    public function savePageContentByType(string $pageType, array $content, ?string $pageTitle = null): void;

    public function findPageByType(string $pageType): ?array;
    public function getPageContentById(int $pageId): array;
    public function findPageById(int $pageId): ?array;
}
