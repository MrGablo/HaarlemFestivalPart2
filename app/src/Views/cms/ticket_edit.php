<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Ticket</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Ticket ID: <span class="font-medium text-slate-900"><?= (int)($ticket['ticket_id'] ?? 0) ?></span>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="/cms/tickets"
                        class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        ← Back to Tickets
                    </a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="POST"
                action="/cms/tickets/<?= (int)($ticket['ticket_id'] ?? 0) ?>"
                class="mt-6 space-y-8">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">General</h2>
                    <p class="mt-1 text-sm text-slate-600">Basic ticket information.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Ticket ID</label>
                            <input
                                type="text"
                                disabled
                                value="<?= (int)($ticket['ticket_id'] ?? 0) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Order ID</label>
                            <input
                                type="text"
                                disabled
                                value="<?= (int)($ticket['order_id'] ?? 0) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Order item ID</label>
                            <input
                                type="text"
                                disabled
                                value="<?= (int)($ticket['order_item_id'] ?? 0) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">User ID</label>
                            <input
                                type="text"
                                disabled
                                value="<?= (int)($ticket['user_id'] ?? 0) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Customer</label>
                            <input
                                type="text"
                                disabled
                                value="<?= htmlspecialchars((string)($ticket['customer_name'] ?? '')) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Email</label>
                            <input
                                type="text"
                                disabled
                                value="<?= htmlspecialchars((string)($ticket['customer_email'] ?? '')) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Event</h2>
                    <p class="mt-1 text-sm text-slate-600">Linked event information.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Event ID</label>
                            <input
                                type="text"
                                disabled
                                value="<?= (int)($ticket['event_id'] ?? 0) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Event type</label>
                            <input
                                type="text"
                                disabled
                                value="<?= htmlspecialchars((string)($ticket['event_type'] ?? '')) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Event title</label>
                            <input
                                type="text"
                                disabled
                                value="<?= htmlspecialchars((string)($ticket['event_title'] ?? '')) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Editable fields</h2>
                    <p class="mt-1 text-sm text-slate-600">Only safe ticket fields can be changed here.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">QR</label>
                            <input
                                name="qr"
                                type="text"
                                required
                                value="<?= htmlspecialchars((string)($ticket['qr'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 font-mono text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Scanned</label>
                            <select
                                name="is_scanned"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="0" <?= ((int)($ticket['is_scanned'] ?? 0) === 0) ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= ((int)($ticket['is_scanned'] ?? 0) === 1) ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Order status</label>
                            <input
                                type="text"
                                disabled
                                value="<?= htmlspecialchars((string)($ticket['order_status'] ?? '')) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/tickets"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Save changes
                    </button>
                </div>
            </form>

            <div class="border-t border-slate-200 pt-6">
                <h2 class="text-lg font-semibold text-slate-900">Danger zone</h2>
                <p class="mt-1 text-sm text-slate-600">Deleting a ticket cannot be undone.</p>

                <form method="POST"
                    action="/cms/tickets/<?= (int)($ticket['ticket_id'] ?? 0) ?>/delete"
                    class="mt-4"
                    onsubmit="return confirm('Delete this ticket? This cannot be undone.');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                    <button type="submit"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                        Delete ticket
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>

</html>