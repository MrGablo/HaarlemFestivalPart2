<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Jazz Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-6xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Jazz Events</h1>
                    <p class="mt-1 text-sm text-slate-600">Create, edit, and delete jazz events securely.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/cms/jazz/artists"
                       class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Manage Artists
                    </a>

                    <a href="/cms/events/jazz/create"
                       class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        + Create Jazz Event
                    </a>

                    <a href="/cms"
                       class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to CMS</a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Artist</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Start</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Location</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Price</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-slate-600">No jazz events found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $e): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= (int)$e->event_id ?></td>
                                    <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars((string)$e->title) ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($e->artist_name ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($e->start_date ?? '')) ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($e->location ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($e->price ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="/cms/events/jazz/<?= (int)$e->event_id ?>"
                                               class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                                Edit jazz event
                                            </a>

                                            <form method="POST"
                                                  action="/cms/events/jazz/<?= (int)$e->event_id ?>/delete"
                                                  onsubmit="return confirm('Delete this jazz event? This cannot be undone.');">
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