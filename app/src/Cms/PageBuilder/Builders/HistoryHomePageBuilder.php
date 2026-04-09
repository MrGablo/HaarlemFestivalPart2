<?php

declare(strict_types=1);

namespace App\Cms\PageBuilder\Builders;

use App\Cms\PageBuilder\AbstractPageViewModelBuilder;
use App\Cms\PageBuilder\Content\HistoryHomePageContentViewModel;

final class HistoryHomePageBuilder extends AbstractPageViewModelBuilder
{
    public function pageType(): string
    {
        return 'History_Homepage';
    }

    public function buildViewModel(array $content): object
    {
        $normalized = $this->normalizeInput($content);

        return new HistoryHomePageContentViewModel(
            $normalized['hero'] ?? [],
            $normalized['overview'] ?? [],
            $normalized['booking'] ?? [],
            $normalized['map'] ?? []
        );
    }

    public function editorSchema(): array
    {
        return [
            [
                'title' => 'Hero',
                'description' => 'Landing-page hero content for the history overview.',
                'fields' => [
                    [
                        'key' => 'hero',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'kicker', 'type' => 'text', 'label' => 'Kicker'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                            ['key' => 'subtitle_html', 'type' => 'wysiwyg', 'label' => 'Subtitle'],
                            ['key' => 'background_image', 'type' => 'image', 'storage' => 'string', 'label' => 'Background Image'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Overview',
                'description' => 'Editorial copy blocks shown above the booking section.',
                'fields' => [
                    [
                        'key' => 'overview',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'lead_html', 'type' => 'wysiwyg', 'label' => 'Lead Copy'],
                            ['key' => 'route_html', 'type' => 'wysiwyg', 'label' => 'Route Copy'],
                            ['key' => 'break_html', 'type' => 'wysiwyg', 'label' => 'Break Copy'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Booking',
                'description' => 'Booking form labels and helper copy. Live single and family prices now come from HistoryEvent records.',
                'fields' => [
                    [
                        'key' => 'booking',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Section Title', 'default' => 'Choose your tour'],
                            ['key' => 'description_html', 'type' => 'wysiwyg', 'label' => 'Description'],
                            ['key' => 'date_label', 'type' => 'text', 'label' => 'Date Label', 'default' => 'Select Date'],
                            ['key' => 'time_label', 'type' => 'text', 'label' => 'Time Label', 'default' => 'Select Time'],
                            ['key' => 'language_label', 'type' => 'text', 'label' => 'Language Label', 'default' => 'Select Language'],
                            ['key' => 'tickets_label', 'type' => 'text', 'label' => 'Tickets Label', 'default' => 'Tickets'],
                            ['key' => 'schedule_title', 'type' => 'text', 'label' => 'Schedule Title', 'default' => 'Tour Schedule'],
                            ['key' => 'selected_tour_label', 'type' => 'text', 'label' => 'Selected Tour Label', 'default' => 'Selected Tour'],
                            ['key' => 'selected_location_label', 'type' => 'text', 'label' => 'Selected Location Label', 'default' => 'Meeting Point'],
                            ['key' => 'selected_availability_label', 'type' => 'text', 'label' => 'Selected Availability Label', 'default' => 'Seats Left'],
                            ['key' => 'selected_price_label', 'type' => 'text', 'label' => 'Selected Price Label', 'default' => 'Price'],
                            ['key' => 'reserve_button_label', 'type' => 'text', 'label' => 'Reserve Button Label', 'default' => 'Reserve now'],
                            ['key' => 'empty_selection_message', 'type' => 'text', 'label' => 'Empty Selection Message', 'default' => 'Choose a date, time and language to book a tour.'],
                            ['key' => 'no_events_message', 'type' => 'text', 'label' => 'No Events Message', 'default' => 'No history tours are available right now.'],
                            ['key' => 'slot_count_label', 'type' => 'text', 'label' => 'Slot Count Label', 'default' => 'tour(s) available'],
                            ['key' => 'single_price_label', 'type' => 'text', 'label' => 'Single Price Label', 'default' => 'Single'],
                            ['key' => 'family_price_label', 'type' => 'text', 'label' => 'Family Price Label', 'default' => 'Family (max 4)'],
                            ['key' => 'selection_help_html', 'type' => 'wysiwyg', 'label' => 'Selection Help'],
                            ['key' => 'availability_note_html', 'type' => 'wysiwyg', 'label' => 'Availability Note'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Map Section',
                'description' => 'Copy shown alongside the route map and location cards.',
                'fields' => [
                    [
                        'key' => 'map',
                        'type' => 'object',
                        'fields' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Map Title', 'default' => 'Route Map'],
                            ['key' => 'description_html', 'type' => 'wysiwyg', 'label' => 'Map Description'],
                            ['key' => 'card_button_label', 'type' => 'text', 'label' => 'Card Button Label', 'default' => 'Bekijk locatie'],
                        ],
                    ],
                ],
            ],
        ];
    }
}