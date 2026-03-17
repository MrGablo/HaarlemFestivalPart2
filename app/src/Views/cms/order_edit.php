<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-7xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Manage Order #<?= (int)($order['order_id'] ?? 0) ?></h1>
                    <p class="mt-1 text-sm text-slate-600">Update status and item quantities. Set quantity to 0 to remove an item.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="/cms/orders/export?format=csv&amp;scope=order&amp;order_id=<?= (int)($order['order_id'] ?? 0) ?>"
                        class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                        Export CSV
                    </a>
                    <a href="/cms/orders/export?format=excel&amp;scope=order&amp;order_id=<?= (int)($order['order_id'] ?? 0) ?>"
                        class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                        Export Excel
                    </a>
                    <a href="/cms/orders" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to Orders</a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <div class="mt-6 grid grid-cols-1 gap-4 rounded-lg bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">User</p>
                    <p class="mt-1 text-sm text-slate-900"><?= htmlspecialchars((string)($order['customer_name'] ?? 'Unknown user')) ?></p>
                    <p class="text-xs text-slate-500">ID: <?= (int)($order['user_id'] ?? 0) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
                    <p class="mt-1 text-sm text-slate-900"><?= htmlspecialchars((string)($order['customer_email'] ?? '')) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</p>
                    <p class="mt-1 text-sm text-slate-900"><?= htmlspecialchars((string)($order['created_at'] ?? '')) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Totals</p>
                    <p class="mt-1 text-sm text-slate-900">Items: <?= (int)($order['item_count'] ?? 0) ?></p>
                    <p class="text-sm text-slate-900">Total: €<?= number_format((float)($order['total_amount'] ?? 0), 2) ?></p>
                    <p class="text-sm text-slate-900">Paid: €<?= number_format((float)($order['paid_amount'] ?? 0), 2) ?></p>
                </div>
            </div>

            <form method="POST" action="/cms/orders/<?= (int)($order['order_id'] ?? 0) ?>" class="mt-6 space-y-6">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div class="max-w-sm">
                    <label class="block text-sm font-medium text-slate-700">Order Status</label>
                    <select name="order_status"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <?php foreach (($statuses ?? []) as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>"
                                <?= ((string)($order['status'] ?? '') === $status) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Order Item ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Event</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Unit Price</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Quantity</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Line Total</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-600">This order has no items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">#<?= (int)($item['order_item_id'] ?? 0) ?></td>
                                        <td class="px-4 py-3 text-slate-700">
                                            <div class="font-medium text-slate-900"><?= htmlspecialchars((string)($item['title'] ?? 'Event')) ?></div>
                                            <div class="text-xs text-slate-500">
                                                <?= htmlspecialchars((string)($item['artist_name'] ?? '')) ?>
                                                <?php if (!empty($item['venue_name'])): ?>
                                                    · <?= htmlspecialchars((string)$item['venue_name']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($item['start_date'] ?? '')) ?></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">€<?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <input type="number"
                                                name="quantities[<?= (int)($item['order_item_id'] ?? 0) ?>]"
                                                min="0"
                                                value="<?= (int)($item['quantity'] ?? 0) ?>"
                                                class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">€<?= number_format((float)($item['line_total'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <a href="/cms/orders" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Save Order
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
