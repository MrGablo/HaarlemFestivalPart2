<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-6xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Events</h1>
                    <p class="mt-1 text-sm text-slate-600">Filter by event type and edit shared event fields.</p>
                </div>
                <a href="/cms" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to CMS</a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="GET" action="/cms/events" class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <label for="type" class="block text-sm font-medium text-slate-700">Filter by event type</label>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <select id="type" name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                        <option value="">All event types</option>
                        <?php foreach (($allowedTypes ?? []) as $type): ?>
                            <option value="<?= htmlspecialchars((string)$type) ?>" <?= ($selectedType === $type) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)ucfirst($type)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">Apply</button>
                    <a href="/cms/events" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
                </div>
            </form>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Event Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Availability</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-slate-600">No events found for the selected filter.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <?php
                                $eventId = (int)($event['event_id'] ?? 0);
                                $typeQuery = $selectedType !== null ? ('?type=' . urlencode($selectedType)) : '';
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= $eventId ?></td>
                                    <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars((string)($event['title'] ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($event['event_type'] ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= (int)($event['availability'] ?? 0) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <a href="/cms/events/<?= $eventId . $typeQuery ?>" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            Edit event
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>
