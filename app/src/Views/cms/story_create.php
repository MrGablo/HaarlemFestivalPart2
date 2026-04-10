<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Stories Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create New Stories Event</h1>
                    <p class="mt-1 text-sm text-slate-600">Fill in the details below to add a new event to the CMS.</p>
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
                action="/cms/events/stories/create" 
                class="mt-6 space-y-8">
                
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">General</h2>
                    <p class="mt-1 text-sm text-slate-600">Primary event information.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Title</label>
                            <input
                                name="title"
                                type="text"
                                required
                                placeholder="e.g. The Mystery of the Old Oak"
                                value="<?= htmlspecialchars((string)($_POST['title'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Event type</label>
                            <input
                                type="text"
                                value="stories"
                                readonly
                                class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Stories details</h2>
                    <p class="mt-1 text-sm text-slate-600">Specific details for the story telling session.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Language</label>
                            <select
                                name="language"
                                required
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">Select language</option>
                                <option value="NL" <?= (($_POST['language'] ?? '') === 'NL') ? 'selected' : '' ?>>NL</option>
                                <option value="ENG" <?= (($_POST['language'] ?? '') === 'ENG') ? 'selected' : '' ?>>ENG</option>
                                <option value="NL/ENG" <?= (($_POST['language'] ?? '') === 'NL/ENG') ? 'selected' : '' ?>>NL/ENG</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Age group</label>
                            <input
                                name="age_group"
                                type="text"
                                required
                                placeholder="e.g. 6-12 years"
                                value="<?= htmlspecialchars((string)($_POST['age_group'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Story type</label>
                            <input
                                name="story_type"
                                type="text"
                                required
                                placeholder="e.g. Fantasy"
                                value="<?= htmlspecialchars((string)($_POST['story_type'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Location</label>
                            <input
                                name="location"
                                type="text"
                                required
                                placeholder="e.g. Library Hall A"
                                value="<?= htmlspecialchars((string)($_POST['location'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Description</label>
                            <textarea
                                name="description"
                                rows="5"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"><?= htmlspecialchars((string)($_POST['description'] ?? '')) ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start date</label>
                            <input
                                name="start_date"
                                type="datetime-local"
                                required
                                value="<?= htmlspecialchars((string)($_POST['start_date'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">End date</label>
                            <input
                                name="end_date"
                                type="datetime-local"
                                required
                                value="<?= htmlspecialchars((string)($_POST['end_date'] ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price (€)</label>
                            <input
                                name="price"
                                type="number"
                                step="0.01"
                                min="0"
                                value="<?= htmlspecialchars((string)($_POST['price'] ?? '0.00')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Background image</label>
                            <input
                                name="img_background_file"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,.gif"
                                class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm
                                file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700
                                hover:file:bg-slate-200">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/events/stories"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        Create Event
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>