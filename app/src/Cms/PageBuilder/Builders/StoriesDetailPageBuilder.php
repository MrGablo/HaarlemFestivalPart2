<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\StoriesDetailPageContentViewModel;

final class StoriesDetailPageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'Stories_Detail_Page';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new StoriesDetailPageContentViewModel(
            $normalized['story'] ?? [],
            $normalized['event_card'] ?? [],
            $normalized['intro'] ?? [],
            $normalized['origin'] ?? [],
            $normalized['video'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'story hero',
                'description' => 'main hero content and media rail',
                'fields' => [
                    [
                        'key' => 'story',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'name', 'type' => 'text', 'label' => 'name'],
                            ['key' => 'cover_image', 'type' => 'image', 'storage' => 'string', 'label' => 'cover image'],
                            [
                                'key' => 'breadcrumb',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'back_href', 'type' => 'text', 'label' => 'back href'],
                                    ['key' => 'back_label', 'type' => 'text', 'label' => 'back label'],
                                    ['key' => 'current', 'type' => 'text', 'label' => 'current label'],
                                ],
                            ],
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'kicker'],
                            ['key' => 'hero_title', 'type' => 'text', 'label' => 'hero title'],
                            ['key' => 'hero_subtitle', 'type' => 'text', 'label' => 'hero subtitle'],
                            ['key' => 'hero_body_html', 'type' => 'wysiwyg', 'label' => 'hero body html'],
                            [
                                'key' => 'hero_media',
                                'type' => 'object',
                                'fields' => [
                                    [
                                        'key' => 'main',
                                        'type' => 'object',
                                        'fields' => [
                                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'main image'],
                                            ['key' => 'caption', 'type' => 'text', 'label' => 'main caption'],
                                        ],
                                    ],
                                    [
                                        'key' => 'secondary',
                                        'type' => 'repeater',
                                        'label' => 'secondary media',
                                        'addLabel' => 'add secondary media',
                                        'fields' => [
                                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'image'],
                                            ['key' => 'caption', 'type' => 'text', 'label' => 'caption'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'event card',
                'description' => 'reserve card labels and static copy',
                'fields' => [
                    [
                        'key' => 'event_card',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'reserve_title', 'type' => 'text', 'label' => 'reserve title'],
                            ['key' => 'price_label', 'type' => 'text', 'label' => 'price label'],
                            ['key' => 'price_suffix', 'type' => 'text', 'label' => 'price suffix'],
                            ['key' => 'quantity_label', 'type' => 'text', 'label' => 'quantity label'],
                            ['key' => 'total_label', 'type' => 'text', 'label' => 'total label'],
                            ['key' => 'button_label', 'type' => 'text', 'label' => 'button label'],
                            ['key' => 'about_title', 'type' => 'text', 'label' => 'about title'],
                            ['key' => 'about_text', 'type' => 'textarea', 'label' => 'about text'],
                            [
                                'key' => 'meta_labels',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'date', 'type' => 'text', 'label' => 'date label'],
                                    ['key' => 'time', 'type' => 'text', 'label' => 'time label'],
                                    ['key' => 'place', 'type' => 'text', 'label' => 'place label'],
                                    ['key' => 'age_group', 'type' => 'text', 'label' => 'age group label'],
                                    ['key' => 'language', 'type' => 'text', 'label' => 'language label'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'intro section',
                'description' => 'intro image, html and bullets',
                'fields' => [
                    [
                        'key' => 'intro',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'image'],
                            ['key' => 'html', 'type' => 'wysiwyg', 'label' => 'html'],
                            [
                                'key' => 'bullets',
                                'type' => 'repeater',
                                'label' => 'bullets',
                                'itemType' => 'text',
                                'itemField' => ['type' => 'text', 'label' => 'bullet'],
                                'addLabel' => 'add bullet',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'origin section',
                'description' => 'origin image and story copy',
                'fields' => [
                    [
                        'key' => 'origin',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'title'],
                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'image'],
                            ['key' => 'html', 'type' => 'wysiwyg', 'label' => 'html'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'video section',
                'description' => 'video title, copy, embed and thumbnail',
                'fields' => [
                    [
                        'key' => 'video',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'title'],
                            ['key' => 'description', 'type' => 'textarea', 'label' => 'description'],
                            ['key' => 'embed_url', 'type' => 'text', 'label' => 'embed url'],
                            ['key' => 'thumbnail', 'type' => 'image', 'storage' => 'string', 'label' => 'thumbnail'],
                        ],
                    ],
                ],
            ],
        ];
    }
}