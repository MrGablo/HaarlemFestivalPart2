<?php

declare(strict_types=1);

namespace App\ViewModels;


final class DanceHomePageViewModel
{
    public string $pageTitle;

    // Hero: two title lines, subtitle HTML, button label, marquee strip text.
    public array $hero;

    // Intro: kicker, body HTML, image alt text, stats line under the image.
    public array $intro;

    public string $lineupTitle;

    // Lineup: each item has name, image URL, alt, optional link to artist page.
    public array $lineupArtists;

    public string $timetableTitle;

    public string $timetableDateRange;

    // Optional all-access pass row (label, note, price, Stripe/event id), or null.
    public ?array $allAccess;

    // Timetable: one entry per day with day pass + list of sessions.
    public array $timetableDays;

    public bool $timetableHasRows;

    public string $heroBackgroundImageUrl;

    public string $introSideImageUrl;

    public string $timetableSectionBackgroundUrl;

    // Packs all home page data for dance_home.php and partials.
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
