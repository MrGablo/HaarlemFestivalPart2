<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\JazzHomePageContentViewModel;

final class JazzHomePageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'Jazz_Homepage';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new JazzHomePageContentViewModel(
            $normalized['hero'] ?? [],
            $normalized['intro'] ?? [],
            $normalized['day_ticket_pass'] ?? [],
            $normalized['schedule'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Hero',
                'description' => 'Hero banner content for the jazz landing page.',
                'fields' => [
                    [
                        'key' => 'hero',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            [
                                'key' => 'background_image',
                                'type' => 'object',
                                'label' => 'Background Image',
                                'coerceStringKey' => 'src',
                                'fields' => [
                                    ['key' => 'src', 'type' => 'text', 'label' => 'Image Path'],
                                    ['key' => 'alt', 'type' => 'text', 'label' => 'Alt Text'],
                                ],
                            ],
                            ['key' => 'subtitle_html', 'type' => 'wysiwyg', 'label' => 'Subtitle'],
                            [
                                'key' => 'primary_button',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'label', 'type' => 'text', 'label' => 'Primary Button Label'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Introduction',
                'description' => 'Intro heading and body copy.',
                'fields' => [
                    [
                        'key' => 'intro',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                            ['key' => 'body_html', 'type' => 'wysiwyg', 'label' => 'Body'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Day Ticket Pass',
                'description' => 'Authored copy for the pass section. Buttons remain derived from pass data.',
                'fields' => [
                    [
                        'key' => 'day_ticket_pass',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Schedule',
                'description' => 'Schedule headings and authored filter labels. Event rows stay database-driven.',
                'fields' => [
                    [
                        'key' => 'schedule',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Schedule Title'],
                            ['key' => 'venue_title', 'type' => 'text', 'label' => 'Venue Title'],
                            [
                                'key' => 'filters',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'group_label', 'type' => 'text', 'label' => 'Group Label'],
                                    [
                                        'key' => 'days',
                                        'type' => 'repeater',
                                        'label' => 'Day Filters',
                                        'addLabel' => 'Add day filter',
                                        'fields' => [
                                            ['key' => 'value', 'type' => 'text', 'label' => 'Value'],
                                            ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'key' => 'all_events_button',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'label', 'type' => 'text', 'label' => 'Button Label'],
                                    ['key' => 'href', 'type' => 'text', 'label' => 'Button Link'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}