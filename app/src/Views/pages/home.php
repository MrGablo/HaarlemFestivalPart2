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
</head>

<body class="bg-white text-[#333] leading-[1.6] m-0 p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] box-border">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container mx-auto max-w-[1200px] p-5">

        <header class="hero flex items-center justify-between gap-5 overflow-visible py-20">
            <div class="hero-text w-[45%] max-w-[500px] flex-none">
                <h1 class="mb-[25px] text-[4.5rem] font-extrabold leading-[0.95] text-black">
                    Discover <br>
                    <span class="block text-[#3fa9f5]">Haarlem</span>
                    <span class="block text-[#3fa9f5]">Festival</span>
                </h1>
                <div class="hero-subtitle max-w-[400px] text-[1.1rem] font-medium text-[#333]">
                    <?php echo $content['hero']['subtitle_html'] ?? ''; ?>
                </div>
            </div>

            <div class="hero-visuals relative mt-[60px] flex w-[50%] flex-none items-center justify-center">
                <div class="hero-bg-shape absolute left-1/2 top-1/2 z-0 h-[75%] w-[105%] -translate-x-1/2 -translate-y-1/2 rounded-[40px] bg-[#3fa9f5]"></div>

                <div class="image-grid relative z-10 flex w-full grid-cols-[1fr_1.6fr_1fr] items-center gap-[15px] grid">
                    
                    <div class="col-left flex flex-col items-end gap-[15px] text-right">
                        <div class="img-wrapper flex w-fit flex-col items-end text-right">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][0] ?? ''); ?>"
                                class="img-vertical h-[180px] w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)] block max-w-full" alt="Dance">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Haarlemmerhout, Bevrijdingspop</p>
                        </div>

                        <div class="img-wrapper flex w-fit flex-col items-end text-right">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][4] ?? ''); ?>"
                                class="img-wide h-[200px] w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)] block max-w-full" alt="Crowd">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Haarlemmerhout, Bevrijdingspop</p>
                        </div>
                    </div>

                    <div class="img-wrapper flex w-fit flex-col">
                        <img src="/<?php echo htmlspecialchars($content['hero']['images'][3] ?? ''); ?>"
                            class="img-main h-[420px] w-full rounded-[20px] object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)] block max-w-full" alt="Church">
                        <p class="img-caption items-start pl-[5px] text-left mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Grote markt, Haarlem Jazz</p>
                    </div>

                    <div class="col-right flex flex-col items-start gap-[15px] text-left">
                        <div class="img-wrapper flex w-fit flex-col items-start text-left">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][2] ?? ''); ?>"
                                class="img-wide h-[200px] w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)] block max-w-full" alt="Fireworks">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Grote markt, Haarlem Jazz</p>
                        </div>

                        <div class="img-wrapper flex w-fit flex-col items-start text-left">
                            <img src="/<?php echo htmlspecialchars($content['hero']['images'][1] ?? ''); ?>"
                                class="img-vertical h-[180px] w-full rounded-xl object-cover shadow-[0_4px_15px_rgba(0,0,0,0.15)] block max-w-full" alt="City">
                            <p class="img-caption mt-1.5 whitespace-nowrap text-[0.75rem] font-medium italic leading-[1.2] text-[#555]">Amsterdamse Poort, Haarlem</p>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <section class="intro flex items-center gap-[60px] py-[80px]">
            <div class="intro-text flex-[1.2]">
                <h2 class="mb-[25px] text-[2.5rem] font-extrabold"><?php echo htmlspecialchars($content['introduction']['title'] ?? ''); ?></h2>
                <div class="intro-body mb-5 text-[1.05rem] leading-[1.8] text-[#444]">
                    <?php echo $content['introduction']['body_html'] ?? ''; ?>
                </div>
            </div>

            <div class="stats flex flex-[0.8] flex-col items-end gap-[10px]">
                <?php if (!empty($content['introduction']['statistics'])): ?>
                    <?php 
                    // Arrays to map the specific underline colors for each stat index
                    $statColors = ['#f8c3d6', '#3fa9f5', '#e9c46a', '#e63946']; 
                    ?>
                    <?php foreach (array_values($content['introduction']['statistics']) as $i => $stat): ?>
                        <div class="stat-item flex items-baseline gap-[15px]">
                            <div class="stat-number text-[4.5rem] font-black leading-none text-black"><?php echo htmlspecialchars($stat['value']); ?></div>
                            <div class="stat-label relative z-10 text-[2.2rem] font-extrabold uppercase leading-none tracking-[0.5px] after:absolute after:-bottom-[-12px] after:-left-[5px] after:-right-[5px] after:-z-10 after:h-[6px] after:opacity-80 after:content-['']"
                                 style="--tw-after-bg-color: <?php echo $statColors[$i % 4]; ?>; after:bg-[var(--tw-after-bg-color)]">
                                <?php echo htmlspecialchars($stat['label']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="events-section py-[60px]">
            <div class="section-title mb-[35px] text-[2.5rem] font-extrabold text-black">Highlighted events</div>
            <div class="events-grid grid grid-cols-3 auto-rows-auto gap-[30px]">
                <?php if (!empty($content['highlighted_events'])): ?>
                    <?php foreach (array_values($content['highlighted_events']) as $i => $event): 
                        // Map the specific CSS grid areas based on the index like your original CSS did
                        $gridClass = '';
                        $imgClass = 'h-[220px]'; // Default height
                        
                        if ($i === 0) $gridClass = 'col-start-1 col-end-2 row-start-1 row-end-2';
                        if ($i === 1) $gridClass = 'col-start-2 col-end-3 row-start-1 row-end-2';
                        if ($i === 2) $gridClass = 'col-start-1 col-end-2 row-start-2 row-end-3';
                        if ($i === 3) $gridClass = 'col-start-2 col-end-3 row-start-2 row-end-3';
                        if ($i === 4) $gridClass = 'hidden'; // From nth-child(5) display: none;
                        if ($i === 5) {
                            $gridClass = 'col-start-3 col-end-4 row-start-1 row-end-3 h-full';
                            $imgClass = 'h-full min-h-[520px]';
                        }
                    ?>
                        <div class="event-card group flex flex-col <?php echo $gridClass; ?>">
                            <img src="/<?php echo htmlspecialchars($event['image'] ?? 'assets/img/homepage/placeholder.jpg'); ?>"
                                class="event-img mb-[15px] w-full rounded-[20px] object-cover shadow-[0_8px_20px_rgba(0,0,0,0.15)] transition-transform duration-300 group-hover:-translate-y-[5px] block max-w-full <?php echo $imgClass; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">

                            <div class="event-details px-1.5">
                                <div class="event-title mb-1.5 text-[1.1rem] font-extrabold text-black"><?php echo htmlspecialchars($event['title']); ?></div>
                                <div class="event-meta text-[0.9rem] font-bold text-[#888]">
                                    <?php echo htmlspecialchars($event['date']); ?>,
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="categories-section py-[60px]">
            <div class="section-title mb-[35px] text-[2.5rem] font-extrabold text-black">All categories</div>
            <div class="categories-flex flex flex-wrap justify-between gap-[10px]">
                <?php if (!empty($content['categories'])): ?>
                    <?php foreach ($content['categories'] as $cat): ?>
                        <img src="/<?php echo htmlspecialchars($cat['image']); ?>"
                            alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                            class="category-svg h-[260px] min-w-[220px] flex-1 cursor-pointer transition-transform duration-200 hover:scale-105 block max-w-full">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="newsletter border-t border-[#eee] bg-white py-[80px]">
            <div class="newsletter-wrapper mx-auto flex max-w-[1100px] items-start gap-[60px]">

                <div class="newsletter-logo flex w-[250px] flex-none items-center justify-center">
                    <img src="<?php echo htmlspecialchars($content['newsletter']['logo'] ?? '/assets/svg/logo.svg'); ?>"
                        alt="Haarlem Festival Logo" class="mt-[100px] w-[150%] max-w-[250px] block">
                </div>

                <div class="newsletter-content flex-1 text-left">
                    <h2 class="mb-[15px] flex items-center gap-[15px] text-[2.8rem] font-extrabold">Stay Updated ✉️</h2>
                    <div class="newsletter-desc mb-[25px] max-w-[500px] text-[1.1rem] leading-[1.6] text-[#555]">
                        <?php echo $content['newsletter']['description_html'] ?? ''; ?>
                    </div>

                    <label class="preferences-label mb-3 block font-bold text-black">Notify me about:</label>

                    <div class="preferences-grid mb-[15px] flex flex-wrap gap-3">
                        <?php if (!empty($content['newsletter']['preferences'])): ?>
                            <?php foreach ($content['newsletter']['preferences'] as $pref): ?>
                                <div class="pref-chip flex cursor-pointer items-center gap-2 rounded-lg bg-[#f0f5fa] px-[18px] py-2.5 text-[0.9rem] font-bold transition-colors duration-200 hover:bg-[#e2e8f0]">
                                    <?php
                                    $icon = '🎵'; // Default
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

                    <div class="select-all mb-[25px] flex cursor-pointer items-center gap-2 text-[0.95rem] font-semibold text-[#333]">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll" style="cursor:pointer;">Select all</label>
                    </div>

                    <form class="subscribe-form flex max-w-[500px] rounded-lg shadow-[0_4px_20px_rgba(0,0,0,0.08)]">
                        <input type="email" placeholder="Enter your email address" required 
                               class="flex-1 rounded-l-lg border border-[#eee] px-[25px] py-[18px] text-[1rem] outline-none">
                        <button type="submit" class="cursor-pointer rounded-r-lg border-none bg-black px-[40px] text-[1rem] font-extrabold text-white">Subscribe</button>
                    </form>

                    <p class="privacy-text mt-[15px] text-[0.85rem] text-[#888] underline">
                        <?php echo htmlspecialchars($content['newsletter']['privacy_text'] ?? ''); ?>
                    </p>
                </div>

            </div>
        </section>

    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>

</html>