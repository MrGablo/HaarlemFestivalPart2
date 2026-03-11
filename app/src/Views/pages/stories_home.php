<?php
/** @var \App\ViewModels\StoriesHomePageViewModel $viewModel */
$content = $viewModel->pageContent;
$events = $viewModel->events;

// 1. Group events by day so we can create 4 separate sections
$groupedEvents = [];
foreach ($events as $event) {
    $day = $event['day_key']; // e.g., "Thursday"
    $groupedEvents[$day][] = $event;
}

// 2. Define the order of days we want to display
$displayDays = ['Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($content['hero']['title'] ?? 'Haarlem Stories') ?></title>
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

    <header class="relative flex min-h-[450px] w-full items-center justify-start overflow-hidden bg-black">
        <img src="/<?= htmlspecialchars($content['hero']['image_path'] ?? '') ?>" 
             class="absolute inset-0 h-full w-full object-cover opacity-40" alt="Hero Background">
        <div class="relative z-10 px-10 md:px-24">
            <h1 class="m-0 text-6xl font-black uppercase tracking-tighter md:text-8xl">
                <?= htmlspecialchars($content['hero']['title'] ?? 'STORIES') ?>
            </h1>
            <p class="mt-4 max-w-xl text-xl font-medium text-gray-200">
                <?= htmlspecialchars($content['hero']['subtitle'] ?? '') ?>
            </p>
        </div>
    </header>

    <section class="mx-auto flex max-w-[1300px] flex-col gap-12 px-10 py-24 md:flex-row md:items-center">
        <div class="flex-1">
            <h2 class="mb-8 text-4xl font-extrabold uppercase tracking-tight">
                <?= htmlspecialchars($content['introduction']['title'] ?? 'A CITY FULL OF STORIES') ?>
            </h2>
            <div class="text-lg leading-relaxed text-gray-400">
                <?= $content['introduction']['body_html'] ?? '' ?>
            </div>
        </div>
        <div class="flex-1">
            <div class="overflow-hidden rounded-2xl shadow-2xl">
                <img src="/<?= htmlspecialchars($content['introduction']['image_path'] ?? '') ?>" 
                     class="block w-full aspect-[16/9] object-contain bg-black" 
                     alt="Introduction Visual">
            </div>
        </div>
    </section>

    <section id="schedule" class="bg-[#0b0b0b] px-10 pb-20 md:px-24">
        
        <?php foreach ($displayDays as $dayTitle): ?>
            <?php if (isset($groupedEvents[$dayTitle])): ?>
                <div class="day-section mb-20">
                    <div class="mb-10 flex items-center gap-6">
                        <h2 class="m-0 text-5xl font-black uppercase tracking-tighter"><?= $dayTitle ?></h2>
                        <div class="h-[2px] flex-1 bg-white opacity-20"></div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
                        <?php foreach ($groupedEvents[$dayTitle] as $event): ?>
                            <article class="story-card group flex flex-col">
                                
                                <div class="mb-3 flex items-center gap-2 text-lg font-bold">
                                    <span class="opacity-70">🕒</span>
                                    <?= htmlspecialchars($event['display_time']) ?>
                                </div>

                                <div class="relative mb-5 overflow-hidden rounded-xl bg-black">
                                    <img src="/<?= htmlspecialchars($event['img_background'] ?? 'assets/img/placeholder.jpg') ?>" 
                                         class="block w-full aspect-[16/9] object-contain transition-transform duration-500 group-hover:scale-105" 
                                         alt="<?= htmlspecialchars($event['title']) ?>">
                                    
                                    <div class="absolute bottom-3 left-3 flex gap-2">
                                        <span class="rounded bg-[#e63946] px-2 py-1 text-[10px] font-black uppercase tracking-wider text-white">
                                            <?= htmlspecialchars($event['language']) ?>
                                        </span>
                                        <span class="rounded bg-white px-2 py-1 text-[10px] font-black uppercase tracking-wider text-black">
                                            <?= htmlspecialchars($event['age_group']) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col">
                                    <h3 class="mb-2 text-2xl font-black leading-tight group-hover:text-gray-300">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h3>
                                    <div class="mb-1 flex items-center gap-2 text-sm font-semibold text-[#e63946]">
                                        <span class="text-xs">●</span> <?= htmlspecialchars($event['story_type']) ?>
                                    </div>
                                    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
                                        <span>📍</span> <?= htmlspecialchars($event['location']) ?>
                                    </div>

                                    <div class="mt-auto flex items-center justify-between pt-4">
                                        <button class="cursor-pointer border-0 bg-transparent p-0 text-sm font-bold text-white underline underline-offset-4 hover:text-gray-400">
                                            Read more →
                                        </button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

    </section>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>