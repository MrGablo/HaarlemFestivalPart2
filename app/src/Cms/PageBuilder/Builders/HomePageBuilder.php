<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\HomePageContentViewModel;

final class HomePageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'HomePage';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);
        $categories = is_array($normalized['schedule']['filters']['tabs'] ?? null)
            ? $normalized['schedule']['filters']['tabs']
            : [];

        return new HomePageContentViewModel($normalized, $categories);
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Hero',
                'description' => 'Main homepage heading, intro copy, and image collage.',
                'fields' => [
                    [
                        'key' => 'hero',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'subtitle_html', 'type' => 'wysiwyg', 'label' => 'Subtitle'],
                            [
                                'key' => 'images',
                                'type' => 'repeater',
                                'label' => 'Hero Images',
                                'itemType' => 'image',
                                'itemField' => ['type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                                'addLabel' => 'Add image',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Introduction',
                'description' => 'Intro section body and headline stats.',
                'fields' => [
                    [
                        'key' => 'introduction',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Heading'],
                            ['key' => 'body_html', 'type' => 'wysiwyg', 'label' => 'Body'],
                            [
                                'key' => 'statistics',
                                'type' => 'repeater',
                                'label' => 'Statistics',
                                'addLabel' => 'Add statistic',
                                'fields' => [
                                    ['key' => 'value', 'type' => 'text', 'label' => 'Value'],
                                    ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Highlighted Events',
                'description' => 'Cards shown in the homepage spotlight grid.',
                'fields' => [
                    [
                        'key' => 'highlighted_events',
                        'type' => 'repeater',
                        'label' => 'Highlighted Events',
                        'addLabel' => 'Add event card',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'date', 'type' => 'text', 'label' => 'Date'],
                            ['key' => 'location', 'type' => 'text', 'label' => 'Location'],
                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Categories',
                'description' => 'Category tiles rendered on the homepage.',
                'fields' => [
                    [
                        'key' => 'categories',
                        'type' => 'repeater',
                        'label' => 'Categories',
                        'addLabel' => 'Add category',
                        'fields' => [
                            ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Newsletter',
                'description' => 'Newsletter signup content and preference chips.',
                'fields' => [
                    [
                        'key' => 'newsletter',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'logo', 'type' => 'image', 'storage' => 'string', 'label' => 'Logo'],
                            ['key' => 'description_html', 'type' => 'wysiwyg', 'label' => 'Description'],
                            [
                                'key' => 'preferences',
                                'type' => 'repeater',
                                'label' => 'Preferences',
                                'itemType' => 'text',
                                'itemField' => ['type' => 'text', 'label' => 'Preference'],
                                'addLabel' => 'Add preference',
                            ],
                            ['key' => 'privacy_text', 'type' => 'text', 'label' => 'Privacy Text'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Legacy Filters',
                'description' => 'Optional tab labels still exposed through the existing home page view model.',
                'fields' => [
                    [
                        'key' => 'schedule',
                        'type' => 'object',
                        'fields' => [
                            [
                                'key' => 'filters',
                                'type' => 'object',
                                'fields' => [
                                    [
                                        'key' => 'tabs',
                                        'type' => 'repeater',
                                        'label' => 'Filter Tabs',
                                        'itemType' => 'text',
                                        'itemField' => ['type' => 'text', 'label' => 'Tab'],
                                        'addLabel' => 'Add filter tab',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}