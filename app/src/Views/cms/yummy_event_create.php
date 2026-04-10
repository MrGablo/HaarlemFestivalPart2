<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Yummy Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create Yummy Event</h1>
                    <p class="mt-1 text-sm text-slate-600">Add a new Yummy restaurant session.</p>
                </div>

                <a href="/cms/events/yummy"
                    class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                    ← Back to Yummy Events
                </a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <?php
            $old = is_array($old ?? null) ? $old : [];
            $titleValue = htmlspecialchars((string)($old['title'] ?? ''));
            $availabilityValue = htmlspecialchars((string)($old['availability'] ?? '40'));
            $cuisineValue = htmlspecialchars((string)($old['cuisine'] ?? 'Dutch'));
            $startTimeValue = htmlspecialchars((string)($old['start_time'] ?? ''));
            $endTimeValue = htmlspecialchars((string)($old['end_time'] ?? ''));
            $priceValue = htmlspecialchars((string)($old['price'] ?? '10.00'));
            $starRatingValue = htmlspecialchars((string)($old['star_rating'] ?? '4'));
            $pageIdValue = htmlspecialchars((string)($old['page_id'] ?? ''));
            ?>

            <form method="POST" action="/cms/events/yummy/create" enctype="multipart/form-data" class="mt-6 space-y-8">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">General</h2>
                    <p class="mt-1 text-sm text-slate-600">Stored across the Event and YummyEvent tables.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Title (Restaurant name)</label>
                            <input name="title" type="text" maxlength="120" required value="<?= $titleValue ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Reservation Capacity</label>
                            <input name="availability" type="number" min="0" step="1" required value="<?= $availabilityValue ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Cuisine</label>
                            <input name="cuisine" type="text" required value="<?= $cuisineValue ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start date & time</label>
                            <input name="start_time" type="datetime-local" required value="<?= $startTimeValue ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">End date & time</label>
                            <input name="end_time" type="datetime-local" required value="<?= $endTimeValue ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price (Reservation fee)</label>
                            <input name="price" type="number" min="0" step="0.01" required value="<?= $priceValue ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Star Rating (1-5)</label>
                            <input name="star_rating" type="number" min="1" max="5" step="1" required value="<?= $starRatingValue ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Thumbnail Image (optional)</label>
                            <input name="thumbnail_path_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif"
                                class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Linked CMS Page (optional)</label>
                            <select name="page_id" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">No linked page</option>
                                <?php foreach (($detailPages ?? []) as $page): ?>
                                    <option value="<?= htmlspecialchars((string)($page['Page_ID'] ?? '')) ?>" <?= ($pageIdValue === (string)($page['Page_ID'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string)($page['Page_Title'] ?? 'Untitled')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/events/yummy"
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