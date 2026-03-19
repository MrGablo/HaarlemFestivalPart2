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

    public function __construct(array $content = [], array $yummyEvents = [])
    {
        // Re-index the sections array using 'sectionType' as the key and 'data' as the value
        $sections = $content['sections'] ?? [];
        $sectionMap = array_combine(
            array_column($sections, 'sectionType'),
            array_column($sections, 'data')
        ) ?: [];

        $this->pageTitle = (string)($content['pageTitle'] ?? '');
        $this->hero = $sectionMap['hero'] ?? [];
        $this->heroTitleHtml = strip_tags((string)($this->hero['titleHtml'] ?? ''), '<br><span><strong><em><b><i>');

        $this->intro = $sectionMap['intro'] ?? [];
        $this->gallery = $sectionMap['gallery'] ?? [];
        $this->map = $sectionMap['map'] ?? [];
        $this->restaurants = $sectionMap['restaurants'] ?? [];

        $images = is_array($this->gallery['images'] ?? null) ? $this->gallery['images'] : [];
        $this->galleryImages = array_map([$this, 'normalizeAssetPath'], array_filter($images));

        $captions = is_array($this->gallery['captions'] ?? null) ? $this->gallery['captions'] : [];
        $this->galleryCaptions = array_values(array_filter($captions, 'is_string'));

        $this->visibleRestaurantItems = $this->buildRestaurantItemsFromDb($yummyEvents);

        $this->mapImageCaption = trim((string)($this->map['imageCaption'] ?? ''));
        $this->heroImage = $this->normalizeAssetPath((string)($this->hero['bgImage'] ?? ''));
        $this->mapImage = $this->normalizeAssetPath((string)($this->map['image'] ?? ''));
    }

    private function buildRestaurantItemsFromDb(array $yummyEvents): array
    {
        $items = [];
        foreach ($yummyEvents as $event) {
            $items[] = [
                'id' => $event->id,
                'name' => $event->title,
                'image' => $event->thumbnail_path ?? '',
                'cuisine' => str_replace([' - ', ','], ' • ', $event->cuisine ?? ''),
                'star_rating' => $event->star_rating ?? 0,
            ];
        }
        return $items;
    }

    private function normalizeAssetPath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        return str_starts_with($path, '/') ? $path : '/' . ltrim($path, '/');
    }
}
