<?php

use App\Utils\CmsForm;

//get the TinyMCE API key from env variables, with fallback to empty string if not set
$tinyMceApiKey = trim((string)($_ENV['TINYMCE_API_KEY'] ?? $_SERVER['TINYMCE_API_KEY'] ?? getenv('TINYMCE_API_KEY') ?: ''));
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Edit Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script
        src="https://cdn.tiny.cloud/1/<?= htmlspecialchars($tinyMceApiKey, ENT_QUOTES, 'UTF-8') ?>/tinymce/6/tinymce.min.js"
        referrerpolicy="origin">
    </script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit page content</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        #<?= (int)($page['Page_ID'] ?? 0) ?> · <?= CmsForm::h((string)($page['Page_Title'] ?? '')) ?> ·
                        <?= CmsForm::h((string)($page['Page_Type'] ?? '')) ?>
                    </p>
                </div>
                <a href="/cms" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to CMS</a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="POST" action="/cms/page/<?= (int)($page['Page_ID'] ?? 0) ?>/update" class="mt-6 space-y-4">
                <?php if ($content === []): ?>
                    <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">This page currently
                        has empty JSON content.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-3">
                        <?php CmsForm::renderContent($content); ?>
                    </div>
                <?php endif; ?>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Save content
                    </button>
                    <a href="/cms" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        if (window.tinymce) {
            tinymce.init({
                selector: 'textarea.js-wysiwyg',
                menubar: false,
                plugins: 'link lists code',
                toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
                height: 260,
                branding: false,
                promotion: false,
                convert_urls: false
            });
        }
    </script>
</body>

</html>