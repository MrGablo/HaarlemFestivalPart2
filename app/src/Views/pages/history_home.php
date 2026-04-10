<?php

declare(strict_types=1);

/** @var \App\ViewModels\HistoryHomePageViewModel $vm */

$hero = $vm->hero;
$overview = $vm->overview;
$booking = $vm->booking;
$map = $vm->map;
$bookingEvents = is_array($vm->bookingEvents ?? null) ? $vm->bookingEvents : [];
$cartCsrfToken = \App\Utils\Csrf::token('cart_csrf_token');
$bookingEventsJson = json_encode($vm->bookingEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$bookingEventsJson = is_string($bookingEventsJson) ? $bookingEventsJson : '[]';
$formatPriceSummary = static function (array $events, string $key, string $fallback): string {
    $prices = [];

    foreach ($events as $event) {
        $value = $event[$key] ?? null;
        if (!is_numeric($value)) {
            continue;
        }

        $price = (float)$value;
        if ($price <= 0) {
            continue;
        }

        $prices[] = $price;
    }

    if ($prices === []) {
        return $fallback;
    }

    $min = min($prices);
    $max = max($prices);
    $formatted = 'EUR ' . number_format($min, 2);

    if (abs($max - $min) > 0.0001) {
        return 'From ' . $formatted;
    }

    return $formatted;
};
$singlePriceSummary = $formatPriceSummary($bookingEvents, 'price', 'EUR 17.50');
$familyPriceSummary = $formatPriceSummary($bookingEvents, 'family_price', 'EUR 60.00');
$tailwindLoaded = true;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string)($hero['title'] ?? 'A Stroll Through History')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="/assets/js/history-tailwind-config.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-history-paper text-history-ink font-historySans">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <div class="mx-auto w-full max-w-history px-8">
        <?php require __DIR__ . '/../partials/flash_success.php'; ?>
    </div>

    <section class="relative overflow-hidden bg-history-charcoal text-white">
        <?php if (!empty($hero['background_image'])): ?>
            <img src="/<?= htmlspecialchars((string)$hero['background_image']) ?>" alt="History hero" class="absolute inset-0 h-full w-full object-cover opacity-60">
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-r from-black/75 to-black/30"></div>
        <div class="relative mx-auto max-w-history px-8 py-28">
            <?php if (!empty($hero['kicker'])): ?>
                <div class="text-sm font-bold uppercase tracking-[0.3em] text-history-gold"><?= htmlspecialchars((string)$hero['kicker']) ?></div>
            <?php endif; ?>
            <h1 class="max-w-[700px] font-historyDisplay text-5xl font-bold uppercase leading-none md:text-7xl">
                <?= htmlspecialchars((string)($hero['title'] ?? 'A Stroll Through History')) ?>
            </h1>
            <?php if (!empty($hero['subtitle_html'])): ?>
                <div class="mt-6 max-w-[760px] text-lg leading-8 text-white/90">
                    <?= $hero['subtitle_html'] ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="mx-auto max-w-history px-8 py-16">
        <section class="grid gap-8 lg:grid-cols-3">
            <?php foreach (['lead_html', 'route_html', 'break_html'] as $copyKey): ?>
                <?php if (!empty($overview[$copyKey])): ?>
                    <article class="rounded-historyLg bg-white p-8 shadow-historySoft">
                        <div class="text-[15px] leading-7 text-history-muted">
                            <?= $overview[$copyKey] ?>
                        </div>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <section class="mt-16 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-historyXl bg-history-cream p-8 shadow-historyCard"
                data-history-booking
                data-empty-message="<?= htmlspecialchars((string)($booking['empty_selection_message'] ?? 'Choose a date, time and language to book a tour.')) ?>"
                data-no-events-message="<?= htmlspecialchars((string)($booking['no_events_message'] ?? 'No history tours are available right now.')) ?>"
                data-reserve-label="<?= htmlspecialchars((string)($booking['reserve_button_label'] ?? 'Reserve now')) ?>"
                data-slot-count-label="<?= htmlspecialchars((string)($booking['slot_count_label'] ?? 'tour(s) available')) ?>"
                data-family-size="4">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-historyDisplay text-3xl font-bold"><?= htmlspecialchars((string)($booking['title'] ?? 'Choose your tour')) ?></h2>
                    <a href="#history-schedule" class="rounded-full border border-history-sand bg-white px-5 py-3 text-sm font-bold text-history-ink no-underline transition hover:bg-history-paper">
                        <?= htmlspecialchars((string)($booking['schedule_title'] ?? 'Tour Schedule')) ?>
                    </a>
                </div>
                <?php if (!empty($booking['description_html'])): ?>
                    <div class="mt-4 text-[15px] leading-7 text-history-muted">
                        <?= $booking['description_html'] ?>
                    </div>
                <?php endif; ?>

                <script id="history-booking-data" type="application/json"><?= $bookingEventsJson ?></script>

                <div class="mt-8 space-y-6">
                    <section>
                        <div class="mb-3 text-sm font-bold uppercase tracking-[0.2em] text-history-warm">
                            <?= htmlspecialchars((string)($booking['date_label'] ?? 'Select Date')) ?>
                        </div>
                        <div class="flex flex-wrap gap-3" data-history-picker="date"></div>
                    </section>

                    <section>
                        <div class="mb-3 text-sm font-bold uppercase tracking-[0.2em] text-history-warm">
                            <?= htmlspecialchars((string)($booking['time_label'] ?? 'Select Time')) ?>
                        </div>
                        <div class="flex flex-wrap gap-3" data-history-picker="time"></div>
                    </section>

                    <section>
                        <div class="mb-3 text-sm font-bold uppercase tracking-[0.2em] text-history-warm">
                            <?= htmlspecialchars((string)($booking['language_label'] ?? 'Select Language')) ?>
                        </div>
                        <div class="flex flex-wrap gap-3" data-history-picker="language"></div>
                    </section>

                    <section>
                        <div class="mb-3 text-sm font-bold uppercase tracking-[0.2em] text-history-warm">
                            <?= htmlspecialchars((string)($booking['tickets_label'] ?? 'Tickets')) ?>
                        </div>
                        <div class="flex flex-wrap gap-3" data-history-picker="quantity"></div>
                    </section>
                </div>

                <div class="mt-8 rounded-historyLg bg-white p-6 shadow-historyInset">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-history-warm">
                                <?= htmlspecialchars((string)($booking['selected_tour_label'] ?? 'Selected Tour')) ?>
                            </div>
                            <div class="mt-2 text-lg font-bold text-history-ink" data-history-selected-tour>
                                <?= htmlspecialchars((string)($booking['empty_selection_message'] ?? 'Choose a date, time and language to book a tour.')) ?>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-history-warm">
                                <?= htmlspecialchars((string)($booking['selected_location_label'] ?? 'Meeting Point')) ?>
                            </div>
                            <div class="mt-2 text-base font-semibold text-history-ink" data-history-selected-location>-</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-history-warm">
                                <?= htmlspecialchars((string)($booking['selected_availability_label'] ?? 'Seats Left')) ?>
                            </div>
                            <div class="mt-2 text-base font-semibold text-history-ink" data-history-selected-availability>-</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-history-warm">
                                <?= htmlspecialchars((string)($booking['selected_price_label'] ?? 'Price')) ?>
                            </div>
                            <div class="mt-2 text-base font-semibold text-history-ink" data-history-selected-price>-</div>
                        </div>
                    </div>

                    <form method="POST" action="/order/item/add" class="ticket-form mt-6" data-history-booking-form>
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($cartCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="event_id" value="" data-history-event-id>
                        <input type="hidden" name="quantity" value="1" data-history-quantity>
                        <button type="submit" class="w-full cursor-pointer rounded-full border-0 bg-history-olive px-6 py-4 text-sm font-bold uppercase tracking-[0.18em] text-white transition hover:bg-history-oliveDark disabled:cursor-not-allowed disabled:bg-[#b4b29f]" data-history-submit>
                            <?= htmlspecialchars((string)($booking['reserve_button_label'] ?? 'Reserve now')) ?>
                        </button>
                    </form>
                </div>

                <?php if (!empty($booking['selection_help_html'])): ?>
                    <div class="mt-4 text-xs leading-6 text-history-muted">
                        <?= $booking['selection_help_html'] ?>
                    </div>
                <?php endif; ?>

                <div class="mt-6 flex flex-wrap items-center gap-6 text-sm text-[#444]">
                    <span><strong><?= htmlspecialchars((string)($booking['single_price_label'] ?? 'Single')) ?>:</strong> <?= htmlspecialchars($singlePriceSummary) ?></span>
                    <span><strong><?= htmlspecialchars((string)($booking['family_price_label'] ?? 'Family')) ?>:</strong> <?= htmlspecialchars($familyPriceSummary) ?></span>
                </div>
                <?php if (!empty($booking['availability_note_html'])): ?>
                    <div class="mt-4 text-xs leading-6 text-history-muted">
                        <?= $booking['availability_note_html'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="history-schedule" class="rounded-historyXl bg-white p-8 shadow-historyCard">
                <h2 class="font-historyDisplay text-2xl font-bold"><?= htmlspecialchars((string)($booking['schedule_title'] ?? 'Tour Schedule')) ?></h2>
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-[#ece7d8] text-history-muted">
                                <th class="pb-3 pr-6 font-semibold">Date</th>
                                <th class="pb-3 pr-6 font-semibold">Day</th>
                                <th class="pb-3 pr-6 font-semibold">Time</th>
                                <th class="pb-3 pr-6 font-semibold">NL</th>
                                <th class="pb-3 pr-6 font-semibold">EN</th>
                                <th class="pb-3 font-semibold">CH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vm->scheduleRows as $row): ?>
                                <tr class="border-b border-[#f1ede2] transition"
                                    data-history-schedule-row
                                    data-date="<?= htmlspecialchars((string)($row['date_key'] ?? '')) ?>"
                                    data-time="<?= htmlspecialchars((string)($row['time'] ?? '')) ?>">
                                    <td class="py-3 pr-6"><?= htmlspecialchars((string)($row['date_label'] ?? '')) ?></td>
                                    <td class="py-3 pr-6"><?= htmlspecialchars((string)($row['day'] ?? '')) ?></td>
                                    <td class="py-3 pr-6"><?= htmlspecialchars((string)($row['time'] ?? '')) ?></td>
                                    <td class="py-3 pr-6"><?= (int)($row['nl'] ?? 0) ?></td>
                                    <td class="py-3 pr-6"><?= (int)($row['en'] ?? 0) ?></td>
                                    <td class="py-3"><?= (int)($row['ch'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="mt-16">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <h2 class="font-historyDisplay text-3xl font-bold"><?= htmlspecialchars((string)($map['title'] ?? 'Route Map')) ?></h2>
                    <?php if (!empty($map['description_html'])): ?>
                        <div class="mt-3 max-w-[760px] text-[15px] leading-7 text-history-muted">
                            <?= $map['description_html'] ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($vm->locations as $location): ?>
                    <a href="<?= htmlspecialchars((string)$location['detail_url']) ?>" class="group block overflow-hidden rounded-historyLg bg-white no-underline shadow-historySoft transition duration-200 hover:-translate-y-1 hover:shadow-historyMedia focus:outline-none focus:ring-2 focus:ring-history-olive focus:ring-offset-2">
                        <article>
                            <?php if (!empty($location['image'])): ?>
                                <img src="/<?= htmlspecialchars((string)$location['image']) ?>" alt="<?= htmlspecialchars((string)$location['title']) ?>" class="block h-52 w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="font-historyDisplay text-2xl font-bold text-history-ink"><?= htmlspecialchars((string)$location['title']) ?></h3>
                                <?php if (!empty($location['summary'])): ?>
                                    <p class="mt-3 text-[15px] leading-7 text-history-muted">
                                        <?= htmlspecialchars((string)$location['summary']) ?>
                                    </p>
                                <?php endif; ?>
                                <span class="mt-5 inline-block rounded-full bg-history-charcoal px-5 py-3 text-sm font-bold text-white">
                                    <?= htmlspecialchars((string)($map['card_button_label'] ?? 'Bekijk locatie')) ?>
                                </span>
                            </div>
                        </article>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script>
        (function () {
            var root = document.querySelector('[data-history-booking]');
            if (!(root instanceof HTMLElement)) {
                return;
            }

            var dataElement = document.getElementById('history-booking-data');
            var events = [];
            if (dataElement && dataElement.textContent) {
                try {
                    events = JSON.parse(dataElement.textContent);
                } catch (error) {
                    events = [];
                }
            }

            var dateContainer = root.querySelector('[data-history-picker="date"]');
            var timeContainer = root.querySelector('[data-history-picker="time"]');
            var languageContainer = root.querySelector('[data-history-picker="language"]');
            var quantityContainer = root.querySelector('[data-history-picker="quantity"]');
            var selectedTour = root.querySelector('[data-history-selected-tour]');
            var selectedLocation = root.querySelector('[data-history-selected-location]');
            var selectedAvailability = root.querySelector('[data-history-selected-availability]');
            var selectedPrice = root.querySelector('[data-history-selected-price]');
            var eventInput = root.querySelector('[data-history-event-id]');
            var quantityInput = root.querySelector('[data-history-quantity]');
            var submitButton = root.querySelector('[data-history-submit]');
            var scheduleRows = document.querySelectorAll('[data-history-schedule-row]');
            var state = { date: '', time: '', language: '', quantity: 1 };
            var emptyMessage = root.dataset.emptyMessage || 'Choose a date, time and language to book a tour.';
            var noEventsMessage = root.dataset.noEventsMessage || 'No history tours are available right now.';
            var reserveLabel = root.dataset.reserveLabel || 'Reserve now';
            var slotCountLabel = root.dataset.slotCountLabel || 'tour(s) available';
            var familySize = Number(root.dataset.familySize || 4);

            function parseMoney(value) {
                var normalized = String(value || '').replace(/[^0-9,.]/g, '');
                if (normalized.indexOf(',') !== -1 && normalized.indexOf('.') === -1) {
                    normalized = normalized.replace(',', '.');
                } else {
                    normalized = normalized.replace(/,/g, '');
                }

                var parsed = Number(normalized);
                return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
            }

            function uniqueOptions(list, key, labelKey) {
                var seen = {};
                var values = [];
                list.forEach(function (item) {
                    var value = item[key];
                    if (!value || seen[value]) {
                        return;
                    }
                    seen[value] = true;
                    values.push({
                        value: value,
                        label: item[labelKey] || value
                    });
                });
                return values;
            }

            function filterEvents() {
                return events.filter(function (event) {
                    return (!state.date || event.date_key === state.date)
                        && (!state.time || event.time_key === state.time)
                        && (!state.language || event.language_code === state.language);
                });
            }

            function renderButtons(container, options, selectedValue, onPick) {
                if (!(container instanceof HTMLElement)) {
                    return;
                }

                container.innerHTML = '';
                options.forEach(function (option) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = option.label;
                    button.className = 'rounded-full border px-4 py-2 text-sm font-semibold transition ' +
                        (option.value === selectedValue
                            ? 'border-[#171717] bg-[#171717] text-white'
                            : 'border-[#d9d1c1] bg-white text-[#303030] hover:border-[#171717]');
                    button.addEventListener('click', function () {
                        onPick(option.value);
                    });
                    container.appendChild(button);
                });
            }

            function renderQuantityButtons() {
                if (!(quantityContainer instanceof HTMLElement)) {
                    return;
                }

                quantityContainer.innerHTML = '';
                for (var value = 1; value <= familySize; value += 1) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = String(value);
                    button.className = 'rounded-full border px-4 py-2 text-sm font-semibold transition ' +
                        (value === state.quantity
                            ? 'border-[#171717] bg-[#171717] text-white'
                            : 'border-[#d9d1c1] bg-white text-[#303030] hover:border-[#171717]');
                    button.addEventListener('click', (function (nextValue) {
                        return function () {
                            state.quantity = nextValue;
                            render();
                        };
                    })(value));
                    quantityContainer.appendChild(button);
                }
            }

            function ensureStateDefaults() {
                var dateOptions = uniqueOptions(events, 'date_key', 'date_label');
                if (!state.date && dateOptions.length > 0) {
                    state.date = dateOptions[0].value;
                }

                var timeEvents = events.filter(function (event) {
                    return !state.date || event.date_key === state.date;
                });
                var timeOptions = uniqueOptions(timeEvents, 'time_key', 'time_label');
                if (!timeOptions.some(function (option) { return option.value === state.time; })) {
                    state.time = timeOptions.length > 0 ? timeOptions[0].value : '';
                }

                var languageEvents = timeEvents.filter(function (event) {
                    return !state.time || event.time_key === state.time;
                });
                var languageOptions = uniqueOptions(languageEvents, 'language_code', 'language_label');
                if (!languageOptions.some(function (option) { return option.value === state.language; })) {
                    state.language = languageOptions.length > 0 ? languageOptions[0].value : '';
                }
            }

            function pickEvent(matches) {
                var available = matches.filter(function (event) {
                    return !!event.is_available;
                });
                return (available[0] || matches[0] || null);
            }

            function updateScheduleHighlight() {
                scheduleRows.forEach(function (row) {
                    if (!(row instanceof HTMLElement)) {
                        return;
                    }

                    var rowDate = row.dataset.date || '';
                    var rowTime = row.dataset.time || '';
                    var dateMatch = !state.date || rowDate === state.date;
                    var timeMatch = !state.time || rowTime === state.time;
                    var isActive = dateMatch && timeMatch;
                    row.classList.toggle('bg-[#f7efe0]', isActive);
                    row.classList.toggle('font-semibold', isActive);
                    row.classList.toggle('opacity-60', !dateMatch);
                });
            }

            function updateSummary() {
                var matches = filterEvents();
                var selected = pickEvent(matches);
                var totalAvailability = matches.reduce(function (sum, event) {
                    return sum + (parseInt(event.availability, 10) || 0);
                }, 0);

                if (!(selectedTour instanceof HTMLElement) || !(selectedLocation instanceof HTMLElement)
                    || !(selectedAvailability instanceof HTMLElement) || !(selectedPrice instanceof HTMLElement)
                    || !(eventInput instanceof HTMLInputElement) || !(quantityInput instanceof HTMLInputElement)
                    || !(submitButton instanceof HTMLButtonElement)) {
                    return;
                }

                if (!selected) {
                    selectedTour.textContent = events.length > 0 ? emptyMessage : noEventsMessage;
                    selectedLocation.textContent = '-';
                    selectedAvailability.textContent = '-';
                    selectedPrice.textContent = '-';
                    eventInput.value = '';
                    quantityInput.value = String(state.quantity);
                    submitButton.disabled = true;
                    submitButton.textContent = reserveLabel;
                    return;
                }

                var effectiveUnitPrice = parseMoney(selected.price || 0);
                var totalPrice = effectiveUnitPrice * state.quantity;
                var usedFamilyPrice = state.quantity === familySize;
                var familyPrice = parseMoney(selected.family_price || 0);
                if (usedFamilyPrice && familyPrice > 0) {
                    totalPrice = familyPrice;
                    effectiveUnitPrice = familyPrice / familySize;
                } else {
                    usedFamilyPrice = false;
                }

                selectedTour.textContent = selected.full_date_label + ' at ' + selected.time_label + ' (' + selected.language_label + ')';
                selectedLocation.textContent = selected.location || '-';
                selectedAvailability.textContent = totalAvailability + ' seats · ' + matches.length + ' ' + slotCountLabel;
                selectedPrice.textContent = 'EUR ' + totalPrice.toFixed(2) + (usedFamilyPrice
                    ? ' total · family bundle'
                    : ' total · EUR ' + effectiveUnitPrice.toFixed(2) + ' p.p.');
                eventInput.value = totalAvailability >= state.quantity ? String(selected.event_id || '') : '';
                quantityInput.value = String(state.quantity);
                submitButton.disabled = totalAvailability < state.quantity;
                submitButton.textContent = totalAvailability > 0 ? reserveLabel : 'Sold out';
            }

            function render() {
                ensureStateDefaults();

                var dateOptions = uniqueOptions(events, 'date_key', 'date_label');
                var timeOptions = uniqueOptions(events.filter(function (event) {
                    return !state.date || event.date_key === state.date;
                }), 'time_key', 'time_label');
                var languageOptions = uniqueOptions(events.filter(function (event) {
                    return (!state.date || event.date_key === state.date)
                        && (!state.time || event.time_key === state.time);
                }), 'language_code', 'language_label');

                renderButtons(dateContainer, dateOptions, state.date, function (value) {
                    state.date = value;
                    state.time = '';
                    state.language = '';
                    render();
                });
                renderButtons(timeContainer, timeOptions, state.time, function (value) {
                    state.time = value;
                    state.language = '';
                    render();
                });
                renderButtons(languageContainer, languageOptions, state.language, function (value) {
                    state.language = value;
                    render();
                });
                renderQuantityButtons();

                updateSummary();
                updateScheduleHighlight();
            }

            render();
        })();
    </script>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>