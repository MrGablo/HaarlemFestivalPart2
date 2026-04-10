<?php
/** @var \App\ViewModels\StoriesHomePageViewModel $viewModel */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewModel->hero['title'] ?? 'STORIES') ?> | Haarlem Festival</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/stories/tailwind.config.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body class="bg-black text-white antialiased font-sans m-0 p-0">

    <?php include __DIR__ . '/../partials/header.php'; ?>

    <section class="relative h-[55vh] min-h-[480px] w-full overflow-hidden">
        <img src="/<?= htmlspecialchars($viewModel->hero['image_path'] ?? '') ?>" 
             class="absolute inset-0 h-full w-full object-cover object-top">
        
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/70 to-transparent"></div>
        
        <div class="relative z-10 mx-auto flex h-full max-w-[1400px] flex-col justify-center px-8 lg:px-16">
            <div class="mb-4 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-white/50">
                <span class="opacity-100">EXPLORE HAARLEM</span>
                <span>/</span>
                <span class="text-white uppercase"><?= htmlspecialchars($viewModel->hero['title'] ?? 'STORIES') ?></span>
            </div>

            <div class="max-w-4xl">
                <h1 class="text-6xl font-black uppercase leading-[0.85] tracking-tighter sm:text-7xl lg:text-8xl m-0">
                    <span class="block text-white/20">STORIES</span>
                    <span class="block"><?= htmlspecialchars($viewModel->hero['title'] ?? 'STORIES') ?></span>
                </h1>
                
                <?php if (!empty($viewModel->hero['subtitle'])): ?>
                    <p class="mt-6 text-lg font-bold italic text-white">
                        <?= htmlspecialchars($viewModel->hero['subtitle']) ?>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($viewModel->hero['description'])): ?>
                    <p class="mt-2 max-w-md text-sm text-white/60 leading-relaxed">
                        <?= htmlspecialchars($viewModel->hero['description']) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <main class="mx-auto max-w-[1400px] px-8 py-24 lg:px-16">
        
        <section class="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:items-center mb-32">
            <div>
                <h2 class="mb-8 text-5xl font-black uppercase tracking-tighter italic m-0">
                    <?= htmlspecialchars($viewModel->introduction['title'] ?? '') ?>
                </h2>
                <div class="text-lg leading-relaxed text-white/70">
                    <?= $viewModel->introduction['body_html'] ?? '' ?>
                </div>
            </div>
            <div class="overflow-hidden border border-white/10 p-2 bg-zinc-900 rounded-xl">
                <img src="/<?= htmlspecialchars($viewModel->introduction['image_path'] ?? '') ?>" 
                     class="w-full aspect-video object-cover grayscale-[20%] transition-all hover:grayscale-0 duration-700 block">
            </div>
        </section>

        <section id="schedule">
            <?php foreach ($viewModel->days as $day): ?>
                <div class="mb-24">
                    <div class="mb-10 flex items-center gap-6">
                        <h2 class="text-5xl font-black uppercase tracking-tighter text-white m-0">
                            <?= htmlspecialchars($day['title']) ?>
                        </h2>
                        <div class="h-[2px] flex-1 bg-white opacity-20"></div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-10 gap-y-16 sm:grid-cols-2 lg:grid-cols-4">
                        <?php foreach ($day['events'] as $event): ?>
                            <a href="/stories/detail?page_id=<?= (int)$event['page_id'] ?>" class="group flex flex-col no-underline text-inherit">
                                
                                <div class="mb-4 flex items-center gap-2 text-lg font-bold">
                                    <span class="opacity-80">🕒</span>
                                    <?= htmlspecialchars($event['display_time']) ?>
                                </div>

                                <div class="mb-5 overflow-hidden rounded-xl bg-zinc-900 shadow-xl border border-transparent group-hover:border-white/20 transition-all">
                                    <img src="/<?= htmlspecialchars(!empty($event['img_background']) ? $event['img_background'] : 'assets/img/placeholder.jpg') ?>"
                                        class="block h-[180px] w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        alt="<?= htmlspecialchars($event['title']) ?>">
                                </div>

                                <h3 class="mb-2 text-2xl font-black leading-tight uppercase tracking-tighter group-hover:text-stories-accent transition-colors m-0">
                                    <?= htmlspecialchars($event['title']) ?>
                                </h3>

                                <div class="space-y-1 text-sm font-medium">
                                    <div class="flex items-center gap-2 text-white/70 italic">
                                        <span class="text-stories-accent text-[8px]">●</span>
                                        <?= htmlspecialchars($event['story_type']) ?>
                                    </div>
                                    <div class="flex items-center gap-2 text-white/50">
                                        <span class="text-stories-accent">📍</span>
                                        <?= htmlspecialchars($event['location']) ?>
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center justify-between border-t border-white/10 pt-4">
                                    <div class="flex gap-2">
                                        <span class="rounded bg-stories-accent px-2 py-0.5 text-[10px] font-black uppercase text-white">
                                            <?= htmlspecialchars($event['language']) ?>
                                        </span>
                                        <span class="rounded bg-stories-accent px-2 py-0.5 text-[10px] font-black uppercase text-white">
                                            <?= htmlspecialchars($event['age_group']) ?>
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-black uppercase tracking-widest group-hover:underline">
                                        Read more →
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>