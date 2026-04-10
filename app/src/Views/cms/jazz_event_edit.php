<?php
$pageTitle = 'Edit Jazz Event';
$backRoute = '/cms/events/jazz';
$backLabel = '← Back to Jazz Events';
$formAction = '/cms/events/jazz/' . (int)($event->event_id ?? 0);
$isEdit = true;
$submitLabel = 'Save changes';
$submitClass = 'bg-blue-600 hover:bg-blue-700';

require __DIR__ . '/partials/cms_event_form.php';
?>

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
            <label class="block text-sm font-medium text-slate-700">Venue</label>
            <select
                name="venue_id"
                required
                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="">Select venue</option>
                <?php foreach (($venues ?? []) as $venue): ?>
                    <?php $isSelected = ((int)($event->venue_id ?? 0) === (int)$venue->venue_id) ? 'selected' : ''; ?>
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
            <label class="block text-sm font-medium text-slate-700">Linked page (optional)</label>
            <select
                name="page_id"
                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="">No linked page</option>
                <?php foreach (($pages ?? []) as $page): ?>
                    <?php
                    $pageId = (string)($page['Page_ID'] ?? '');
                    $isSelected = ((string)($event->page_id ?? '') === $pageId) ? 'selected' : '';
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
<?php require __DIR__ . '/partials/cms_event_form_footer.php'; ?>