<?php

declare(strict_types=1);

/** @var \App\ViewModels\YummyDetailPageViewModel $vm */
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($vm->pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script>
    tailwind = {
        config: {
            corePlugins: {
                preflight: false
            },
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Cormorant Garamond', 'Times New Roman', 'serif'],
                        body: ['Manrope', 'Segoe UI', 'sans-serif']
                    }
                }
            }
        }
    };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-white font-body text-black">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <?php $old = is_array($old ?? null) ? $old : []; ?>

    <main class="max-w-6xl mx-auto px-6 py-12 font-sans text-gray-900">

        <?php require __DIR__ . '/../partials/flash_success.php'; ?>
        <?php require __DIR__ . '/../partials/error_general.php'; ?>

        <header class="mb-10">
            <a href="/yummy" class="text-gray-500 hover:text-gray-900 mb-4 inline-block">&larr; Yummy event -
                <?= htmlspecialchars($vm->event->title) ?></a>
            <div class="flex items-center gap-4">
                <h1 class="text-5xl font-extrabold tracking-tight"><?= htmlspecialchars($vm->event->title) ?></h1>
                <div class="flex text-yellow-400 text-3xl">
                    <?= str_repeat('★', $vm->event->star_rating) ?>
                </div>
            </div>
            <p class="text-gray-400 mt-2 text-lg"><?= htmlspecialchars($vm->event->cuisine) ?></p>
        </header>

        <?php if (!empty($vm->pageContent['gallery'])): ?>
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-20">
            <?php foreach ($vm->pageContent['gallery'] as $image): ?>
            <div class="flex flex-col">
                <img src="<?= htmlspecialchars($image['src'] ?? '') ?>"
                    alt="<?= htmlspecialchars($image['alt'] ?? '') ?>"
                    class="w-full aspect-[4/3] object-cover rounded-3xl shadow-md">
                <p class="text-xs text-gray-400 text-center mt-3 px-4"><?= htmlspecialchars($image['caption'] ?? '') ?>
                </p>
            </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <?php if (!empty($vm->pageContent['aboutSection'])): ?>
        <section
            class="max-w-3xl mx-auto text-center mb-24 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:mb-4 [&_p]:text-gray-600">
            <?= $vm->pageContent['aboutSection'] ?>
        </section>
        <?php endif; ?>

        <?php if (!empty($vm->pageContent['amuse_bouche']['html'])): ?>
        <section class="grid md:grid-cols-2 gap-12 items-center mb-24">
            <div
                class="max-w-md [&_h3]:text-3xl [&_h3]:font-bold [&_h3]:mb-6 [&_em]:italic [&_em]:font-medium [&_p]:text-gray-600 [&_p]:text-sm [&_p]:leading-relaxed">
                <?= $vm->pageContent['amuse_bouche']['html'] ?>
            </div>
            <div class="w-full max-w-md mx-auto md:ml-auto">
                <div class="flex gap-4 items-start">
                    <img src="<?= htmlspecialchars((string)($vm->pageContent['amuse_bouche']['images'][0] ?? '')) ?>"
                        class="w-1/2 aspect-[4/5] object-cover rounded-3xl shadow-lg mt-12">
                    <img src="<?= htmlspecialchars((string)($vm->pageContent['amuse_bouche']['images'][1] ?? $vm->pageContent['amuse_bouche']['images'][0] ?? '')) ?>"
                        class="w-1/2 aspect-[4/5] object-cover rounded-3xl shadow-lg mb-12">
                </div>
                <p class="text-xs text-gray-400 mt-6 text-right">
                    <?= htmlspecialchars((string)($vm->pageContent['amuse_bouche']['caption'] ?? '')) ?></p>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($vm->pageContent['chef'])): ?>
        <section class="grid md:grid-cols-2 gap-12 items-center mb-24">
            <div class="flex flex-col items-center">
                <img src="<?= htmlspecialchars((string)($vm->pageContent['chef']['image'] ?? '')) ?>" alt="Chef"
                    class="w-64 h-64 rounded-full object-cover shadow-xl mb-4">
                <p class="text-xs text-gray-400">Head chef</p>
            </div>
            <div
                class="max-w-md [&_h3]:text-3xl [&_h3]:font-bold [&_h3]:mb-6 [&_p]:text-gray-600 [&_p]:text-sm [&_p]:leading-relaxed">
                <?= $vm->pageContent['chef']['html'] ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($vm->pageContent['menu']['html'])): ?>
        <section class="grid md:grid-cols-2 gap-12 items-center mb-24">
            <div class="w-full max-w-md mx-auto md:mr-auto order-last md:order-first">
                <img src="<?= htmlspecialchars((string)($vm->pageContent['menu']['image'] ?? '')) ?>"
                    class="w-full aspect-square object-cover rounded-[2rem] shadow-lg">
            </div>
            <div
                class="max-w-md [&_h3]:text-3xl [&_h3]:font-bold [&_h3]:mb-6 [&_p]:text-gray-600 [&_p]:text-sm [&_p]:leading-relaxed order-first md:order-last">
                <?= $vm->pageContent['menu']['html'] ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="bg-[#FDF3D8] rounded-[3rem] p-10 mt-12 shadow-sm relative">
            <h2 class="text-2xl font-bold mb-2">Book your experience:</h2>
            <p class="text-xs text-gray-500 mb-8 max-w-2xl">Reservation is mandatory. A reservation fee of €10,- per
                person will be charged when a reservation is made on the Haarlem Festival site. This fee will be
                deducted from the final check on visiting the restaurant.</p>

            <div class="grid md:grid-cols-3 gap-8">
                <div
                    class="bg-[#F8E1AC] rounded-3xl p-6 [&_h3]:font-bold [&_h3]:mb-4 [&_ul]:text-sm [&_ul]:space-y-2 [&_ul]:text-gray-800">
                    <?= $vm->pageContent['informationBlock'] ?? '' ?>
                </div>

                <div class="col-span-2 bg-[#F8E1AC] rounded-3xl p-6">
                    <form action="/reservation/book" method="POST" class="grid md:grid-cols-2 gap-6">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('reservation_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="event_id" value="<?= (int)($old['event_id'] ?? $vm->event->event_id) ?>">
                        <input type="hidden" name="yummy_event_id" value="<?= (int)($old['yummy_event_id'] ?? $vm->event->id) ?>">
                        <div>
                            <label class="block text-sm mb-2">Number of guests</label>
                            <div class="flex gap-4 mb-4">
                                <input type="number" name="adult_count" value="<?= htmlspecialchars((string)($old['adult_count'] ?? '2'), ENT_QUOTES, 'UTF-8') ?>" min="1"
                                    class="w-16 p-2 rounded-xl text-center">
                                <input type="number" name="child_count" value="<?= htmlspecialchars((string)($old['child_count'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" min="0"
                                    class="w-16 p-2 rounded-xl text-center">
                            </div>
                            <label class="block text-sm mb-2">Leave a note (optional)</label>
                            <textarea name="note" class="w-full p-3 rounded-xl h-24 text-sm"
                                placeholder="Leave a note about allergies..."><?= htmlspecialchars((string)($old['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="flex flex-col">
                            <label class="block text-sm mb-2">Available time-slots</label>
                            <div class="space-y-3 mb-6">
                                <?php
                                $currentDate = '';
                                foreach ($vm->sessions as $session):
                                    $date = date('l, F j', strtotime($session['start_time']));
                                    $time = date('H:i', strtotime($session['start_time'])) . ' - ' . date('H:i', strtotime($session['end_time']));

                                    if ($date !== $currentDate):
                                        if ($currentDate !== '') echo '</div><div class="space-y-3 mb-6">';
                                        echo "<p class=\"text-xs text-gray-500 mb-2 mt-4 font-bold uppercase tracking-wider\">" . htmlspecialchars($date) . "</p>";
                                        $currentDate = $date;
                                    endif;

                                    $isActive = (int)$session['event_id'] === $vm->event->event_id;
                                ?>
                                <a href="/yummy/restaurant?id=<?= $session['event_id'] ?>"
                                    class="w-full p-3 rounded-xl text-left text-sm flex justify-between items-center no-underline transition-colors block
                                       <?= $isActive ? 'bg-yellow-100 border-2 border-yellow-400 text-black font-semibold' : 'bg-white hover:bg-gray-50 text-gray-800' ?>">
                                    <span><?= htmlspecialchars($time) ?></span>
                                    <span
                                        class="text-xs <?= $isActive ? 'text-black' : 'text-gray-400' ?>"><?= $session['capacity'] ?>
                                        seats left</span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit"
                                class="w-full mt-auto bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-3 rounded-xl transition-colors">Reserve</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>