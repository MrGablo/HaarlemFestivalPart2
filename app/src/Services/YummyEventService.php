<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\YummyEvent;
use App\Repositories\PageRepository;
use App\Repositories\YummyEventRepository;

class YummyEventService
{
    public function __construct(
        private YummyEventRepository $events = new YummyEventRepository(),
        private PageRepository $pages = new PageRepository(),
        private EventValidationService $validator = new EventValidationService()
    ) {}

    public function allEvents(): array
    {
        return $this->events->getAllCMSYummyEvents();
    }

    public function allPages(): array
    {
        $pages = $this->pages->getAllPages();
        // Filter to only show Yummy detail pages
        return array_filter($pages, function ($page) {
            return ($page['Page_Type'] ?? '') === 'Yummy_Detail_Page';
        });
    }

    public function findEvent(int $id): ?YummyEvent
    {
        return $this->events->findYummyEventById($id);
    }

    public function hydrateEventFromInput(YummyEvent $event, array $input): void
    {
        $event->title = $this->validator->requireText($input, 'title', 'Title');
        $event->availability = $this->validator->parseRequiredPositiveInt((string)($input['availability'] ?? '0'), 'Availability');
        $event->start_time = $this->validator->normalizeDateTime((string)($input['start_time'] ?? ''));
        $event->end_time = $this->validator->normalizeDateTime((string)($input['end_time'] ?? ''));
        $event->cuisine = $this->validator->requireText($input, 'cuisine', 'Cuisine');
        $event->star_rating = (int)($input['star_rating'] ?? 0);

        if ($event->star_rating < 0 || $event->star_rating > 5) {
            throw new \RuntimeException('Star rating must be between 0 and 5.');
        }

        $event->price = $this->validator->parsePrice((string)($input['price'] ?? ''));
        $event->page_id = $this->validator->parseOptionalPositiveInt((string)($input['page_id'] ?? ''), 'Page ID');
    }

    public function createEvent(YummyEvent $event): int
    {
        return $this->events->createYummyEvent($event);
    }

    public function updateEvent(YummyEvent $event): void
    {
        $this->events->updateYummyEvent($event);
    }

    public function deleteEvent(int $id): bool
    {
        return $this->events->deleteYummyEventById($id);
    }
}
