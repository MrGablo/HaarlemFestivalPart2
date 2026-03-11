<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\JazzEvent;
use App\Repositories\ArtistRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\Interfaces\IJazzEventRepository;
use App\ViewModels\JazzArtistPageViewModel;

class JazzArtistService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private IJazzEventRepository $eventRepo,
        private ArtistRepository $artistRepo
    ) {}

    public function getArtistPageViewModel(int $artistId, ?string $tab): JazzArtistPageViewModel
    {
        $artist = $this->artistRepo->findArtistById($artistId);
        if (!$artist instanceof Artist) {
            throw new \RuntimeException('Artist not found.');
        }

        $content = $artist->page_id !== null ? $this->pageRepo->getPageContentById($artist->page_id) : [];

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
        } else {
            $models = $this->eventRepo->getJazzEventsByArtistId($artistId);
            $events = array_map([$this, 'mapEventForArtistPage'], $models);
        }

        return new JazzArtistPageViewModel($artistId, $content, $events, $activeTab);
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
            'price' => (float)$ev->price,
        ];
    }
}