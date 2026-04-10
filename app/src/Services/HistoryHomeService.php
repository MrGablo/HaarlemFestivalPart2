<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\PageBuilder\Builders\HistoryDetailPageBuilder;
use App\Cms\PageBuilder\Builders\HistoryHomePageBuilder;
use App\Cms\PageBuilder\Content\HistoryDetailPageContentViewModel;
use App\Cms\PageBuilder\Content\HistoryHomePageContentViewModel;
use App\Models\HistoryEvent;
use App\Repositories\Interfaces\IHistoryEventRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\HistoryHomePageViewModel;

class HistoryHomeService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private IHistoryEventRepository $historyEvents,
        private HistoryHomePageBuilder $homeBuilder = new HistoryHomePageBuilder(),
        private HistoryDetailPageBuilder $detailBuilder = new HistoryDetailPageBuilder()
    ) {}

    public function getHistoryHomePageViewModel(): HistoryHomePageViewModel
    {
        /** @var HistoryHomePageContentViewModel $page */
        $page = $this->homeBuilder->buildViewModel(
            $this->pageRepo->getPageContentByType($this->homeBuilder->pageType())
        );
        $historyEvents = $this->historyEvents->getAllHistoryEvents();

        $locationRows = $this->pageRepo->getPagesByType($this->detailBuilder->pageType());
        $locations = [];

        foreach ($locationRows as $row) {
            $pageId = (int)($row['Page_ID'] ?? 0);
            if ($pageId <= 0) {
                continue;
            }

            $content = json_decode((string)($row['Content'] ?? ''), true);
            $content = is_array($content) ? $content : [];

            /** @var HistoryDetailPageContentViewModel $detail */
            $detail = $this->detailBuilder->buildViewModel($content);
            $meta = $detail->meta;
            $hero = $detail->hero;
            $slug = $this->normalizeSlug((string)($meta['slug'] ?? ''), (string)($meta['listing_title'] ?? $hero['title'] ?? ($row['Page_Title'] ?? '')));

            $locations[] = [
                'page_id' => $pageId,
                'title' => (string)($meta['listing_title'] ?? $hero['title'] ?? ($row['Page_Title'] ?? 'History stop')),
                'summary' => (string)($meta['listing_summary'] ?? ''),
                'image' => (string)($meta['listing_image'] ?? $hero['main_image'] ?? ''),
                'slug' => $slug,
                'sort_order' => (int)($meta['sort_order'] ?? 0),
                'map_x' => (float)($meta['map_marker']['x'] ?? 50),
                'map_y' => (float)($meta['map_marker']['y'] ?? 50),
                'detail_url' => $slug !== '' ? '/history/' . rawurlencode($slug) : '/history/detail?page_id=' . $pageId,
            ];
        }

        usort($locations, static function (array $left, array $right): int {
            $sort = ($left['sort_order'] ?? 0) <=> ($right['sort_order'] ?? 0);
            if ($sort !== 0) {
                return $sort;
            }

            return strcmp((string)($left['title'] ?? ''), (string)($right['title'] ?? ''));
        });

        $scheduleRows = $this->buildScheduleRows($historyEvents);
        $bookingEvents = $this->buildBookingEvents($historyEvents);

        return new HistoryHomePageViewModel(
            $page->hero,
            $page->overview,
            $page->booking,
            $page->map,
            $locations,
            $scheduleRows,
            $bookingEvents
        );
    }

    /** @param HistoryEvent[] $events */
    private function buildScheduleRows(array $events): array
    {
        $grouped = [];

        foreach ($events as $event) {
            $timestamp = strtotime($event->start_date) ?: 0;
            if ($timestamp <= 0) {
                continue;
            }

            $day = date('l', $timestamp);
            $dateKey = date('Y-m-d', $timestamp);
            $dateLabel = date('d M', $timestamp);
            $time = date('H:i', $timestamp);
            $key = $dateKey . '|' . $time;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'date_key' => $dateKey,
                    'date_label' => $dateLabel,
                    'day' => $day,
                    'time' => $time,
                    'sort_ts' => $timestamp,
                    'nl' => 0,
                    'en' => 0,
                    'ch' => 0,
                ];
            }

            $language = strtolower($event->language);
            if (isset($grouped[$key][$language])) {
                $grouped[$key][$language]++;
            }
        }

        usort($grouped, static function (array $left, array $right): int {
            return (int)($left['sort_ts'] ?? 0) <=> (int)($right['sort_ts'] ?? 0);
        });

        return array_values(array_map(static function (array $row): array {
            unset($row['sort_ts']);
            return $row;
        }, $grouped));
    }

    /** @param HistoryEvent[] $events */
    private function buildBookingEvents(array $events): array
    {
        $rows = [];

        foreach ($events as $event) {
            $timestamp = strtotime($event->start_date) ?: 0;
            if ($timestamp <= 0) {
                continue;
            }

            $languageCode = strtoupper(trim($event->language));
            $rows[] = [
                'event_id' => (int)$event->event_id,
                'title' => (string)$event->title,
                'location' => (string)$event->location,
                'language_code' => $languageCode,
                'language_label' => $this->languageLabel($languageCode),
                'start_date' => (string)$event->start_date,
                'date_key' => date('Y-m-d', $timestamp),
                'date_label' => date('D d M', $timestamp),
                'full_date_label' => date('l d F Y', $timestamp),
                'day_label' => date('l', $timestamp),
                'time_key' => date('H:i', $timestamp),
                'time_label' => date('H:i', $timestamp),
                'availability' => (int)$event->availability,
                'is_available' => (int)$event->availability > 0,
                'price' => (float)($event->price ?? 0),
                'price_label' => number_format((float)($event->price ?? 0), 2),
                'family_price' => (float)($event->family_price ?? 0),
                'family_price_label' => number_format((float)($event->family_price ?? 0), 2),
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            $timeCompare = strcmp((string)($left['start_date'] ?? ''), (string)($right['start_date'] ?? ''));
            if ($timeCompare !== 0) {
                return $timeCompare;
            }

            $languageCompare = strcmp((string)($left['language_code'] ?? ''), (string)($right['language_code'] ?? ''));
            if ($languageCompare !== 0) {
                return $languageCompare;
            }

            return (int)($left['event_id'] ?? 0) <=> (int)($right['event_id'] ?? 0);
        });

        return $rows;
    }

    private function languageLabel(string $languageCode): string
    {
        return match (strtoupper($languageCode)) {
            'NL' => 'Dutch',
            'EN' => 'English',
            'CH' => 'Chinese',
            default => $languageCode,
        };
    }

    private function normalizeSlug(string $slug, string $fallback): string
    {
        $candidate = trim($slug);
        if ($candidate === '') {
            $candidate = strtolower($fallback);
        }

        $candidate = strtolower($candidate);
        $candidate = preg_replace('/[^a-z0-9]+/', '-', $candidate) ?? '';
        return trim($candidate, '-');
    }
}