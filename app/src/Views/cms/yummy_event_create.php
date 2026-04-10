<?php
$pageTitle = 'Create Yummy Event';
$pageSubtitle = 'Add a new yummy session with event and specific details.';
$backRoute = '/cms/events/yummy';
$backLabel = '← Back to Yummy Events';
$formAction = '/cms/events/yummy/create';
$isEdit = false;
$submitLabel = 'Create event';
$submitClass = 'bg-emerald-600 hover:bg-emerald-700';

$old = is_array($old ?? null) ? $old : [];
$v = static function (string $key, string $default = '') use ($old): string {
    return htmlspecialchars((string)($old[$key] ?? $default));
};

require __DIR__ . '/partials/cms_event_form.php';
?>

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Yummy details</h2>
                    <p class="mt-1 text-sm text-slate-600">Stored in the <span class="font-medium">YummyEvent</span> table.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start Time</label>
                            <input
                                name="start_time"
                                type="datetime-local"
                                required
                                value="<?= $v('start_time') ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">End Time</label>
                            <input
                                name="end_time"
                                type="datetime-local"
                                required
                                value="<?= $v('end_time') ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Cuisine</label>
                            <input
                                name="cuisine"
                                type="text"
                                required
                                value="<?= $v('cuisine') ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Star Rating (0-5)</label>
                            <input
                                name="star_rating"
                                type="number"
                                min="0"
                                max="5"
                                required
                                value="<?= $v('star_rating', '0') ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Availability (seats)</label>
                            <input
                                name="availability"
                                type="number"
                                min="1"
                                required
                                value="<?= $v('availability', '300') ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price (€)</label>
                            <input
                                name="price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                value="<?= $v('price', '0.00') ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Thumbnail Image (optional)</label>
                            <input
                                name="thumbnail_path_file"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,.gif"
                                class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm
                                       file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700
                                       hover:file:bg-slate-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Linked page (optional)</label>
                            <select
                                name="page_id"
                                class="mt-1 w-full sm:w-1/2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
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
<?php require __DIR__ . '/partials/cms_event_form_footer.php'; ?>