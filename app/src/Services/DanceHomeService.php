<?php

declare(strict_types=1);

namespace App\Services;

use App\Cms\PageBuilder\Builders\DanceHomePageBuilder;
use App\Cms\PageBuilder\Content\DanceHomePageContentViewModel;
use App\Repositories\DanceHomeRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\Utils\Media;
use App\ViewModels\DanceHomePageViewModel;

/** Dance home page data: DB timetable/lineup + CMS hero/intro text. */
final class DanceHomeService
{
    private const ALLOWED_DANCE_ASSETS = [
        'dance-hero-bg.png', 'dance-intro-side.png', 'dance-timetable-texture.png',
        'dj-martin.png', 'dj-armin.png', 'dj-tiesto.png', 'dj-hardwell.png',
        'dj-nicky-romero.png', 'dj-afrojack.png',
    ];

    private const DEFAULT_LINEUP = [
        ['name' => 'MARTIN GARRIX', 'image' => 'assets/img/dance-assets/dj-martin.png'],
        ['name' => 'ARMIN VAN BUUREN', 'image' => 'assets/img/dance-assets/dj-armin.png'],
        ['name' => 'TIËSTO', 'image' => 'assets/img/dance-assets/dj-tiesto.png'],
        ['name' => 'HARDWELL', 'image' => 'assets/img/dance-assets/dj-hardwell.png'],
        ['name' => 'AFROJACK', 'image' => 'assets/img/dance-assets/dj-afrojack.png'],
        ['name' => 'NICKY ROMERO', 'image' => 'assets/img/dance-assets/dj-nicky-romero.png'],
    ];

    public function __construct(
        private IPageRepository $pageRepo,
        private DanceHomeRepository $danceRepo,
        private DanceHomePageBuilder $builder = new DanceHomePageBuilder(),
    ) {}

