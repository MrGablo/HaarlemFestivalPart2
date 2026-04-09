<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\YummyDetailPageContentViewModel;

final class YummyDetailPageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'Yummy_Detail_Page';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new YummyDetailPageContentViewModel(
            $normalized['heroSection'] ?? [],
            $normalized['aboutSection'] ?? [],
            $normalized['contentSection1'] ?? [],
            $normalized['chefSection'] ?? [],
            $normalized['menuSection'] ?? [],
            (string)($normalized['informationBlock'] ?? '')
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Hero Section',
                'description' => 'Image gallery displayed at the top of the restaurant page.',
                'fields' => [
                    [
                        'key' => 'heroSection',
                        'type' => 'object',
                        'fields' => [
                            [
                                'key' => 'images',
                                'type' => 'repeater',
                                'label' => 'Gallery Images',
                                'addLabel' => 'Add image',
                                'fields' => [
                                    ['key' => 'path', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                                    ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'About Section',
                'description' => 'Main description text about the restaurant.',
                'fields' => [
                    [
                        'key' => 'aboutSection',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'descriptionHtml', 'type' => 'wysiwyg', 'label' => 'Description'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Content Section 1 (Amuse-Bouche)',
                'description' => 'A descriptive section often used for Amuse-Bouche highlights.',
                'fields' => [
                    [
                        'key' => 'contentSection1',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'html', 'type' => 'wysiwyg', 'label' => 'HTML Content'],
                            [
                                'key' => 'images',
                                'type' => 'repeater',
                                'label' => 'Images',
                                'addLabel' => 'Add image',
                                'fields' => [
                                    ['key' => 'path', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                                    ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Chef Section',
                'description' => 'Information about the chef.',
                'fields' => [
                    [
                        'key' => 'chefSection',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'html', 'type' => 'wysiwyg', 'label' => 'HTML Content'],
                            ['key' => 'imagePath', 'type' => 'image', 'storage' => 'string', 'label' => 'Chef Image'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Menu Section',
                'description' => 'The restaurant menu highlights.',
                'fields' => [
                    [
                        'key' => 'menuSection',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'html', 'type' => 'wysiwyg', 'label' => 'HTML Content'],
                            ['key' => 'imagePath', 'type' => 'image', 'storage' => 'string', 'label' => 'Menu Image'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Information Block',
                'description' => 'General information for the restaurant details.',
                'fields' => [
                    [
                        'key' => 'informationBlock',
                        'type' => 'wysiwyg',
                        'label' => 'Information HTML',
                    ],
                ],
            ],
        ];
    }
}
