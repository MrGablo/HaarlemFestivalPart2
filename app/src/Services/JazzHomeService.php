<?php

namespace App\Services;

use App\Repositories\Interfaces\IJazzHomeRepository;
use App\Repositories\Interfaces\IJazzEventRepository;
use App\ViewModel\JazzHomePageViewModel;

class JazzHomeService
{
    public function __construct(
        private IJazzHomeRepository $pageRepo,
        private IJazzEventRepository $eventRepo
    ) {}

    public function getJazzHomePageViewModel(): JazzHomePageViewModel
    {
        $content = $this->pageRepo->getJazzHomePageContent();
        $eventsRaw = $this->eventRepo->getAllJazzEvents();

        $events = array_map([$this, 'mapEvent'], $eventsRaw);

        return new JazzHomePageViewModel($content, $events);
    }

    private function mapEvent(array $row): array
    {
        $start = (string)($row['start_date'] ?? '');
        $ts = strtotime($start) ?: 0;

        $location = (string)($row['location'] ?? '');
        $hall = $this->mapHall($location);

        return [
            'event_id' => (int)$row['event_id'],
            'title' => (string)$row['title'],
            'artist_name' => (string)($row['artist_name'] ?? ''),
            'img_background' => (string)($row['img_background'] ?? ''),
            'price' => (float)($row['price'] ?? 0),
            'location' => $location,
            'hall' => $hall, // Main Hall / Second Hall / Third Hall / Free
            'page_id' => isset($row['page_id']) ? (int)$row['page_id'] : null,

            'day_key' => $ts ? date('l', $ts) : 'Unknown',
            'display_date' => $ts ? date('D j M', $ts) : '',
            'display_time' => $ts ? date('H:i', $ts) : '',
        ];
    }

    private function mapHall(string $location): string
    {
        // Rule you gave: "Free is only for the location of grotemarkt"
        if (mb_strtolower(trim($location)) === mb_strtolower('Grote Markt')) {
            return 'Free';
        }

        // Otherwise you probably store "Main Hall", "Second Hall", "Third Hall"
        return $location;
    }
}