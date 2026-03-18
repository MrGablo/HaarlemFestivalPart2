<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-4xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <?php
            $eventId = (int)($event['event_id'] ?? 0);
            $returnSuffix = $selectedType !== null ? ('?type=' . urlencode($selectedType)) : '';
            ?>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Event</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Event ID: <span class="font-medium text-slate-900"><?= $eventId ?></span>
                        · Type: <span class="font-medium text-slate-900"><?= htmlspecialchars((string)($event['event_type'] ?? '')) ?></span>
                    </p>
                </div>
                <a href="/cms/events<?= $returnSuffix ?>" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                    ← Back to Events
                </a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="POST" action="/cms/events/<?= $eventId . $returnSuffix ?>" class="mt-6 space-y-6">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                <input type="hidden" name="return_type" value="<?= htmlspecialchars((string)($selectedType ?? '')) ?>">

                <div>
                    <label class="block text-sm font-medium text-slate-700">Event Name</label>
                    <input
                        name="title"
                        type="text"
                        required
                        value="<?= htmlspecialchars((string)($event['title'] ?? '')) ?>"
                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Availability</label>
                    <input
                        name="availability"
                        type="number"
                        min="0"
                        step="1"
                        required
                        value="<?= (int)($event['availability'] ?? 0) ?>"
                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="mt-1 text-xs text-slate-500">Total amount of available tickets/seats for this event.</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/events<?= $returnSuffix ?>" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Save changes
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
