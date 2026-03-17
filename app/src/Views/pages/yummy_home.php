<?php

declare(strict_types=1);

/** @var \App\ViewModels\YummyHomePageViewModel $vm */
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

    <div class="flex overflow-hidden flex-col pb-6 bg-white">
        <div class="flex w-full min-h-0 bg-white max-md:max-w-full"></div>

        <header
            class="flex w-full min-h-[433px] flex-col items-start overflow-hidden bg-cover bg-center bg-no-repeat pt-44 pr-10 pb-12 pl-20 font-bold max-md:max-w-full max-md:px-5 max-md:pt-24"
            style="background-image: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('<?= htmlspecialchars($vm->heroImage, ENT_QUOTES, 'UTF-8') ?>');">
            <h1
                class="ml-4 inline-block max-w-full px-4 py-3 font-display text-[80px] font-bold uppercase leading-[80px] tracking-[-0.05em] text-white max-md:ml-0 max-md:text-4xl max-md:leading-9">
                <?= $vm->heroTitleHtml ?>
            </h1>
            <p
                class="mt-1.5 max-w-[860px] text-2xl font-semibold leading-8 text-white max-md:max-w-full max-md:text-xl">
                <?= htmlspecialchars((string)($vm->hero['description'] ?? '')) ?>
            </p>
            <p class="mt-auto self-end pt-10 text-sm leading-tight text-right text-white/75 max-md:mt-10">
                <?= htmlspecialchars((string)($vm->hero['caption'] ?? 'Grote Markt, Haarlem Festival 2019')) ?>
            </p>
        </header>

        <main class="flex flex-col self-center mt-6 max-w-full w-[1252px] px-4 md:px-0">
            <section class="flex flex-col self-center mt-6 max-w-full w-[1252px]">
                <div
                    class="[&_h2]:self-center [&_h2]:text-center [&_h2]:text-3xl [&_h2]:font-bold [&_h2]:leading-none [&_h2]:text-black [&_p]:self-end [&_p]:mt-14 [&_p]:mr-28 [&_p]:max-w-[832px] [&_p]:text-xl [&_p]:font-medium [&_p]:leading-9 [&_p]:text-black max-md:[&_p]:mr-2.5 max-md:[&_p]:max-w-full max-md:[&_p]:text-lg">
                    <?= $vm->intro['contentHtml'] ?? '' ?>
                </div>

                <div
                    class="self-center mt-36 ml-8 max-w-full w-[583px] max-md:mt-10 max-md:ml-0 [&_h1]:m-0 [&_h1]:flex [&_h1]:flex-wrap [&_h1]:gap-3.5 [&_h1]:text-6xl [&_h1]:font-semibold max-md:[&_h1]:text-4xl [&_h2]:m-0 [&_h2]:flex [&_h2]:flex-wrap [&_h2]:gap-3.5 [&_h2]:text-6xl [&_h2]:font-semibold max-md:[&_h2]:text-4xl [&_.text-highlight]:flex [&_.text-highlight]:flex-col [&_.text-highlight]:leading-none max-md:[&_.text-highlight]:text-4xl [&_.text-highlight]:text-orange-500 [&_.text-highlight_br+*]:text-yellow-400">
                    <?= $vm->gallery['headingHtml'] ?? '' ?>
                </div>
            </section>

            <section class="self-center mt-20 ml-10 max-w-full w-[977px] max-md:mt-10 max-md:ml-0">
                <div class="flex gap-1 max-md:flex-col">
                    <div class="w-[27%] max-md:ml-0 max-md:w-full">
                        <?php if (isset($vm->galleryImages[0])): ?>
                            <figure
                                class="flex flex-col mt-8 text-sm font-light leading-none text-right text-black max-md:mt-9">
                                <img src="<?= htmlspecialchars((string)$vm->galleryImages[0]) ?>"
                                    class="object-cover w-full rounded-3xl aspect-[0.75]"
                                    alt="<?= htmlspecialchars($vm->galleryCaptions[0] ?? 'Restaurant photo') ?>">
                                <figcaption class="self-end mt-1.5 max-md:mr-2.5">
                                    <?= htmlspecialchars($vm->galleryCaptions[0] ?? 'Restaurant photo') ?>
                                </figcaption>
                            </figure>
                        <?php endif; ?>
                    </div>

                    <div class="ml-1 w-[48%] max-md:ml-0 max-md:w-full">
                        <div
                            class="flex flex-col grow text-sm font-light leading-none text-right max-md:mt-1 max-md:max-w-full">
                            <?php if (isset($vm->galleryImages[1])): ?>
                                <figure
                                    class="flex relative flex-col items-start px-4 pt-60 pb-3 w-full text-black rounded-3xl min-h-[259px] overflow-hidden max-md:pt-24 max-md:pr-5 max-md:max-w-full">
                                    <img src="<?= htmlspecialchars((string)$vm->galleryImages[1]) ?>"
                                        class="object-cover absolute inset-0 size-full"
                                        alt="<?= htmlspecialchars($vm->galleryCaptions[1] ?? 'Restaurant photo') ?>">
                                    <figcaption class="relative self-end">
                                        <?= htmlspecialchars($vm->galleryCaptions[1] ?? 'Restaurant photo') ?></figcaption>
                                </figure>
                            <?php endif; ?>

                            <?php if (isset($vm->galleryImages[2])): ?>
                                <figure>
                                    <img src="<?= htmlspecialchars((string)$vm->galleryImages[2]) ?>"
                                        class="object-cover mt-2 w-full rounded-3xl aspect-[2.06] max-md:max-w-full"
                                        alt="<?= htmlspecialchars($vm->galleryCaptions[2] ?? 'Restaurant photo') ?>">
                                    <figcaption class="self-end mt-1.5 text-black max-md:mr-2.5">
                                        <?= htmlspecialchars($vm->galleryCaptions[2] ?? 'Restaurant photo') ?>
                                    </figcaption>
                                </figure>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ml-2 w-3/12 max-md:ml-0 max-md:w-full">
                        <?php if (isset($vm->galleryImages[3])): ?>
                            <figure
                                class="flex flex-col mt-28 text-sm font-light leading-none text-right text-black max-md:mt-10">
                                <img src="<?= htmlspecialchars((string)$vm->galleryImages[3]) ?>"
                                    class="object-cover w-full rounded-3xl aspect-[0.75]"
                                    alt="<?= htmlspecialchars($vm->galleryCaptions[3] ?? 'Restaurant photo') ?>">
                                <figcaption class="self-end mt-1.5 max-md:mr-2.5">
                                    <?= htmlspecialchars($vm->galleryCaptions[3] ?? 'Restaurant photo') ?>
                                </figcaption>
                            </figure>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="self-end mt-36 max-w-full w-[1111px] max-md:mt-10">
                <div class="flex gap-5 max-md:flex-col">
                    <div class="w-6/12 max-md:ml-0 max-md:w-full">
                        <div
                            class="mt-4 text-black max-md:mt-10 max-md:max-w-full [&_h2]:text-3xl [&_h2]:font-bold [&_h2]:leading-none [&_h2]:max-md:max-w-full [&_p]:mt-7 [&_p]:text-xl [&_p]:font-medium [&_p]:leading-9 [&_p]:max-md:mr-2 [&_p]:max-md:max-w-full">
                            <?= $vm->map['contentHtml'] ?? '<h2>Find restaurant near your event</h2>' ?>
                        </div>
                    </div>

                    <div class="ml-5 w-6/12 max-md:ml-0 max-md:w-full">
                        <div class="flex flex-col w-full text-sm leading-none max-md:mt-10 max-md:max-w-full">
                            <div
                                class="flex relative flex-col items-end px-16 pt-72 pb-2 w-full text-black rounded-2xl shadow-sm min-h-[311px] overflow-hidden max-md:pt-24 max-md:pl-5 max-md:max-w-full">
                                <?php if ($vm->mapImage !== ''): ?>
                                    <img src="<?= htmlspecialchars($vm->mapImage) ?>"
                                        class="object-cover absolute inset-0 size-full"
                                        alt="Interactive map with event pinpoints">
                                <?php endif; ?>
                                <?php if ($vm->mapImageCaption !== ''): ?>
                                    <p class="relative">
                                        <?= htmlspecialchars($vm->mapImageCaption) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div
                                class="self-center mt-5 [&_a]:flex [&_a]:max-w-full [&_a]:items-center [&_a]:justify-center [&_a]:gap-0 [&_a]:rounded-2xl [&_a]:bg-amber-400 [&_a]:p-2.5 [&_a]:font-semibold [&_a]:text-white [&_a]:no-underline [&_a]:w-[131px] [&_.btn-primary]:flex [&_.btn-primary]:max-w-full [&_.btn-primary]:items-center [&_.btn-primary]:justify-center [&_.btn-primary]:gap-0 [&_.btn-primary]:rounded-2xl [&_.btn-primary]:bg-amber-400 [&_.btn-primary]:p-2.5 [&_.btn-primary]:font-semibold [&_.btn-primary]:text-white [&_.btn-primary]:no-underline [&_.btn-primary]:w-[131px]">
                                <a href="/map" class="btn-primary">go to map &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <div
                    class="self-start mt-16 max-md:mt-10 max-md:max-w-full [&_h1]:m-0 [&_h1]:text-3xl [&_h1]:font-bold [&_h1]:leading-none [&_h1]:text-black [&_h2]:m-0 [&_h2]:text-3xl [&_h2]:font-bold [&_h2]:leading-none [&_h2]:text-black">
                    <?= $vm->restaurants['headingHtml'] ?? '<h2>Participating Restaurants</h2>' ?>
                </div>

                <div
                    class="mx-auto mt-14 grid w-full max-w-[1044px] justify-items-center grid-cols-1 gap-x-10 gap-y-8 max-md:mt-10 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($vm->visibleRestaurantItems as $restaurantIndex => $restaurantItem): ?>
                        <?php
                        $itemClasses = 'w-full';
                        $visibleRestaurantCount = count($vm->visibleRestaurantItems);
                        $lastRowRemainder = $visibleRestaurantCount % 3;
                        $lastRowStartIndex = $visibleRestaurantCount - $lastRowRemainder;

                        if ($lastRowRemainder === 1 && $restaurantIndex === $lastRowStartIndex) {
                            $itemClasses .= ' xl:col-start-2';
                        }

                        if ($lastRowRemainder === 2 && $restaurantIndex === $lastRowStartIndex) {
                            $itemClasses .= ' xl:col-start-1';
                        }
                        ?>
                        <article class="<?= $itemClasses ?> max-w-[331px]">
                            <a href="/yummy/restaurant?id=<?= $restaurantItem['id'] ?? '' ?>" class="block no-underline">
                                <img src="<?= htmlspecialchars($restaurantItem['image']) ?>"
                                    class="object-cover w-full rounded-2xl aspect-[1.86]"
                                    alt="<?= htmlspecialchars($restaurantItem['name']) ?>">
                                <div class="mt-3 w-full">
                                    <header class="flex min-h-8 items-center gap-2">
                                        <h3 class="text-xl font-semibold leading-none text-black m-0">
                                            <?= htmlspecialchars($restaurantItem['name']) ?>
                                        </h3>
                                        <div class="flex gap-1" aria-label="<?= (int)($restaurantItem['star_rating'] ?? 0) ?> stars">
                                            <?php for ($i = 0; $i < (int)($restaurantItem['star_rating'] ?? 0); $i++): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                    </header>
                                    <?php if (!empty($restaurantItem['cuisine'])): ?>
                                        <p class="mt-1 text-sm text-gray-500 m-0">
                                            <?= htmlspecialchars($restaurantItem['cuisine']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>

        <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>