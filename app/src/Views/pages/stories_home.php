<?php
/** @var \App\ViewModels\StoriesHomePageViewModel $viewModel */
$hero = $viewModel->hero;
$introduction = $viewModel->introduction;
$days = $viewModel->days;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hero['title'] ?? 'Haarlem Stories') ?></title>

    <script>
        tailwind = {
            config: {
                corePlugins: { preflight: false }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-[#0b0b0b] font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-white">

<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- HERO -->
<header class="relative flex min-h-[450px] w-full items-center overflow-hidden bg-black">
    <img src="/<?= htmlspecialchars($hero['image_path'] ?? '') ?>"
         class="absolute inset-0 h-full w-full object-cover opacity-40" alt="Hero Background">

    <div class="relative z-10 px-10 md:px-24">
        <h1 class="text-6xl font-black uppercase tracking-tighter md:text-8xl">
            <?= htmlspecialchars($hero['title'] ?? 'STORIES') ?>
        </h1>

        <p class="mt-4 max-w-xl text-xl text-gray-200">
            <?= htmlspecialchars($hero['subtitle'] ?? '') ?>
        </p>
    </div>
</header>

<!-- INTRO -->
<section class="mx-auto flex max-w-[1300px] flex-col gap-12 px-10 py-24 md:flex-row md:items-center">

    <div class="flex-1">
        <h2 class="mb-8 text-4xl font-extrabold uppercase tracking-tight">
            <?= htmlspecialchars($introduction['title'] ?? '') ?>
        </h2>

        <div class="text-lg leading-relaxed text-gray-400">
            <?= $introduction['body_html'] ?? '' ?>
        </div>
    </div>

    <div class="flex-1">
        <div class="overflow-hidden rounded-2xl shadow-2xl">
            <img src="/<?= htmlspecialchars($introduction['image_path'] ?? '') ?>"
                 class="block w-full aspect-[16/9] object-contain bg-black"
                 alt="Introduction">
        </div>
    </div>

</section>

<!-- SCHEDULE -->
<section id="schedule" class="bg-[#0b0b0b] px-6 py-20">

    <?php foreach ($days as $day): ?>
        <div class="mx-auto mb-20 max-w-[1150px]">

            <div class="mb-10 flex items-center gap-6">
                <h2 class="text-4xl font-black uppercase tracking-tighter">
                    <?= htmlspecialchars($day['title']) ?>
                </h2>
                <div class="h-[1px] flex-1 bg-white opacity-20"></div>
            </div>

            <div class="flex flex-wrap justify-center gap-x-6 gap-y-12 md:justify-start">

                <?php foreach ($day['events'] as $event): ?>

                    <article class="group flex w-[260px] flex-col">

                        <div class="mb-3 flex items-center gap-2 text-[13px] font-bold">
                            <span class="opacity-70 text-xs">🕒</span>
                            <?= htmlspecialchars($event['display_time']) ?>
                        </div>

                        <div class="mb-4 overflow-hidden rounded-xl bg-black">
                            <img
                                src="/<?= htmlspecialchars(!empty($event['img_background']) ? $event['img_background'] : 'assets/img/placeholder.jpg') ?>"
                                class="block h-[170px] w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                alt="<?= htmlspecialchars($event['title']) ?>"
                            >
                        </div>

                        <div class="flex flex-1 flex-col">

                            <h3 class="mb-1 text-lg font-black leading-tight group-hover:text-gray-300">
                                <?= htmlspecialchars($event['title']) ?>
                            </h3>

                            <div class="mb-1 flex items-center gap-1 text-[11px] font-semibold text-[#e63946]">
                                <span class="text-[8px]">●</span>
                                <span class="italic"><?= htmlspecialchars($event['story_type']) ?></span>
                            </div>

                            <div class="mb-4 flex items-center gap-1 text-[11px] text-gray-400">
                                <span class="text-[#e63946]">📍</span>
                                <?= htmlspecialchars($event['location']) ?>
                            </div>

                            <div class="mt-auto flex items-center justify-between">
                                <div class="flex gap-1">
                                    <span class="rounded-sm bg-[#e63946] px-2 py-0.5 text-[10px] font-black text-white">
                                        <?= htmlspecialchars($event['language']) ?>
                                    </span>
                                    <span class="rounded-sm bg-[#e63946] px-2 py-0.5 text-[10px] font-black text-white">
                                        <?= htmlspecialchars($event['age_group']) ?>
                                    </span>
                                </div>

                                <button class="text-[11px] font-bold underline underline-offset-4 hover:text-gray-400">
                                    Read more →
                                </button>
                            </div>

                        </div>
                    </article>

                <?php endforeach; ?>

            </div>
        </div>
    <?php endforeach; ?>

</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>