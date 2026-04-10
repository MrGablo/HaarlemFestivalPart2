<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-7xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Orders</h1>
                    <p class="mt-1 text-sm text-slate-600">Review orders, adjust status, and export order data.</p>
                </div>

                <a href="/cms" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to CMS</a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="GET" action="/cms/orders" class="mt-6 space-y-4 rounded-lg bg-slate-50 p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Search</label>
                        <input type="text" name="search" placeholder="Order ID, user ID, customer, status..."
                            value="<?= htmlspecialchars((string)($search ?? '')) ?>"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Status</label>
                        <select name="status"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                            <option value="">All Statuses</option>
                            <?php foreach (($statuses ?? []) as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>"
                                    <?= ((string)($statusFilter ?? '') === $status) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($status)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Sort</label>
                        <div class="mt-1 flex gap-2">
                            <select name="sort"
                                class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="created_at" <?= ((string)($sortColumn ?? 'created_at') === 'created_at') ? 'selected' : '' ?>>Created</option>
                                <option value="order_id" <?= ((string)($sortColumn ?? 'created_at') === 'order_id') ? 'selected' : '' ?>>Order ID</option>
                                <option value="total_amount" <?= ((string)($sortColumn ?? 'created_at') === 'total_amount') ? 'selected' : '' ?>>Total</option>
                                <option value="paid_amount" <?= ((string)($sortColumn ?? 'created_at') === 'paid_amount') ? 'selected' : '' ?>>Paid</option>
                            </select>
                            <select name="dir"
                                class="w-24 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="ASC" <?= ((string)($sortDirection ?? 'DESC') === 'ASC') ? 'selected' : '' ?>>↑ Asc</option>
                                <option value="DESC" <?= ((string)($sortDirection ?? 'DESC') === 'DESC') ? 'selected' : '' ?>>↓ Desc</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Apply Filters
                    </button>
                    <a href="/cms/orders"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">
                        Reset
                    </a>
                </div>
            </form>

            <form method="GET" action="/cms/orders/export/options" class="mt-6 rounded-lg border border-slate-200 bg-white p-4">
                <input type="hidden" name="search" value="<?= htmlspecialchars((string)($search ?? '')) ?>">
                <input type="hidden" name="status" value="<?= htmlspecialchars((string)($statusFilter ?? '')) ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars((string)($sortColumn ?? 'created_at')) ?>">
                <input type="hidden" name="dir" value="<?= htmlspecialchars((string)($sortDirection ?? 'DESC')) ?>">

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Export order information</h2>
                        <p class="mt-1 text-sm text-slate-600">Export all orders or a specific user's orders. For single-order export with custom columns, click Export from the order row.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" name="format" value="csv"
                            class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Export CSV
                        </button>
                        <button type="submit" name="format" value="excel"
                            class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Export Excel
                        </button>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Export Scope</label>
                        <select name="scope"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                            <option value="all" <?= ((string)($exportScope ?? 'all') === 'all') ? 'selected' : '' ?>>All Orders</option>
                            <option value="user" <?= ((string)($exportScope ?? 'all') === 'user') ? 'selected' : '' ?>>Specific User</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">User ID (for specific user scope)</label>
                        <input type="number" min="1" name="user_id"
                            value="<?= (int)($exportUserId ?? 0) > 0 ? (int)$exportUserId : '' ?>"
                            placeholder="e.g. 19"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Single-order export</label>
                        <div class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                            Open an order and use its Export button to choose columns based on available data.
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Order ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">User</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Items</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Total</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Paid</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Created</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-slate-600">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-900">#<?= (int)($order['order_id'] ?? 0) ?></td>
                                    <td class="px-4 py-3 text-slate-700">
                                        <div class="font-medium text-slate-900"><?= htmlspecialchars((string)($order['customer_name'] ?? 'Unknown user')) ?></div>
                                        <div class="text-xs text-slate-500">User ID: <?= (int)($order['user_id'] ?? 0) ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars((string)($order['customer_email'] ?? '')) ?></div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                            <?= htmlspecialchars(ucfirst((string)($order['status'] ?? 'pending'))) ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= (int)($order['item_count'] ?? 0) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">€<?= number_format((float)($order['total_amount'] ?? 0), 2) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">€<?= number_format((float)($order['paid_amount'] ?? 0), 2) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($order['created_at'] ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <a href="/cms/orders/<?= (int)($order['order_id'] ?? 0) ?>"
                                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            Manage
                                        </a>
                                        <a href="/cms/orders/<?= (int)($order['order_id'] ?? 0) ?>/export"
                                            class="ml-2 rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">
                                            Export
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