    public function buildViewModel(): DanceHomePageViewModel
    {
        /** @var DanceHomePageContentViewModel $page */
        $page = $this->builder->buildViewModel(
            $this->pageRepo->getPageContentByType($this->builder->pageType())
        );

        $hero = $page->hero;
        $intro = $page->intro;
        $lineup = $page->lineup;
        $timetableCms = $page->timetable;

        $heroBg = $this->normaliseAsset(
            Media::image($hero['background_image'] ?? null)['src'],
            'assets/img/dance-assets/dance-hero-bg.png'
        );
        $introImg = $this->normaliseAsset(
            Media::image($intro['side_image'] ?? null)['src'],
            'assets/img/dance-assets/dance-intro-side.png'
        );

        $heroTitle = (string) ($hero['title'] ?? 'HAARLEM DANCE EVENT');
        $parts = preg_match('/^(.+?)\s+(.+)$/', $heroTitle, $m) ? [$m[1], $m[2]] : [$heroTitle, ''];

        $subtitleHtml = is_string($hero['subtitle_html'] ?? null) ? $hero['subtitle_html'] : '';
        $bodyHtml = is_string($intro['body_html'] ?? null) ? $intro['body_html'] : '';

        $stats = $intro['stats'] ?? [];
        $stats = is_array($stats) ? $stats : [];

        $rows = $this->danceRepo->findDanceTimetableRows();
        $statsLine = $this->buildStatsLineFromTimetable($rows, $stats);
        $dateRangeFromDb = $this->buildDateRangeLabelFromTimetable($rows);

        $lineupArtists = $this->buildLineupArtistsFromDatabase($lineup);
        [$allAccess, $days] = $this->buildTimetableFromRows($rows, $timetableCms);

        $pageTitle = (string) ($hero['title'] ?? 'Dance');
        $heroBgUrl = self::webPath($heroBg);
        $introImgUrl = self::webPath($introImg);
        $timetableBgUrl = self::webPath('assets/img/dance-assets/dance-timetable-texture.png');

        $lineupWithUrls = [];
        foreach ($lineupArtists as $a) {
            $lineupWithUrls[] = [
                'name' => $a['name'],
                'imageUrl' => self::webPath($a['imageSrc']),
                'alt' => $a['alt'],
            ];
        }

        return new DanceHomePageViewModel(
            $pageTitle,
            [
                'titleLine1' => $parts[0],
                'titleLine2' => $parts[1],
                'subtitleHtml' => $subtitleHtml,
                'primaryButtonLabel' => (string) ($hero['primary_button']['label'] ?? 'Buy ticket'),
                'stripText' => (string) ($hero['strip_text'] ?? 'HAARLEM FESTIVAL DANCE'),
            ],
            [
                'kicker' => (string) ($intro['kicker'] ?? 'Let Haarlem\'s music welcome you in'),
                'bodyHtml' => $bodyHtml,
                'sideImageAlt' => (string) (Media::image($intro['side_image'] ?? null)['alt'] ?: 'Dance event'),
                'statsLine' => $statsLine,
            ],
            $this->lineupSectionTitle($lineup, $rows),
            $lineupWithUrls,
            (string) ($timetableCms['title'] ?? 'time table'),
            $dateRangeFromDb ?? (string) ($timetableCms['date_range'] ?? 'Friday July 25th → Sunday July 27th'),
            $allAccess,
            $days,
            $rows !== [],
            $heroBgUrl,
            $introImgUrl,
            $timetableBgUrl,
        );
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array{0: ?array, 1: list<array>}
     */
    private function buildTimetableFromRows(array $rows, array $timetableCms): array
    {
        $allAccess = null;
        $dayOrder = [];
        $passByDay = [];
        $sessionsByDay = [];

        $cmsAa = null;
        foreach ($timetableCms['passes'] ?? [] as $p) {
            if (!is_array($p)) {
                continue;
            }
            $lbl = (string) ($p['label'] ?? '');
            if (stripos($lbl, 'ALL-ACCESS') !== false) {
                $cmsAa = $p;
                break;
            }
        }

        foreach ($rows as $r) {
            $kind = (string) ($r['row_kind'] ?? '');
            if ($kind === 'all_access') {
                $eid = (int) ($r['event_id'] ?? 0);
                $allAccess = [
                    'label' => (string) ($cmsAa['label'] ?? $r['title'] ?? 'ALL-ACCESS PASS 3 DAYS'),
                    'note' => (string) ($cmsAa['note'] ?? 'NO garanteed'),
                    'priceLabel' => $this->formatEuro((float) ($r['price'] ?? 0)),
                    'eventId' => $eid,
                ];
                continue;
            }

            $day = (string) ($r['day_display_label'] ?? '');
            if ($day === '') {
                continue;
            }
            if (!in_array($day, $dayOrder, true)) {
                $dayOrder[] = $day;
            }
            if ($kind === 'day_pass') {
                $passByDay[$day] = $r;
            } elseif ($kind === 'session') {
                $sessionsByDay[$day][] = $r;
            }
        }

        $days = [];
        foreach ($dayOrder as $dayLabel) {
            $pass = $passByDay[$dayLabel] ?? null;
            $passEid = $pass !== null ? (int) ($pass['event_id'] ?? 0) : 0;
            $days[] = [
                'dayLabel' => $dayLabel,
                'passLabel' => $pass !== null
                    ? (string) ($pass['title'] ?? 'DAY PASS')
                    : 'DAY PASS',
                'passPriceLabel' => $pass !== null
                    ? $this->formatEuro((float) ($pass['price'] ?? 0))
                    : '',
                'passEventId' => $passEid,
                'sessions' => $this->mapSessions($sessionsByDay[$dayLabel] ?? []),
            ];
        }

        return [$allAccess, $days];
    }

    /**
     * @param list<array<string, mixed>> $sessionRows
     * @return list<array<string, mixed>>
     */
    private function mapSessions(array $sessionRows): array
    {
        $out = [];
        foreach ($sessionRows as $r) {
            $start = (string) ($r['session_start'] ?? '');
            $end = (string) ($r['session_end'] ?? '');
            $out[] = [
                'title' => (string) ($r['title'] ?? ''),
                'tag' => (string) ($r['session_tag'] ?? ''),
                'tagSpecial' => !empty($r['tag_special']),
                'timeRange' => $start !== '' || $end !== '' ? $start . ' - ' . $end : '',
                'venueName' => (string) ($r['location_name'] ?? ''),
                'priceLabel' => $this->formatEuro((float) ($r['price'] ?? 0)),
                'eventId' => (int) ($r['event_id'] ?? 0),
            ];
        }

        return $out;
    }

    private function formatEuro(float $amount): string
    {
        return '€' . number_format($amount, 2, '.', '');
    }

    /** Same as Jazz: static files under public/ → URL /assets/... */
    private static function webPath(string $path): string
    {
        return '/' . ltrim(str_replace('\\', '/', $path), '/');
    }

    private function normaliseAsset(string $path, string $fallback): string
    {
        $path = trim($path);
        if ($path === '') {
            return $fallback;
        }

        if (strpos($path, 'assets/img/page/') === 0) {
            return $path;
        }

        if (strpos($path, 'assets/img/dance-assets/') !== 0) {
            return $fallback;
        }
        $base = basename($path);

        return in_array($base, self::ALLOWED_DANCE_ASSETS, true) ? $path : $fallback;
    }

    /** @return list<array{name: string, imageSrc: string, alt: string}> */
    private function buildLineupArtistsFromDatabase(array $lineup): array
    {
        $dbHeadlines = $this->danceRepo->findDanceLineupHeadlines(6);
        $cycle = array_column(self::DEFAULT_LINEUP, 'image');
        $n = count($cycle);
        if ($n === 0) {
            $cycle = ['assets/img/dance-assets/dj-martin.png'];
            $n = 1;
        }

        if ($dbHeadlines !== []) {
            $out = [];
            foreach ($dbHeadlines as $i => $h) {
                $title = trim($h['title']);
                if ($title === '') {
                    continue;
                }
                $out[] = [
                    'name' => mb_strtoupper($title, 'UTF-8'),
                    'imageSrc' => $cycle[$i % $n],
                    'alt' => $title,
                ];
            }

            return $out !== [] ? $out : $this->buildLineupFromCmsOrDefaults($lineup);
        }

        return $this->buildLineupFromCmsOrDefaults($lineup);
    }

    /**
     * @return list<array{name: string, imageSrc: string, alt: string}>
     */
    private function buildLineupFromCmsOrDefaults(array $lineup): array
    {
        $artistsIn = is_array($lineup['artists'] ?? null) ? $lineup['artists'] : [];
        $artistsSrc = $artistsIn !== [] ? $artistsIn : self::DEFAULT_LINEUP;
        $lineupArtists = [];
        $idx = 0;
        $fallbacks = array_column(self::DEFAULT_LINEUP, 'image');
        foreach (array_slice($artistsSrc, 0, 6) as $a) {
            $raw = Media::image($a['image'] ?? null)['src'];
            $imgSrc = $this->normaliseAsset($raw, $fallbacks[$idx] ?? 'assets/img/dance-assets/dj-martin.png');
            ++$idx;
            $lineupArtists[] = [
                'name' => (string) ($a['name'] ?? 'DJ'),
                'imageSrc' => $imgSrc,
                'alt' => (string) ($a['name'] ?? ''),
            ];
        }

        return $lineupArtists;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string|int|float> $cmsStats
     */
    private function buildStatsLineFromTimetable(array $rows, array $cmsStats): string
    {
        if ($rows === []) {
            return implode('  ', array_map('strval', $cmsStats));
        }

        $daysSeen = [];
        $sessions = 0;
        $venues = [];
        foreach ($rows as $r) {
            $kind = (string) ($r['row_kind'] ?? '');
            $day = trim((string) ($r['day_display_label'] ?? ''));
            if ($day !== '' && ($kind === 'session' || $kind === 'day_pass' || $kind === 'all_access')) {
                $daysSeen[$day] = true;
            }
            if ($kind === 'session') {
                ++$sessions;
            }
            if ($kind === 'session' && isset($r['venue_id']) && (int) $r['venue_id'] > 0) {
                $venues[(string) (int) $r['venue_id']] = true;
            }
        }

        $nd = count($daysSeen);

        return sprintf('%d days  ·  %d sessions  ·  %d venues', $nd, $sessions, count($venues));
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function buildDateRangeLabelFromTimetable(array $rows): ?string
    {
        $ordered = [];
        foreach ($rows as $r) {
            $d = trim((string) ($r['day_display_label'] ?? ''));
            if ($d !== '' && !in_array($d, $ordered, true)) {
                $ordered[] = $d;
            }
        }
        if ($ordered === []) {
            return null;
        }
        if (count($ordered) === 1) {
            return $ordered[0];
        }

        return $ordered[0] . ' → ' . $ordered[count($ordered) - 1];
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function lineupSectionTitle(array $lineup, array $rows): string
    {
        $t = trim((string) ($lineup['title'] ?? ''));
        if ($t !== '') {
            return $t;
        }
        if ($rows !== []) {
            return 'TOP ACTS';
        }

        return 'TOP TIER LINEUP';
    }
}
