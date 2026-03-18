<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use App\Repositories\DanceHomeRepository;
use App\Repositories\Interfaces\IPageRepository;
use App\Utils\Media;
use App\ViewModels\DanceHomePageViewModel;

/**
 * Builds the Dance homepage ViewModel: timetable, lineup headlines, stats and date range
 * from {@see DanceHomeRepository} (DanceEvent + Event + Venue); hero/intro copy from CMS Page JSON.
 */
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

    private const DEFAULT_HERO_SUBTITLE_LINES = [
        'Discover Haarlem\'s vibrant nightlife',
        'Experience top international DJs',
        'Celebrate dance culture in the heart of the city',
    ];

    private const DEFAULT_INTRO_PARAS = [
        'The Dance Event is where Haarlem truly comes alive. As the sun goes down, the city switches into a completely different mode — neon lights, deep bass, and a crowd that\'s ready to move. World-class DJs, immersive light shows, and the city\'s vibrant nightlife all come together to create nights that feel electric.',
        'Here, it doesn\'t matter if you\'re a die-hard rave lover or someone who\'s just curious about the scene. Maybe you come for the heavy drops, maybe for the atmosphere, or maybe you just want to dance with friends until your legs can\'t keep up — either way, you\'ll fit right in.',
        'Across the festival\'s 3 days, Haarlem transforms into a playground for rhythmic energy: back-to-back DJ sets, intimate experimental sessions, and massive stages that pull you in with sound you can feel straight in your chest.',
        'So dive into the lights, join the crowd, and let yourself get carried by the rhythm. This is Dance — where excitement, connection, and pure nightlife energy meet.',
    ];

    public function __construct(
        private IPageRepository $pageRepo,
        private DanceHomeRepository $danceRepo,
    ) {}

    public function buildViewModel(): DanceHomePageViewModel
    {
        $content = $this->pageRepo->getPageContentByType('Dance_Homepage');
        $danceBase = Config::getDanceBasePath();
        $orderAdd = Config::getOrderItemAddPath();

        $hero = $content['hero'] ?? [];
        $intro = $content['intro'] ?? [];
        if (!is_array($intro)) {
            $intro = [];
        }
        $lineup = $content['lineup'] ?? [];
        $timetableCms = $content['timetable'] ?? [];

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

        $subtitleHtml = $hero['subtitle_html'] ?? null;
        $subtitleLines = $hero['subtitle'] ?? null;
        $subtitleMode = 'default';
        if (is_string($subtitleHtml) && $subtitleHtml !== '') {
            $subtitleMode = 'html';
        } elseif (is_array($subtitleLines) && $subtitleLines !== []) {
            $subtitleMode = 'lines';
        }

        $bodyHtml = $intro['body_html'] ?? null;
        $paras = $intro['paragraphs'] ?? null;
        $introBodyMode = 'default';
        if (is_string($bodyHtml) && $bodyHtml !== '') {
            $introBodyMode = 'html';
        } elseif (is_array($paras) && $paras !== []) {
            $introBodyMode = 'paragraphs';
        }

        $stats = $intro['stats'] ?? $hero['stats'] ?? ['3 days', '6 DJs', '2490 min'];
        $stats = is_array($stats) ? $stats : [];

        $rows = $this->danceRepo->findDanceTimetableRows();
        $statsLine = $this->buildStatsLineFromTimetable($rows, $stats);
        $dateRangeFromDb = $this->buildDateRangeLabelFromTimetable($rows);

        $lineupArtists = $this->buildLineupArtistsFromDatabase($lineup);
        [$allAccess, $days] = $this->buildTimetableFromRows($rows, $timetableCms);

        $pageTitle = (string) ($hero['title'] ?? 'Dance');
        $heroBgUrl = Config::publicAssetUrl($heroBg);
        $introImgUrl = Config::publicAssetUrl($introImg);
        $timetableBgUrl = Config::publicAssetUrl('assets/img/dance-assets/dance-timetable-texture.png');

        $lineupWithUrls = [];
        foreach ($lineupArtists as $a) {
            $lineupWithUrls[] = [
                'name' => $a['name'],
                'imageUrl' => Config::publicAssetUrl($a['imageSrc']),
                'alt' => $a['alt'],
            ];
        }

        return new DanceHomePageViewModel(
            $pageTitle,
            $danceBase,
            $orderAdd,
            [
                'titleLine1' => $parts[0],
                'titleLine2' => $parts[1],
                'subtitleMode' => $subtitleMode,
                'subtitleHtml' => is_string($subtitleHtml) ? $subtitleHtml : '',
                'subtitleLines' => is_array($subtitleLines) ? array_map('strval', $subtitleLines) : [],
                'defaultSubtitleLines' => self::DEFAULT_HERO_SUBTITLE_LINES,
                'primaryButtonLabel' => (string) ($hero['primary_button']['label'] ?? 'Buy ticket'),
                'stripText' => (string) ($hero['strip_text'] ?? 'HAARLEM FESTIVAL DANCE'),
            ],
            [
                'kicker' => (string) ($intro['kicker'] ?? 'Let Haarlem\'s music welcome you in'),
                'bodyMode' => $introBodyMode,
                'bodyHtml' => is_string($bodyHtml) ? $bodyHtml : '',
                'paragraphs' => is_array($paras) ? array_map('strval', $paras) : self::DEFAULT_INTRO_PARAS,
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

    private function normaliseAsset(string $path, string $fallback): string
    {
        $path = trim($path);
        if ($path === '' || strpos($path, 'assets/img/dance-assets/') !== 0) {
            return $fallback;
        }
        $base = basename($path);

        return in_array($base, self::ALLOWED_DANCE_ASSETS, true) ? $path : $fallback;
    }

    /**
     * Lineup names from {@see DanceHomeRepository::findDanceLineupHeadlines} (Event titles of session rows).
     * Falls back to CMS artists, then static defaults.
     *
     * @return list<array{name: string, imageSrc: string, alt: string}>
     */
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
