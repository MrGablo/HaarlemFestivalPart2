<?php

use App\Utils\CmsForm;

$tinyMceApiKey = trim((string)($_ENV['TINYMCE_API_KEY'] ?? $_SERVER['TINYMCE_API_KEY'] ?? getenv('TINYMCE_API_KEY') ?: ''));
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create CMS Page</title>
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
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create page</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        <?= CmsForm::h((string)$pageTypeLabel) ?> · <?= CmsForm::h((string)$pageType) ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="/cms/page/create"
                        class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Change type
                    </a>
                    <a href="/cms/pages" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to pages</a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="POST" enctype="multipart/form-data" action="/cms/page/create/<?= urlencode((string)$pageType) ?>" class="mt-6 space-y-4">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div class="rounded-xl border border-slate-200 p-4">
                    <label for="page_title" class="mb-1 block text-sm font-medium text-slate-700">Page title</label>
                    <input
                        id="page_title"
                        name="page_title"
                        type="text"
                        required
                        value="<?= htmlspecialchars((string)($pageTitle ?? '')) ?>"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <?php CmsForm::renderSchema($editorSchema, $content); ?>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        Create page
                    </button>
                    <a href="/cms/pages" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        const tinyConfig = {
            menubar: false,
            plugins: 'link lists code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
            height: 260,
            branding: false,
            promotion: false,
            convert_urls: false
        };

        function initTinyEditors(root = document) {
            if (!window.tinymce) {
                return;
            }

            root.querySelectorAll('textarea.js-wysiwyg').forEach((textarea) => {
                if (textarea.dataset.wysiwygInitialized === '1') {
                    return;
                }

                textarea.dataset.wysiwygInitialized = '1';
                tinymce.init({
                    ...tinyConfig,
                    target: textarea
                });
            });
        }

        initTinyEditors();

        document.querySelectorAll('[data-repeater]').forEach((repeater) => {
            const items = repeater.querySelector('[data-repeater-items]');
            const template = repeater.querySelector('template[data-repeater-template]');

            repeater.addEventListener('click', (event) => {
                const addButton = event.target.closest('[data-repeater-add]');
                if (addButton) {
                    const nextIndex = Number(repeater.dataset.nextIndex || '0');
                    const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html.trim();
                    const item = wrapper.firstElementChild;

                    if (item) {
                        items.appendChild(item);
                        repeater.dataset.nextIndex = String(nextIndex + 1);
                        initTinyEditors(item);
                    }
                    return;
                }

                const removeButton = event.target.closest('[data-repeater-remove]');
                if (removeButton) {
                    const item = removeButton.closest('[data-repeater-item]');
                    if (item) {
                        item.remove();
                    }
                }
            });
        });
    </script>
</body>

</html>
