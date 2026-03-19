<?php

namespace App\Services;

use App\Models\YummyEvent;
use App\Repositories\PageRepository;
use App\Repositories\YummyEventRepository;

class YummyDetailService
{
    private YummyEventRepository $eventRepository;
    private PageRepository $pageRepository;

    public function __construct(YummyEventRepository $eventRepository, PageRepository $pageRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->pageRepository = $pageRepository;
    }

    public function getEventDetails(int $id): ?YummyEvent
    {
        return $this->eventRepository->getEventDetails($id);
    }

    public function getEventSessions(int $id): array
    {
        return $this->eventRepository->getSessionsForYummyEvent($id);
    }

    public function getPageContent(?int $pageId): array
    {
        if (!$pageId) {
            return [];
        }

        return $this->pageRepository->getPageContentById($pageId);
    }
}
