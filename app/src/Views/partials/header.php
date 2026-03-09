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
}

.cart-overlay__close {
    border: none;
    background: transparent;
    font-size: 1.2rem;
    cursor: pointer;
}

.cart-overlay__body {
    padding: 14px 18px;
    overflow: auto;
    flex: 1;
}

.cart-empty {
    margin: 8px 0 0;
    color: #666;
    font-size: 0.95rem;
}

.cart-item {
    border: 1px solid #ececec;
    border-radius: 12px;
    padding: 10px 12px;
    margin-bottom: 10px;
}

.cart-item__title {
    margin: 0;
    font-weight: 800;
    font-size: 0.98rem;
}

.cart-item__meta {
    margin: 6px 0 10px;
    color: #666;
    font-size: 0.9rem;
}

.cart-item__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.cart-remove-btn {
    border: 1px solid #ddd;
    background: #fafafa;
    border-radius: 8px;
    padding: 6px 10px;
    cursor: pointer;
    font-weight: 700;
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
            <button type="button" class="nav-link cart-link" id="cartToggleBtn" aria-haspopup="dialog" aria-controls="cartOverlay" aria-expanded="false">
                Program
                <div class="cart-icon-wrapper">
                    <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="cart-icon">
                    <span class="cart-badge"><?php echo (int)$headerCartCount; ?></span>
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

<div class="cart-overlay-backdrop" id="cartOverlayBackdrop"></div>
<aside class="cart-overlay" id="cartOverlay" role="dialog" aria-modal="true" aria-labelledby="cartOverlayTitle">
    <div class="cart-overlay__head">
        <h2 class="cart-overlay__title" id="cartOverlayTitle">Your Cart</h2>
        <button type="button" class="cart-overlay__close" id="cartCloseBtn" aria-label="Close cart">x</button>
    </div>

    <div class="cart-overlay__body">
        <?php if (!$headerIsLoggedIn): ?>
            <p class="cart-empty">Log in to add tickets to your cart.</p>
        <?php elseif ($headerCartOrder === null || count($headerCartOrder->items) === 0): ?>
            <p class="cart-empty">Your cart is empty.</p>
        <?php else: ?>
            <?php foreach ($headerCartOrder->items as $item): ?>
                <?php $event = $item->event; ?>
                <article class="cart-item">
                    <h3 class="cart-item__title"><?php echo htmlspecialchars((string)($event?->title ?? 'Event')); ?></h3>
                    <p class="cart-item__meta">
                        <?php echo htmlspecialchars((string)$item->getLocation()); ?>
                    </p>

                    <div class="cart-item__row">
                        <span>
                            Qty: <?php echo (int)$item->quantity; ?>
                            x EUR <?php echo number_format($item->getUnitPrice(), 2); ?>
                        </span>

                        <form method="POST" action="/order/item/remove">
                            <input type="hidden" name="order_item_id" value="<?php echo (int)$item->order_item_id; ?>">
                            <button type="submit" class="cart-remove-btn">Remove</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="cart-overlay__foot">
        <p class="cart-total">
            <span>Total</span>
            <span>EUR <?php echo number_format($headerCartTotal, 2); ?></span>
        </p>
    </div>
</aside>

<script>
(function () {
    var toggleBtn = document.getElementById('cartToggleBtn');
    var overlay = document.getElementById('cartOverlay');
    var backdrop = document.getElementById('cartOverlayBackdrop');
    var closeBtn = document.getElementById('cartCloseBtn');

    if (!toggleBtn || !overlay || !backdrop || !closeBtn) {
        return;
    }

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
})();
</script>