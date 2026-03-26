<?php

namespace App\Services;

use App\Cms\PageBuilder\Builders\JazzArtistPageBuilder;
use App\Cms\PageBuilder\Content\JazzArtistPageContentViewModel;
use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\Interfaces\IJazzEventRepository;
use App\ViewModels\JazzArtistPageViewModel;
use App\Models\JazzEvent;

class JazzArtistService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private IJazzEventRepository $eventRepo,
        private JazzArtistPageBuilder $builder = new JazzArtistPageBuilder()
    ) {}

    public function getArtistPageViewModel(int $pageId, ?string $tab): JazzArtistPageViewModel
    {
        /** @var JazzArtistPageContentViewModel $page */
        $page = $this->builder->buildViewModel($this->pageRepo->getPageContentById($pageId));

        $allowed = ['events', 'career', 'album'];

        $defaultTab = (string)($page->tabs['default'] ?? 'events');
        if (!in_array($defaultTab, $allowed, true)) {
            $defaultTab = 'events';
        }

        $activeTab = $tab ? (string)$tab : $defaultTab;
        if (!in_array($activeTab, $allowed, true)) {
            $activeTab = $defaultTab;
        }

        /** @var JazzEvent[] $models */
        $models = $this->eventRepo->getJazzEventsByPageId($pageId);
        $events = array_map([$this, 'mapEventForArtistPage'], $models);

        $artist = $page->artist;
        $breadcrumb = is_array($artist['breadcrumb'] ?? null) ? $artist['breadcrumb'] : [];

        $heroMedia = is_array($artist['hero_media'] ?? null) ? $artist['hero_media'] : [];
        $mainMedia = is_array($heroMedia['main'] ?? null) ? $heroMedia['main'] : null;
        $secondaryMedia = is_array($heroMedia['secondary'] ?? null) ? $heroMedia['secondary'] : [];

        $tabLabelsRaw = is_array($page->tabs['labels'] ?? null) ? $page->tabs['labels'] : [];
        $tabLabels = [
            'events' => (string)($tabLabelsRaw['events'] ?? 'Events'),
            'career' => (string)($tabLabelsRaw['career'] ?? 'Career Highlights'),
            'album' => (string)($tabLabelsRaw['album'] ?? 'Album'),
        ];

        $tabLinks = [
            'events' => '/jazz/artist?page_id=' . $pageId . '&tab=events',
            'career' => '/jazz/artist?page_id=' . $pageId . '&tab=career',
            'album' => '/jazz/artist?page_id=' . $pageId . '&tab=album',
        ];

        $career = $page->careerHighlights;
        $careerLeftItems = is_array($career['left'] ?? null) ? $career['left'] : [];
        $careerRightItems = is_array($career['right'] ?? null) ? $career['right'] : [];

        $albumsRaw = $page->albums;
        $albums = array_map([$this, 'mapAlbumForArtistPage'], $albumsRaw);

        $about = $page->about;
        $band = $page->bandMembers;

        $kickerText = trim((string)($artist['kicker'] ?? ''));
        $heroTitleText = trim((string)($artist['hero_title'] ?? ($artist['name'] ?? '')));
        $heroSubtitleText = trim((string)($artist['hero_subtitle'] ?? ''));

        if (strcasecmp($kickerText, $heroTitleText) === 0) {
            $kickerText = '';
        }

        if (
            strcasecmp($heroSubtitleText, $heroTitleText) === 0 ||
            ($kickerText !== '' && strcasecmp($heroSubtitleText, $kickerText) === 0)
        ) {
            $heroSubtitleText = '';
        }

        return new JazzArtistPageViewModel(
            $pageId,
            (string)($artist['name'] ?? 'Artist'),
            (string)($artist['cover_image'] ?? ''),
            [
                'back_href' => (string)($breadcrumb['back_href'] ?? '/jazz'),
                'back_label' => (string)($breadcrumb['back_label'] ?? 'Back'),
                'current' => (string)($breadcrumb['current'] ?? ''),
            ],
            $kickerText,
            $heroTitleText,
            $heroSubtitleText,
            $mainMedia,
            $secondaryMedia,
            $tabLabels,
            $tabLinks,
            $activeTab,
            $events,
            (string)($page->events['ticket_button_label'] ?? 'Tickets'),
            isset($career['left_html']) ? (string)$career['left_html'] : null,
            isset($career['right_html']) ? (string)$career['right_html'] : null,
            $careerLeftItems,
            $careerRightItems,
            $albums,
            (string)($about['title'] ?? 'About'),
            isset($about['html']) ? (string)$about['html'] : null,
            (string)($about['text'] ?? ''),
            (string)($band['title'] ?? 'Band Members'),
            is_array($band['items'] ?? null) ? $band['items'] : []
        );
    }

    private function mapEventForArtistPage(JazzEvent $ev): array
    {
        $ts = strtotime($ev->start_date) ?: 0;

        return [
            'event_id' => $ev->event_id,
            'start_label' => $ts ? date('l j F Y H:i', $ts) : '',
            'title' => $ev->title,
            'location' => $ev->venue_name !== '' ? $ev->venue_name : $ev->location,
            'img_background' => (string)($ev->img_background ?? ''),
            'price' => (float)$ev->price
        ];
    }

    private function mapAlbumForArtistPage(array $album): array
    {
        $image = $album['image'] ?? null;
        $imageSrc = '';
        $imageAlt = (string)($album['title'] ?? 'Album');
        $imageCaption = null;

        if (is_string($image)) {
            $imageSrc = $image;
        } elseif (is_array($image)) {
            $imageSrc = (string)($image['src'] ?? '');
            $imageAlt = (string)($image['alt'] ?? $imageAlt);
            $imageCaption = isset($image['caption']) ? (string)$image['caption'] : null;
        }

        return [
            'artist' => (string)($album['artist'] ?? ''),
            'title' => (string)($album['title'] ?? ''),
            'description' => (string)($album['description'] ?? ''),
            'description_html' => isset($album['description_html']) ? (string)$album['description_html'] : null,
            'image_src' => $imageSrc,
            'image_alt' => $imageAlt,
            'image_caption' => $imageCaption,
        ];
    }
}
