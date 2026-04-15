<?php
declare(strict_types=1);

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;

/** @var int $totalEvents */
/** @var float $subtotal */
// Totals for the editable cart (not paid yet).
$totalEvents = (int)($totalEvents ?? 0);
$subtotal = (float)($subtotal ?? 0);
/** @var array<int, array<string, mixed>> $awaitingPaymentEvents */
// Items currently in checkout-in-progress (waiting for payment).
$awaitingPaymentEvents = $awaitingPaymentEvents ?? [];
$awaitingSubtotal = (float)($awaitingSubtotal ?? 0);
$paymentDeadlineAt = trim((string)($paymentDeadlineAt ?? ''));
$awaitingPaymentOrderId = (int)($awaitingPaymentOrderId ?? 0);
/** @var array<int, array{orderId:int,createdAt:string,lines:array<int,array<string,mixed>>}> $cancelledOrdersDisplay */
$cancelledOrdersDisplay = $cancelledOrdersDisplay ?? [];

$programFlashSuccess = \App\Utils\Flash::getSuccess();
$programFlashErrors = \App\Utils\Flash::getErrors();

// Convert raw DB datetime to a user-friendly deadline string.
$formatProgramDeadline = static function (string $raw): string {
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    if ($ts === false) {
        return htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
    }

    return htmlspecialchars(date('d M Y, H:i', $ts), ENT_QUOTES, 'UTF-8');
};
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
        <?php if (is_string($programFlashSuccess) && $programFlashSuccess !== ''): ?>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900" role="status">
                <?= htmlspecialchars($programFlashSuccess, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($programFlashErrors)): ?>
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900" role="alert">
                <?= htmlspecialchars((string)($programFlashErrors['general'] ?? reset($programFlashErrors)), ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <header class="flex flex-wrap items-start justify-between gap-6">
            <div class="min-w-[280px] flex-1">
                <h1 class="mb-3 text-4xl font-extrabold leading-tight">
                    <span class="text-black">MY</span>
                    <span class="text-[#2F80ED]"> PROGRAM</span>
                </h1>
                <p class="mb-1 text-base text-neutral-600">Your personal festival schedule and reservations</p>
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
                                <?php
                                $ticketStartRaw = trim((string)($event['eventStartRaw'] ?? ''));
                                if ($ticketStartRaw !== ''):
                                    $ticketStartTs = strtotime($ticketStartRaw);
                                    ?>
                                    <div class="text-sm text-neutral-600">
                                        <?php if ($ticketStartTs !== false): ?>
                                            <?= htmlspecialchars(date('D, d M Y H:i', $ticketStartTs), ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($ticketStartRaw, ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
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

        <?php if (!empty($cancelledOrdersDisplay)): ?>
            <section>
                <h2 class="mb-2 text-lg font-bold text-black">Cancelled orders</h2>
                <p class="mb-6 text-base text-neutral-600">These orders were not paid within 24 hours after checkout was started. No tickets were issued.</p>

                <?php foreach ($cancelledOrdersDisplay as $cancelled): ?>
                    <div class="mb-6 rounded-lg border border-neutral-300 bg-neutral-50 px-4 py-3">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-bold text-neutral-800">Order #<?= (int)$cancelled['orderId'] ?></span>
                            <?php if (!empty($cancelled['createdAt'])): ?>
                                <span class="text-xs text-neutral-600"><?= htmlspecialchars((string)$cancelled['createdAt'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span class="text-xs font-bold uppercase text-neutral-500">Cancelled</span>
                        </div>
                        <?php foreach ($cancelled['lines'] as $line): ?>
                            <div class="mb-2 border-t border-neutral-200 pt-2 first:border-t-0 first:pt-0">
                                <div class="font-semibold text-neutral-700"><?= htmlspecialchars((string)$line['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($line['location'])): ?>
                                    <div class="text-sm text-neutral-500"><?= htmlspecialchars((string)$line['location'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <div class="text-sm text-neutral-600">
                                    Qty: <?= (int)$line['quantity'] ?> ·
                                    €<?= number_format((float)$line['unitPrice'], 2, '.', '') ?> each ·
                                    Total €<?= number_format((float)$line['totalPrice'], 2, '.', '') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if (!empty($awaitingPaymentEvents)): ?>
            <section>
                <h2 class="mb-2 text-lg font-bold text-black">Payment pending</h2>
                <p class="mb-2 text-base text-neutral-600">
                    You started checkout but have not finished paying. Complete payment within 24 hours of clicking Pay, or this order will be cancelled automatically.
                </p>
                <?php if ($paymentDeadlineAt !== ''): ?>
                    <p class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                        Pay before: <?= $formatProgramDeadline($paymentDeadlineAt) ?> (server time)
                    </p>
                <?php endif; ?>

                <?php foreach ($awaitingPaymentEvents as $event): ?>
                    <div class="mb-2.5 rounded-lg border border-amber-200 bg-amber-50/40 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($event['location'])): ?>
                                    <div class="text-sm text-neutral-600"><?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <div class="mt-1 text-sm text-neutral-700">
                                    Qty: <?= (int)$event['quantity'] ?> ·
                                    €<?= number_format((float)$event['unitPrice'], 2, '.', '') ?> each ·
                                    Total €<?= number_format((float)$event['totalPrice'], 2, '.', '') ?>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs font-bold uppercase text-amber-800">Awaiting payment</span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <p class="mb-4 text-xl font-bold text-black">Total due: €<?= number_format($awaitingSubtotal, 2, '.', '') ?></p>

                <div class="flex flex-wrap items-center gap-3">
                    <?php // Resume the same pending checkout in Stripe. ?>
                    <form action="/payment/checkout" method="POST" class="m-0 inline">
                        <?php // CSRF token for payment POST endpoint. ?>
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('payment_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="cursor-pointer rounded-lg border-0 bg-[#2F80ED] px-7 py-3.5 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-[#1c6ddb]">Continue to payment</button>
                    </form>
                    <?php if ($awaitingPaymentOrderId > 0): ?>
                        <?php // User can cancel this pending checkout and unlock cart editing again. ?>
                        <form action="/program/cancel-awaiting-payment" method="POST" class="m-0 inline" onsubmit="return confirm('Cancel this checkout? You can add tickets again afterwards.');">
                            <?php // Separate CSRF token for cancel action. ?>
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('program_cancel_awaiting_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="order_id" value="<?= $awaitingPaymentOrderId ?>">
                            <button type="submit" class="cursor-pointer rounded-lg border-2 border-neutral-300 bg-white px-7 py-3.5 text-sm font-bold uppercase tracking-wide text-neutral-800 transition hover:bg-neutral-50">Cancel checkout</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($unpaidEvents)): ?>
            <section>
                <h2 class="mb-2 text-lg font-bold text-black">Cart (not at checkout yet)</h2>
                <p class="mb-6 text-base text-neutral-600">These events are in your cart. Click Pay to open Stripe checkout. You will then have 24 hours to complete payment.</p>

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

        <?php if (!empty($unpaidEvents)): ?>
            <p class="text-2xl font-bold text-black">Subtotal (cart): €<?= number_format($subtotal, 2, '.', '') ?></p>
        <?php endif; ?>

        <div class="flex flex-wrap gap-4">
            <a href="/" class="inline-block rounded-lg border-0 bg-[#2F80ED] px-7 py-3.5 text-sm font-bold uppercase tracking-wide text-white no-underline transition hover:bg-[#1c6ddb]">Add more events</a>
            <?php if (!empty($unpaidEvents) && empty($awaitingPaymentEvents)): ?>
                <?php // Start a new Stripe checkout only when no checkout is already pending. ?>
                <form action="/payment/checkout" method="POST" class="inline m-0">
                    <?php // CSRF token used by PaymentController::checkoutRedirect(). ?>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('payment_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="cursor-pointer rounded-lg border-0 bg-[#2F80ED] px-7 py-3.5 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-[#1c6ddb]">Pay from cart</button>
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
