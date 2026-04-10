<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Jazz Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create Jazz Event</h1>
                    <p class="mt-1 text-sm text-slate-600">Add a new jazz event with parent and jazz-specific details.</p>
                </div>

                <a href="/cms/events/jazz"
                    class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                    ← Back to Jazz Events
                </a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <?php $old = is_array($old ?? null) ? $old : []; ?>

            <form method="POST"
                enctype="multipart/form-data"
                action="/cms/events/jazz/create"
                class="mt-6 space-y-8">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">General</h2>
                    <p class="mt-1 text-sm text-slate-600">Stored in the <span class="font-medium">Event</span> table.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Title</label>
                            <input
                                name="title"
                                type="text"
                                maxlength="120"
                                required
                                value="<?= htmlspecialchars((string)($old['title'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Availability</label>
                            <input
                                name="availability"
                                type="number"
                                min="0"
                                step="1"
                                required
                                value="<?= htmlspecialchars((string)($old['availability'] ?? '300')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                            <p class="mt-1 text-xs text-slate-500">Total seats available for this jazz event.</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Jazz details</h2>
                    <p class="mt-1 text-sm text-slate-600">Stored in the <span class="font-medium">JazzEvent</span> table.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start date</label>
                            <input
                                name="start_date"
                                type="datetime-local"
                                required
                                value="<?= htmlspecialchars((string)($old['start_date'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">End date</label>
                            <input
                                name="end_date"
                                type="datetime-local"
                                required
                                value="<?= htmlspecialchars((string)($old['end_date'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Venue</label>
                            <select
                                name="venue_id"
                                required
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">Select venue</option>
                                <?php foreach (($venues ?? []) as $venue): ?>
                                    <?php if (!is_object($venue)) { continue; } ?>
                                    <?php $isSelected = ((string)($old['venue_id'] ?? '') === (string)$venue->venue_id) ? 'selected' : ''; ?>
                                    <option value="<?= (int)$venue->venue_id ?>" <?= $isSelected ?>>
                                        <?= htmlspecialchars((string)$venue->name) ?> (ID <?= (int)$venue->venue_id ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Artist</label>
                            <select
                                name="artist_id"
                                required
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">Select artist</option>
                                <?php foreach (($artists ?? []) as $artist): ?>
                                    <?php if (!is_object($artist)) { continue; } ?>
                                    <?php $isSelected = ((string)($old['artist_id'] ?? '') === (string)$artist->artist_id) ? 'selected' : ''; ?>
                                    <option value="<?= (int)$artist->artist_id ?>" <?= $isSelected ?>>
                                        <?= htmlspecialchars((string)$artist->name) ?> (ID <?= (int)$artist->artist_id ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Background image (optional)</label>
                            <input
                                name="img_background_file"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,.gif"
                                class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm
                                       file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700
                                       hover:file:bg-slate-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price</label>
                            <input
                                name="price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                value="<?= htmlspecialchars((string)($old['price'] ?? '0.00')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Linked page (optional)</label>
                            <select
                                name="page_id"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">No linked page</option>
                                <?php foreach (($pages ?? []) as $page): ?>
                                    <?php
                                    $pageId = (string)($page['Page_ID'] ?? '');
                                    $isSelected = ((string)($old['page_id'] ?? '') === $pageId) ? 'selected' : '';
                                    $label = (string)($page['Page_Title'] ?? 'Untitled');
                                    $type = (string)($page['Page_Type'] ?? '');
                                    ?>
                                    <option value="<?= htmlspecialchars($pageId) ?>" <?= $isSelected ?>>
                                        <?= htmlspecialchars($label) ?><?= $type !== '' ? ' (' . htmlspecialchars($type) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/events/jazz"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Create event
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
