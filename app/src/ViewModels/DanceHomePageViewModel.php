<?php

declare(strict_types=1);

namespace App\ViewModels;

/**
 * Presentation model for the Dance homepage: hero, intro, lineup, timetable (DB-driven).
 *
 * @phpstan-type HeroVm array{
 *   titleLine1: string,
 *   titleLine2: string,
 *   subtitleMode: 'html'|'lines'|'default',
 *   subtitleHtml: string,
 *   subtitleLines: list<string>,
 *   defaultSubtitleLines: list<string>,
 *   primaryButtonLabel: string,
 *   stripText: string
 * }
 * @phpstan-type IntroVm array{
 *   kicker: string,
 *   bodyMode: 'html'|'paragraphs'|'default',
 *   bodyHtml: string,
 *   paragraphs: list<string>,
 *   sideImageAlt: string,
 *   statsLine: string
 * }
 * @phpstan-type LineupArtistVm array{name: string, imageUrl: string, alt: string}
 * @phpstan-type TimetableSessionVm array{
 *   title: string,
 *   tag: string,
 *   tagSpecial: bool,
 *   timeRange: string,
 *   venueName: string,
 *   priceLabel: string,
 *   eventId: int
 * }
 * @phpstan-type TimetableDayVm array{
 *   dayLabel: string,
 *   passLabel: string,
 *   passPriceLabel: string,
 *   passEventId: int,
 *   sessions: list<TimetableSessionVm>
 * }
 * @phpstan-type AllAccessVm array{
 *   label: string,
 *   note: string,
 *   priceLabel: string,
 *   eventId: int
 * }
 */
final class DanceHomePageViewModel
{
    public string $pageTitle;

    public string $danceBasePath;

    public string $orderItemAddPath;

    /** @var HeroVm */
    public array $hero;

    /** @var IntroVm */
    public array $intro;

    public string $lineupTitle;

    /** @var list<LineupArtistVm> */
    public array $lineupArtists;

    public string $timetableTitle;

    public string $timetableDateRange;

    /** @var AllAccessVm|null */
    public ?array $allAccess;

    /** @var list<TimetableDayVm> */
    public array $timetableDays;

    public bool $timetableHasRows;

    /** Absolute URL path prefix for dance assets (e.g. /dance + relative image path). */
    public string $heroBackgroundImageUrl;

    public string $introSideImageUrl;

    public string $timetableSectionBackgroundUrl;

    /**
     * @param HeroVm $hero
     * @param IntroVm $intro
     * @param list<LineupArtistVm> $lineupArtists
     * @param AllAccessVm|null $allAccess
     * @param list<TimetableDayVm> $timetableDays
     */
    public function __construct(
        string $pageTitle,
        string $danceBasePath,
        string $orderItemAddPath,
        array $hero,
        array $intro,
        string $lineupTitle,
        array $lineupArtists,
        string $timetableTitle,
        string $timetableDateRange,
        ?array $allAccess,
        array $timetableDays,
        bool $timetableHasRows,
        string $heroBackgroundImageUrl,
        string $introSideImageUrl,
        string $timetableSectionBackgroundUrl
    ) {
        $this->pageTitle = $pageTitle;
        $this->danceBasePath = $danceBasePath;
        $this->orderItemAddPath = $orderItemAddPath;
        $this->hero = $hero;
        $this->intro = $intro;
        $this->lineupTitle = $lineupTitle;
        $this->lineupArtists = $lineupArtists;
        $this->timetableTitle = $timetableTitle;
        $this->timetableDateRange = $timetableDateRange;
        $this->allAccess = $allAccess;
        $this->timetableDays = $timetableDays;
        $this->timetableHasRows = $timetableHasRows;
        $this->heroBackgroundImageUrl = $heroBackgroundImageUrl;
        $this->introSideImageUrl = $introSideImageUrl;
        $this->timetableSectionBackgroundUrl = $timetableSectionBackgroundUrl;
    }
}
