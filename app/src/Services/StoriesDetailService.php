<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\PageBuilder\Builders\StoriesDetailPageBuilder;
use App\Cms\PageBuilder\Content\StoriesDetailPageContentViewModel;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\Interfaces\IStoriesRepository;
use App\ViewModels\StoriesDetailPageViewModel;

final class StoriesDetailService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private IStoriesRepository $storiesRepo,
        private StoriesDetailPageBuilder $builder = new StoriesDetailPageBuilder()
    ) {}

    public function getStoriesDetailPageViewModel(int $pageId): StoriesDetailPageViewModel
    {
        /** @var StoriesDetailPageContentViewModel $page */
        $page = $this->builder->buildViewModel($this->pageRepo->getPageContentById($pageId));

        $event = $this->storiesRepo->getStoriesEventByPageId($pageId);
        if ($event === null) {
            throw new \RuntimeException('Stories event not found for this page.');
        }

        $story = $page->story;
        $eventCard = $page->eventCard;
        $intro = $page->intro;
        $origin = $page->origin;
        $video = $page->video;

        $breadcrumb = is_array($story['breadcrumb'] ?? null) ? $story['breadcrumb'] : [];
        $heroMedia = is_array($story['hero_media'] ?? null) ? $story['hero_media'] : [];
        $mainMedia = is_array($heroMedia['main'] ?? null) ? $heroMedia['main'] : null;
        $secondaryMedia = is_array($heroMedia['secondary'] ?? null) ? $heroMedia['secondary'] : [];

        $startTimestamp = strtotime((string)($event['start_date'] ?? '')) ?: 0;
        $endTimestamp = strtotime((string)($event['end_date'] ?? '')) ?: 0;

        $eventMetaLabels = is_array($eventCard['meta_labels'] ?? null) ? $eventCard['meta_labels'] : [];

        $eventMeta = [
            'date_label' => (string)($eventMetaLabels['date'] ?? 'date'),
            'date_value' => $startTimestamp ? date('F j, Y', $startTimestamp) : '',
            'time_label' => (string)($eventMetaLabels['time'] ?? 'time'),
            'time_value' => ($startTimestamp && $endTimestamp) ? date('H:i', $startTimestamp) . ' - ' . date('H:i', $endTimestamp) : '',
            'place_label' => (string)($eventMetaLabels['place'] ?? 'place'),
            'place_value' => (string)($event['location'] ?? ''),
            'age_group_label' => (string)($eventMetaLabels['age_group'] ?? 'age group'),
            'age_group_value' => (string)($event['age_group'] ?? ''),
            'language_label' => (string)($eventMetaLabels['language'] ?? 'language'),
            'language_value' => (string)($event['language'] ?? ''),
        ];

        return new StoriesDetailPageViewModel(
            $pageId,
            (int)($event['event_id'] ?? 0),
            (string)($story['name'] ?? ($event['title'] ?? 'Story')),
            [
                'back_href' => (string)($breadcrumb['back_href'] ?? '/stories'),
                'back_label' => (string)($breadcrumb['back_label'] ?? 'Stories'),
                'current' => (string)($breadcrumb['current'] ?? ($event['title'] ?? 'Story')),
            ],
            (string)($story['kicker'] ?? 'Stories'),
            (string)($story['hero_title'] ?? ($event['title'] ?? 'Story')),
            (string)($story['hero_subtitle'] ?? ''),
            isset($story['hero_body_html']) ? (string)$story['hero_body_html'] : null,
            (string)($story['cover_image'] ?? ''),
            $mainMedia,
            $secondaryMedia,
            $eventMeta,
            [
                'reserve_title' => (string)($eventCard['reserve_title'] ?? 'Reserve tickets'),
                'price_label' => (string)($eventCard['price_label'] ?? 'Ticket price:'),
                'price_suffix' => (string)($eventCard['price_suffix'] ?? 'per person'),
                'quantity_label' => (string)($eventCard['quantity_label'] ?? 'How many people are coming?'),
                'total_label' => (string)($eventCard['total_label'] ?? 'Total:'),
                'button_label' => (string)($eventCard['button_label'] ?? 'Add tickets to cart'),
                'about_title' => (string)($eventCard['about_title'] ?? 'About tickets'),
                'about_text' => (string)($eventCard['about_text'] ?? ''),
            ],
            [
                'image' => (string)($intro['image'] ?? ''),
                'html' => isset($intro['html']) ? (string)$intro['html'] : '',
                'bullets' => is_array($intro['bullets'] ?? null) ? $intro['bullets'] : [],
            ],
            [
                'title' => (string)($origin['title'] ?? ''),
                'image' => (string)($origin['image'] ?? ''),
                'html' => isset($origin['html']) ? (string)$origin['html'] : '',
            ],
            [
                'title' => (string)($video['title'] ?? ''),
                'description' => (string)($video['description'] ?? ''),
                'embed_url' => (string)($video['embed_url'] ?? ''),
                'thumbnail' => (string)($video['thumbnail'] ?? ''),
            ],
            (float)($event['price'] ?? 0),
            (int)($event['availability'] ?? 0)
        );
    }
}