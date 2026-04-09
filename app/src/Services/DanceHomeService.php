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
        $dayLabels = [];
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

            $dayMeta = $this->resolveDayMeta($r);
            if ($dayMeta === null) {
                continue;
            }
            $dayKey = $dayMeta['key'];
            if (!in_array($dayKey, $dayOrder, true)) {
                $dayOrder[] = $dayKey;
            }
            $dayLabels[$dayKey] = $dayMeta['label'];
            if ($kind === 'day_pass') {
                $passByDay[$dayKey] = $r;
            } elseif ($kind === 'session') {
                $sessionsByDay[$dayKey][] = $r;
            }
        }

        $days = [];
        foreach ($dayOrder as $dayKey) {
            $pass = $passByDay[$dayKey] ?? null;
            $passEid = $pass !== null ? (int) ($pass['event_id'] ?? 0) : 0;
            $days[] = [
                'dayKey' => (string)$dayKey,
                'dayLabel' => (string) ($dayLabels[$dayKey] ?? $dayKey),
                'passLabel' => $pass !== null
                    ? (string) ($pass['title'] ?? 'DAY PASS')
                    : 'DAY PASS',
                'passPriceLabel' => $pass !== null
                    ? $this->formatEuro((float) ($pass['price'] ?? 0))
                    : '',
                'passEventId' => $passEid,
                'sessions' => $this->mapSessions($sessionsByDay[$dayKey] ?? []),
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
            $start = $this->formatSessionTimeValue($r['session_start'] ?? null);
            $end = $this->formatSessionTimeValue($r['session_end'] ?? null);
            $timeRange = '';
            if ($start !== '' && $end !== '') {
                $timeRange = $start . ' - ' . $end;
            } elseif ($start !== '') {
                $timeRange = $start;
            } elseif ($end !== '') {
                $timeRange = $end;
            }
            $out[] = [
                'title' => (string) ($r['title'] ?? ''),
                'tag' => (string) ($r['session_tag'] ?? ''),
                'tagSpecial' => !empty($r['tag_special']),
                'timeRange' => $timeRange,
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
        $dbArtists = $this->danceRepo->findDanceLineupArtists(6);
        $dbHeadlines = $this->danceRepo->findDanceLineupHeadlines(6);
        $cycle = array_column(self::DEFAULT_LINEUP, 'image');
        $n = count($cycle);
        if ($n === 0) {
            $cycle = ['assets/img/dance-assets/dj-martin.png'];
            $n = 1;
        }

        if ($dbArtists !== []) {
            $out = [];
            foreach ($dbArtists as $i => $artist) {
                $artistName = trim((string) ($artist['name'] ?? ''));
                if ($artistName === '') {
                    continue;
                }
                $out[] = [
                    'name' => mb_strtoupper($artistName, 'UTF-8'),
                    'imageSrc' => $cycle[$i % $n],
                    'alt' => $artistName,
                ];
            }
            if ($out !== []) {
                return $out;
            }
        }

        if ($dbHeadlines !== []) {
            $artistNames = [];
            foreach ($dbHeadlines as $h) {
                foreach ($this->extractArtistNames((string) ($h['title'] ?? '')) as $artistName) {
                    if (!in_array($artistName, $artistNames, true)) {
                        $artistNames[] = $artistName;
                    }
                    if (count($artistNames) >= 6) {
                        break 2;
                    }
                }
            }

            if ($artistNames !== []) {
                $out = [];
                foreach ($artistNames as $i => $artistName) {
                    $out[] = [
                        'name' => mb_strtoupper($artistName, 'UTF-8'),
                        'imageSrc' => $cycle[$i % $n],
                        'alt' => $artistName,
                    ];
                }

                return $out;
            }

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

    /** @return list<string> */
    private function extractArtistNames(string $rawTitle): array
    {
        $title = trim($rawTitle);
        if ($title === '') {
            return [];
        }

        // Remove day/time hints often present in event titles.
        $title = (string) preg_replace('/\s*\([^)]*\)\s*/', ' ', $title);
        $title = (string) preg_replace('/\s+-\s+/', ' / ', $title);
        $title = (string) preg_replace('/\s+&\s+/i', ' / ', $title);
        $title = (string) preg_replace('/\s+b2b\s+/i', ' / ', $title);

        $parts = preg_split('/\s*\/\s*/', $title) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $name = trim((string) preg_replace('/\s+/', ' ', $part));
            if ($name === '') {
                continue;
            }
            if (preg_match('/^(all[\s-]*access|day\s*pass)$/i', $name) === 1) {
                continue;
            }
            $out[] = $name;
        }

        return $out;
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
            $dayMeta = $this->resolveDayMeta($r);
            if ($dayMeta !== null && ($kind === 'session' || $kind === 'day_pass' || $kind === 'all_access')) {
                $daysSeen[$dayMeta['key']] = true;
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
            $dayMeta = $this->resolveDayMeta($r);
            if ($dayMeta === null) {
                continue;
            }
            $d = $dayMeta['label'];
            if (!in_array($d, $ordered, true)) {
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
     * @param array<string, mixed> $row
     * @return array{key: string, label: string}|null
     */
    private function resolveDayMeta(array $row): ?array
    {
        $rawLabel = trim((string) ($row['day_display_label'] ?? ''));
        $dayDate = $this->parseDateTimeValue($row['session_start'] ?? null)
            ?? $this->parseDateTimeValue($row['session_end'] ?? null)
            ?? $this->parseDateTimeValue($rawLabel);

        if ($dayDate instanceof \DateTimeImmutable) {
            return [
                'key' => $dayDate->format('Y-m-d'),
                'label' => $dayDate->format('l F jS'),
            ];
        }

        if ($rawLabel === '') {
            return null;
        }

        return ['key' => $rawLabel, 'label' => $rawLabel];
    }

    private function formatSessionTimeValue(mixed $value): string
    {
        $dt = $this->parseDateTimeValue($value);
        if ($dt instanceof \DateTimeImmutable) {
            return $dt->format('H:i');
        }

        if (!is_scalar($value)) {
            return '';
        }
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $text) === 1) {
            return substr($text, 0, 5);
        }

        return $text;
    }

    private function parseDateTimeValue(mixed $value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }
        if (!is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($text);
        } catch (\Throwable) {
            return null;
        }
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
