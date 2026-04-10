<?php
declare(strict_types=1);

/** @var \App\ViewModels\StoriesDetailPageViewModel $vm */

$heroImage = (string)($vm->mainMedia['image'] ?? $vm->coverImage ?? '');
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($vm->heroTitle) ?> | Stories</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/stories/tailwind.config.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body class="bg-black text-white antialiased font-sans m-0 p-0">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <section class="relative h-[55vh] min-h-[480px] w-full overflow-hidden">
        <img src="/<?= htmlspecialchars($heroImage) ?>" class="absolute inset-0 h-full w-full object-cover object-top">
        
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/70 to-transparent"></div>
        
        <div class="relative z-10 mx-auto flex h-full max-w-[1400px] flex-col justify-center px-8 lg:px-16">
            <div class="mb-4 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-white/50">
                <a href="<?= htmlspecialchars($vm->breadcrumb['back_href']) ?>" class="hover:text-white transition no-underline text-inherit uppercase">
                   ← <?= htmlspecialchars($vm->breadcrumb['back_label']) ?>
                </a>
                <span>/</span>
                <span class="text-white uppercase"><?= htmlspecialchars($vm->heroTitle) ?></span>
            </div>

            <div class="max-w-4xl">
                <h1 class="text-6xl font-black uppercase leading-[0.85] tracking-tighter sm:text-7xl lg:text-8xl m-0">
                    <span class="block text-white/20">STORIES</span>
                    <span class="block"><?= htmlspecialchars($vm->heroTitle) ?></span>
                </h1>
                
                <p class="mt-6 text-lg font-bold italic text-white"><?= htmlspecialchars($vm->heroSubtitle) ?></p>
                
                <?php if ($vm->heroBodyHtml): ?>
                    <div class="mt-2 max-w-md text-sm text-white/60 leading-relaxed">
                        <?= $vm->heroBodyHtml ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <main class="mx-auto max-w-[1400px] px-8 py-20 lg:px-16">
        <div class="grid grid-cols-1 items-start gap-16 lg:grid-cols-12">
            
            <div class="space-y-24 lg:col-span-7">
                <article class="space-y-10">
                    <div class="overflow-hidden rounded-xl shadow-2xl">
                        <img src="/<?= htmlspecialchars($vm->intro['image']) ?>" class="w-full object-cover">
                    </div>
                    
                    <div class="max-w-xl">
                        <div class="text-lg leading-relaxed text-white/80 [&_p]:mb-4">
                            <?= $vm->intro['html'] ?>
                        </div>
                        <?php if (!empty($vm->intro['bullets'])): ?>
                        <ul class="mt-10 space-y-5 list-none p-0">
                            <?php foreach ($vm->intro['bullets'] as $bullet): ?>
                                <li class="flex items-start gap-4">
                                    <span class="mt-2.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-stories-accent"></span>
                                    <span class="text-lg text-white/80 font-medium"><?= htmlspecialchars((string)$bullet) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </article>

                <section>
                    <img src="/<?= htmlspecialchars($vm->origin['image']) ?>" class="mb-12 w-full grayscale-[20%] rounded-xl shadow-lg">
                    <h2 class="text-5xl font-black tracking-tighter mb-8 uppercase italic"><?= htmlspecialchars($vm->origin['title']) ?></h2>
                    <div class="text-xl text-white/70 leading-relaxed [&_p]:mb-6">
                        <?= $vm->origin['html'] ?>
                    </div>
                </section>
            </div>

            <aside class="lg:col-span-5 lg:sticky lg:top-12">
                <div class="border border-white/20 p-8 bg-black rounded-2xl shadow-2xl">
                    <div class="divide-y divide-white/10 border-b border-white/10">
                        <?php foreach (['date', 'time', 'place', 'age_group', 'language'] as $key): ?>
                        <div class="flex items-center justify-between py-5">
                            <span class="text-xl font-black lowercase text-white opacity-40"><?= htmlspecialchars($vm->eventMeta[$key . '_label']) ?></span>
                            <span class="text-xl font-black text-stories-accent uppercase"><?= htmlspecialchars((string)$vm->eventMeta[$key . '_value']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-10">
                        <h3 class="text-4xl font-black uppercase tracking-tighter mb-6"><?= htmlspecialchars($vm->eventCard['reserve_title']) ?></h3>
                        
                        <form id="storiesAddToCartForm" method="POST" action="/order/item/add">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('cart_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="event_id" value="<?= (int)$vm->eventId ?>">

                            <div class="flex justify-between items-end mb-8">
                                <div>
                                    <p class="text-[10px] font-black uppercase opacity-40 tracking-widest mb-1"><?= htmlspecialchars($vm->eventCard['price_label']) ?></p>
                                    <p class="text-3xl font-black m-0">€<?= number_format($vm->price, 0) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black uppercase opacity-40 tracking-widest mb-2"><?= htmlspecialchars($vm->eventCard['quantity_label']) ?></p>
                                    
                                    <div class="flex items-center justify-between border border-white/40 rounded-xl w-44 h-12 px-2 overflow-hidden">
                                        <button type="button" id="minus" class="text-white text-3xl font-light hover:bg-white/10 transition-colors w-10 h-10 border-none bg-transparent cursor-pointer flex items-center justify-center">−</button>
                                        
                                        <input type="number" name="quantity" id="qty" value="1" readonly 
                                               class="w-12 bg-transparent text-center text-white text-xl font-black border-none outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                        
                                        <button type="button" id="plus" class="text-white text-2xl font-light hover:bg-white/10 transition-colors w-10 h-10 border-none bg-transparent cursor-pointer flex items-center justify-center">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-white/20 pt-6 mb-8">
                                <span class="text-sm font-bold uppercase opacity-60"><?= htmlspecialchars($vm->eventCard['total_label']) ?></span>
                                <span id="total" class="text-4xl font-black italic tracking-tighter text-white">€<?= number_format($vm->price, 0) ?></span>
                            </div>

                            <button type="submit" class="w-full bg-stories-accent py-5 text-xl font-black uppercase tracking-tighter text-white hover:bg-stories-accentDark transition-all border-none rounded-xl cursor-pointer">
                                <?= htmlspecialchars($vm->eventCard['button_label']) ?>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-16">
                    <h3 class="text-4xl font-black uppercase tracking-tighter mb-4 italic">Watch <?= htmlspecialchars($vm->heroTitle) ?></h3>
                    <div class="relative aspect-video w-full overflow-hidden border border-white/10 bg-white/5 rounded-2xl shadow-xl">
                         <?php if (!empty($vm->video['embed_url'])): ?>
                            <iframe class="absolute inset-0 h-full w-full border-none" src="<?= htmlspecialchars($vm->video['embed_url']) ?>" allowfullscreen></iframe>
                         <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <div id="cartToast" class="hidden fixed bottom-6 right-6 z-[1200] rounded-xl bg-zinc-900 px-6 py-4 text-left shadow-2xl ring-1 ring-white/15 cursor-pointer">
        <span class="block font-bold text-white">✓ Ticket added to cart</span>
        <span class="block text-xs text-zinc-400 mt-1 uppercase tracking-widest">Click to view cart</span>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
        const unitPrice = <?= (float)$vm->price ?>;
        const qtyInp = document.getElementById('qty');
        const totalLbl = document.getElementById('total');

        document.getElementById('plus').onclick = () => { qtyInp.value++; update(); };
        document.getElementById('minus').onclick = () => { if(qtyInp.value > 1) qtyInp.value--; update(); };
        function update() { totalLbl.textContent = '€' + (qtyInp.value * unitPrice); }

        document.getElementById('storiesAddToCartForm').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(e.target.action, { method: 'POST', body: formData });
                if (response.ok) {
                    const toast = document.getElementById('cartToast');
                    toast.classList.remove('hidden');
                    setTimeout(() => toast.classList.add('hidden'), 5000);
                }
            } catch (err) { console.error(err); }
        };
        document.getElementById('cartToast').onclick = () => window.location.href = '/order/cart';
    </script>
</body>
</html>