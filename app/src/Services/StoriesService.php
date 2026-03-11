<?php

namespace App\Services;

use App\Repositories\Interfaces\IStoriesRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\StoriesHomePageViewModel;
use DateTime;
use Exception;

class StoriesService
{
    private IStoriesRepository $storiesRepo;
    private IPageRepository $pageRepo;

    public function __construct(IStoriesRepository $storiesRepo, IPageRepository $pageRepo)
    {
        $this->storiesRepo = $storiesRepo;
        $this->pageRepo = $pageRepo;
    }

    /**
     * @throws Exception
     */
    public function getStoriesPageData(): StoriesHomePageViewModel
    {
        // 1. Get the static JSON content for the page text and filters
        $content = $this->pageRepo->getPageContentByType('Stories_Homepage');

        // 2. Get the raw schedule data from the database
        $events = $this->storiesRepo->getAllStoriesEvents();

        // 3. Format the data for the view
        foreach ($events as &$event) {
            $startDate = new DateTime($event['start_date']);
            $endDate = new DateTime($event['end_date']);
            
            // Format time for display 
            $event['display_time'] = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
            
            // day of the week 
            $event['day_key'] = $startDate->format('l');
            
            // pay as you like formatting
            if ((float)$event['price'] === 0.0) {
                $event['display_price'] = 'Pay as you like';
            } else {
                $event['display_price'] = '€' . number_format((float)$event['price'], 2, ',', '.');
            }
        }
        unset($event); 

        return new StoriesHomePageViewModel($content, $events);
    }
}