<?php
if (!function_exists('toDatetimeLocal')) {
    /**
     * Converts "YYYY-MM-DD HH:MM:SS" or DateTime-like string to "YYYY-MM-DDTHH:MM"
     * for <input type="datetime-local">
     */
    function toDatetimeLocal($value): string
    {
        if ($value === null || $value === '') return '';
        $s = (string)$value;

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $s)) {
            return str_replace(' ', 'T', substr($s, 0, 16));
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $s)) {
            return substr($s, 0, 16);
        }

        try {
            $dt = new DateTime($s);
            return $dt->format('Y-m-d\TH:i');
        } catch (Throwable $e) {
            return '';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'CMS Event') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                        <?= htmlspecialchars($pageTitle ?? 'Event Form') ?></h1>
                    <p class="mt-1 text-sm text-slate-600">
                        <?php if ($isEdit ?? false): ?>
                            Event ID: <span class="font-medium text-slate-900"><?= (int)($event->event_id ?? 0) ?></span>
                            · Type: <span
                                class="font-medium text-slate-900"><?= htmlspecialchars((string)($event->event_type ?? '')) ?></span>
                        <?php else: ?>
                            <?= htmlspecialchars($pageSubtitle ?? '') ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="<?= htmlspecialchars($backRoute ?? '/cms/events') ?>"
                        class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        <?= htmlspecialchars($backLabel ?? '← Back') ?>
                    </a>
                </div>
            </div>

            <?php require __DIR__ . '/../../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../../partials/error_general.php'; ?>

            <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($formAction ?? '') ?>"
                class="mt-6 space-y-8">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <!-- Parent Event fields -->
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">General Information</h2>
                    <p class="mt-1 text-sm text-slate-600">Stored in the <span class="font-medium">Event</span> table.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="<?= ($isEdit ?? false) ? 'sm:col-span-1' : 'sm:col-span-2' ?>">
                            <label class="block text-sm font-medium text-slate-700">Event Title</label>
                            <input name="title" type="text" maxlength="120" required
                                value="<?= htmlspecialchars((string)(($isEdit ?? false) ? ($event->title ?? '') : ($old['title'] ?? ''))) ?>"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>
                    </div>
                </div>