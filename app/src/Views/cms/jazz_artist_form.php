<?php
$old = is_array($old ?? null) ? $old : [];
$content = is_array($content ?? null) ? $content : [];
$linkedEventIds = is_array($linkedEventIds ?? null) ? $linkedEventIds : [];

$contentValue = static function (array $source, array $path, string $default = ''): string {
    $cursor = $source;
    foreach ($path as $segment) {
        if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
            return $default;
        }
        $cursor = $cursor[$segment];
    }
    return is_string($cursor) ? $cursor : $default;
};

$value = static function (string $key, string $default = '') use ($old): string {
    return htmlspecialchars((string)($old[$key] ?? $default));
};

$contentText = static function (array $path, string $default = '') use ($contentValue, $content): string {
    return $contentValue($content, $path, $default);
};

$selectedEventIds = isset($old['event_ids']) && is_array($old['event_ids'])
    ? array_map('strval', $old['event_ids'])
    : array_map('strval', $linkedEventIds);

$bandItems = '';
if (isset($old['band_members_items'])) {
    $bandItems = (string)$old['band_members_items'];
} elseif (!empty($content['band_members']['items']) && is_array($content['band_members']['items'])) {
    $bandItems = implode(PHP_EOL, array_map('strval', $content['band_members']['items']));
}

$secondaryItems = is_array($content['artist']['hero_media']['secondary'] ?? null) ? $content['artist']['hero_media']['secondary'] : [];
$albums = is_array($content['albums'] ?? null) ? $content['albums'] : [];
?>

