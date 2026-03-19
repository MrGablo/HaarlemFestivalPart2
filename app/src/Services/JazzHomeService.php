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

        $rawDayTabs = is_array($filters['days'] ?? null) ? $filters['days'] : [];
        $dayTabs = $this->buildDayTabs($rawDayTabs, $events);

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
            'start_ts' => $ts,
            'event_date' => $ts ? date('Y-m-d', $ts) : '',

            'day_name' => $ts ? date('l', $ts) : 'Unknown',
            'day_key' => $ts ? date('l', $ts) : 'Unknown',
            'display_date' => $ts ? date('D j M', $ts) : '',
            'display_time' => $ts ? date('H:i', $ts) : '',
        ];
    }

    /**
     * @param array<int, mixed> $rawDayTabs
     * @param array<int, array<string, mixed>> $events
     * @return array<int, array{value: string, label: string}>
     */
    private function buildDayTabs(array $rawDayTabs, array $events): array
    {
        $tabs = [['value' => 'all', 'label' => 'All Days']];
        $seenValues = ['all' => true];

        foreach ($rawDayTabs as $rawDayTab) {
            $normalized = $this->normalizeDayTab($rawDayTab);
            if ($normalized === null) {
                continue;
            }

            if (isset($seenValues[$normalized['value']])) {
                continue;
            }

            $tabs[] = $normalized;
            $seenValues[$normalized['value']] = true;
        }

        // If content does not define day filters, infer dates from existing events.
        if (count($tabs) === 1) {
            foreach ($events as $event) {
                $eventDate = trim((string)($event['event_date'] ?? ''));
                if ($this->isIsoDate($eventDate) && !isset($seenValues[$eventDate])) {
                    $tabs[] = ['value' => $eventDate, 'label' => $this->formatDateLabel($eventDate)];
                    $seenValues[$eventDate] = true;
                }
            }

            usort($tabs, function (array $a, array $b): int {
                if ($a['value'] === 'all') {
                    return -1;
                }
                if ($b['value'] === 'all') {
                    return 1;
                }
                return strcmp($a['value'], $b['value']);
            });
        }

        return $tabs;
    }

    /**
     * @param mixed $rawDayTab
     * @return array{value: string, label: string}|null
     */
    private function normalizeDayTab(mixed $rawDayTab): ?array
    {
        if (is_string($rawDayTab)) {
            $value = trim($rawDayTab);
            if ($value === '') {
                return null;
            }

            if ($this->isAllDaysValue($value)) {
                return ['value' => 'all', 'label' => 'All Days'];
            }

            if ($this->isIsoDate($value)) {
                return ['value' => $value, 'label' => $this->formatDateLabel($value)];
            }

            // Backward compatibility: legacy values such as "Thursday".
            return ['value' => $value, 'label' => $value];
        }

        if (!is_array($rawDayTab)) {
            return null;
        }

        $value = trim((string)($rawDayTab['value'] ?? $rawDayTab['date'] ?? ''));
        $label = trim((string)($rawDayTab['label'] ?? ''));

        if ($value === '') {
            return null;
        }

        if ($this->isAllDaysValue($value)) {
            return ['value' => 'all', 'label' => $label !== '' ? $label : 'All Days'];
        }

        if ($this->isIsoDate($value)) {
            return ['value' => $value, 'label' => $label !== '' ? $label : $this->formatDateLabel($value)];
        }

        return ['value' => $value, 'label' => $label !== '' ? $label : $value];
    }

    private function isAllDaysValue(string $value): bool
    {
        $normalized = mb_strtolower(trim($value));
        return $normalized === 'all' || $normalized === 'all days';
    }

    private function isIsoDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $dt instanceof \DateTimeImmutable && $dt->format('Y-m-d') === $value;
    }

    private function formatDateLabel(string $isoDate): string
    {
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $isoDate);
        if (!$dt instanceof \DateTimeImmutable) {
            return $isoDate;
        }

        return $dt->format('l');
    }
}