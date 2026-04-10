<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Pass</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <?php
            $eventId = (int)($pass['event_id'] ?? 0);
            $title = (string)($pass['title'] ?? '');
            $festivalType = (string)($pass['festival_type'] ?? '');
            $passScope = (string)($pass['pass_scope'] ?? '');
            $basePrice = (float)($pass['base_price'] ?? 0);
            $active = (int)($pass['active'] ?? 0) === 1;
            ?>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Pass</h1>
                    <p class="mt-1 text-sm text-slate-600">Pass ID: <span class="font-medium text-slate-900"><?= $eventId ?></span></p>
                </div>

                <a href="/cms/passes" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                    ← Back to Passes
                </a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form id="passUpdateForm" method="POST" action="/cms/passes/<?= $eventId ?>" class="mt-6 space-y-6">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Title</label>
                        <input name="title" type="text" required maxlength="255" value="<?= htmlspecialchars($title) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Festival type</label>
                        <select name="festival_type" required class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <?php foreach (($festivalTypes ?? []) as $type): ?>
                                <option value="<?= htmlspecialchars((string)$type) ?>" <?= ((string)$festivalType === (string)$type) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst((string)$type)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Pass scope</label>
                        <select name="pass_scope" required class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <?php foreach (($passScopes ?? []) as $scope): ?>
                                <option value="<?= htmlspecialchars((string)$scope) ?>" <?= ((string)$passScope === (string)$scope) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$scope) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Base price (€)</label>
                        <input name="base_price" type="number" required min="0" step="0.01" value="<?= htmlspecialchars(number_format($basePrice, 2, '.', '')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div class="flex items-center gap-2 pt-6">
                        <input id="active" name="active" value="1" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" <?= $active ? 'checked' : '' ?>>
                        <label for="active" class="text-sm font-medium text-slate-700">Active</label>
                    </div>
                </div>

            </form>

            <div class="mt-6 flex items-center justify-between border-t border-slate-200 pt-6">
                <form method="POST" action="/cms/passes/<?= $eventId ?>/delete" onsubmit="return confirm('Delete this pass product? This cannot be undone.');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Delete</button>
                </form>

                <div class="flex items-center gap-3">
                    <a href="/cms/passes" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Cancel</a>
                    <button type="submit" form="passUpdateForm" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save changes</button>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
