<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\StoriesHomePageContentViewModel;

final class StoriesHomePageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'Stories_Homepage';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new StoriesHomePageContentViewModel(
            $normalized['hero'] ?? [],
            $normalized['introduction'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Hero',
                'description' => 'Stories hero section.',
                'fields' => [
                    [
                        'key' => 'hero',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'subtitle', 'type' => 'text', 'label' => 'Subtitle'],
                            [
                                'key' => 'image_path',
                                'type' => 'image',
                                'storage' => 'string',
                                'label' => 'Background Image',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Introduction',
                'description' => 'Intro section text and image.',
                'fields' => [
                    [
                        'key' => 'introduction',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'body_html', 'type' => 'wysiwyg', 'label' => 'Body HTML'],
                            [
                                'key' => 'image_path',
                                'type' => 'image',
                                'storage' => 'string',
                                'label' => 'Image',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
