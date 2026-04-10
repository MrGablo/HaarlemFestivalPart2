<?php
use App\Config;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['hero']['title'] ?? 'Haarlem Festival'); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/home/tailwind.config.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800;900&display=swap" rel="stylesheet">
</head>

<body class="bg-[#f4f9ff] lg:bg-white text-[#333] leading-[1.6] m-0 p-0 font-sans box-border overflow-x-hidden">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container mx-auto w-full max-w-[1200px] px-5 lg:px-5">

        <header class="flex flex-col items-start justify-between gap-6 overflow-visible pt-10 pb-16 lg:flex-row lg:items-center lg:gap-5 lg:py-20">
            <div class="w-full max-w-[500px] flex-none text-left lg:w-[45%]">
                <h1 class="mb-4 text-[3.2rem] font-extrabold leading-[1.05] tracking-tight text-black lg:mb-[25px] lg:text-[4.5rem]">
                    <?php 
                        $title = $content['hero']['title'] ?? 'Discover Haarlem Festival';
                        $parts = explode('Haarlem', $title);
                        echo htmlspecialchars($parts[0]);
                    ?>
                    <span class="block text-[#3fa9f5]">Haarlem</span>
                    <span class="block text-[#3fa9f5]"><?php echo htmlspecialchars(trim($parts[1] ?? 'Festival')); ?></span>
                </h1>

                <div class="hero-subtitle-container text-[1rem] font-medium leading-[1.5] text-[#333] lg:mx-0 lg:text-[1.1rem] [&_p]:mb-2">
                    <?php echo $content['hero']['subtitle_html'] ?? ''; ?>
                </div>
            </div>

            <div class="relative mt-6 flex w-full flex-none items-center justify-center lg:mt-[60px] lg:w-[50%]">
                <div class="relative block w-[85%] max-w-[340px] lg:hidden">
                    <div class="absolute -bottom-6 -right-4 -top-4 left-6 z-0 rounded-[1.5rem] bg-[#3fa9f5]"></div>
                    <img src="/<?php echo htmlspecialchars($content['hero']['images'][4] ?? 'assets/img/homepage/hero-mobile.jpg'); ?>"
                         class="relative z-10 block h-[220px] w-full rounded-2xl object-cover shadow-md" alt="Festival Crowd">
                </div>

                <div class="relative z-10 hidden w-full grid-cols-[1fr_1.6fr_1fr] items-center gap-[15px] lg:grid">
                    <div class="absolute left-1/2 top-1/2 z-0 h-[75%] w-[105%] -translate-x-1/2 -translate-y-1/2 rounded-[40px] bg-[#3fa9f5]"></div>
                    
                    <div class="z-10 flex flex-col items-end gap-[15px] text-right">
                        <div class="flex w-fit flex-col items-end text-right">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][0] ?? ''); ?>"
                                class="block h-[180px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Dance">
                        </div>
                        <div class="flex w-fit flex-col items-end text-right">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][4] ?? ''); ?>"
                                class="block h-[200px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Crowd">
                        </div>
                    </div>

                    <div class="z-10 flex w-fit flex-col">
                        <img src="/<?php echo htmlspecialchars($content['hero']['images'][3] ?? ''); ?>"
                            class="block h-[420px] w-full max-w-full rounded-[20px] object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Church">
                    </div>

                    <div class="z-10 flex flex-col items-start gap-[15px] text-left">
                        <div class="flex w-fit flex-col items-start text-left">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][2] ?? ''); ?>"
                                class="block h-[200px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Fireworks">
                        </div>
                        <div class="flex w-fit flex-col items-start text-left">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][1] ?? ''); ?>"
                                class="block h-[180px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15_rgba(0,0,0,0.15)]" alt="City">
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="flex flex-col items-center gap-10 py-10 lg:flex-row lg:gap-[60px] lg:py-[80px]">
            <div class="flex-[1.2] text-left">
                <h2 class="mb-4 text-[2rem] font-extrabold leading-tight lg:mb-[25px] lg:text-[2.5rem]"><?php echo htmlspecialchars($content['introduction']['title'] ?? ''); ?></h2>
                <div class="mb-5 text-[1rem] leading-[1.6] text-[#444] lg:text-[1.05rem] lg:leading-[1.8] [&_p]:mb-4">
                    <?php echo $content['introduction']['body_html'] ?? ''; ?>
                </div>
            </div>

            <div class="flex w-full flex-[0.8] flex-col gap-[20px] sm:flex-row sm:justify-between lg:w-auto lg:flex-col lg:items-end lg:gap-[10px]">
                <?php if (!empty($content['introduction']['statistics'])): ?>
                    <?php $statColors = ['#f8c3d6', '#3fa9f5', '#e9c46a', '#e63946']; ?>
                    <?php foreach (array_values($content['introduction']['statistics']) as $i => $stat): ?>
                        <div class="flex items-baseline gap-[10px] lg:gap-[15px]">
                            <div class="text-[3rem] font-black leading-none text-black lg:text-[4.5rem]"><?php echo htmlspecialchars($stat['value']); ?></div>
                            <div class="relative z-10 text-[1.4rem] font-extrabold uppercase leading-none tracking-[0.5px] after:absolute after:-bottom-[-6px] after:-left-[5px] after:-right-[5px] after:-z-10 after:h-[6px] after:opacity-80 after:content-[''] after:bg-stat-highlight lg:text-[2.2rem] lg:after:-bottom-[-12px]"
                                 style="--tw-after-bg-color: <?php echo $statColors[$i % 4]; ?>;">
                                <?php echo htmlspecialchars($stat['label']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="py-8 lg:py-[60px]">
            <div class="flex items-end justify-between mb-6 lg:mb-[35px]">
                <h2 class="text-[1.7rem] font-extrabold text-black lg:text-[2.5rem] leading-none m-0">Highlighted events</h2>
                <a href="/program" class="text-[#3fa9f5] font-bold text-sm lg:text-base no-underline hover:underline whitespace-nowrap">See all events</a>
            </div>
            
            <div class="hide-scrollbar flex snap-x snap-mandatory overflow-x-auto pb-6 gap-4 lg:grid lg:grid-cols-3 lg:gap-[30px] lg:overflow-visible lg:pb-0">
                <?php if (!empty($content['highlighted_events'])): ?>
                    <?php foreach (array_values($content['highlighted_events']) as $i => $event): 
                        $gridClass = '';
                        $imgClass = 'h-[160px] lg:h-[220px]'; 
                        if ($i === 0) $gridClass = 'lg:col-start-1 lg:col-end-2 lg:row-start-1 lg:row-end-2';
                        if ($i === 1) $gridClass = 'lg:col-start-2 lg:col-end-3 lg:row-start-1 lg:row-end-2';
                        if ($i === 2) $gridClass = 'lg:col-start-1 lg:col-end-2 lg:row-start-2 lg:row-end-3';
                        if ($i === 3) $gridClass = 'lg:col-start-2 lg:col-end-3 lg:row-start-2 lg:row-end-3';
                        if ($i === 4) {
                            $gridClass = 'lg:col-start-3 lg:col-end-4 lg:row-start-1 lg:row-end-3 lg:h-full';
                            $imgClass = 'h-[160px] lg:h-full lg:min-h-[520px]';
                        }
                    ?>
                        <a href="/program" class="group flex w-[260px] shrink-0 snap-start flex-col no-underline text-inherit lg:w-auto <?php echo $gridClass; ?>">
                            <img src="/<?php echo htmlspecialchars($event['image'] ?? 'assets/img/placeholder.jpg'); ?>"
                                 class="mb-[12px] block w-full max-w-full rounded-[24px] object-cover shadow-[0_8px_15px_rgba(0,0,0,0.1)] transition-transform duration-300 group-hover:-translate-y-[5px] <?php echo $imgClass; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">

                            <div class="px-1 text-left">
                                <div class="mb-1 text-[1.1rem] font-extrabold leading-tight text-black"><?php echo htmlspecialchars($event['title']); ?></div>
                                <div class="text-[0.85rem] font-bold text-[#888]">
                                    <?php echo htmlspecialchars($event['date']); ?>, <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="py-8 lg:py-[60px]">
            <h2 class="mb-6 text-left text-[1.7rem] font-extrabold text-black lg:mb-[35px] lg:text-[2.5rem] m-0">All categories</h2>
            <div class="flex flex-wrap justify-center gap-[15px] sm:gap-[20px] lg:justify-between">
                <?php if (!empty($content['categories'])): ?>
                    <?php foreach ($content['categories'] as $cat): ?>
                        <a href="/<?php echo strtolower(str_replace(' ', '', $cat['name'])); ?>" 
                           class="w-[30%] min-w-[90px] max-w-[130px] lg:w-auto lg:max-w-none lg:flex-1 cursor-pointer transition-transform duration-200 hover:scale-105 no-underline">
                            <img src="/<?php echo htmlspecialchars($cat['image']); ?>"
                                 title="<?php echo htmlspecialchars($cat['name']); ?>" 
                                 class="block w-full max-w-full rounded-2xl object-contain lg:h-[260px] lg:min-w-[220px]">
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="border-t border-[#eee] bg-transparent py-12 lg:bg-white lg:py-[80px]">
            <div class="mx-auto flex max-w-[1100px] flex-col items-center gap-8 lg:flex-row lg:items-start lg:gap-[60px]">
                <div class="flex w-[120px] flex-none items-center justify-center lg:w-[250px]">
                    <img src="/<?php echo htmlspecialchars($content['newsletter']['logo'] ?? 'assets/svg/logo.svg'); ?>"
                        alt="Haarlem Festival Logo" class="block w-full max-w-[250px] lg:mt-[100px]">
                </div>

                <div class="flex flex-1 flex-col items-center text-center lg:items-start lg:text-left">
                    <h2 class="mb-3 flex items-center gap-3 text-[2rem] font-extrabold lg:mb-[15px] lg:gap-[15px] lg:text-[2.8rem]">
                        <?php echo htmlspecialchars($content['newsletter']['title'] ?? 'Stay Updated'); ?> ✉️
                    </h2>
                    <div class="mb-6 max-w-[500px] text-[1rem] leading-[1.5] text-[#555] lg:mb-[25px] lg:text-[1.1rem] lg:leading-[1.6]">
                        <?php echo $content['newsletter']['description_html'] ?? ''; ?>
                    </div>

                    <label class="mb-3 block font-bold text-black">Notify me about:</label>
                    <div class="mb-4 flex flex-wrap justify-center gap-2 lg:mb-[15px] lg:justify-start lg:gap-3">
                        <?php if (!empty($content['newsletter']['preferences'])): ?>
                            <?php foreach ($content['newsletter']['preferences'] as $pref): ?>
                                <div class="flex cursor-pointer items-center gap-2 rounded-lg bg-[#f0f5fa] px-3 py-2 text-[0.85rem] font-bold transition-colors duration-200 hover:bg-[#e2e8f0] lg:px-[18px] lg:py-2.5 lg:text-[0.9rem]">
                                    <?php
                                        $icon = '🎵'; 
                                        if (str_contains($pref, 'Dance')) $icon = '🎵';
                                        elseif (str_contains($pref, 'Jazz')) $icon = '🎷';
                                        elseif (str_contains($pref, 'Food') || str_contains($pref, 'Restaurant')) $icon = '🍴';
                                        elseif (str_contains($pref, 'History')) $icon = '📖';
                                        elseif (str_contains($pref, 'Stories')) $icon = '✒️';
                                    ?>
                                    <span><?php echo $icon; ?></span>
                                    <?php echo htmlspecialchars($pref); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6 flex cursor-pointer items-center justify-center gap-2 text-[0.9rem] font-semibold text-[#333] lg:mb-[25px] lg:justify-start lg:text-[0.95rem]">
                        <input type="checkbox" id="selectAll" class="cursor-pointer">
                        <label for="selectAll" class="cursor-pointer">Select all</label>
                    </div>

                    <form class="flex w-full max-w-[500px] flex-col rounded-lg shadow-[0_4px_20px_rgba(0,0,0,0.08)] sm:flex-row">
                        <input type="email" placeholder="Enter your email address" required 
                               class="flex-1 rounded-t-lg border border-[#eee] bg-white px-5 py-4 text-[1rem] outline-none sm:rounded-l-lg sm:rounded-tr-none lg:px-[25px] lg:py-[18px]">
                        <button type="submit" class="cursor-pointer rounded-b-lg border-none bg-black px-8 py-4 text-[1rem] font-extrabold text-white sm:rounded-r-lg sm:rounded-bl-none sm:py-0 lg:px-[40px]">Subscribe</button>
                    </form>

                    <p class="mt-4 text-[0.8rem] text-[#888] underline lg:mt-[15px] lg:text-[0.85rem]">
                        <?php echo htmlspecialchars($content['newsletter']['privacy_text'] ?? ''); ?>
                    </p>
                </div>
            </div>
        </section>

    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>