<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddHistoryPagesAndEvents extends AbstractMigration
{
    public function up(): void
    {
        $this->ensurePageTypes(['History_Homepage', 'History_Detail_Page']);

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `HistoryEvent` (
                `event_id` INT NOT NULL,
                `language` ENUM('NL', 'EN', 'CH') NOT NULL,
                `start_date` DATETIME NOT NULL,
                `location` VARCHAR(255) NOT NULL DEFAULT '',
                `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                PRIMARY KEY (`event_id`),
                KEY `idx_history_event_start_date` (`start_date`),
                KEY `idx_history_event_language` (`language`),
                CONSTRAINT `fk_history_event_event` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->seedHistoryHomePage();
        $this->seedHistoryDetailPage(
            'Grote Markt',
            [
                'meta' => [
                    'slug' => 'grote-markt',
                    'sort_order' => 1,
                    'listing_title' => 'Grote Markt',
                    'listing_summary' => 'The Grote Markt anchors Haarlem\'s historic centre and serves as one of the key stops on the walking route.',
                    'listing_image' => 'assets/img/homepage/city.png',
                    'navigation' => [
                        'back_href' => '/history',
                        'back_label' => 'Back to route',
                    ],
                    'map_marker' => [
                        'x' => 48,
                        'y' => 58,
                    ],
                ],
                'hero' => [
                    'kicker' => 'Historic landmark',
                    'title' => 'Grote Markt',
                    'main_image' => 'assets/img/homepage/city.png',
                    'gallery' => [
                        ['image' => 'assets/img/homepage/history.png', 'caption' => 'Historic street scene'],
                        ['image' => 'assets/img/homepage/church.png', 'caption' => 'Market square view'],
                        ['image' => 'assets/img/jazz/event/wicked-jazz-sounds-grote-markt.jpg', 'caption' => 'The square today'],
                    ],
                ],
                'story_blocks' => [
                    [
                        'title' => 'The civic heart of Haarlem',
                        'body_html' => '<p>The Grote Markt has long been the social and commercial centre of Haarlem. Traders, performers, public announcements, and celebrations all converged here, making the square a natural starting point for a guided walk through the city.</p>',
                        'image' => 'assets/img/homepage/city.png',
                        'image_position' => 'left',
                    ],
                    [
                        'title' => 'A square shaped by trade',
                        'body_html' => '<p>During the Middle Ages the square connected Haarlem to regional trade routes. Markets expanded around the Town Hall and church buildings, while nearby facades reflected the prosperity of the Dutch Golden Age and the city\'s civic pride.</p>',
                        'image' => 'assets/img/homepage/history.png',
                        'image_position' => 'right',
                    ],
                    [
                        'title' => 'A living public space',
                        'body_html' => '<p>Today the Grote Markt remains one of Haarlem\'s most recognizable places. Its surrounding buildings preserve layers of civic identity, making the location an essential stop for understanding the city and for orienting visitors along the history route.</p>',
                        'image' => 'assets/img/jazz/event/wicked-jazz-sounds-grote-markt.jpg',
                        'image_position' => 'left',
                    ],
                ],
                'map_card' => [
                    'title' => 'Grote Markt',
                    'summary' => 'Use this stop as one of the central points on the route map and as the place where the city\'s public life is easiest to read.',
                    'button_label' => 'Bekijk locatie',
                ],
            ]
        );
        $this->seedHistoryDetailPage(
            'Church of St. Bavo',
            [
                'meta' => [
                    'slug' => 'church-of-st-bavo',
                    'sort_order' => 2,
                    'listing_title' => 'Church of St. Bavo',
                    'listing_summary' => 'The Grote Kerk of St. Bavo reveals Haarlem\'s religious, civic, and architectural history in a single landmark.',
                    'listing_image' => 'assets/img/homepage/church.png',
                    'navigation' => [
                        'back_href' => '/history',
                        'back_label' => 'Back to route',
                    ],
                    'map_marker' => [
                        'x' => 42,
                        'y' => 52,
                    ],
                ],
                'hero' => [
                    'kicker' => 'Historic church',
                    'title' => 'Church of St. Bavo',
                    'main_image' => 'assets/img/homepage/church.png',
                    'gallery' => [
                        ['image' => 'assets/img/homepage/history.png', 'caption' => 'Historic painting of St. Bavo'],
                        ['image' => 'assets/img/homepage/church.png', 'caption' => 'View across the square'],
                        ['image' => 'assets/img/homepage/city.png', 'caption' => 'Church interior and surrounding streets'],
                    ],
                ],
                'story_blocks' => [
                    [
                        'title' => 'A landmark at the heart of Haarlem',
                        'body_html' => '<p>The Church of St. Bavo, also known as the Grote Kerk, stands at the centre of Haarlem\'s historic core. Its scale and construction history reflect the city\'s growth in wealth, faith, and urban ambition from the late medieval period onward.</p>',
                        'image' => 'assets/img/homepage/church.png',
                        'image_position' => 'left',
                    ],
                    [
                        'title' => 'Architecture and civic memory',
                        'body_html' => '<p>As the city expanded, the church evolved from a smaller stone structure into the large basilica seen today. Details in the nave, chapels, and stained glass reveal the influence of guilds, wealthy patrons, and the civic identity that shaped Haarlem for centuries.</p>',
                        'image' => 'assets/img/homepage/history.png',
                        'image_position' => 'right',
                    ],
                    [
                        'title' => 'Music, faith, and continuity',
                        'body_html' => '<p>Inside, visitors encounter the famous Muller organ and a layered interior that connects Catholic origins, Protestant adaptation, and modern heritage use. The church remains one of the route\'s clearest windows into Haarlem\'s long cultural continuity.</p>',
                        'image' => 'assets/img/homepage/city.png',
                        'image_position' => 'left',
                    ],
                ],
                'map_card' => [
                    'title' => 'Church of St. Bavo',
                    'summary' => 'This stop explains how religion, patronage, architecture, and music helped shape Haarlem\'s identity over time.',
                    'button_label' => 'Bekijk locatie',
                ],
            ]
        );
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS `HistoryEvent`');
    }

    private function ensurePageTypes(array $requiredTypes): void
    {
        $row = $this->fetchRow(
            "SELECT COLUMN_TYPE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'Page'
               AND COLUMN_NAME = 'Page_Type'"
        );

        $columnType = (string)($row['COLUMN_TYPE'] ?? '');
        preg_match_all("/'([^']+)'/", $columnType, $matches);
        $values = is_array($matches[1] ?? null) ? $matches[1] : [];

        foreach ($requiredTypes as $type) {
            if (!in_array($type, $values, true)) {
                $values[] = $type;
            }
        }

        if ($values === []) {
            throw new RuntimeException('Unable to read Page.Page_Type enum definition.');
        }

        $enum = implode(', ', array_map(static fn(string $value): string => "'" . str_replace("'", "\\'", $value) . "'", $values));
        $this->execute("ALTER TABLE `Page` MODIFY COLUMN `Page_Type` ENUM({$enum}) NOT NULL");
    }

    private function seedHistoryHomePage(): void
    {
        $payload = [
            'hero' => [
                'kicker' => 'Walk the city through time',
                'title' => 'A Stroll Through History',
                'subtitle_html' => '<p>Discover Haarlem through a guided walking tour that moves from the Grote Markt to the Church of St. Bavo and beyond. The route combines architecture, civic history, and memorable stories into one accessible festival experience.</p>',
                'background_image' => 'assets/img/homepage/history.png',
            ],
            'overview' => [
                'lead_html' => '<p>This route invites visitors to explore the city centre through the places that shaped Haarlem\'s identity. Designed for a broad audience, it combines clear storytelling with historic landmarks and visual cues from the cityscape.</p>',
                'route_html' => '<p>Starting at the Grote Markt and continuing past the Grote Kerk, the walk highlights political, religious, and cultural milestones. Each stop explains how buildings, streets, and public spaces connected to larger moments in Haarlem\'s development.</p>',
                'break_html' => '<p>Midway through the route, visitors pause at a historic stop for a short break before continuing toward the final section of the city. The tour closes with a view of Haarlem\'s heritage as something still visible in daily life.</p>',
            ],
            'booking' => [
                'title' => 'Choose your tour',
                'description_html' => '<p>Select a date, time, and language to reserve a spot on the history walk. Timetable availability is sourced from the HistoryEvent table so the schedule remains tied to the actual bookable tours.</p>',
                'date_label' => 'Select Date',
                'time_label' => 'Select Time',
                'language_label' => 'Select Language',
                'tickets_label' => 'Tickets',
                'reserve_button_label' => 'Reserve now',
                'single_price_label' => 'Single',
                'single_price_value' => '17.50',
                'family_price_label' => 'Family (max 4)',
                'family_price_value' => '60',
                'availability_note_html' => '<p>Availability updates automatically from the bookable history tours stored in the database.</p>',
            ],
            'map' => [
                'title' => 'Route Map',
                'description_html' => '<p>The map section introduces the main landmarks on the route and links through to each detail page so editors can manage every stop separately through the CMS.</p>',
                'card_button_label' => 'Bekijk locatie',
            ],
        ];

        $this->upsertPage('A Stroll Through History', 'History_Homepage', $payload);
    }

    private function seedHistoryDetailPage(string $title, array $payload): void
    {
        $this->upsertPage($title, 'History_Detail_Page', $payload);
    }

    private function upsertPage(string $title, string $type, array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Failed to encode page seed payload.');
        }

        $escapedTitle = str_replace("'", "\\'", $title);
        $escapedType = str_replace("'", "\\'", $type);
        $escapedJson = str_replace("'", "\\'", $json);

        $this->execute(
            "UPDATE `Page`
             SET `Content` = '{$escapedJson}',
                 `Updated_At` = NOW()
             WHERE `Page_Title` = '{$escapedTitle}'
               AND `Page_Type` = '{$escapedType}'
               AND (`Content` IS NULL OR `Content` = '' OR `Content` = '{}' OR `Content` = '[]')"
        );

        $this->execute(
            "INSERT INTO `Page` (`Page_Title`, `Page_Type`, `Content`, `Created_At`, `Updated_At`)
             SELECT '{$escapedTitle}', '{$escapedType}', '{$escapedJson}', NOW(), NULL
             WHERE NOT EXISTS (
                SELECT 1 FROM `Page`
                WHERE `Page_Title` = '{$escapedTitle}'
                  AND `Page_Type` = '{$escapedType}'
             )"
        );
    }
}