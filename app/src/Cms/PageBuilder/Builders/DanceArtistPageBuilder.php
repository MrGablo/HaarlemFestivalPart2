<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\DanceArtistPageContentViewModel;

final class DanceArtistPageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'Dance_Detail_Page';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new DanceArtistPageContentViewModel(
            $normalized['artist'] ?? [],
            $normalized['story'] ?? [],
            $normalized['feature'] ?? [],
            $normalized['tickets'] ?? [],
            $normalized['tracks'] ?? [],
            $normalized['gallery'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Artist Hero',
                'description' => 'Top section content and imagery for the dance artist page.',
                'fields' => [
                    [
                        'key' => 'artist',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'name', 'type' => 'text', 'label' => 'Artist Name'],
                            ['key' => 'back_href', 'type' => 'text', 'label' => 'Back Link', 'default' => '/dance'],
                            ['key' => 'back_label', 'type' => 'text', 'label' => 'Back Label', 'default' => 'Dance Event'],
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker', 'default' => 'Haarlem Dance'],
                            ['key' => 'hero_title', 'type' => 'text', 'label' => 'Hero Title'],
                            ['key' => 'hero_subtitle', 'type' => 'text', 'label' => 'Hero Subtitle'],
                            ['key' => 'cover_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Hero Background Image'],
                            ['key' => 'portrait_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Artist Portrait Image'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Story Content',
                'description' => 'Top-right bullets and intro paragraph block.',
                'fields' => [
                    [
                        'key' => 'story',
                        'type' => 'object',
                        'fields' => [
                            [
                                'key' => 'hero_bullets',
                                'type' => 'repeater',
                                'label' => 'Hero Bullets',
                                'itemType' => 'text',
                                'itemField' => ['type' => 'text', 'label' => 'Bullet'],
                                'addLabel' => 'Add bullet',
                            ],
                            ['key' => 'intro_html', 'type' => 'wysiwyg', 'label' => 'Intro Paragraph'],
                            ['key' => 'highlights_html', 'type' => 'wysiwyg', 'label' => 'Right Paragraph Block'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Middle Feature Section',
                'description' => 'Large image, optional overlay image, and right-side text.',
                'fields' => [
                    [
                        'key' => 'feature',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'main_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Main Feature Image'],
                            ['key' => 'overlay_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Overlay Image'],
                            ['key' => 'text_html', 'type' => 'wysiwyg', 'label' => 'Feature Text'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Tickets Section',
                'description' => 'Display texts only. Ticket rows are managed in CMS Dance/Jazz Events via linked page.',
                'fields' => [
                    [
                        'key' => 'tickets',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Section Title', 'default' => 'Available Dance Sets'],
                            ['key' => 'ticket_button_label', 'type' => 'text', 'label' => 'Fallback Ticket Label', 'default' => 'ADD'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Important Tracks / Albums',
                'description' => 'Track list and EP block at the bottom.',
                'fields' => [
                    [
                        'key' => 'tracks',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Section Title', 'default' => 'Important Tracks / Albums'],
                            [
                                'key' => 'tracks',
                                'type' => 'repeater',
                                'label' => 'Tracks',
                                'addLabel' => 'Add track',
                                'fields' => [
                                    ['key' => 'name', 'type' => 'text', 'label' => 'Track Name'],
                                    ['key' => 'description', 'type' => 'wysiwyg', 'label' => 'Track Description'],
                                    ['key' => 'cover_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Cover Image'],
                                ],
                            ],
                            [
                                'key' => 'ep',
                                'type' => 'object',
                                'fields' => [
                                    ['key' => 'label', 'type' => 'text', 'label' => 'EP Label', 'default' => 'EP'],
                                    ['key' => 'name', 'type' => 'text', 'label' => 'EP Name'],
                                    ['key' => 'description', 'type' => 'wysiwyg', 'label' => 'EP Description'],
                                    ['key' => 'cover_image', 'type' => 'image', 'storage' => 'string', 'label' => 'EP Cover Image'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Legacy Gallery (optional)',
                'description' => 'Optional fallback images for older artist pages.',
                'fields' => [
                    [
                        'key' => 'gallery',
                        'type' => 'repeater',
                        'label' => 'Gallery Items',
                        'addLabel' => 'Add gallery item',
                        'fields' => [
                            ['key' => 'image', 'type' => 'image', 'storage' => 'string', 'label' => 'Image'],
                            ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
