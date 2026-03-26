<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\JazzArtistPageContentViewModel;

final class JazzArtistPageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'Jazz_Detail_Page';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new JazzArtistPageContentViewModel(
            $normalized['artist'] ?? [],
            $normalized['tabs'] ?? [],
            $normalized['events'] ?? [],
            $normalized['career_highlights'] ?? [],
            $normalized['albums'] ?? [],
            $normalized['about'] ?? [],
            $normalized['band_members'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Artist Hero',
                'description' => 'Main artist hero image, breadcrumb, and media rail.',
                'fields' => [
                    [
                        'key' => 'artist',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'name', 'type' => 'text', 'label' => 'Artist Name'],
                            ['key' => 'cover_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Cover Image'],
                            [
                                'key' => 'breadcrumb',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'back_href', 'type' => 'text', 'label' => 'Back Link'],
                                    ['key' => 'back_label', 'type' => 'text', 'label' => 'Back Label'],
                                    ['key' => 'current', 'type' => 'text', 'label' => 'Current Label'],
                                ],
                            ],
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker'],
                            ['key' => 'hero_title', 'type' => 'text', 'label' => 'Hero Title'],
                            ['key' => 'hero_subtitle', 'type' => 'text', 'label' => 'Hero Subtitle'],
                            [
                                'key' => 'hero_media',
                                'type' => 'object',
                                'fields' => [
                                    [
                                        'key' => 'main',
                                        'type' => 'object',
                                        'fields' => [
                                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'Main Image'],
                                        ],
                                    ],
                                    [
                                        'key' => 'secondary',
                                        'type' => 'repeater',
                                        'label' => 'Secondary Media',
                                        'addLabel' => 'Add secondary media',
                                        'fields' => [
                                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                                            ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Tabs',
                'description' => 'Tab labels and defaults for the artist page.',
                'fields' => [
                    [
                        'key' => 'tabs',
                        'type' => 'object',
                        'fields' => [
                            [
                                'key' => 'default',
                                'type' => 'select',
                                'label' => 'Default Tab',
                                'default' => 'events',
                                'options' => [
                                    ['value' => 'events', 'label' => 'Events'],
                                    ['value' => 'career', 'label' => 'Career'],
                                    ['value' => 'album', 'label' => 'Album'],
                                ],
                            ],
                            [
                                'key' => 'labels',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'events', 'type' => 'text', 'label' => 'Events Label'],
                                    ['key' => 'career', 'type' => 'text', 'label' => 'Career Label'],
                                    ['key' => 'album', 'type' => 'text', 'label' => 'Album Label'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Ticketing',
                'description' => 'Static copy around event ticket actions.',
                'fields' => [
                    [
                        'key' => 'events',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'ticket_button_label', 'type' => 'text', 'label' => 'Fallback Ticket Button Label'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Career Highlights',
                'description' => 'Either rich text columns or bullet list fallback.',
                'fields' => [
                    [
                        'key' => 'career_highlights',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'left_html', 'type' => 'wysiwyg', 'label' => 'Left Column HTML'],
                            ['key' => 'right_html', 'type' => 'wysiwyg', 'label' => 'Right Column HTML'],
                            [
                                'key' => 'left',
                                'type' => 'repeater',
                                'label' => 'Left Fallback Items',
                                'itemType' => 'text',
                                'itemField' => ['type' => 'text', 'label' => 'Item'],
                                'addLabel' => 'Add left item',
                            ],
                            [
                                'key' => 'right',
                                'type' => 'repeater',
                                'label' => 'Right Fallback Items',
                                'itemType' => 'text',
                                'itemField' => ['type' => 'text', 'label' => 'Item'],
                                'addLabel' => 'Add right item',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Albums',
                'description' => 'Album cards and descriptions.',
                'fields' => [
                    [
                        'key' => 'albums',
                        'type' => 'repeater',
                        'label' => 'Albums',
                        'addLabel' => 'Add album',
                        'fields' => [
                            ['key' => 'artist', 'type' => 'text', 'label' => 'Artist'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Album Title'],
                            ['key' => 'description', 'type' => 'textarea', 'label' => 'Plain Description'],
                            ['key' => 'description_html', 'type' => 'wysiwyg', 'label' => 'Rich Description'],
                            [
                                'key' => 'image',
                                'type' => 'image',
                                'storage' => 'object',
                                'fields' => [
                                    ['key' => 'alt', 'type' => 'text', 'label' => 'Alt Text'],
                                    ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'About And Band',
                'description' => 'About copy and band members list.',
                'fields' => [
                    [
                        'key' => 'about',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'About Title'],
                            ['key' => 'html', 'type' => 'wysiwyg', 'label' => 'About HTML'],
                            ['key' => 'text', 'type' => 'textarea', 'label' => 'About Text'],
                        ],
                    ],
                    [
                        'key' => 'band_members',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Band Title'],
                            [
                                'key' => 'items',
                                'type' => 'repeater',
                                'label' => 'Band Members',
                                'itemType' => 'text',
                                'itemField' => ['type' => 'text', 'label' => 'Member'],
                                'addLabel' => 'Add member',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}