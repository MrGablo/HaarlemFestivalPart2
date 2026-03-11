<?php

namespace App\ViewModels;

class YummyHomePageViewModel
{
    public string $pageTitle;
    public array $hero;
    public array $heroTitleLines;
    public array $intro;
    public array $gallery;
    public array $map;
    public array $restaurants;
    public array $galleryImages;
    public array $galleryCaptions;
    public array $visibleRestaurantItems;
    public string $heroImage;
    public string $mapUrl;
    public string $mapImage;
    public string $mapImageCaption;

    public function __construct(array $content = [])
    {
        $sectionMap = $this->buildSectionMap($content['sections'] ?? []);

        $this->pageTitle = (string)($content['pageTitle'] ?? 'Haarlem Yummy Event');
        $this->hero = $sectionMap['hero'] ?? [];
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

        $this->heroTitleLines = $this->buildHeroTitleLines();

        $this->mapUrl = $this->extractMapUrl();
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

    private function buildHeroTitleLines(): array
    {
        $heroTitleSource = (string)($this->hero['titleHtml'] ?? 'Haarlem Yummy Event');
        $heroTitleSource = preg_replace('/<br\s*\/?>/i', "\n", $heroTitleSource) ?? $heroTitleSource;
        $heroTitleSource = preg_replace('~</(?:p|div|h1|h2|h3|h4)>~i', "\n", $heroTitleSource) ?? $heroTitleSource;
        $heroTitlePlain = html_entity_decode(strip_tags($heroTitleSource), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $heroTitlePlain = str_replace("\xc2\xa0", ' ', $heroTitlePlain);
        $heroTitleLines = preg_split('/\R+/', $heroTitlePlain) ?: [];
        $heroTitleLines = array_values(array_filter(array_map(
            static fn(string $line): string => trim((string)preg_replace('/\s+/', ' ', $line)),
            $heroTitleLines
        ), static fn(string $line): bool => $line !== ''));

        if ($heroTitleLines === []) {
            return ['Haarlem', 'YUMMY EVENT'];
        }

        return $heroTitleLines;
    }

    private function extractMapUrl(): string
    {
        $mapUrl = '/map';

        if (preg_match('/href=[\"\']([^\"\']+)[\"\']/', (string)($this->map['buttonHtml'] ?? ''), $matches) === 1) {
            $mapUrl = $matches[1];
        }

        return $mapUrl;
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
