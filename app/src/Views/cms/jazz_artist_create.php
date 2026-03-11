<!doctype html>
<html lang="en">

<?php
$tinyMceApiKey = trim((string)($_ENV['TINYMCE_API_KEY'] ?? $_SERVER['TINYMCE_API_KEY'] ?? getenv('TINYMCE_API_KEY') ?: 'no-api-key'));
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Jazz Artist</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script
        src="https://cdn.tiny.cloud/1/<?= htmlspecialchars($tinyMceApiKey, ENT_QUOTES, 'UTF-8') ?>/tinymce/6/tinymce.min.js"
        referrerpolicy="origin">
    </script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-6xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create Jazz Artist</h1>
                    <p class="mt-1 text-sm text-slate-600">Build a new artist record and Jazz_Detail_Page content entry.</p>
                </div>

                <a href="/cms/jazz/artists"
                    class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                    ← Back to Jazz Artists
                </a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <?php
            $formAction = '/cms/jazz/artists/create';
            $submitLabel = 'Create artist';
            $content = [];
            $linkedEventIds = [];
            require __DIR__ . '/jazz_artist_form.php';
            ?>
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