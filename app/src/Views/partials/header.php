<?php

use \App\Utils\Session;
use \App\Utils\AuthSessionData;
use App\Cms\Services\CmsNavigationService;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;

// session and auth setup
Session::ensureStarted();
$authPayload = AuthSessionData::read();

$headerIsLoggedIn = isset($isLoggedIn) ? (bool) $isLoggedIn : ($authPayload !== null);
$headerProfilePicturePath = (string) ($profilePicturePath ?? ($authPayload['profilePicturePath'] ?? '/assets/img/default-user.png'));
$headerIsAdmin = strtolower((string) ($authPayload['userRole'] ?? '')) === 'admin';
$headerIsStaff = in_array(strtolower((string) ($authPayload['userRole'] ?? '')), ['admin', 'employee'], true);

// get editable cart order if logged in (checkout-in-progress is not editable)
$headerCartOrder = null;
$headerCartCount = 0;
$headerCartTotal = 0.00; 

if ($headerIsLoggedIn && isset($authPayload['userId'])) {
    try {
        $orderService = new OrderService(new OrderRepository(), new EventModelBuilderService());
        $headerCartOrder = $orderService->getPendingOrderForUser((int) $authPayload['userId']);
    } catch (\Throwable $e) {
        $headerCartOrder = null;
    }
}

if ($headerCartOrder instanceof Order) {
    $headerCartCount = $headerCartOrder->getItemCount();
    $headerCartTotal = $headerCartOrder->getTotalPrice();
}

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$cmsNavigation = [];
$activeCmsNavKey = null;

if ($headerIsAdmin && ($currentPath === '/cms' || str_starts_with($currentPath, '/cms/'))) {
    $cmsNavigationService = new CmsNavigationService();
    $cmsNavigation = $cmsNavigationService->items();
    $activeCmsNavKey = $cmsNavigationService->activeKey($currentPath);
}

// safely define nav function so it doesn't break if header is included twice
if (!function_exists('getNavClass')) {
    function getNavClass($path, $currentPath, $matchPrefix = false) {
        $baseClasses = 'no-underline font-bold text-base py-2.5 px-5 transition-all duration-200 rounded-full';
        $isActive = ($currentPath === $path) || ($matchPrefix && $path !== '/' && strpos($currentPath, $path . '/') === 0);
        return $isActive 
            ? $baseClasses . ' bg-blue-600 text-white shadow-md shadow-blue-500/30' 
            : $baseClasses . ' text-black hover:bg-gray-100';
    }
}

if (!function_exists('getCmsNavClass')) {
    function getCmsNavClass(bool $isActive): string {
        $baseClasses = 'whitespace-nowrap rounded-full px-3 py-1.5 text-sm font-semibold transition-colors';

        return $isActive
            ? $baseClasses . ' bg-white text-slate-950'
            : $baseClasses . ' text-slate-300 hover:bg-slate-800 hover:text-white';
    }
}
?>

