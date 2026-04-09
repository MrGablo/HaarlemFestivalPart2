<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create History Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create History Event</h1>
                    <p class="mt-1 text-sm text-slate-600">Add a new bookable history tour session.</p>
                </div>

                <a href="/cms/events/history"
                    class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                    ← Back to History Events
                </a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <?php
            $old = is_array($old ?? null) ? $old : [];
            $titleValue = htmlspecialchars((string)($old['title'] ?? ''));
            $availabilityValue = htmlspecialchars((string)($old['availability'] ?? '12'));
            $languageValue = (string)($old['language'] ?? 'NL');
            $startDateValue = htmlspecialchars((string)($old['start_date'] ?? ''));
            $locationValue = htmlspecialchars((string)($old['location'] ?? 'Historic city centre'));
            $priceValue = htmlspecialchars((string)($old['price'] ?? '17.50'));
            $familyPriceValue = htmlspecialchars((string)($old['family_price'] ?? '60.00'));
            ?>

            <form method="POST" action="/cms/events/history/create" class="mt-6 space-y-8">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">General</h2>
                    <p class="mt-1 text-sm text-slate-600">Stored across the Event and HistoryEvent tables.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Title</label>
                            <input name="title" type="text" maxlength="120" required value="<?= $titleValue ?>"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Availability</label>
                            <input name="availability" type="number" min="0" step="1" required value="<?= $availabilityValue ?>"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Language</label>
                            <select name="language" required
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <?php foreach (['NL', 'EN', 'CH'] as $language): ?>
                                    <option value="<?= $language ?>" <?= ($languageValue === $language) ? 'selected' : '' ?>><?= $language ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start date</label>
                            <input name="start_date" type="datetime-local" required value="<?= $startDateValue ?>"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Location</label>
                            <input name="location" type="text" required value="<?= $locationValue ?>"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price</label>
                            <input name="price" type="number" min="0" step="0.01" required value="<?= $priceValue ?>"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Family price</label>
                            <input name="family_price" type="number" min="0" step="0.01" required value="<?= $familyPriceValue ?>"
                                   class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>
                    </div>
                </div>

                <?php if (!empty($detailPages)): ?>
                    <div class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-900">Available History Detail Pages</h2>
                        <p class="mt-1 text-sm text-slate-600">These detail pages are managed separately through the CMS pages module.</p>
                        <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-slate-700">
                            <?php foreach ($detailPages as $page): ?>
                                <li><?= htmlspecialchars((string)($page['Page_Title'] ?? 'Untitled')) ?> (Page ID <?= (int)($page['Page_ID'] ?? 0) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="/cms/events/history"
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