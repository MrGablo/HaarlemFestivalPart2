<?php

declare(strict_types=1);

namespace App\ViewModels;


final class DanceHomePageViewModel
{
    public string $pageTitle;

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