<header class="bg-gradient-to-r from-blue-600 to-white lg:bg-none lg:bg-white border-b border-gray-100 font-sans sticky top-0 z-30">
    <div class="mx-auto flex max-w-6xl items-stretch justify-between pl-5 lg:px-5">
        <a href="/" class="flex items-center py-4">
            <img src="/assets/svg/logo.svg" alt="Haarlem Festival" class="block h-10 brightness-0 invert lg:brightness-100 lg:invert-0 transition-all">
        </a>

        <nav class="hidden lg:flex items-center gap-3 py-4">
            <a href="/" class="<?= getNavClass('/', $currentPath) ?>">Home</a>
            <a href="/dance" class="<?= getNavClass('/dance', $currentPath) ?>">Dance</a>
            <a href="/jazz" class="<?= getNavClass('/jazz', $currentPath, true) ?>">Jazz</a>
            <a href="/yummy" class="<?= getNavClass('/yummy', $currentPath) ?>">Yummy</a>
            <a href="/stories" class="<?= getNavClass('/stories', $currentPath) ?>">Stories</a>
            <a href="/history" class="<?= getNavClass('/history', $currentPath) ?>">History</a>
            <a href="/program" class="<?= getNavClass('/program', $currentPath) ?>">Program</a>

            <button
                type="button"
                id="cartToggleBtn"
                class="<?= getNavClass('/cart', $currentPath) ?> flex items-center gap-2 border-0 bg-transparent cursor-pointer"
                aria-haspopup="dialog"
                aria-controls="cartOverlay"
                aria-expanded="false">
                Cart
                <span class="relative flex items-center">
                    <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="block h-5 w-5 min-w-[20px] flex-none object-contain">
                    <span
                        id="cartBadge"
                        class="absolute -top-2 -right-2 flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 border-white bg-[#E63946] text-[0.7rem] font-bold text-white">
                        <?= (int) $headerCartCount ?>
                    </span>
                </span>
            </button>

            <?php if ($headerIsLoggedIn): ?>
                <a class="flex items-center gap-2 font-bold px-3 py-2 rounded-full hover:bg-gray-100" href="/account/manage">
                    <img class="h-8 w-8 rounded-full object-cover" src="<?= htmlspecialchars($headerProfilePicturePath) ?>" alt="User">
                </a>
                <a href="/logout" class="<?= getNavClass('/logout', $currentPath) ?>">Logout</a>
            <?php else: ?>
                <a href="/login" class="<?= getNavClass('/login', $currentPath) ?>">Login</a>
            <?php endif; ?>

            <?php if ($headerIsAdmin): ?>
                <a href="/cms" class="<?= getNavClass('/cms', $currentPath) ?>">CMS</a>
            <?php endif; ?>

            <?php if ($headerIsStaff): ?>
                <a href="/scanner" class="<?= getNavClass('/scanner', $currentPath) ?>">Scanner</a>
            <?php endif; ?>
        </nav>

        <div class="flex lg:hidden items-stretch">
            <button type="button" class="js-cart-toggle relative px-4 flex items-center border-0 bg-transparent cursor-pointer">
                <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="h-7 w-7 drop-shadow-md lg:drop-shadow-none">
                <span class="absolute top-4 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow-sm">
                    <?= (int)$headerCartCount ?>
                </span>
            </button>
            <button id="mobileMenuBtn" class="bg-blue-600 px-5 flex items-center justify-center text-white cursor-pointer border-0 transition-colors hover:bg-blue-700">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <div id="mobileMenuBackdrop" class="hidden fixed inset-0 z-40 bg-black/40 backdrop-blur-sm transition-opacity"></div>

    <div id="mobileMenu" class="fixed top-0 right-0 h-full w-72 sm:w-80 z-50 bg-white p-6 text-black flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl overflow-y-auto">
        <div class="flex justify-between items-center mb-8">
            <img src="/assets/svg/logo.svg" alt="Logo" class="h-10">
            <button id="closeMenuBtn" class="text-4xl text-gray-500 hover:text-black leading-none pb-2">&times;</button>
        </div>
        <nav class="flex flex-col gap-5 text-xl font-bold">
            <a href="/">Home</a>
            <a href="/dance">Dance</a>
            <a href="/jazz">Jazz</a>
            <a href="/yummy">Yummy</a>
            <a href="/history">History</a>
            <a href="/stories">Stories</a>
            <a href="/program" class="text-blue-600">Program</a>
            <hr class="border-gray-100 my-2">
            
            <?php if ($headerIsLoggedIn): ?>
                <a href="/account/manage">Account</a>
                <a href="/logout" class="text-red-500">Logout</a>
            <?php else: ?>
                <a href="/login">Login</a>
            <?php endif; ?>

            <?php if ($headerIsAdmin || $headerIsStaff): ?>
                <hr class="border-gray-100 my-2">
                <?php if ($headerIsAdmin): ?>
                    <a href="/cms" class="text-emerald-600">CMS</a>
                <?php endif; ?>
                <?php if ($headerIsStaff): ?>
                    <a href="/scanner" class="text-emerald-600">Scanner</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>

    <?php if ($cmsNavigation !== []): ?>
        <div class="border-t border-slate-800 bg-slate-950 text-white">
            <div class="mx-auto max-w-6xl overflow-x-auto px-4">
                <nav class="flex min-w-max items-center gap-2 py-3">
                    <?php foreach ($cmsNavigation as $item): ?>
                        <?php $isActiveCmsItem = ($activeCmsNavKey === (string) ($item['key'] ?? '')); ?>
                        <a href="<?= htmlspecialchars((string) ($item['href'] ?? '/cms')) ?>" class="<?= getCmsNavClass($isActiveCmsItem) ?>">
                            <?= htmlspecialchars((string) ($item['title'] ?? 'CMS')) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // reusable drawer function for side panels
    const setupDrawer = (triggerSelector, drawerId, backdropId, closeBtnId) => {
        const drawer = document.getElementById(drawerId);
        const backdrop = document.getElementById(backdropId);
        const triggers = document.querySelectorAll(triggerSelector);
        const closeBtn = document.getElementById(closeBtnId);

        if (!drawer || !backdrop) return;

        const openDrawer = (e) => {
            if (e) e.preventDefault();
            backdrop.classList.remove('hidden');
            setTimeout(() => drawer.classList.remove('translate-x-full'), 10);
            document.body.style.overflow = 'hidden';
        };

        const closeDrawer = () => {
            drawer.classList.add('translate-x-full');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        };

        triggers.forEach(t => t.addEventListener('click', openDrawer));
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        backdrop.addEventListener('click', closeDrawer);
    };

    // initialize both sliding components
    setupDrawer('.js-cart-toggle', 'cartOverlay', 'cartOverlayBackdrop', 'cartCloseBtn');
    setupDrawer('#mobileMenuBtn', 'mobileMenu', 'mobileMenuBackdrop', 'closeMenuBtn');
});
</script>

<?php require __DIR__ . '/cart.php'; ?>