<form method="POST"
    enctype="multipart/form-data"
    action="<?= htmlspecialchars((string)$formAction) ?>"
    class="mt-6 space-y-8">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

    <div>
        <h2 class="text-lg font-semibold text-slate-900">Artist record</h2>
        <p class="mt-1 text-sm text-slate-600">This creates or updates an <span class="font-medium">Artist</span> row and a linked <span class="font-medium">Jazz_Detail_Page</span>.</p>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Artist name</label>
                <input
                    name="artist_name"
                    type="text"
                    required
                    maxlength="120"
                    value="<?= $value('artist_name', $contentText(['artist', 'name'], isset($artist) ? $artist->name : '')) ?>"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Kicker</label>
                <input
                    name="artist_kicker"
                    type="text"
                    maxlength="120"
                    value="<?= $value('artist_kicker', $contentText(['artist', 'kicker'])) ?>"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Hero title</label>
                <input
                    name="hero_title"
                    type="text"
                    maxlength="160"
                    value="<?= $value('hero_title', $contentText(['artist', 'hero_title'])) ?>"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Hero subtitle</label>
                <input
                    name="hero_subtitle"
                    type="text"
                    maxlength="160"
                    value="<?= $value('hero_subtitle', $contentText(['artist', 'hero_subtitle'])) ?>"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h2 class="text-lg font-semibold text-slate-900">Navigation</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-700">Breadcrumb current</label>
                <input name="breadcrumb_current" type="text" value="<?= $value('breadcrumb_current', $contentText(['artist', 'breadcrumb', 'current'])) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Back link</label>
                <input name="breadcrumb_back_href" type="text" value="<?= $value('breadcrumb_back_href', $contentText(['artist', 'breadcrumb', 'back_href'], '/jazz')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Back label</label>
                <input name="breadcrumb_back_label" type="text" value="<?= $value('breadcrumb_back_label', $contentText(['artist', 'breadcrumb', 'back_label'], 'Jazz Event')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h2 class="text-lg font-semibold text-slate-900">Tabs</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Events label</label>
                <input name="tabs_events_label" type="text" value="<?= $value('tabs_events_label', $contentText(['tabs', 'labels', 'events'], 'Events')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Career label</label>
                <input name="tabs_career_label" type="text" value="<?= $value('tabs_career_label', $contentText(['tabs', 'labels', 'career'], 'Career Highlights')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Album label</label>
                <input name="tabs_album_label" type="text" value="<?= $value('tabs_album_label', $contentText(['tabs', 'labels', 'album'], 'Album')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Default tab</label>
                <?php $defaultTab = (string)($old['tabs_default'] ?? ($content['tabs']['default'] ?? 'events')); ?>
                <select name="tabs_default" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    <?php foreach (['events' => 'Events', 'career' => 'Career', 'album' => 'Album'] as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $defaultTab === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h2 class="text-lg font-semibold text-slate-900">Hero Media</h2>
        <div class="mt-4 space-y-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Cover image</label>
                    <input name="cover_image_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                    <?php $currentCover = $contentValue($content, ['artist', 'cover_image']); ?>
                    <?php if ($currentCover !== ''): ?><p class="mt-1 text-xs text-slate-500">Current: <?= htmlspecialchars($currentCover) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Main hero image</label>
                    <input name="hero_main_image_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                    <?php $currentHeroMain = $contentValue($content, ['artist', 'hero_media', 'main', 'image']); ?>
                    <?php if ($currentHeroMain !== ''): ?><p class="mt-1 text-xs text-slate-500">Current: <?= htmlspecialchars($currentHeroMain) ?></p><?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Main hero caption</label>
                <input name="hero_main_caption" type="text" value="<?= $value('hero_main_caption', $contentText(['artist', 'hero_media', 'main', 'caption'])) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>

            <?php for ($slot = 1; $slot <= 2; $slot++): ?>
                <?php $existingSecondary = is_array($secondaryItems[$slot - 1] ?? null) ? $secondaryItems[$slot - 1] : []; ?>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Secondary image <?= $slot ?></h3>
                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Upload image</label>
                            <input name="hero_secondary_<?= $slot ?>_image_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                            <?php if (!empty($existingSecondary['image'])): ?><p class="mt-1 text-xs text-slate-500">Current: <?= htmlspecialchars((string)$existingSecondary['image']) ?></p><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Caption</label>
                            <input name="hero_secondary_<?= $slot ?>_caption" type="text" value="<?= $value('hero_secondary_' . $slot . '_caption', (string)($existingSecondary['caption'] ?? '')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h2 class="text-lg font-semibold text-slate-900">About and Career</h2>
        <div class="mt-4 grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">About title</label>
                <input name="about_title" type="text" value="<?= $value('about_title', $contentText(['about', 'title'], 'About the Artist:')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">About HTML</label>
                <textarea name="about_html" rows="5" class="js-wysiwyg mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"><?= htmlspecialchars((string)($old['about_html'] ?? $contentValue($content, ['about', 'html']))) ?></textarea>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Career left HTML</label>
                    <textarea name="career_left_html" rows="8" class="js-wysiwyg mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"><?= htmlspecialchars((string)($old['career_left_html'] ?? $contentValue($content, ['career_highlights', 'left_html']))) ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Career right HTML</label>
                    <textarea name="career_right_html" rows="8" class="js-wysiwyg mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"><?= htmlspecialchars((string)($old['career_right_html'] ?? $contentValue($content, ['career_highlights', 'right_html']))) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h2 class="text-lg font-semibold text-slate-900">Events and Band Members</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Linked jazz events</label>
                <select name="event_ids[]" multiple size="8" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    <?php foreach (($availableEvents ?? []) as $event): ?>
                        <?php $selected = in_array((string)$event->event_id, $selectedEventIds, true) ? 'selected' : ''; ?>
                        <option value="<?= (int)$event->event_id ?>" <?= $selected ?>>
                            #<?= (int)$event->event_id ?> · <?= htmlspecialchars($event->title) ?> · <?= htmlspecialchars($event->start_date) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-xs text-slate-500">Hold Ctrl or Cmd to select multiple events.</p>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Ticket button label</label>
                    <input name="ticket_button_label" type="text" value="<?= $value('ticket_button_label', $contentText(['events', 'ticket_button_label'], 'Tickets')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Band members title</label>
                    <input name="band_members_title" type="text" value="<?= $value('band_members_title', $contentText(['band_members', 'title'], 'Band Members:')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Band members</label>
                    <textarea name="band_members_items" rows="6" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"><?= htmlspecialchars($bandItems) ?></textarea>
                    <p class="mt-1 text-xs text-slate-500">One member per line.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Albums</h2>
                <p class="mt-1 text-sm text-slate-600">Up to three album cards, each with its own uploaded image.</p>
            </div>
        </div>

        <div class="mt-4 space-y-4">
            <?php for ($slot = 1; $slot <= 3; $slot++): ?>
                <?php $existingAlbum = is_array($albums[$slot - 1] ?? null) ? $albums[$slot - 1] : []; ?>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Album <?= $slot ?></h3>
                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Album image</label>
                            <input name="album_<?= $slot ?>_image_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                            <?php if (!empty($existingAlbum['image'])): ?><p class="mt-1 text-xs text-slate-500">Current: <?= htmlspecialchars((string)$existingAlbum['image']) ?></p><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Album title</label>
                            <input name="album_<?= $slot ?>_title" type="text" value="<?= $value('album_' . $slot . '_title', (string)($existingAlbum['title'] ?? '')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Album artist</label>
                            <input name="album_<?= $slot ?>_artist" type="text" value="<?= $value('album_' . $slot . '_artist', (string)($existingAlbum['artist'] ?? '')) ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Description HTML</label>
                            <textarea name="album_<?= $slot ?>_description_html" rows="5" class="js-wysiwyg mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"><?= htmlspecialchars((string)($old['album_' . $slot . '_description_html'] ?? (string)($existingAlbum['description_html'] ?? ''))) ?></textarea>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
        <a href="/cms/jazz/artists"
            class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
            Cancel
        </a>
        <button type="submit"
            class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <?= htmlspecialchars((string)$submitLabel) ?>
        </button>
    </div>
</form>