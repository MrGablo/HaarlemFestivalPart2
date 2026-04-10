<?php
$pageTitle = 'Edit Yummy Event';
$backRoute = '/cms/events/yummy';
$backLabel = '← Back to Yummy Events';
$formAction = '/cms/events/yummy/' . (int)($event->event_id ?? 0);
$isEdit = true;
$submitLabel = 'Save changes';
$submitClass = 'bg-blue-600 hover:bg-blue-700';

require __DIR__ . '/partials/cms_event_form.php';
?>

                <!-- Child YummyEvent fields -->
                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold text-slate-900">Yummy details</h2>
                    <p class="mt-1 text-sm text-slate-600">These are stored in the <span
                            class="font-medium">YummyEvent</span> table.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Start Time</label>
                            <input name="start_time" type="datetime-local" required
                                value="<?= htmlspecialchars((string)toDatetimeLocal($event->start_time ?? null)) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">End Time</label>
                            <input name="end_time" type="datetime-local" required
                                value="<?= htmlspecialchars((string)toDatetimeLocal($event->end_time ?? null)) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Cuisine</label>
                            <input name="cuisine" type="text" required
                                value="<?= htmlspecialchars((string)($event->cuisine ?? '')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Star Rating (0-5)</label>
                            <input name="star_rating" type="number" min="0" max="5" required
                                value="<?= (int)($event->star_rating ?? 0) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Availability (seats)</label>
                            <input name="availability" type="number" min="1" required
                                value="<?= (int)($event->availability ?? 300) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Price (€)</label>
                            <input name="price" type="number" step="0.01" min="0" required
                                value="<?= htmlspecialchars((string)($event->price ?? '0.00')) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Thumbnail Image (optional)</label>
                            <input name="thumbnail_path_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm
                                       file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700
                                       hover:file:bg-slate-200">
                            <?php if (!empty($event->thumbnail_path)): ?>
                                <p class="mt-3 text-xs text-slate-600">
                                    Current image path: <span
                                        class="font-mono"><?= htmlspecialchars((string)$event->thumbnail_path) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Linked page (optional)</label>
                            <select name="page_id"
                                class="mt-1 w-full sm:w-1/2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
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
