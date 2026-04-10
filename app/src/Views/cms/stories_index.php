<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stories Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-7xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Stories Events</h1>
        <p class="mt-1 text-sm text-slate-600">Manage stories events in the CMS.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="/cms/events/stories/create"
            class="rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700 shadow-sm transition-colors">
            + Create New Story
        </a>
        
        <a href="/cms"
            class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition-colors">
            ← Back to CMS
        </a>
    </div>
</div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <div class="mt-8 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Event ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Language</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Age Group</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Story Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Location</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Start Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Price</th>
                            <th class="min-w-[220px] px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-slate-600">
                                    No stories events found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        <?= (int)($event['event_id'] ?? 0) ?>
                                    </td>

                                    <td class="px-4 py-3 text-slate-700">
                                        <div class="font-medium text-slate-900">
                                            <?= htmlspecialchars((string)($event['title'] ?? '')) ?>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        <?= htmlspecialchars((string)($event['language'] ?? '')) ?>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        <?= htmlspecialchars((string)($event['age_group'] ?? '')) ?>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        <?= htmlspecialchars((string)($event['story_type'] ?? '')) ?>
                                    </td>

                                    <td class="px-4 py-3 text-slate-700">
                                        <?= htmlspecialchars((string)($event['location'] ?? '')) ?>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        <?= htmlspecialchars((string)($event['start_date'] ?? '')) ?>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        €<?= htmlspecialchars(number_format((float)($event['price'] ?? 0), 2)) ?>
                                    </td>

                                    <td class="min-w-[220px] whitespace-nowrap px-4 py-3">
                                        <div class="flex flex-nowrap items-center gap-2">
                                            <a href="/cms/events/stories/<?= (int)($event['event_id'] ?? 0) ?>"
                                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                action="/cms/events/stories/<?= (int)($event['event_id'] ?? 0) ?>/delete"
                                                onsubmit="return confirm('Delete this stories event? This cannot be undone.');">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                                                <button type="submit"
                                                    class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
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