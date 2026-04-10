<?php
declare(strict_types=1);

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;

/** @var int $totalEvents */
/** @var float $subtotal */
$totalEvents = (int)($totalEvents ?? 0);
$subtotal = (float)($subtotal ?? 0);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Program – Haarlem Festival</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-white font-[system-ui,'Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-neutral-800 leading-relaxed">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto flex w-full max-w-[1200px] flex-col gap-10 px-5 py-10">
        <header class="flex flex-wrap items-start justify-between gap-6">
            <div class="min-w-[280px] flex-1">
                <h1 class="mb-3 text-4xl font-extrabold leading-tight">
                    <span class="text-black">MY</span>
                    <span class="text-[#2F80ED]"> PROGRAM</span>
                </h1>
                <p class="mb-1 text-base text-neutral-600">Your personal festival schedule and reservations</p>
                <p class="text-base text-neutral-600">For the bought tickets check the mobile Wallet app!</p>
            </div>
            <div class="shrink-0 min-w-[180px] rounded-xl border-2 border-[#2F80ED] px-8 py-6 text-center">
                <svg class="mx-auto mb-3 block h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="#2F80ED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <div class="text-3xl font-extrabold text-black"><?= $totalEvents ?></div>
                <div class="text-sm text-neutral-600">Total Events</div>
            </div>
        </header>

        <?php if (!empty($paidEvents)): ?>
            <section>
                <h2 class="mb-2 text-lg font-bold text-black">Paid tickets</h2>
                <p class="mb-6 text-base text-neutral-600">These events are already paid. Your tickets are in your email / mobile wallet.</p>

                <?php foreach ($paidEvents as $event): ?>
                    <?php
                    $ticketQr = trim((string)($event['ticketQr'] ?? ''));
                    $ticketPanelBaseId = (int)($event['ticketId'] ?? 0);
                    if ($ticketPanelBaseId <= 0) {
                        $ticketPanelBaseId = (int)($event['orderItemId'] ?? 0);
                    }
                    $ticketPanelId = 'ticket-qr-' . $ticketPanelBaseId;
                    $ticketHasQr = $ticketQr !== '';
                    $qrImageSrc = '';

                    if ($ticketHasQr) {
                        try {
                            $qrImageSrc = (new QRCode(new QROptions([
                                'outputInterface' => QRMarkupSVG::class,
                                'outputBase64' => true,
                                'svgAddXmlHeader' => false,
                                'eccLevel' => 'M',
                                'addQuietzone' => true,
                            ])))->render($ticketQr);
                        } catch (\Throwable) {
                            $ticketHasQr = false;
                        }
                    }
                    ?>
                    <div class="mb-2.5 rounded-lg border border-neutral-200 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($event['location'])): ?>
                                    <div class="text-sm text-neutral-600"><?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <div class="mt-1 text-sm text-neutral-700">
                                    Ticket #<?= (int)($event['ticketId'] ?? 0) ?> ·
                                    Qty: <?= (int)$event['quantity'] ?> ·
                                    €<?= number_format($event['unitPrice'], 2, '.', '') ?> each ·
                                    Total €<?= number_format($event['totalPrice'], 2, '.', '') ?>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-3">
                                <span class="text-xs font-bold uppercase text-[#219653]">Paid</span>
                                <?php if ($ticketHasQr): ?>
                                    <button
                                        type="button"
                                        class="cursor-pointer rounded-md border border-[#2F80ED] px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-[#2F80ED] transition hover:bg-[#2F80ED] hover:text-white"
                                        data-ticket-toggle="<?= htmlspecialchars($ticketPanelId, ENT_QUOTES, 'UTF-8') ?>"
                                        aria-controls="<?= htmlspecialchars($ticketPanelId, ENT_QUOTES, 'UTF-8') ?>"
                                        aria-expanded="false"
                                    >
                                        Show QR
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($ticketHasQr): ?>
                            <div id="<?= htmlspecialchars($ticketPanelId, ENT_QUOTES, 'UTF-8') ?>" class="mt-4 hidden rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                                <p class="mb-3 text-sm text-neutral-700">Present this QR code at the venue entrance.</p>
                                <img
                                    src="<?= htmlspecialchars($qrImageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Ticket QR code"
                                    class="block h-[220px] w-[220px] rounded-md bg-white p-2 shadow-sm"
                                >
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if (!empty($unpaidEvents)): ?>
            <section>
                <h2 class="mb-2 text-lg font-bold text-black">Unpaid tickets (to be paid)</h2>
                <p class="mb-6 text-base text-neutral-600">These events are in your cart. Click &quot;Pay unpaid cart&quot; to complete checkout in one payment.</p>

                <?php foreach ($unpaidEvents as $event): ?>
                    <div class="mb-2.5 rounded-lg border border-neutral-200 px-4 py-3">
                        <div class="font-bold"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if (!empty($event['location'])): ?>
                            <div class="text-sm text-neutral-600"><?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="mt-1 text-sm text-neutral-700">
                            Qty: <?= (int)$event['quantity'] ?> ·
                            €<?= number_format($event['unitPrice'], 2, '.', '') ?> each ·
                            Total €<?= number_format($event['totalPrice'], 2, '.', '') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if (!empty($reservations)): ?>
            <section>
                <h2 class="mb-2 text-lg font-bold text-black">Restaurant reservations</h2>
                <p class="mb-6 text-base text-neutral-600">Your booked Yummy reservations appear here with guest counts and timeslots.</p>

                <?php foreach ($reservations as $reservation): ?>
                    <div class="mb-2.5 rounded-lg border border-neutral-200 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold"><?= htmlspecialchars((string)($reservation['title'] ?? 'Restaurant reservation'), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="text-sm text-neutral-600">
                                    <?= htmlspecialchars((string)($reservation['reservation_time'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="mt-1 text-sm text-neutral-700">
                                    Guests: <?= (int)($reservation['guest_total'] ?? 0) ?>
                                    <?php if ((int)($reservation['adult_count'] ?? 0) > 0): ?>
                                        · Adults: <?= (int)($reservation['adult_count'] ?? 0) ?>
                                    <?php endif; ?>
                                    <?php if ((int)($reservation['children_count'] ?? 0) > 0): ?>
                                        · Children: <?= (int)($reservation['children_count'] ?? 0) ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($reservation['note'])): ?>
                                    <div class="mt-1 text-sm text-neutral-600">Note: <?= htmlspecialchars((string)$reservation['note'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="shrink-0 text-xs font-bold uppercase text-[#2F80ED]">Reserved</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <p class="text-2xl font-bold text-black">Subtotal (unpaid): €<?= number_format($subtotal, 2, '.', '') ?></p>

        <div class="flex flex-wrap gap-4">
            <a href="/" class="inline-block rounded-lg border-0 bg-[#2F80ED] px-7 py-3.5 text-sm font-bold uppercase tracking-wide text-white no-underline transition hover:bg-[#1c6ddb]">Add more events</a>
            <?php if (!empty($unpaidEvents)): ?>
                <form action="/payment/checkout" method="POST" class="inline m-0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('payment_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="cursor-pointer rounded-lg border-0 bg-[#2F80ED] px-7 py-3.5 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-[#1c6ddb]">Pay unpaid cart</button>
                </form>
            <?php endif; ?>
        </div>

        <section class="flex flex-col gap-6 border-t border-neutral-200 pt-8">
            <div>
                <h2 class="mb-2 text-lg font-bold text-black">Ticket Collection</h2>
                <p class="text-base text-neutral-600">All tickets are digital. Check your email for QR codes to present at venue entrances.</p>
            </div>
            <div>
                <h2 class="mb-2 text-lg font-bold text-black">Cancellation Policy</h2>
                <p class="text-base text-neutral-600">Free cancellation up to 24 hours before each event. Refunds processed within 5–7 business days.</p>
            </div>
            <div>
                <h2 class="mb-2 text-lg font-bold text-black">Need Help?</h2>
                <p class="text-base text-neutral-600">
                    Contact our support team at
                    <a href="mailto:support@haarlemfestival.nl" class="text-[#2F80ED] no-underline hover:underline">support@haarlemfestival.nl</a>
                    or call
                    <a href="tel:+31231234567" class="text-[#2F80ED] no-underline hover:underline">+31 23 123 4567</a>.
                </p>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
        document.querySelectorAll('[data-ticket-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const panelId = button.getAttribute('data-ticket-toggle');
                if (!panelId) {
                    return;
                }

                const panel = document.getElementById(panelId);
                if (!panel) {
                    return;
                }

                const isHidden = panel.classList.toggle('hidden');
                button.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                button.textContent = isHidden ? 'Show QR' : 'Hide QR';
            });
        });
    </script>
</body>

</html>
