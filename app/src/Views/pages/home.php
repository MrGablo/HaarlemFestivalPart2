<?php
use App\Config;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['hero']['title'] ?? 'Haarlem Festival'); ?></title>
    
    <script>
        tailwind = {
            config: {
                corePlugins: {
                    preflight: false 
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* hides scrollbar for smooth horizontal scrolling on mobile */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-[#f4f9ff] lg:bg-white text-[#333] leading-[1.6] m-0 p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] box-border overflow-x-hidden">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container mx-auto w-full max-w-[1200px] px-5 lg:px-5">

        <header class="hero flex flex-col items-start justify-between gap-6 overflow-visible pt-10 pb-16 lg:flex-row lg:items-center lg:gap-5 lg:py-20">
            <div class="hero-text w-full max-w-[500px] flex-none text-left lg:w-[45%]">
                <h1 class="mb-4 text-[3.2rem] font-extrabold leading-[1.05] tracking-tight text-black lg:mb-[25px] lg:text-[4.5rem]">
                    Discover <br>
                    <span class="block text-[#3fa9f5]">Haarlem</span>
                    <span class="block text-[#3fa9f5]">Festival</span>
                </h1>
                <div class="hero-subtitle text-[1rem] font-medium leading-[1.5] text-[#333] lg:mx-0 lg:text-[1.1rem]">
                    Explore jazz and dance events, discover Haarlem's<br>
                    stories and history.<br>
                    Find the best places to eat during the festival.
                </div>
            </div>

            <div class="hero-visuals relative mt-6 flex w-full flex-none items-center justify-center lg:mt-[60px] lg:w-[50%]">
                
                <div class="relative block w-[85%] max-w-[340px] lg:hidden">
                    <div class="absolute -bottom-6 -right-4 -top-4 left-6 z-0 rounded-[1.5rem] bg-[#3fa9f5]"></div>
                    <img src="/<?php echo htmlspecialchars($content['hero']['images'][4] ?? 'assets/img/homepage/hero-mobile.jpg'); ?>"
                         class="relative z-10 block h-[220px] w-full rounded-2xl object-cover shadow-md" alt="Festival Crowd">
                    <p class="relative z-10 mt-2 text-right text-[0.65rem] font-bold italic text-[#555] pr-1">Haarlemmerhout, Bevrijdingspop</p>
                </div>

                <div class="image-grid relative z-10 hidden w-full grid-cols-[1fr_1.6fr_1fr] items-center gap-[15px] lg:grid">
                    <div class="absolute left-1/2 top-1/2 z-0 h-[75%] w-[105%] -translate-x-1/2 -translate-y-1/2 rounded-[40px] bg-[#3fa9f5]"></div>
                    
                    <div class="col-left z-10 flex flex-col items-end gap-[15px] text-right">
                        <div class="img-wrapper flex w-fit flex-col items-end text-right">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][0] ?? ''); ?>"
                                class="img-vertical block h-[180px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Dance">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Haarlemmerhout, Bevrijdingspop</p>
                        </div>
                        <div class="img-wrapper flex w-fit flex-col items-end text-right">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][4] ?? ''); ?>"
                                class="img-wide block h-[200px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Crowd">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Haarlemmerhout, Bevrijdingspop</p>
                        </div>
                    </div>

                    <div class="img-wrapper z-10 flex w-fit flex-col">
                        <img src="/<?php echo htmlspecialchars($content['hero']['images'][3] ?? ''); ?>"
                            class="img-main block h-[420px] w-full max-w-full rounded-[20px] object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Church">
                        <p class="img-caption mt-1.5 items-start pl-[5px] text-left whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Grote markt, Haarlem Jazz</p>
                    </div>

                    <div class="col-right z-10 flex flex-col items-start gap-[15px] text-left">
                        <div class="img-wrapper flex w-fit flex-col items-start text-left">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][2] ?? ''); ?>"
                                class="img-wide block h-[200px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="Fireworks">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Grote markt, Haarlem Jazz</p>
                        </div>
                        <div class="img-wrapper flex w-fit flex-col items-start text-left">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][1] ?? ''); ?>"
                                class="img-vertical block h-[180px] w-full max-w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)]" alt="City">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Amsterdamse Poort, Haarlem</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="intro flex flex-col items-center gap-10 py-10 lg:flex-row lg:gap-[60px] lg:py-[80px]">
            <div class="intro-text flex-[1.2] text-left">
                <h2 class="mb-4 text-[2rem] font-extrabold leading-tight lg:mb-[25px] lg:text-[2.5rem]"><?php echo htmlspecialchars($content['introduction']['title'] ?? ''); ?></h2>
                <div class="intro-body mb-5 text-[1rem] leading-[1.6] text-[#444] lg:text-[1.05rem] lg:leading-[1.8]">
                    <?php echo $content['introduction']['body_html'] ?? ''; ?>
                </div>
            </div>

            <div class="stats flex w-full flex-[0.8] flex-col gap-[20px] sm:flex-row sm:justify-between lg:w-auto lg:flex-col lg:items-end lg:gap-[10px]">
                <?php if (!empty($content['introduction']['statistics'])): ?>
                    <?php $statColors = ['#f8c3d6', '#3fa9f5', '#e9c46a', '#e63946']; ?>
                    <?php foreach (array_values($content['introduction']['statistics']) as $i => $stat): ?>
                        <div class="stat-item flex items-baseline gap-[10px] lg:gap-[15px]">
                            <div class="stat-number text-[3rem] font-black leading-none text-black lg:text-[4.5rem]"><?php echo htmlspecialchars($stat['value']); ?></div>
                            <div class="stat-label relative z-10 text-[1.4rem] font-extrabold uppercase leading-none tracking-[0.5px] after:absolute after:-bottom-[-6px] after:-left-[5px] after:-right-[5px] after:-z-10 after:h-[6px] after:opacity-80 after:content-[''] lg:text-[2.2rem] lg:after:-bottom-[-12px]"
                                 style="--tw-after-bg-color: <?php echo $statColors[$i % 4]; ?>; after:bg-[var(--tw-after-bg-color)]">
                                <?php echo htmlspecialchars($stat['label']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="events-section py-8 lg:py-[60px]">
            <div class="flex items-end justify-between mb-6 lg:mb-[35px]">
                <h2 class="section-title text-[1.7rem] font-extrabold text-black lg:text-[2.5rem] leading-none">Highlighted events</h2>
                <a href="/program" class="text-[#3fa9f5] font-bold text-sm lg:text-base hover:underline leading-none mb-1 whitespace-nowrap">See all events</a>
            </div>
            
            <div class="events-grid hide-scrollbar flex snap-x snap-mandatory overflow-x-auto pb-6 gap-4 lg:grid lg:grid-cols-3 lg:gap-[30px] lg:overflow-visible lg:pb-0">
                <?php if (!empty($content['highlighted_events'])): ?>
                    <?php foreach (array_values($content['highlighted_events']) as $i => $event): 
                        $gridClass = '';
                        $imgClass = 'h-[160px] lg:h-[220px]'; 
                        
                        if ($i === 0) $gridClass = 'lg:col-start-1 lg:col-end-2 lg:row-start-1 lg:row-end-2';
                        if ($i === 1) $gridClass = 'lg:col-start-2 lg:col-end-3 lg:row-start-1 lg:row-end-2';
                        if ($i === 2) $gridClass = 'lg:col-start-1 lg:col-end-2 lg:row-start-2 lg:row-end-3';
                        if ($i === 3) $gridClass = 'lg:col-start-2 lg:col-end-3 lg:row-start-2 lg:row-end-3';
                        if ($i === 4) $gridClass = 'hidden lg:hidden'; 
                        if ($i === 5) {
                            $gridClass = 'lg:col-start-3 lg:col-end-4 lg:row-start-1 lg:row-end-3 lg:h-full';
                            $imgClass = 'h-[160px] lg:h-full lg:min-h-[520px]';
                        }
                    ?>
                        <div class="event-card group flex w-[260px] shrink-0 snap-start flex-col lg:w-auto <?php echo $gridClass; ?>">
                            <img src="/<?php echo htmlspecialchars($event['image'] ?? 'assets/img/homepage/placeholder.jpg'); ?>"
                                class="event-img mb-[12px] block w-full max-w-full rounded-[24px] object-cover shadow-[0_8px_15px_rgba(0,0,0,0.1)] transition-transform duration-300 group-hover:-translate-y-[5px] <?php echo $imgClass; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">

                            <div class="event-details px-1">
                                <div class="event-title mb-1 text-[1.1rem] font-extrabold leading-tight text-black"><?php echo htmlspecialchars($event['title']); ?></div>
                                <div class="event-meta text-[0.85rem] font-bold text-[#888]">
                                    <?php echo htmlspecialchars($event['date']); ?>,
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="categories-section py-8 lg:py-[60px]">
            <h2 class="section-title mb-6 text-left text-[1.7rem] font-extrabold text-black lg:mb-[35px] lg:text-[2.5rem]">All categories</h2>
            
            <div class="categories-flex flex flex-wrap justify-center gap-[15px] sm:gap-[20px] lg:justify-between">
                <?php if (!empty($content['categories'])): ?>
                    <?php foreach ($content['categories'] as $cat): ?>
                        <div class="category-wrapper w-[30%] min-w-[90px] max-w-[130px] lg:w-auto lg:max-w-none lg:flex-1 cursor-pointer transition-transform duration-200 hover:scale-105">
                            <img src="/<?php echo htmlspecialchars($cat['image']); ?>"
                                alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                                class="category-svg block w-full max-w-full rounded-2xl object-contain lg:h-[260px] lg:min-w-[220px]">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="newsletter border-t border-[#eee] bg-transparent py-12 lg:bg-white lg:py-[80px]">
            <div class="newsletter-wrapper mx-auto flex max-w-[1100px] flex-col items-center gap-8 lg:flex-row lg:items-start lg:gap-[60px]">

                <div class="newsletter-logo flex w-[120px] flex-none items-center justify-center lg:w-[250px]">
                    <img src="<?php echo htmlspecialchars($content['newsletter']['logo'] ?? '/assets/svg/logo.svg'); ?>"
                        alt="Haarlem Festival Logo" class="block w-full max-w-[250px] lg:mt-[100px] lg:w-[150%]">
                </div>

                <div class="newsletter-content flex flex-1 flex-col items-center text-center lg:items-start lg:text-left">
                    <h2 class="mb-3 flex items-center gap-3 text-[2rem] font-extrabold lg:mb-[15px] lg:gap-[15px] lg:text-[2.8rem]">Stay Updated ✉️</h2>
                    <div class="newsletter-desc mb-6 max-w-[500px] text-[1rem] leading-[1.5] text-[#555] lg:mb-[25px] lg:text-[1.1rem] lg:leading-[1.6]">
                        <?php echo $content['newsletter']['description_html'] ?? ''; ?>
                    </div>

                    <label class="preferences-label mb-3 block font-bold text-black">Notify me about:</label>

                    <div class="preferences-grid mb-4 flex flex-wrap justify-center gap-2 lg:mb-[15px] lg:justify-start lg:gap-3">
                        <?php if (!empty($content['newsletter']['preferences'])): ?>
                            <?php foreach ($content['newsletter']['preferences'] as $pref): ?>
                                <div class="pref-chip flex cursor-pointer items-center gap-2 rounded-lg bg-[#f0f5fa] px-3 py-2 text-[0.85rem] font-bold transition-colors duration-200 hover:bg-[#e2e8f0] lg:px-[18px] lg:py-2.5 lg:text-[0.9rem]">
                                    <?php
                                    // simple icon map
                                    $icon = '🎵'; 
                                    if (str_contains($pref, 'Dance')) $icon = '🎵';
                                    if (str_contains($pref, 'Jazz')) $icon = '🎷';
                                    if (str_contains($pref, 'Food') || str_contains($pref, 'Restaurant')) $icon = '🍴';
                                    if (str_contains($pref, 'History')) $icon = '📖';
                                    if (str_contains($pref, 'Stories')) $icon = '✒️';
                                    ?>
                                    <span><?php echo $icon; ?></span>
                                    <?php echo htmlspecialchars($pref); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="select-all mb-6 flex cursor-pointer items-center justify-center gap-2 text-[0.9rem] font-semibold text-[#333] lg:mb-[25px] lg:justify-start lg:text-[0.95rem]">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll" style="cursor:pointer;">Select all</label>
                    </div>

                    <form class="subscribe-form flex w-full max-w-[500px] flex-col rounded-lg shadow-[0_4px_20px_rgba(0,0,0,0.08)] sm:flex-row">
                        <input type="email" placeholder="Enter your email address" required 
                               class="flex-1 rounded-t-lg border border-[#eee] bg-white px-5 py-4 text-[1rem] outline-none sm:rounded-l-lg sm:rounded-tr-none lg:px-[25px] lg:py-[18px]">
                        <button type="submit" class="cursor-pointer rounded-b-lg border-none bg-black px-8 py-4 text-[1rem] font-extrabold text-white sm:rounded-r-lg sm:rounded-bl-none sm:py-0 lg:px-[40px]">Subscribe</button>
                    </form>

                    <p class="privacy-text mt-4 text-[0.8rem] text-[#888] underline lg:mt-[15px] lg:text-[0.85rem]">
                        <?php echo htmlspecialchars($content['newsletter']['privacy_text'] ?? ''); ?>
                    </p>
                </div>

            </div>
        </section>

    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>

</html>