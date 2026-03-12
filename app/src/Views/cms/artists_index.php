<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Artists</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-6xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Artists</h1>
                    <p class="mt-1 text-sm text-slate-600">Create, edit, and delete artists used by jazz events.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/cms/artists/create"
                        class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        + Create Artist
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
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Page ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Updated</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (empty($artists)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-slate-600">No artists found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($artists as $a): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= (int)$a->artist_id ?></td>
                                    <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars((string)$a->name) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($a->page_id ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($a->updated_at ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="/cms/artists/<?= (int)$a->artist_id ?>"
                                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                                Edit artist
                                            </a>

                                            <form method="POST"
                                                action="/cms/artists/<?= (int)$a->artist_id ?>/delete"
                                                onsubmit="return confirm('Delete this artist? Events linked to this artist must be reassigned first.');">
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
