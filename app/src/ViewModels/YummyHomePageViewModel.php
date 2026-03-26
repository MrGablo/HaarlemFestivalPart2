<?php

namespace App\ViewModels;

class YummyHomePageViewModel
{
    public string $pageTitle;
    public array $hero;
    public string $heroTitleHtml;
    public array $intro;
    public array $gallery;
    public array $map;
    public array $restaurants;
    public array $galleryImages;
    public array $galleryCaptions;
    public array $visibleRestaurantItems;
    public string $heroImage;
    public string $mapImage;
    public string $mapImageCaption;

    public function __construct(array $content, array $yummyEvents)
    {
        $this->pageTitle = $content['pageTitle'];
        $this->hero = $content['hero'];
        $this->heroTitleHtml = strip_tags($content['hero']['titleHtml'], '<br><span><strong><em><b><i>');

        $this->intro = $content['intro'];
        $this->gallery = $content['gallery'];
        $this->map = $content['map'];
        $this->restaurants = $content['restaurants'];

        $this->galleryImages = $content['gallery']['images'];
        $this->galleryCaptions = $content['gallery']['captions'];

        $this->visibleRestaurantItems = $this->buildRestaurantItemsFromDb($yummyEvents);

        $this->mapImageCaption = trim($content['map']['imageCaption'] ?? '');
        $this->heroImage = $content['hero']['bgImage'];
        $this->mapImage = $content['map']['image'];
    }

    private function buildRestaurantItemsFromDb(array $yummyEvents): array
    {
        return array_map(fn($event) => [
            'id' => $event->event_id,
            'name' => $event->title,
            'image' => $event->thumbnail_path,
            'cuisine' => str_replace([' - ', ','], ' • ', $event->cuisine),
            'star_rating' => $event->star_rating,
        ], $yummyEvents);
    }
}
