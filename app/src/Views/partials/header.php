<?php
use \App\Utils\Session;
use \App\Utils\AuthSessionData;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;

Session::ensureStarted();
$authPayload = AuthSessionData::read();

$headerIsLoggedIn = isset($isLoggedIn) ? (bool) $isLoggedIn : ($authPayload !== null);
$headerProfilePicturePath = (string) ($profilePicturePath ?? ($authPayload['profilePicturePath'] ?? '/assets/img/default-user.png'));
$headerIsAdmin = strtolower((string) ($authPayload['userRole'] ?? '')) === 'admin';

// order count logic
$headerCartOrder = null;
$headerCartCount = 0;
$headerCartOrder = null;
if ($headerIsLoggedIn && isset($authPayload['userId'])) {
    try {
        $orderService = new OrderService(new OrderRepository(), new EventModelBuilderService());
        $headerCartOrder = $orderService->getPendingOrderForUser((int) $authPayload['userId']);
    } catch (\Throwable $e) {
        $headerCartOrder = null;
    }
}

if (!($headerCartOrder instanceof Order)) {
    $headerCartOrder = null;
}

$headerCartCount = $headerCartOrder ? $headerCartOrder->getItemCount() : 0;
$headerCartTotal = $headerCartOrder ? $headerCartOrder->getTotalPrice() : 0.0;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

function getNavClass($path, $currentPath, $matchPrefix = false)
{
    $baseClasses = 'no-underline font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px]';

    $isActive = $currentPath === $path;

    if ($matchPrefix && $path !== '/') {
        $isActive = $isActive || strpos($currentPath, $path . '/') === 0;
    }

    if ($isActive) {
        return $baseClasses . ' bg-[#2F80ED] text-white shadow-[0_4px_10px_rgba(47,128,237,0.3)]';
    }

    return $baseClasses . ' text-black hover:bg-[#f5f5f5]';
}
?>

<header class="bg-white border-b border-[#f0f0f0] py-[15px] font-sans">
    <div class="mx-auto flex max-w-[1200px] items-center justify-between px-5">
        <a href="/" class="block">
            <img src="/assets/img/homepage/logo.svg" alt="Haarlem Festival" class="block h-10">
        </a>

        <nav class="flex items-center gap-[15px]">
            <a href="/" class="<?= getNavClass('/', $currentPath) ?>">Home</a>
            <a href="/dance" class="<?= getNavClass('/dance', $currentPath) ?>">Dance</a>
            <a href="/jazz" class="<?= getNavClass('/jazz', $currentPath, true) ?>">Jazz</a>
            <a href="/yummy" class="<?= getNavClass('/yummy', $currentPath) ?>">Yummy</a>
            <a href="/stories" class="<?= getNavClass('/stories', $currentPath) ?>">Stories</a>
            <a href="/history" class="<?= getNavClass('/history', $currentPath) ?>">History</a>

            <button
                type="button"
                id="cartToggleBtn"
                class="<?= getNavClass('/cart', $currentPath) ?> flex items-center gap-2 border-0 bg-transparent cursor-pointer"
                aria-haspopup="dialog"
                aria-controls="cartOverlay"
                aria-expanded="false"
            >
                Program
                <span class="relative flex items-center">
                    <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="block h-5 w-5 min-w-[20px] flex-none object-contain">
                    <span
                        id="cartBadge"
                        class="absolute -top-2 -right-2 flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 border-white bg-[#E63946] text-[0.7rem] font-bold text-white"
                    >
                        <?= (int) $headerCartCount ?>
                    </span>
                </span>
            </button>

            <?php if ($headerIsLoggedIn): ?>
                <a
                    class="inline-flex items-center gap-2 no-underline text-black font-bold text-[0.95rem] py-2 px-3 rounded-[25px] transition-all duration-200 hover:bg-[#f5f5f5]"
                    href="/account/manage"
                    title="Manage account"
                    aria-label="Manage account"
                >
                    <img
                        class="block h-8 w-8 min-w-[32px] rounded-full object-cover"
                        src="<?= htmlspecialchars($headerProfilePicturePath) ?>"
                        onerror="this.onerror=null;this.src='/assets/img/default-user.png';"
                        alt="Account"
                    >
                    <span>Account</span>
                </a>
                <a href="/logout" class="<?= getNavClass('/logout', $currentPath) ?>">Logout</a>
            <?php else: ?>
                <a href="/login" class="<?= getNavClass('/login', $currentPath) ?>">Login</a>
            <?php endif; ?>

            <?php if ($headerIsAdmin): ?>
                <a href="/cms" class="<?= getNavClass('/cms', $currentPath) ?>">CMS</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php require __DIR__ . '/cart.php'; ?>
