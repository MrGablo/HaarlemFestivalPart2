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

    public function __construct(array $content = [])
    {
        $sectionMap = $this->buildSectionMap($content['sections'] ?? []);

        $this->pageTitle = (string)($content['pageTitle'] ?? 'Haarlem Yummy Event');
        $this->hero = $sectionMap['hero'] ?? [];
        $heroTitleHtml = trim((string)($this->hero['titleHtml'] ?? ''));
        $heroTitleHtml = preg_replace('~^<h1[^>]*>|</h1>$~i', '', $heroTitleHtml) ?? $heroTitleHtml;
        $this->heroTitleHtml = strip_tags($heroTitleHtml, '<br><span><strong><em><b><i>');
        $this->intro = $sectionMap['intro'] ?? [];
        $this->gallery = $sectionMap['gallery'] ?? [];
        $this->map = $sectionMap['map'] ?? [];
        $this->restaurants = $sectionMap['restaurants'] ?? [];

        $this->galleryImages = array_values(array_filter(
            is_array($this->gallery['images'] ?? null) ? $this->gallery['images'] : [],
            static fn(mixed $image): bool => is_string($image) && $image !== ''
        ));
        $this->galleryImages = array_map([$this, 'normalizeAssetPath'], $this->galleryImages);

        $restaurantItems = $this->buildRestaurantItems();
        $this->galleryCaptions = $this->buildGalleryCaptions($restaurantItems);
        $this->visibleRestaurantItems = array_slice($restaurantItems, 0, 7);

        $this->mapImageCaption = trim((string)($this->map['imageCaption'] ?? ''));
        $this->heroImage = $this->normalizeAssetPath((string)($this->hero['bgImage'] ?? ''));
        $this->mapImage = $this->normalizeAssetPath((string)($this->map['image'] ?? ''));
    }

    private function buildSectionMap(mixed $rawSections): array
    {
        $sectionMap = [];

        foreach (is_array($rawSections) ? $rawSections : [] as $section) {
            if (!is_array($section)) {
                continue;
            }

            $sectionType = (string)($section['sectionType'] ?? '');
            if ($sectionType === '') {
                continue;
            }

            $sectionMap[$sectionType] = is_array($section['data'] ?? null)
                ? $section['data']
                : [];
        }

        return $sectionMap;
    }

    private function buildRestaurantItems(): array
    {
        $defaultRestaurantImages = [
            '/assets/meal-1.jpg',
            '/assets/meal-2.jpg',
            '/assets/meal-3.jpg',
            '/assets/meal-4.jpg',
        ];

        $restaurantItems = [];
        $rawRestaurants = is_array($this->restaurants['list'] ?? null) ? $this->restaurants['list'] : [];

        foreach ($rawRestaurants as $index => $restaurantItem) {
            if (is_string($restaurantItem) && $restaurantItem !== '') {
                $restaurantItems[] = [
                    'slug' => $restaurantItem,
                    'name' => $this->formatRestaurantName($restaurantItem),
                    'image' => $defaultRestaurantImages[$index % count($defaultRestaurantImages)],
                ];
                continue;
            }

            if (!is_array($restaurantItem)) {
                continue;
            }

            $slug = (string)($restaurantItem['slug'] ?? '');
            $restaurantItems[] = [
                'slug' => $slug,
                'name' => (string)($restaurantItem['name'] ?? ($slug !== '' ? $this->formatRestaurantName($slug) : 'Restaurant')),
                'image' => $this->normalizeAssetPath((string)($restaurantItem['image'] ?? $defaultRestaurantImages[$index % count($defaultRestaurantImages)])),
            ];
        }

        return $restaurantItems;
    }

    private function buildGalleryCaptions(array $restaurantItems): array
    {
        $galleryCaptions = [];

        foreach (array_slice($restaurantItems, 0, max(4, count($this->galleryImages))) as $restaurantItem) {
            $galleryCaptions[] = ($restaurantItem['name'] ?? 'Featured Restaurant') . ' Food';
        }

        return $galleryCaptions;
    }

    private function formatRestaurantName(string $slug): string
    {
        $parts = array_filter(explode('-', $slug), static fn(string $part): bool => $part !== '');
        $words = array_map(static function (string $part): string {
            return match (strtolower($part)) {
                'ml' => 'ML',
                default => ucwords($part),
            };
        }, $parts);

        return implode(' ', $words);
    }

    private function normalizeAssetPath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        return str_starts_with($path, '/') ? $path : '/' . ltrim($path, '/');
    }
}
