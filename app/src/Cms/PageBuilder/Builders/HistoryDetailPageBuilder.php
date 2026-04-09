<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\HistoryDetailPageContentViewModel;

final class HistoryDetailPageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'History_Detail_Page';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new HistoryDetailPageContentViewModel(
            $normalized['meta'] ?? [],
            $normalized['hero'] ?? [],
            $normalized['story_blocks'] ?? [],
            $normalized['map_card'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Meta',
                'description' => 'Routing and listing metadata for the history detail page.',
                'fields' => [
                    [
                        'key' => 'meta',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'slug', 'type' => 'text', 'label' => 'Slug'],
                            ['key' => 'sort_order', 'type' => 'number', 'mode' => 'int', 'label' => 'Sort Order', 'default' => 0],
                            ['key' => 'listing_title', 'type' => 'text', 'label' => 'Listing Title'],
                            ['key' => 'listing_summary', 'type' => 'textarea', 'label' => 'Listing Summary'],
                            ['key' => 'listing_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Listing Image'],
                            [
                                'key' => 'navigation',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'back_href', 'type' => 'text', 'label' => 'Back Link', 'default' => '/history'],
                                    ['key' => 'back_label', 'type' => 'text', 'label' => 'Back Label', 'default' => 'Back'],
                                ],
                            ],
                            [
                                'key' => 'map_marker',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'x', 'type' => 'number', 'mode' => 'float', 'label' => 'Map Marker X (%)', 'default' => 50],
                                    ['key' => 'y', 'type' => 'number', 'mode' => 'float', 'label' => 'Map Marker Y (%)', 'default' => 50],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Hero And Gallery',
                'description' => 'Main title and supporting gallery cluster for the landmark detail page.',
                'fields' => [
                    [
                        'key' => 'hero',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'main_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Main Image'],
                            [
                                'key' => 'gallery',
                                'type' => 'repeater',
                                'label' => 'Gallery Images',
                                'addLabel' => 'Add gallery image',
                                'fields' => [
                                    ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                                    ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Story Blocks',
                'description' => 'Alternating landmark story rows with image and rich text.',
                'fields' => [
                    [
                        'key' => 'story_blocks',
                        'type' => 'repeater',
                        'label' => 'Story Blocks',
                        'addLabel' => 'Add story block',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Block Title'],
                            ['key' => 'body_html', 'type' => 'wysiwyg', 'label' => 'Body HTML'],
                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                            [
                                'key' => 'image_position',
                                'type' => 'select',
                                'label' => 'Image Position',
                                'default' => 'left',
                                'options' => [
                                    ['value' => 'left', 'label' => 'Left'],
                                    ['value' => 'right', 'label' => 'Right'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Map Card',
                'description' => 'Card copy rendered on top of the route map.',
                'fields' => [
                    [
                        'key' => 'map_card',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Card Title'],
                            ['key' => 'summary', 'type' => 'textarea', 'label' => 'Card Summary'],
                            ['key' => 'button_label', 'type' => 'text', 'label' => 'Button Label', 'default' => 'Bekijk locatie'],
                        ],
                    ],
                ],
            ],
        ];
    }
}