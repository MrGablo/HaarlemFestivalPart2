<?php
/**
 * @var array $pages Array of \App\Models\Page objects
 * @var array $old
 * @var array $errors
 * @var string $csrfToken
 */
$pageIdStr = (string)($old['page_id'] ?? '');

$startTimeStr = (string)($old['start_time'] ?? '');
$endTimeStr = (string)($old['end_time'] ?? '');
if ($startTimeStr) {
    try {
        $startTimeStr = (new \DateTime($startTimeStr))->format('Y-m-d\TH:i');
    } catch (\Exception $e) { }
}
if ($endTimeStr) {
    try {
        $endTimeStr = (new \DateTime($endTimeStr))->format('Y-m-d\TH:i');
    } catch (\Exception $e) { }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Yummy Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="container mx-auto p-4 mb-16">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold mb-6">Create Yummy Event</h1>

        <?php require __DIR__ . '/../partials/error_general.php'; ?>

        <form action="/cms/events/yummy/create" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold mb-1" for="title">Title *</label>
                    <input type="text" id="title" name="title" required
                           value="<?= htmlspecialchars((string)($old['title'] ?? '')) ?>"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1" for="cuisine">Cuisine *</label>
                    <input type="text" id="cuisine" name="cuisine" required
                           value="<?= htmlspecialchars((string)($old['cuisine'] ?? '')) ?>"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold mb-1" for="start_time">Start Time *</label>
                    <input type="datetime-local" id="start_time" name="start_time" required
                           value="<?= htmlspecialchars($startTimeStr) ?>"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1" for="end_time">End Time *</label>
                    <input type="datetime-local" id="end_time" name="end_time" required
                           value="<?= htmlspecialchars($endTimeStr) ?>"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold mb-1" for="price">Price *</label>
                    <input type="number" step="0.01" min="0" id="price" name="price" required
                           value="<?= htmlspecialchars((string)($old['price'] ?? '')) ?>"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1" for="availability">Availability *</label>
                    <input type="number" min="1" id="availability" name="availability" required
                           value="<?= htmlspecialchars((string)($old['availability'] ?? '')) ?>"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1" for="star_rating">Star Rating (0-5)</label>
                    <input type="number" min="0" max="5" id="star_rating" name="star_rating"
                           value="<?= htmlspecialchars((string)($old['star_rating'] ?? '')) ?>"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1" for="page_id">Linked Details Page</label>
                <select id="page_id" name="page_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">-- None --</option>
                    <?php foreach ($pages as $p): ?>
                        <?php $pData = (array)$p; ?>
                        <option value="<?= (int)($pData['Page_ID'] ?? 0) ?>" <?= $pageIdStr === (string)($pData['Page_ID'] ?? '') ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)($pData['Page_Title'] ?? 'Untitled Page')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Select a CMS Page to provide extra info when users click on this event.</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-1" for="thumbnail_path_file">Thumbnail Image</label>
                <input type="file" id="thumbnail_path_file" name="thumbnail_path_file" accept=".jpg,.jpeg,.png,.webp,.gif"
                       class="block w-full text-sm text-gray-600
                              file:mr-4 file:py-2 file:px-4
                              file:rounded file:border-0
                              file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100">
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="/cms/events/yummy" class="text-sm font-medium text-gray-600 hover:underline">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</main>

</body>
</html>