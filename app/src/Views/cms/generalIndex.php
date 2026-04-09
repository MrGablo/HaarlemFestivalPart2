<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Overview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-6xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">CMS Editor</h1>
                <a href="/" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to home</a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <?php
            // CMS modules shown on the overview page
            
            //update view
            $modules = [
                [
                    'id' => 1,
                    'title' => 'Pages',
                    'type' => 'pages',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/pages',
                    'label' => 'Open',
                ],
                [
                    'id' => 2,
                    'title' => 'Events',
                    'type' => 'events',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/events',
                    'label' => 'Open',
                ],
                [
                    'id' => 3,
                    'title' => 'Jazz Events',
                    'type' => 'jazz_events',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/events/jazz',
                    'label' => 'Open',
                ],
                [
                    'id' => 4,
                    'title' => 'Artists',
                    'type' => 'artists',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/artists',
                    'label' => 'Open',
                ],
                [
                    'id' => 5,
                    'title' => 'Users',
                    'type' => 'users',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/users',
                    'label' => 'Open',
                ],
                [
                    'id' => 6,
                    'title' => 'Venues',
                    'type' => 'venues',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/venues',
                    'label' => 'Open',
                ],
                [
                    'id' => 7,
                    'title' => 'Orders',
                    'type' => 'orders',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/orders',
                    'label' => 'Open',
                ],
                [
                    'id' => 8,
                    'title' => 'Passes',
                    'type' => 'passes',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/passes',
                    'label' => 'Open',
                ],
                [ 
                    'id' => 9,
                    'title' => 'Tickets',
                    'type' => 'tickets',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/tickets',
                    'label' => 'Open',
                ],
                [
                    'id' => 10,
                    'title' => 'Stories Events',
                    'type' => 'stories-events',
                    'updated' => '-',
                    'created' => '-',
                    'href' => '/cms/events/stories',
                    'label' => 'Open',
                ],
            ];
            ?>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Updated</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Created</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($modules as $m): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= (int) $m['id'] ?></td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-900">
                                    <?= htmlspecialchars((string) $m['title']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                    <?= htmlspecialchars((string) $m['type']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                    <?= htmlspecialchars((string) $m['updated']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                    <?= htmlspecialchars((string) $m['created']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <a href="<?= htmlspecialchars((string) $m['href']) ?>"
                                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                        <?= htmlspecialchars((string) $m['label']) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>