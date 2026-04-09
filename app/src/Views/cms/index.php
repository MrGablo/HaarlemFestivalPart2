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
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">CMS Pages</h1>
                <div class="flex items-center gap-3">
                    <a href="/cms/page/create"
                        class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        + Create New Page
                    </a>
                    <a href="/" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to home</a>
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
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Updated</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Created</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($pages as $page): ?>
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= (int)($page['Page_ID'] ?? 0) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars((string)($page['Page_Title'] ?? '')) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($page['Page_Type'] ?? '')) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($page['Updated_At'] ?? '-')) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($page['Created_At'] ?? '-')) ?></td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="/cms/page/<?= (int)($page['Page_ID'] ?? 0) ?>" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">Edit</a>
                                        <form method="POST" action="/cms/page/<?= (int)($page['Page_ID'] ?? 0) ?>/delete" onsubmit="return confirm('Delete this page? Linked artist and jazz event page references will be cleared. This cannot be undone.');">
                                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                                            <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">Delete</button>
                                        </form>
                                    </div>
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