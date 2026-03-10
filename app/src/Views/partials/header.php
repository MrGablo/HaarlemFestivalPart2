<?php

use \App\Utils\Session;
use \App\Utils\AuthSessionData;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;

Session::ensureStarted();
$authPayload = AuthSessionData::read();

$headerIsLoggedIn = isset($isLoggedIn) ? (bool)$isLoggedIn : ($authPayload !== null);
$headerProfilePicturePath = (string)($profilePicturePath ?? ($authPayload['profilePicturePath'] ?? '/assets/img/default-user.png'));
$headerIsAdmin = strtolower((string)($authPayload['userRole'] ?? '')) === 'admin';

$headerCartOrder = null;
if ($headerIsLoggedIn && isset($authPayload['userId'])) {
    try {
        $orderService = new OrderService(new OrderRepository(), new EventModelBuilderService());
        $headerCartOrder = $orderService->getPendingOrderForUser((int)$authPayload['userId']);
    } catch (\Throwable $e) {
        // Keep header resilient if cart tables are not yet migrated.
        $headerCartOrder = null;
    }
}

if (!($headerCartOrder instanceof Order)) {
    $headerCartOrder = null;
}

$headerCartCount = $headerCartOrder ? $headerCartOrder->getItemCount() : 0;
$headerCartTotal = $headerCartOrder ? $headerCartOrder->getTotalPrice() : 0.0;
?>
<style>
.main-header {
    background-color: #fff;
    border-bottom: 1px solid #f0f0f0;
    padding: 15px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-logo img {
    height: 40px;
    /* Adjust based on your actual logo size */
    display: block;
}

.main-nav {
    display: flex;
    align-items: center;
    gap: 15px;
    /* Spacing between links */
}

.nav-link {
    text-decoration: none;
    color: #000;
    font-weight: 700;
    font-size: 1rem;
    padding: 10px 18px;
    transition: all 0.2s;
    border-radius: 25px;
    /* Pill shape for hover effects */
}

.nav-link:hover {
    background-color: #f5f5f5;
}

.nav-link.nav-active {
    background-color: #2F80ED;
    /* Bright Blue */
    color: white;
    box-shadow: 0 4px 10px rgba(47, 128, 237, 0.3);
}

.nav-link.nav-active:hover {
    background-color: #1c6ddb;
}

.topbar-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #000;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 8px 12px;
    border-radius: 25px;
    transition: all 0.2s;
}

.topbar-link:hover {
    background-color: #f5f5f5;
}

.topbar-avatar {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 9999px;
    object-fit: cover;
    display: block;
}

.cart-link {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 0;
    background: transparent;
    cursor: pointer;
    font: inherit;
}

.cart-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.cart-icon {
    width: 24px;
    height: 24px;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #E63946;
    color: white;
    font-size: 0.7rem;
    font-weight: bold;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
}

.cart-overlay-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    z-index: 999;
    display: none;
}

.cart-overlay-backdrop.is-open {
    display: block;
}

.cart-overlay {
    position: fixed;
    top: 0;
    right: 0;
    width: min(420px, 100%);
    height: 100dvh;
    background: #fff;
    box-shadow: -12px 0 28px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.22s ease;
    color: #1a1a1a;
}

.cart-overlay.is-open {
    transform: translateX(0);
}

.cart-overlay__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid #ececec;
}

.cart-overlay__title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: #111;
}

.cart-overlay__close {
    border: none;
    background: transparent;
    font-size: 1.2rem;
    cursor: pointer;
    color: #222;
}

.cart-overlay__body {
    padding: 14px 18px;
    overflow: auto;
    flex: 1;
}

.cart-empty {
    margin: 8px 0 0;
    color: #2f2f2f;
    font-size: 0.95rem;
}

.cart-item {
    border: 1px solid #ececec;
    border-radius: 12px;
    padding: 10px 12px;
    margin-bottom: 10px;
    background: #fff;
    color: #171717;
}

.cart-item__title {
    margin: 0;
    font-weight: 800;
    font-size: 0.98rem;
    color: #0f0f0f;
}

.cart-item__meta {
    margin: 6px 0 10px;
    color: #2d2d2d;
    font-size: 0.9rem;
}

