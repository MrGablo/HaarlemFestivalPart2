<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\DanceLocationPageContentViewModel;

final class DanceLocationPageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'Dance_Location_Page';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new DanceLocationPageContentViewModel(
            $normalized['venue'] ?? [],
            $normalized['story'] ?? [],
            $normalized['tickets'] ?? [],
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Venue',
                'description' => 'Public dance location page. Link timetable rows by setting the same Venue ID here as in the database Venue table.',
                'fields' => [
                    [
                        'key' => 'venue',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'name', 'type' => 'text', 'label' => 'Venue display name'],
                            [
                                'key' => 'venue_id',
                                'type' => 'number',
                                'mode' => 'int',
                                'label' => 'Venue ID (matches Venue.venue_id)',
                                'default' => 0,
                            ],
                            ['key' => 'back_href', 'type' => 'text', 'label' => 'Back link', 'default' => '/dance'],
                            ['key' => 'back_label', 'type' => 'text', 'label' => 'Back label', 'default' => 'Dance event'],
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker', 'default' => 'Haarlem Dance'],
                            ['key' => 'hero_title', 'type' => 'text', 'label' => 'Hero title (optional, defaults to name)'],
                            ['key' => 'cover_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Venue photo'],
                            ['key' => 'address', 'type' => 'textarea', 'label' => 'Address'],
                            ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                            ['key' => 'website_url', 'type' => 'text', 'label' => 'Website URL'],
                            ['key' => 'google_maps_url', 'type' => 'text', 'label' => 'Google Maps URL (optional if address is set)'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Extra copy',
                'description' => 'Optional paragraph below the hero.',
                'fields' => [
                    [
                        'key' => 'story',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'intro_html', 'type' => 'wysiwyg', 'label' => 'Intro'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Events list',
                'description' => 'Section title only. Rows are all dance sessions with this venue_id.',
                'fields' => [
                    [
                        'key' => 'tickets',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Section title', 'default' => 'TOTAL EVENTS'],
                            ['key' => 'ticket_button_label', 'type' => 'text', 'label' => 'Button label', 'default' => 'ADD'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
