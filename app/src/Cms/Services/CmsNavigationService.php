<?php

declare(strict_types=1);

namespace App\Cms\Services;

final class CmsNavigationService
{
    public function items(): array
    {
        return [
            [
                'key' => 'overview',
                'title' => 'Overview',
                'type' => 'overview',
                'href' => '/cms',
                'label' => 'Open',
                'match' => ['/cms'],
                'showOnOverview' => false,
            ],
            [
                'key' => 'pages',
                'title' => 'Pages',
                'type' => 'pages',
                'href' => '/cms/pages',
                'label' => 'Open',
                'match' => ['/cms/pages', '/cms/page'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'events',
                'title' => 'Events',
                'type' => 'events',
                'href' => '/cms/events',
                'label' => 'Open',
                'match' => ['/cms/events'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'dance-events',
                'title' => 'Dance Events',
                'type' => 'dance_events',
                'href' => '/cms/events/dance',
                'label' => 'Open',
                'match' => ['/cms/events/dance'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'jazz-events',
                'title' => 'Jazz Events',
                'type' => 'jazz_events',
                'href' => '/cms/events/jazz',
                'label' => 'Open',
                'match' => ['/cms/events/jazz'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'history-events',
                'title' => 'History Events',
                'type' => 'history_events',
                'href' => '/cms/events/history',
                'label' => 'Open',
                'match' => ['/cms/events/history'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'stories-events',
                'title' => 'Stories Events',
                'type' => 'stories-events',
                'href' => '/cms/events/stories',
                'label' => 'Open',
                'match' => ['/cms/events/stories'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'artists',
                'title' => 'Artists',
                'type' => 'artists',
                'href' => '/cms/artists',
                'label' => 'Open',
                'match' => ['/cms/artists'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'users',
                'title' => 'Users',
                'type' => 'users',
                'href' => '/cms/users',
                'label' => 'Open',
                'match' => ['/cms/users'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'venues',
                'title' => 'Venues',
                'type' => 'venues',
                'href' => '/cms/venues',
                'label' => 'Open',
                'match' => ['/cms/venues'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'orders',
                'title' => 'Orders',
                'type' => 'orders',
                'href' => '/cms/orders',
                'label' => 'Open',
                'match' => ['/cms/orders'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'passes',
                'title' => 'Passes',
                'type' => 'passes',
                'href' => '/cms/passes',
                'label' => 'Open',
                'match' => ['/cms/passes'],
                'showOnOverview' => true,
            ],
            [
                'key' => 'tickets',
                'title' => 'Tickets',
                'type' => 'tickets',
                'href' => '/cms/tickets',
                'label' => 'Open',
                'match' => ['/cms/tickets'],
                'showOnOverview' => true,
            ],
        ];
    }

    public function overviewItems(): array
    {
        return array_values(array_filter(
            $this->items(),
            static fn (array $item): bool => (bool) ($item['showOnOverview'] ?? false)
        ));
    }

    public function activeKey(string $currentPath): ?string
    {
        $bestKey = null;
        $bestMatchLength = -1;

        foreach ($this->items() as $item) {
            $matches = is_array($item['match'] ?? null) ? $item['match'] : [(string) ($item['href'] ?? '')];

            foreach ($matches as $matchPath) {
                $matchPath = rtrim((string) $matchPath, '/');
                $normalizedMatchPath = $matchPath === '' ? '/' : $matchPath;

                if (!$this->matchesPath($normalizedMatchPath, $currentPath)) {
                    continue;
                }

                $matchLength = strlen($normalizedMatchPath);
                if ($matchLength > $bestMatchLength) {
                    $bestMatchLength = $matchLength;
                    $bestKey = (string) ($item['key'] ?? '');
                }
            }
        }

        return $bestKey;
    }

    private function matchesPath(string $matchPath, string $currentPath): bool
    {
        if ($currentPath === $matchPath) {
            return true;
        }

        return $matchPath !== '/' && str_starts_with($currentPath, $matchPath . '/');
    }
}