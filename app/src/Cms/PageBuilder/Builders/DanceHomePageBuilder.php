<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\DanceHomePageContentViewModel;

final class DanceHomePageBuilder extends AbstractPageViewModelBuilder
{
    /** @param array<string, mixed> $input */
    public function normalizeInput(array $input): array
    {
        $normalized = parent::normalizeInput($input);

        $normalized['timetable'] = is_array($normalized['timetable'] ?? null) ? $normalized['timetable'] : [];
        $timetable = $normalized['timetable'];

        $title = trim((string)($timetable['title'] ?? ''));
        if ($title === '') {
            $timetable['title'] = 'Plan your night';
        }

        $dateRange = trim((string)($timetable['date_range'] ?? ''));
        if ($dateRange === '') {
            $timetable['date_range'] = 'Friday July 25th → Sunday July 27th';
        }

        $passes = is_array($timetable['passes'] ?? null) ? $timetable['passes'] : [];
        if ($passes === []) {
            $passes = [[
                'label' => 'All-Access Pass 3 Days',
                'note' => 'No guaranteed seats',
            ]];
        } else {
            foreach ($passes as $idx => $pass) {
                if (!is_array($pass)) {
                    continue;
                }
                $label = trim((string)($pass['label'] ?? ''));
                if ($label === '') {
                    $passes[$idx]['label'] = 'Pass';
                }
            }
        }

        $timetable['passes'] = $passes;
        $normalized['timetable'] = $timetable;

        return $normalized;
    }

    public function pageType(): string
    {
        return 'Dance_Homepage';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new DanceHomePageContentViewModel(
            $normalized['hero'] ?? [],
            $normalized['intro'] ?? [],
            $normalized['lineup'] ?? [],
            $normalized['timetable'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Hero',
                'description' => 'Dance hero content. Ticket buttons and strip remain rendered from this authored content.',
                'fields' => [
                    [
                        'key' => 'hero',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            [
                                'key' => 'background_image',
                                'type' => 'image',
                                'storage' => 'object',
                                'fields' => [
                                    ['key' => 'alt', 'type' => 'text', 'label' => 'Background Alt Text'],
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
                            ['key' => 'strip_text', 'type' => 'text', 'label' => 'Marquee Strip Text'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Introduction',
                'description' => 'Dance intro copy and supporting image.',
                'fields' => [
                    [
                        'key' => 'intro',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker'],
                            ['key' => 'body_html', 'type' => 'wysiwyg', 'label' => 'Body'],
                            [
                                'key' => 'side_image',
                                'type' => 'image',
                                'storage' => 'object',
                                'fields' => [
                                    ['key' => 'alt', 'type' => 'text', 'label' => 'Side Image Alt Text'],
                                ],
                            ],
                            [
                                'key' => 'stats',
                                'type' => 'repeater',
                                'label' => 'Stats Line Tokens',
                                'itemType' => 'text',
                                'itemField' => ['type' => 'text', 'label' => 'Stat Token'],
                                'addLabel' => 'Add stat token',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Lineup',
                'description' => 'Editable lineup title and artist cards.',
                'fields' => [
                    [
                        'key' => 'lineup',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Section Title'],
                            [
                                'key' => 'artists',
                                'type' => 'repeater',
                                'label' => 'Lineup Artists',
                                'addLabel' => 'Add artist',
                                'fields' => [
                                    ['key' => 'name', 'type' => 'text', 'label' => 'Artist Name'],
                                    [
                                        'key' => 'image',
                                        'type' => 'image',
                                        'storage' => 'object',
                                        'fields' => [
                                            ['key' => 'alt', 'type' => 'text', 'label' => 'Alt Text'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Timetable Copy',
                'description' => 'Only authored headings and pass labels are editable here. Session rows remain database-driven.',
                'fields' => [
                    [
                        'key' => 'timetable',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title', 'default' => 'Plan your night'],
                            ['key' => 'date_range', 'type' => 'text', 'label' => 'Date Range', 'default' => 'Friday July 25th → Sunday July 27th'],
                            [
                                'key' => 'passes',
                                'type' => 'repeater',
                                'label' => 'Pass Copy',
                                'addLabel' => 'Add pass copy row',
                                'fields' => [
                                    ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                                    ['key' => 'note', 'type' => 'text', 'label' => 'Note'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}