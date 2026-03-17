<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Export Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Export Order #<?= (int)($order['order_id'] ?? 0) ?></h1>
                    <p class="mt-1 text-sm text-slate-600">Choose only the columns that exist in this order's data.</p>
                </div>
                <a href="/cms/orders/<?= (int)($order['order_id'] ?? 0) ?>" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to Order</a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="GET" action="/cms/orders/export" class="mt-6 space-y-6">
                <input type="hidden" name="scope" value="order">
                <input type="hidden" name="order_id" value="<?= (int)($order['order_id'] ?? 0) ?>">

                <div class="max-w-sm">
                    <label class="block text-sm font-medium text-slate-700">Format</label>
                    <select name="format"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        <option value="csv">CSV</option>
                        <option value="excel">Excel (XML)</option>
                    </select>
                </div>

                <div>
                    <h2 class="text-base font-semibold text-slate-900">Columns for this order</h2>
                    <p class="mt-1 text-sm text-slate-600">Fields with no data in this order are automatically hidden.</p>

                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach (($availableColumns ?? []) as $key => $label): ?>
                            <label class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" name="columns[]" value="<?= htmlspecialchars((string)$key) ?>"
                                    <?= in_array((string)$key, $defaultSelectedColumns ?? [], true) ? 'checked' : '' ?>
                                    class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span><?= htmlspecialchars((string)$label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <a href="/cms/orders/<?= (int)($order['order_id'] ?? 0) ?>" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Export Now
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
