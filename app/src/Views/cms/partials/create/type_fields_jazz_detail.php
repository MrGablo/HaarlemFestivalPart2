<?php
$selectedArtistId = (string)($selectedArtistId ?? '0');
$artistOptions = is_array($artistOptions ?? null) ? $artistOptions : [];
?>
<div class="rounded-xl border border-slate-200 p-4">
    <label for="selected_artist_id" class="mb-1 block text-sm font-medium text-slate-700">Artist</label>
    <select
        id="selected_artist_id"
        name="selected_artist_id"
        required
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
        <option value="">Select artist</option>
        <?php foreach ($artistOptions as $artist): ?>
            <?php
            $artistId = (string)($artist->artist_id ?? 0);
            $artistName = (string)($artist->name ?? '');
            $isSelected = $artistId === $selectedArtistId ? ' selected' : '';
            ?>
            <option value="<?= htmlspecialchars($artistId) ?>" data-artist-name="<?= htmlspecialchars($artistName) ?>"<?= $isSelected ?>>
                <?= htmlspecialchars($artistName) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="mt-1 text-xs text-slate-500">Selecting an artist auto-fills artist name and default breadcrumb current label.</p>
</div>
