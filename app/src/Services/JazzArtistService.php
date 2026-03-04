<?php

namespace App\Services;

use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\Interfaces\IJazzEventRepository;
use App\ViewModels\JazzArtistPageViewModel;
use App\Models\JazzEvent;

class JazzArtistService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private IJazzEventRepository $eventRepo
    ) {}

    public function getArtistPageViewModel(int $pageId, ?string $tab): JazzArtistPageViewModel
    {
        $content = $this->pageRepo->getPageContentById($pageId);

        $allowed = ['events', 'career', 'album'];

        $defaultTab = (string)($content['tabs']['default'] ?? 'events');
        if (!in_array($defaultTab, $allowed, true)) {
            $defaultTab = 'events';
        }

        $activeTab = $tab ? (string)$tab : $defaultTab;
        if (!in_array($activeTab, $allowed, true)) {
            $activeTab = $defaultTab;
        }

        $events = [];
        $eventIds = $content['events']['event_ids'] ?? [];
        if (is_array($eventIds) && count($eventIds) > 0) {
            /** @var JazzEvent[] $models */
            $models = $this->eventRepo->getJazzEventsByIds($eventIds);
            $events = array_map([$this, 'mapEventForArtistPage'], $models);
        }

        return new JazzArtistPageViewModel($content, $events, $activeTab);
    }

    private function mapEventForArtistPage(JazzEvent $ev): array
    {
        $ts = strtotime($ev->start_date) ?: 0;

        return [
            'event_id' => $ev->event_id,
            'start_label' => $ts ? date('l j F Y H:i', $ts) : '',
            'title' => $ev->title,
            'location' => $ev->location,
            'img_background' => (string)($ev->img_background ?? ''),
            'price' => (float)$ev->price
        ];
    }
}
