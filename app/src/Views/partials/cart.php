<?php
$headerCartItems = [];

if ($headerCartOrder instanceof \App\Models\Order && is_array($headerCartOrder->items)) {
    $headerCartItems = $headerCartOrder->items;
}
?>

<div id="cartOverlayBackdrop" class="fixed inset-0 z-[999] hidden bg-black/35" aria-hidden="true"></div>
<aside
    id="cartOverlay"
    class="fixed top-0 right-0 z-[1000] flex h-dvh w-full max-w-[420px] translate-x-full flex-col bg-white text-[#1a1a1a] shadow-[-12px_0_28px_rgba(0,0,0,.2)] transition-transform duration-200"
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

<script>
(function () {
    var toggleBtn = document.getElementById('cartToggleBtn');
    var overlay = document.getElementById('cartOverlay');
    var backdrop = document.getElementById('cartOverlayBackdrop');
    var closeBtn = document.getElementById('cartCloseBtn');
    var cartBadge = document.getElementById('cartBadge');
    var cartBody = document.getElementById('cartOverlayBody');
    var cartTotalValue = document.getElementById('cartTotalValue');
    var cartActionFlash = document.getElementById('cartActionFlash');
    var cartActionFlashTimer = null;

    if (!toggleBtn || !overlay || !backdrop || !closeBtn || !cartBody || !cartTotalValue || !cartBadge) {
        return;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderCartEmpty(message) {
        return '<p class="mt-2 text-[0.95rem] text-[#2f2f2f]">' + escapeHtml(message) + '</p>';
    }

    function renderCartItem(item) {
        var title = escapeHtml(item.title || 'Event');
        var location = escapeHtml(item.location || '');
        var quantity = Number(item.quantity || 0);
        var unitPriceLabel = escapeHtml(item.unitPriceLabel || Number(item.unitPrice || 0).toFixed(2));
        var orderItemId = Number(item.orderItemId || 0);

        return [
            '<article class="mb-[10px] rounded-xl border border-[#ececec] bg-white px-3 py-[10px] text-[#171717]">',
                '<h3 class="m-0 text-[0.98rem] font-extrabold text-[#0f0f0f]">' + title + '</h3>',
                '<p class="my-[6px] mb-[10px] text-[0.9rem] text-[#2d2d2d]">' + location + '</p>',
                '<div class="flex items-center justify-between gap-[10px]">',
                    '<div class="inline-flex items-center gap-2">',
                        '<span class="font-bold text-[#171717]">Qty: ' + quantity + ' x EUR ' + unitPriceLabel + '</span>',
                        '<div class="inline-flex items-center gap-[6px]" data-cart-qty-controls data-order-item-id="' + orderItemId + '" data-quantity="' + quantity + '">',
                            '<button type="button" class="h-[30px] w-[30px] rounded-lg border border-[#2f80ed] bg-[#2f80ed] font-bold text-white transition-colors duration-200 hover:bg-[#1d6ed8] disabled:cursor-wait disabled:opacity-60" data-cart-qty-button data-direction="decrease" aria-label="Decrease quantity for ' + title + '">-</button>',
                            '<button type="button" class="h-[30px] w-[30px] rounded-lg border border-[#2f80ed] bg-[#2f80ed] font-bold text-white transition-colors duration-200 hover:bg-[#1d6ed8] disabled:cursor-wait disabled:opacity-60" data-cart-qty-button data-direction="increase" aria-label="Increase quantity for ' + title + '">+</button>',
                        '</div>',
                    '</div>',
                    '<div class="flex items-center gap-2">',
                        '<form method="POST" action="/order/item/remove">',
                            '<input type="hidden" name="order_item_id" value="' + orderItemId + '">',
                            '<button type="submit" class="cursor-pointer rounded-lg border border-[#9f9f9f] bg-[#f3f3f3] px-[10px] py-[6px] font-bold text-[#111] transition-colors duration-200 hover:bg-[#e8e8e8] disabled:cursor-wait disabled:opacity-60">Remove</button>',
                        '</form>',
                    '</div>',
                '</div>',
            '</article>'
        ].join('');
    }

    function updateCartUI(cart) {
        if (!cart) {
            return;
        }

        var count = Number(cart.itemCount || 0);
        var totalLabel = typeof cart.totalLabel === 'string' ? cart.totalLabel : Number(cart.total || 0).toFixed(2);
        var items = Array.isArray(cart.items) ? cart.items : [];

        cartBadge.textContent = String(count);
        cartTotalValue.textContent = 'EUR ' + totalLabel;

        if (cartBody.dataset.loggedIn !== '1') {
            return;
        }

        if (items.length === 0) {
            cartBody.innerHTML = renderCartEmpty('Your cart is empty.');
            return;
        }

        cartBody.innerHTML = items.map(renderCartItem).join('');
    }

    function showCartActionFlash(message) {
        if (!cartActionFlash || !message) {
            return;
        }

        cartActionFlash.textContent = String(message);
        cartActionFlash.classList.remove('hidden');

        if (cartActionFlashTimer) {
            window.clearTimeout(cartActionFlashTimer);
        }

        cartActionFlashTimer = window.setTimeout(function () {
            cartActionFlash.classList.add('hidden');
        }, 2600);
    }

    function setOpen(isOpen) {
        overlay.classList.toggle('translate-x-full', !isOpen);
        backdrop.classList.toggle('hidden', !isOpen);
        overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        backdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    window.HaarlemCart = {
        update: updateCartUI,
        open: function () { setOpen(true); },
        close: function () { setOpen(false); }
    };

    toggleBtn.addEventListener('click', function () {
        var isOpen = toggleBtn.getAttribute('aria-expanded') === 'true';
        setOpen(!isOpen);
    });

    closeBtn.addEventListener('click', function () {
        setOpen(false);
    });

    backdrop.addEventListener('click', function () {
        setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        var action = form.getAttribute('action') || '';
        if (action !== '/order/item/remove') {
            return;
        }

        event.preventDefault();

        var submitBtn = form.querySelector('button[type="submit"]');
        if (!(submitBtn instanceof HTMLButtonElement)) {
            return;
        }

        var originalLabel = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Removing...';

        fetch(action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().catch(function () { return null; }).then(function (payload) {
                    return { response: response, payload: payload };
                });
            })
            .then(function (result) {
                var response = result.response;
                var payload = result.payload;

                if (!response.ok || !payload || payload.ok !== true) {
                    var redirect = payload && typeof payload.redirect === 'string' ? payload.redirect : '';
                    if (redirect) {
                        window.location.href = redirect;
                        return;
                    }

                    var message = payload && typeof payload.message === 'string'
                        ? payload.message
                        : 'Could not remove cart item.';
                    window.alert(message);
                    return;
                }

                updateCartUI(payload.cart || null);
                setOpen(true);
                showCartActionFlash(payload.message || 'Item removed from cart.');
            })
            .catch(function () {
                window.alert('Network error while removing from cart. Please try again.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel || 'Remove';
            });
    }, true);

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        var button = target.closest('[data-cart-qty-button]');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();

        var controls = button.closest('[data-cart-qty-controls]');
        if (!(controls instanceof HTMLElement)) {
            return;
        }

        var orderItemId = Number(controls.dataset.orderItemId || 0);
        var currentQuantity = Number(controls.dataset.quantity || 0);
        var direction = button.getAttribute('data-direction') || '';
        var delta = direction === 'increase' ? 1 : (direction === 'decrease' ? -1 : 0);

        if (orderItemId <= 0 || currentQuantity <= 0 || delta === 0) {
            return;
        }

        var nextQuantity = Math.min(99, Math.max(1, currentQuantity + delta));
        if (nextQuantity === currentQuantity) {
            return;
        }

        var qtyButtons = controls.querySelectorAll('[data-cart-qty-button]');
        qtyButtons.forEach(function (qtyButton) {
            qtyButton.disabled = true;
        });

        var body = new FormData();
        body.append('order_item_id', String(orderItemId));
        body.append('quantity', String(nextQuantity));

        fetch('/order/item/quantity', {
            method: 'POST',
            body: body,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().catch(function () { return null; }).then(function (payload) {
                    return { response: response, payload: payload };
                });
            })
            .then(function (result) {
                var response = result.response;
                var payload = result.payload;

                if (!response.ok || !payload || payload.ok !== true) {
                    var redirect = payload && typeof payload.redirect === 'string' ? payload.redirect : '';
                    if (redirect) {
                        window.location.href = redirect;
                        return;
                    }

                    var message = payload && typeof payload.message === 'string'
                        ? payload.message
                        : 'Could not update cart quantity.';
                    window.alert(message);
                    return;
                }

                updateCartUI(payload.cart || null);
                setOpen(true);
            })
            .catch(function () {
                window.alert('Network error while updating quantity. Please try again.');
            })
            .finally(function () {
                qtyButtons.forEach(function (qtyButton) {
                    qtyButton.disabled = false;
                });
            });
    }, true);
})();
</script>