.cart-item__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.cart-item__actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.cart-item__qty-block {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.cart-qty-controls {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.cart-qty-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #2f80ed;
    background: #2f80ed;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 700;
}

.cart-qty-btn:hover {
    background: #1d6ed8;
}

.cart-qty-btn:disabled {
    opacity: 0.65;
    cursor: wait;
}

.cart-item__row span {
    color: #171717;
    font-weight: 700;
}

.cart-remove-btn {
    border: 1px solid #9f9f9f;
    background: #f3f3f3;
    color: #111;
    border-radius: 8px;
    padding: 6px 10px;
    cursor: pointer;
    font-weight: 700;
}

.cart-remove-btn:hover {
    background: #e8e8e8;
}

.cart-overlay__foot {
    padding: 14px 18px;
    border-top: 1px solid #ececec;
}

.cart-total {
    margin: 0;
    display: flex;
    justify-content: space-between;
    font-size: 1rem;
    font-weight: 800;
    color: #0f0f0f;
}
</style>

<header class="main-header">
    <div class="header-container">
        <a href="/" class="header-logo">
            <img src="/assets/img/homepage/logo.svg" alt="Haarlem Festival">
        </a>

        <nav class="main-nav">
            <a href="/" class="nav-link nav-active">Home</a>

            <a href="/dance" class="nav-link">Dance</a>
            <a href="/jazz" class="nav-link">Jazz</a>
            <a href="/yummy" class="nav-link">Yummy</a>
            <a href="/stories" class="nav-link">Stories</a>
            <a href="/history" class="nav-link">History</a>
            <button type="button" class="nav-link cart-link inline-flex items-center gap-2 border-0 bg-transparent" id="cartToggleBtn" aria-haspopup="dialog" aria-controls="cartOverlay" aria-expanded="false">
                Program
                <div class="cart-icon-wrapper relative flex items-center">
                    <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="cart-icon h-6 w-6">
                    <span class="cart-badge absolute -right-2 -top-2 flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 border-white bg-[#E63946] text-[0.7rem] font-bold text-white" id="cartBadge"><?php echo (int)$headerCartCount; ?></span>
                </div>
            </button>
            <?php if ($headerIsLoggedIn): ?>
            <a class="topbar-link" href="/account/manage" title="Manage account" aria-label="Manage account">
                <img class="topbar-avatar" src="<?php echo htmlspecialchars($headerProfilePicturePath); ?>"
                    onerror="this.onerror=null;this.src='/assets/img/default-user.png';" alt="Account">
                <span>Account</span>
            </a>
            <a class="nav-link" href="/logout">Logout</a>
            <?php else: ?>
            <a class="nav-link" href="/login">Login</a>
            <?php endif; ?>

            <?php if ($headerIsAdmin): ?>
            <a class="nav-link" href="/cms">CMS</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="cart-overlay-backdrop fixed inset-0 z-[999] hidden bg-black/35" id="cartOverlayBackdrop"></div>
<aside class="cart-overlay fixed right-0 top-0 z-[1000] flex h-dvh w-full max-w-[420px] translate-x-full flex-col bg-white text-[#1a1a1a] shadow-[-12px_0_28px_rgba(0,0,0,.2)] transition-transform duration-200" id="cartOverlay" role="dialog" aria-modal="true" aria-labelledby="cartOverlayTitle">
    <div class="cart-overlay__head flex items-center justify-between border-b border-[#ececec] px-5 py-[18px]">
        <h2 class="cart-overlay__title m-0 text-[1.1rem] font-extrabold text-[#111]" id="cartOverlayTitle">Your Cart</h2>
        <button type="button" class="cart-overlay__close cursor-pointer border-0 bg-transparent text-[1.2rem] text-[#222]" id="cartCloseBtn" aria-label="Close cart">x</button>
    </div>

    <div class="cart-overlay__body flex-1 overflow-auto px-[18px] py-[14px]" id="cartOverlayBody" data-logged-in="<?php echo $headerIsLoggedIn ? '1' : '0'; ?>">
        <?php if (!$headerIsLoggedIn): ?>
            <p class="cart-empty mt-2 text-[0.95rem] text-[#2f2f2f]">Log in to add tickets to your cart.</p>
        <?php elseif ($headerCartOrder === null || count($headerCartOrder->items) === 0): ?>
            <p class="cart-empty mt-2 text-[0.95rem] text-[#2f2f2f]">Your cart is empty.</p>
        <?php else: ?>
            <?php foreach ($headerCartOrder->items as $item): ?>
                <?php $event = $item->event; ?>
                <article class="cart-item mb-[10px] rounded-xl border border-[#ececec] bg-white px-3 py-[10px] text-[#171717]">
                    <h3 class="cart-item__title m-0 text-[0.98rem] font-extrabold text-[#0f0f0f]"><?php echo htmlspecialchars((string)($event?->title ?? 'Event')); ?></h3>
                    <p class="cart-item__meta my-[6px] mb-[10px] text-[0.9rem] text-[#2d2d2d]">
                        <?php echo htmlspecialchars((string)$item->getLocation()); ?>
                    </p>

                    <div class="cart-item__row flex items-center justify-between gap-[10px]">
                        <div class="cart-item__qty-block">
                            <span class="font-bold text-[#171717]">
                                Qty: <?php echo (int)$item->quantity; ?>
                                x EUR <?php echo number_format($item->getUnitPrice(), 2); ?>
                            </span>

                            <div class="cart-qty-controls" data-order-item-id="<?php echo (int)$item->order_item_id; ?>" data-quantity="<?php echo (int)$item->quantity; ?>">
                                <button type="button" class="cart-qty-btn" data-direction="decrease" aria-label="Decrease quantity for <?php echo htmlspecialchars((string)($event?->title ?? 'Event')); ?>">-</button>
                                <button type="button" class="cart-qty-btn" data-direction="increase" aria-label="Increase quantity for <?php echo htmlspecialchars((string)($event?->title ?? 'Event')); ?>">+</button>
                            </div>
                        </div>

                        <div class="cart-item__actions">
                            <form method="POST" action="/order/item/remove">
                                <input type="hidden" name="order_item_id" value="<?php echo (int)$item->order_item_id; ?>">
                                <button type="submit" class="cart-remove-btn cursor-pointer rounded-lg border border-[#9f9f9f] bg-[#f3f3f3] px-[10px] py-[6px] font-bold text-[#111] hover:bg-[#e8e8e8]">Remove</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="cart-overlay__foot border-t border-[#ececec] px-[18px] py-[14px]">
        <p class="cart-total m-0 flex justify-between text-base font-extrabold text-[#0f0f0f]">
            <span>Total</span>
            <span id="cartTotalValue">EUR <?php echo number_format($headerCartTotal, 2); ?></span>
        </p>
    </div>
</aside>

<script>
(function () {
    var toggleBtn = document.getElementById('cartToggleBtn');
    var overlay = document.getElementById('cartOverlay');
    var backdrop = document.getElementById('cartOverlayBackdrop');
    var closeBtn = document.getElementById('cartCloseBtn');
    var cartBadge = document.getElementById('cartBadge');
    var cartBody = document.getElementById('cartOverlayBody');
    var cartTotalValue = document.getElementById('cartTotalValue');

    if (!toggleBtn || !overlay || !backdrop || !closeBtn) {
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

    function updateCartUI(cart) {
        if (!cart || !cartBody || !cartTotalValue || !cartBadge) {
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
            cartBody.innerHTML = '<p class="cart-empty mt-2 text-[0.95rem] text-[#2f2f2f]">Your cart is empty.</p>';
            return;
        }

        cartBody.innerHTML = items.map(function (item) {
            var title = escapeHtml(item.title || 'Event');
            var location = escapeHtml(item.location || '');
            var quantity = Number(item.quantity || 0);
            var unitPriceLabel = escapeHtml(item.unitPriceLabel || Number(item.unitPrice || 0).toFixed(2));
            var orderItemId = Number(item.orderItemId || 0);

            return [
                '<article class="cart-item mb-[10px] rounded-xl border border-[#ececec] bg-white px-3 py-[10px] text-[#171717]">',
                    '<h3 class="cart-item__title m-0 text-[0.98rem] font-extrabold text-[#0f0f0f]">' + title + '</h3>',
                    '<p class="cart-item__meta my-[6px] mb-[10px] text-[0.9rem] text-[#2d2d2d]">' + location + '</p>',
                    '<div class="cart-item__row flex items-center justify-between gap-[10px]">',
                        '<div class="cart-item__qty-block">',
                            '<span class="font-bold text-[#171717]">Qty: ' + quantity + ' x EUR ' + unitPriceLabel + '</span>',
                            '<div class="cart-qty-controls" data-order-item-id="' + orderItemId + '" data-quantity="' + quantity + '">',
                                '<button type="button" class="cart-qty-btn" data-direction="decrease" aria-label="Decrease quantity for ' + title + '">-</button>',
                                '<button type="button" class="cart-qty-btn" data-direction="increase" aria-label="Increase quantity for ' + title + '">+</button>',
                            '</div>',
                        '</div>',
                        '<div class="cart-item__actions">',
                            '<form method="POST" action="/order/item/remove">',
                                '<input type="hidden" name="order_item_id" value="' + orderItemId + '">',
                                '<button type="submit" class="cart-remove-btn cursor-pointer rounded-lg border border-[#9f9f9f] bg-[#f3f3f3] px-[10px] py-[6px] font-bold text-[#111] hover:bg-[#e8e8e8]">Remove</button>',
                            '</form>',
                        '</div>',
                    '</div>',
                '</article>'
            ].join('');
        }).join('');
    }

    window.HaarlemCart = {
        update: updateCartUI,
        open: function () { setOpen(true); },
        close: function () { setOpen(false); }
    };

    function setOpen(isOpen) {
        overlay.classList.toggle('is-open', isOpen);
        backdrop.classList.toggle('is-open', isOpen);
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    toggleBtn.addEventListener('click', function () {
        var isOpen = overlay.classList.contains('is-open');
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

    // Handle remove actions without reloading the page.
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
            })
            .catch(function () {
                window.alert('Network error while removing from cart. Please try again.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel || 'Remove';
            });
    }, true);

    // Use quantity arrows for instant increment/decrement updates.
    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        var button = target.closest('.cart-qty-btn');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();

        var controls = button.closest('.cart-qty-controls');
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

        var qtyButtons = controls.querySelectorAll('.cart-qty-btn');
        qtyButtons.forEach(function (btn) {
            btn.disabled = true;
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
                qtyButtons.forEach(function (btn) {
                    btn.disabled = false;
                });
            });
    }, true);
})();
</script>