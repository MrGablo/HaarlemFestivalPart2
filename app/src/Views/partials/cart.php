<?php
$headerCartItems = [];

if ($headerCartOrder instanceof \App\Models\Order && is_array($headerCartOrder->items)) {
    $headerCartItems = $headerCartOrder->items;
}
?>

<div id="cartOverlayBackdrop" class="fixed inset-0 z-[999] hidden bg-black/35" aria-hidden="true"></div>
<aside
    id="cartOverlay"
    class="fixed top-0 right-0 z-[1000] flex h-dvh w-full max-w-[560px] translate-x-full flex-col bg-white text-[#1a1a1a] shadow-[-12px_0_28px_rgba(0,0,0,.2)] transition-transform duration-200"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="cartOverlayTitle"
>
    <div class="flex items-center justify-between border-b border-[#ececec] px-5 py-[18px]">
        <h2 id="cartOverlayTitle" class="m-0 text-[1.1rem] font-extrabold text-[#111]">Your Cart</h2>
        <button type="button" id="cartCloseBtn" class="cursor-pointer border-0 bg-transparent text-[1.2rem] text-[#222]" aria-label="Close cart">x</button>
    </div>

    <div id="cartOverlayBody" class="flex-1 overflow-auto px-[18px] py-[14px]" data-logged-in="<?= $headerIsLoggedIn ? '1' : '0' ?>">
        <?php if (!$headerIsLoggedIn): ?>
            <p class="mt-2 text-[0.95rem] text-[#2f2f2f]">Log in to add tickets to your cart.</p>
        <?php elseif ($headerCartItems === []): ?>
            <p class="mt-2 text-[0.95rem] text-[#2f2f2f]">Your cart is empty.</p>
        <?php else: ?>
            <?php foreach ($headerCartItems as $item): ?>
                <?php $event = $item->event; ?>
                <article class="mb-[10px] rounded-xl border border-[#ececec] bg-white px-3 py-[10px] text-[#171717]">
                    <h3 class="m-0 text-[0.98rem] font-extrabold text-[#0f0f0f]"><?= htmlspecialchars((string) ($event?->title ?? 'Event')) ?></h3>
                    <p class="my-[6px] mb-[10px] text-[0.9rem] text-[#2d2d2d]"><?= htmlspecialchars((string) $item->getLocation()) ?></p>

                    <div class="flex items-center justify-between gap-[10px]">
                        <div class="inline-flex items-center gap-2">
                            <span class="font-bold text-[#171717]">
                                Qty: <?= (int) $item->quantity ?> x EUR <?= number_format($item->getUnitPrice(), 2) ?>
                            </span>

                            <div class="inline-flex items-center gap-[6px]" data-cart-qty-controls data-order-item-id="<?= (int) $item->order_item_id ?>" data-quantity="<?= (int) $item->quantity ?>">
                                <button
                                    type="button"
                                    class="h-[30px] w-[30px] rounded-lg border border-[#2f80ed] bg-[#2f80ed] font-bold text-white transition-colors duration-200 hover:bg-[#1d6ed8] disabled:cursor-wait disabled:opacity-60"
                                    data-cart-qty-button
                                    data-direction="decrease"
                                    aria-label="Decrease quantity for <?= htmlspecialchars((string) ($event?->title ?? 'Event')) ?>"
                                >-</button>
                                <button
                                    type="button"
                                    class="h-[30px] w-[30px] rounded-lg border border-[#2f80ed] bg-[#2f80ed] font-bold text-white transition-colors duration-200 hover:bg-[#1d6ed8] disabled:cursor-wait disabled:opacity-60"
                                    data-cart-qty-button
                                    data-direction="increase"
                                    aria-label="Increase quantity for <?= htmlspecialchars((string) ($event?->title ?? 'Event')) ?>"
                                >+</button>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="/order/item/remove">
                                <input type="hidden" name="order_item_id" value="<?= (int) $item->order_item_id ?>">
                                <button type="submit" class="cursor-pointer rounded-lg border border-[#9f9f9f] bg-[#f3f3f3] px-[10px] py-[6px] font-bold text-[#111] transition-colors duration-200 hover:bg-[#e8e8e8] disabled:cursor-wait disabled:opacity-60">Remove</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="border-t border-[#ececec] px-[18px] py-[14px]">
        <p class="m-0 flex justify-between text-base font-extrabold text-[#0f0f0f]">
            <span>Total</span>
            <span id="cartTotalValue">EUR <?= number_format($headerCartTotal, 2) ?></span>
        </p>
    </div>
</aside>

<div
    id="cartActionFlash"
    class="pointer-events-none fixed right-6 top-6 z-[1300] hidden rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-lg"
    role="status"
    aria-live="polite"
></div>

<script src="/assets/js/cart/cart_overlay.js"></script>