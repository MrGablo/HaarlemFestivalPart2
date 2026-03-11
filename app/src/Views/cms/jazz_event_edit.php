<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Jazz Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Jazz Event</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Event ID: <span class="font-medium text-slate-900"><?= (int)($event->event_id ?? 0) ?></span>
                        · Type: <span class="font-medium text-slate-900"><?= htmlspecialchars((string)($event->event_type ?? '')) ?></span>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="/cms/events/jazz"
                        class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        ← Back to Jazz Events
                    </a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="POST"
                enctype="multipart/form-data"
                action="/cms/events/jazz/<?= (int)($event->event_id ?? 0) ?>"
                class="mt-6 space-y-8">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <!-- Parent Event fields -->
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">General</h2>
                    <p class="mt-1 text-sm text-slate-600">These are stored in the <span class="font-medium">Event</span> table.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Title</label>
                            <input
                                name="title"
                                type="text"
                                required
                                value="<?= htmlspecialchars((string)($event->title ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Event type</label>
                            <input
                                type="text"
                                disabled
                                value="<?= htmlspecialchars((string)($event->event_type ?? 'jazz')) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                            <p class="mt-1 text-xs text-slate-500">Type is locked to jazz here.</p>
                        </div>
                    </div>
                </div>

                <!-- Child JazzEvent fields -->
                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Jazz details</h2>
                    <p class="mt-1 text-sm text-slate-600">These are stored in the <span class="font-medium">JazzEvent</span> table.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start date</label>
                            <input
                                name="start_date"
                                type="datetime-local"
                                value="<?= htmlspecialchars((string)toDatetimeLocal($event->start_date ?? null)) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <p class="mt-1 text-xs text-slate-500">Format: YYYY-MM-DDTHH:MM</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">End date</label>
                            <input
                                name="end_date"
                                type="datetime-local"
                                value="<?= htmlspecialchars((string)toDatetimeLocal($event->end_date ?? null)) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Location</label>
                            <input
                                name="location"
                                type="text"
                                value="<?= htmlspecialchars((string)($event->location ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Artist</label>
                            <select
                                name="artist_id"
                                required
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">Select artist</option>
                                <?php foreach (($artists ?? []) as $artist): ?>
                                    <?php $isSelected = ((int)($event->artist_id ?? 0) === (int)$artist->artist_id) ? 'selected' : ''; ?>
                                    <option value="<?= (int)$artist->artist_id ?>" <?= $isSelected ?>>
                                        <?= htmlspecialchars((string)$artist->name) ?> (ID <?= (int)$artist->artist_id ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($event->artist_name)): ?>
                                <p class="mt-1 text-xs text-slate-500">Current artist: <?= htmlspecialchars((string)$event->artist_name) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Background image</label>

                            <div class="mt-1 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Upload file</label>
                                    <input
                                        name="img_background_file"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp,.gif"
                                        class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm
                       file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700
                       hover:file:bg-slate-200">
                                </div>

                            </div>

                            <?php if (!empty($event->img_background)): ?>
                                <p class="mt-3 text-xs text-slate-600">
                                    Current image path: <span class="font-mono"><?= htmlspecialchars((string)$event->img_background) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price</label>
                            <input
                                name="price"
                                type="number"
                                step="0.01"
                                min="0"
                                value="<?= htmlspecialchars((string)($event->price ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Page ID (optional)</label>
                            <input
                                name="page_id"
                                type="number"
                                min="1"
                                value="<?= htmlspecialchars((string)($event->page_id ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <p class="mt-1 text-xs text-slate-500">Leave empty if not linked.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/events/jazz"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Save changes
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>

<?php
/**
 * Converts "YYYY-MM-DD HH:MM:SS" or DateTime-like string to "YYYY-MM-DDTHH:MM"
 * for <input type="datetime-local">
 */
function toDatetimeLocal($value): string
{
    if ($value === null || $value === '') return '';
    $s = (string)$value;

    // If it's "YYYY-MM-DD HH:MM:SS", convert to "YYYY-MM-DDTHH:MM"
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $s)) {
        return str_replace(' ', 'T', substr($s, 0, 16));
    }

    // If it's already like "YYYY-MM-DDTHH:MM", keep as is
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $s)) {
        return substr($s, 0, 16);
    }

    // Fallback: try DateTime parse
    try {
        $dt = new DateTime($s);
        return $dt->format('Y-m-d\TH:i');
    } catch (Throwable $e) {
        return '';
    }
}
