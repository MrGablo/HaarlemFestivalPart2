<?php

namespace App\Services;

use App\Models\JazzEvent;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\Interfaces\IJazzEventRepository;
use App\Repositories\Interfaces\IVenueRepository;
use App\ViewModels\JazzHomePageViewModel;

class JazzHomeService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private IJazzEventRepository $eventRepo,
        private IVenueRepository $venueRepo
    ) {}

    public function getJazzHomePageViewModel(): JazzHomePageViewModel
    {
        $content = $this->pageRepo->getPageContentByType('Jazz_Homepage');

        /** @var JazzEvent[] $eventsRaw */
        $eventsRaw = $this->eventRepo->getAllJazzEvents();

        $events = array_map([$this, 'mapEvent'], $eventsRaw);

        $hero = is_array($content['hero'] ?? null) ? $content['hero'] : [];
        $intro = is_array($content['intro'] ?? null) ? $content['intro'] : [];
        $dayTicketPass = is_array($content['day_ticket_pass'] ?? null) ? $content['day_ticket_pass'] : [];
        $schedule = is_array($content['schedule'] ?? null) ? $content['schedule'] : [];
        $filters = is_array($schedule['filters'] ?? null) ? $schedule['filters'] : [];

        $venues = $this->venueRepo->getAllVenues();
        $groupLabel = (string)($filters['group_label'] ?? 'By date');
        $hallTabs = array_map(fn(\App\Models\Venue $v) => $v->displayName(), $venues);
        array_unshift($hallTabs, $groupLabel);

        $dayTabs = is_array($filters['days'] ?? null)
            ? $filters['days']
            : ['All Days', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $allEventsButton = is_array($schedule['all_events_button'] ?? null) ? $schedule['all_events_button'] : [];
        $showAllEventsButton = !empty((string)($allEventsButton['href'] ?? ''));
        $allEventsButtonLabel = (string)($allEventsButton['label'] ?? 'All Events');

        $pageTitle = (string)($hero['title'] ?? 'Jazz');
        $scheduleTitle = (string)($schedule['title'] ?? 'SCHEDULE');
        $scheduleVenueTitle = (string)($schedule['venue_title'] ?? 'PATRONAAT');

        return new JazzHomePageViewModel(
            $pageTitle,
            $hero,
            $intro,
            $dayTicketPass,
            $scheduleTitle,
            $scheduleVenueTitle,
            $hallTabs,
            $dayTabs,
            $showAllEventsButton,
            $allEventsButtonLabel,
            $events
        );
    }

    private function mapEvent(JazzEvent $ev): array
    {
        $ts = strtotime($ev->start_date) ?: 0;

        $venueName = $ev->venue_name !== '' ? $ev->venue_name : (string)$ev->location;
        $hall = mb_strtolower(trim($venueName)) === 'grote markt' ? 'Free' : $venueName;

        return [
            'event_id' => $ev->event_id,
            'title' => $ev->title,
            'artist_name' => $ev->artist_name,
            'img_background' => (string)($ev->img_background ?? ''),
            'price' => (float)$ev->price,
            'location' => $venueName,
            'hall' => $hall,
            'page_id' => $ev->page_id,

            'day_key' => $ts ? date('l', $ts) : 'Unknown',
            'display_date' => $ts ? date('D j M', $ts) : '',
            'display_time' => $ts ? date('H:i', $ts) : '',
        ];
    }
}