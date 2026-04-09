<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Stories Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Stories Event</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Event ID: <span class="font-medium text-slate-900"><?= (int)($event->event_id ?? 0) ?></span>
                        · Type: <span class="font-medium text-slate-900"><?= htmlspecialchars((string)($event->event_type ?? 'stories')) ?></span>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="/cms/events/stories"
                        class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        ← Back to Stories Events
                    </a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="POST"
                enctype="multipart/form-data"
                action="/cms/events/stories/<?= (int)($event->event_id ?? 0) ?>"
                class="mt-6 space-y-8">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

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
                                value="<?= htmlspecialchars((string)($event->event_type ?? 'stories')) ?>"
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                            <p class="mt-1 text-xs text-slate-500">Type is locked to stories here.</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Stories details</h2>
                    <p class="mt-1 text-sm text-slate-600">These are stored in the <span class="font-medium">StoriesEvent</span> table.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Language</label>
                            <select
                                name="language"
                                required
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">Select language</option>
                                <option value="NL" <?= (($event->language ?? '') === 'NL') ? 'selected' : '' ?>>NL</option>
                                <option value="ENG" <?= (($event->language ?? '') === 'ENG') ? 'selected' : '' ?>>ENG</option>
                                <option value="NL/ENG" <?= (($event->language ?? '') === 'NL/ENG') ? 'selected' : '' ?>>NL/ENG</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Age group</label>
                            <input
                                name="age_group"
                                type="text"
                                required
                                value="<?= htmlspecialchars((string)($event->age_group ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Story type</label>
                            <input
                                name="story_type"
                                type="text"
                                required
                                value="<?= htmlspecialchars((string)($event->story_type ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Location</label>
                            <input
                                name="location"
                                type="text"
                                required
                                value="<?= htmlspecialchars((string)($event->location ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Description</label>
                            <textarea
                                name="description"
                                rows="5"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"><?= htmlspecialchars((string)($event->description ?? '')) ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start date</label>
                            <input
                                name="start_date"
                                type="datetime-local"
                                required
                                value="<?= htmlspecialchars((string)toDatetimeLocal($event->start_date ?? null)) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <p class="mt-1 text-xs text-slate-500">Format: YYYY-MM-DDTHH:MM</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">End date</label>
                            <input
                                name="end_date"
                                type="datetime-local"
                                required
                                value="<?= htmlspecialchars((string)toDatetimeLocal($event->end_date ?? null)) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price</label>
                            <input
                                name="price"
                                type="number"
                                step="0.01"
                                min="0"
                                value="<?= htmlspecialchars((string)($event->price ?? '0.00')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
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
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/events/stories"
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
                <p class="mt-1 text-sm text-slate-600">Deleting this stories event cannot be undone.</p>

                <form method="POST"
                    action="/cms/events/stories/<?= (int)($event->event_id ?? 0) ?>/delete"
                    class="mt-4"
                    onsubmit="return confirm('Delete this stories event? This cannot be undone.');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                    <button type="submit"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                        Delete event
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>

</html>

<?php
function toDatetimeLocal($value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    $s = (string)$value;

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $s)) {
        return str_replace(' ', 'T', substr($s, 0, 16));
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $s)) {
        return substr($s, 0, 16);
    }

    try {
        $dt = new DateTime($s);
        return $dt->format('Y-m-d\TH:i');
    } catch (Throwable $e) {
        return '';
    }
}