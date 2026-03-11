<?php

namespace App\Services;

use App\Models\JazzEvent;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\Interfaces\IJazzEventRepository;
use App\ViewModels\JazzHomePageViewModel;

class JazzHomeService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private IJazzEventRepository $eventRepo
    ) {}

    public function getJazzHomePageViewModel(): JazzHomePageViewModel
    {
        // uses generic page repo now
        $content = $this->pageRepo->getPageContentByType('Jazz_Homepage');

        /** @var JazzEvent[] $eventsRaw */
        $eventsRaw = $this->eventRepo->getAllJazzEvents();

        $events = array_map([$this, 'mapEvent'], $eventsRaw);

        return new JazzHomePageViewModel($content, $events);
    }

    private function mapEvent(JazzEvent $ev): array
    {
        $ts = strtotime($ev->start_date) ?: 0;

        $location = (string)$ev->location;

        // Free is only Grote Markt (your rule)
        $hall = (mb_strtolower(trim($location)) === mb_strtolower('Grote Markt'))
            ? 'Free'
            : $location;

        return [
            'event_id' => $ev->event_id,
            'title' => $ev->title,
            'artist_id' => $ev->artist_id,
            'artist_name' => $ev->artist_name,
            'img_background' => (string)($ev->img_background ?? ''),
            'price' => (float)$ev->price,
            'location' => $location,
            'hall' => $hall,

            'day_key' => $ts ? date('l', $ts) : 'Unknown',
            'display_date' => $ts ? date('D j M', $ts) : '',
            'display_time' => $ts ? date('H:i', $ts) : '',
        ];
    }
}