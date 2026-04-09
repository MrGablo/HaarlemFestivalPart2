<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create CMS Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-4xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create New Page</h1>
                    <p class="mt-1 text-sm text-slate-600">Select the page type you want to create.</p>
                </div>

                <a href="/cms/pages"
                    class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                    ← Back to Pages
                </a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <div class="mt-6 grid grid-cols-1 gap-4">
                <?php if (empty($pageTypes)): ?>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        No schema-based page types are available yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($pageTypes as $type): ?>
                        <a
                            href="/cms/page/create/<?= urlencode((string)$type['type']) ?>"
                            class="block rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-blue-300 hover:bg-blue-50">
                            <p class="text-sm text-slate-500">Type: <?= htmlspecialchars((string)$type['type']) ?></p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-900"><?= htmlspecialchars((string)$type['label']) ?></h2>
                            <p class="mt-2 text-sm text-slate-600">Open a schema-driven form for this page type.</p>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>

</html>
