<?php

declare(strict_types=1);

namespace App\ViewModels;


final class DanceHomePageViewModel
{
    public string $pageTitle;

    /** @var array{titleLine1: string, titleLine2: string, subtitleHtml: string, primaryButtonLabel: string, stripText: string} */
    public array $hero;

    /** @var array{kicker: string, bodyHtml: string, sideImageAlt: string, statsLine: string} */
    public array $intro;

    public string $lineupTitle;

    /** @var list<array{name: string, imageUrl: string, alt: string}> */
    public array $lineupArtists;

    public string $timetableTitle;

    public string $timetableDateRange;

    /** @var array{label: string, note: string, priceLabel: string, eventId: int}|null */
    public ?array $allAccess;

    /** @var list<array{dayKey: string, dayLabel: string, passLabel: string, passPriceLabel: string, passEventId: int, sessions: list<array{title: string, tag: string, tagSpecial: bool, timeRange: string, venueName: string, priceLabel: string, eventId: int}>}> */
    public array $timetableDays;

    public bool $timetableHasRows;

    public string $heroBackgroundImageUrl;

    public string $introSideImageUrl;

    public string $timetableSectionBackgroundUrl;

    /**
     * @param array{titleLine1: string, titleLine2: string, subtitleHtml: string, primaryButtonLabel: string, stripText: string} $hero
     * @param array{kicker: string, bodyHtml: string, sideImageAlt: string, statsLine: string} $intro
     * @param list<array{name: string, imageUrl: string, alt: string}> $lineupArtists
     * @param array{label: string, note: string, priceLabel: string, eventId: int}|null $allAccess
     * @param list<array{dayKey: string, dayLabel: string, passLabel: string, passPriceLabel: string, passEventId: int, sessions: list<array{title: string, tag: string, tagSpecial: bool, timeRange: string, venueName: string, priceLabel: string, eventId: int}>}> $timetableDays
     */
    public function __construct(
        string $pageTitle,
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
