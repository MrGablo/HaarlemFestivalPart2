<?php

namespace App\Services;

use App\Cms\PageBuilder\Builders\StoriesHomePageBuilder;
use App\Cms\PageBuilder\Content\StoriesHomePageContentViewModel;
use App\Repositories\Interfaces\IStoriesRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\StoriesHomePageViewModel;
use DateTime;
use Exception;

class StoriesService
{
    private IStoriesRepository $storiesRepo;
    private IPageRepository $pageRepo;
    private StoriesHomePageBuilder $builder;

    public function __construct(IStoriesRepository $storiesRepo, IPageRepository $pageRepo, ?StoriesHomePageBuilder $builder = null)
    {
        $this->storiesRepo = $storiesRepo;
        $this->pageRepo = $pageRepo;
        $this->builder = $builder ?? new StoriesHomePageBuilder();
    }

    public function getStoriesPageData(): StoriesHomePageViewModel
    {
        /** @var StoriesHomePageContentViewModel $page */
        $page = $this->builder->buildViewModel(
            $this->pageRepo->getPageContentByType($this->builder->pageType())
        );

        $hero = $page->hero;
        $introduction = $page->introduction;

        $events = $this->storiesRepo->getAllStoriesEvents();

        foreach ($events as &$event) {
            $startDate = new DateTime($event['start_date']);
            $endDate = new DateTime($event['end_date']);

            $event['display_time'] = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
            $event['day_key'] = $startDate->format('l');

            if ((float)$event['price'] === 0.0) {
                $event['display_price'] = 'Pay as you like';
            } else {
                $event['display_price'] = '€' . number_format((float)$event['price'], 2, ',', '.');
            }
        }
        unset($event);

        $displayDays = ['Thursday', 'Friday', 'Saturday', 'Sunday'];

        $groupedEvents = [];
        foreach ($events as $event) {
            $day = $event['day_key'];
            $groupedEvents[$day][] = $event;
        }

        $days = [];
        foreach ($displayDays as $dayTitle) {
            if (isset($groupedEvents[$dayTitle])) {
                $days[] = [
                    'title' => $dayTitle,
                    'events' => $groupedEvents[$dayTitle]
                ];
            }
        }

        return new StoriesHomePageViewModel($hero, $introduction, $days);
    }
}