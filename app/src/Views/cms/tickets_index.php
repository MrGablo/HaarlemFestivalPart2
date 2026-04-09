<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-7xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Tickets</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Manage generated tickets in the CMS.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="/cms"
                        class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        ← Back to CMS
                    </a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="GET" action="/cms/tickets" class="mt-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Filters</h2>
                    <p class="mt-1 text-sm text-slate-600">Search and filter the tickets overview.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Search</label>
                            <input
                                name="search"
                                type="text"
                                value="<?= htmlspecialchars((string)($search ?? '')) ?>"
                                placeholder="Ticket ID, QR, event, customer, email..."
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Scan status</label>
                            <select
                                name="scan"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">All</option>
                                <option value="scanned" <?= ((string)($scanFilter ?? '') === 'scanned') ? 'selected' : '' ?>>Scanned</option>
                                <option value="not_scanned" <?= ((string)($scanFilter ?? '') === 'not_scanned') ? 'selected' : '' ?>>Not scanned</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Sort</label>
                            <div class="mt-1 flex gap-2">
                                <select
                                    name="sort"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="ticket_id" <?= ((string)($sortColumn ?? 'ticket_id') === 'ticket_id') ? 'selected' : '' ?>>Ticket ID</option>
                                    <option value="order_id" <?= ((string)($sortColumn ?? '') === 'order_id') ? 'selected' : '' ?>>Order ID</option>
                                    <option value="user_id" <?= ((string)($sortColumn ?? '') === 'user_id') ? 'selected' : '' ?>>User ID</option>
                                    <option value="event_id" <?= ((string)($sortColumn ?? '') === 'event_id') ? 'selected' : '' ?>>Event ID</option>
                                    <option value="event_title" <?= ((string)($sortColumn ?? '') === 'event_title') ? 'selected' : '' ?>>Event</option>
                                    <option value="customer_name" <?= ((string)($sortColumn ?? '') === 'customer_name') ? 'selected' : '' ?>>Customer</option>
                                    <option value="is_scanned" <?= ((string)($sortColumn ?? '') === 'is_scanned') ? 'selected' : '' ?>>Scanned</option>
                                    <option value="created_at" <?= ((string)($sortColumn ?? '') === 'created_at') ? 'selected' : '' ?>>Created</option>
                                </select>

                                <select
                                    name="dir"
                                    class="w-28 rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="ASC" <?= ((string)($sortDirection ?? 'DESC') === 'ASC') ? 'selected' : '' ?>>ASC</option>
                                    <option value="DESC" <?= ((string)($sortDirection ?? 'DESC') === 'DESC') ? 'selected' : '' ?>>DESC</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/tickets"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Reset
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Apply filters
                    </button>
                </div>
            </form>

            <div class="mt-8 border-t border-slate-200 pt-6">
                <h2 class="text-lg font-semibold text-slate-900">Overview</h2>
                <p class="mt-1 text-sm text-slate-600">All tickets currently in the system.</p>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Ticket ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Order</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">User</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Event</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">QR</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Scanned</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Created</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-slate-600">
                                        No tickets found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                            <?= (int)$ticket['ticket_id'] ?>
                                        </td>

                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                            #<?= (int)$ticket['order_id'] ?>
                                        </td>

                                        <td class="px-4 py-3 text-slate-700">
                                            <div class="font-medium text-slate-900">
                                                <?= htmlspecialchars((string)$ticket['customer_name']) ?>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                <?= htmlspecialchars((string)$ticket['customer_email']) ?>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-slate-700">
                                            <div class="font-medium text-slate-900">
                                                <?= htmlspecialchars((string)$ticket['event_title']) ?>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                Event ID: <?= (int)$ticket['event_id'] ?>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-slate-700">
                                            <span class="font-mono text-xs">
                                                <?= htmlspecialchars((string)$ticket['qr']) ?>
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                            <?= ((int)$ticket['is_scanned'] === 1) ? 'Yes' : 'No' ?>
                                        </td>

                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                            <?= htmlspecialchars((string)$ticket['created_at']) ?>
                                        </td>

                                        <td class="whitespace-nowrap px-4 py-3">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="/cms/tickets/<?= (int)$ticket['ticket_id'] ?>"
                                                    class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                    action="/cms/tickets/<?= (int)$ticket['ticket_id'] ?>/delete"
                                                    onsubmit="return confirm('Delete this ticket? This cannot be undone.');">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                                                    <button type="submit"
                                                        class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">
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
            </div>
        </section>
    </main>
</body>

</html>