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
$headerCartCount = 0;
if ($headerIsLoggedIn && isset($authPayload['userId'])) {
    try {
        $orderService = new OrderService(new OrderRepository(), new EventModelBuilderService());
        $headerCartOrder = $orderService->getPendingOrderForUser((int) $authPayload['userId']);
        if ($headerCartOrder instanceof Order) {
            $headerCartCount = $headerCartOrder->getItemCount();
        }
    } catch (\Throwable $e) {
        $headerCartCount = 0; // fallback 
    }
}

// nav logic
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function getNavClass($path, $currentPath)
{
    $baseClasses = "no-underline font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px]";
    if ($currentPath === $path) {
        return "$baseClasses bg-[#2F80ED] text-white shadow-[0_4px_10px_rgba(47,128,237,0.3)]";
    }
    return "$baseClasses text-black hover:bg-[#f5f5f5]";
}
?>

<header class="bg-white border-b border-[#f0f0f0] py-[15px] font-sans">
    <div class="max-w-[1200px] mx-auto px-5 flex justify-between items-center">

        <a href="/" class="block">
            <img src="/assets/img/homepage/logo.svg" alt="Haarlem Festival" class="h-10 block">
        </a>

        <nav class="flex items-center gap-[15px]">
            <a href="/" class="<?= getNavClass('/', $currentPath) ?>">Home</a>
            <a href="/dance" class="<?= getNavClass('/dance', $currentPath) ?>">Dance</a>
            <a href="/jazz" class="<?= getNavClass('/jazz', $currentPath) ?>">Jazz</a>
            <a href="/yummy" class="<?= getNavClass('/yummy', $currentPath) ?>">Yummy</a>
            <a href="/stories" class="<?= getNavClass('/stories', $currentPath) ?>">Stories</a>
            <a href="/history" class="<?= getNavClass('/history', $currentPath) ?>">History</a>

            <a href="/cart" class="<?= getNavClass('/cart', $currentPath) ?> flex items-center gap-2">
                Program
                <div class="relative flex items-center">
                    <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="w-6 h-6">
                    <span
                        class="absolute -top-2 -right-2 bg-[#E63946] text-white text-[0.7rem] font-bold w-[18px] h-[18px] rounded-full flex items-center justify-center border-2 border-white">
                        <?= (int) $headerCartCount ?>
                    </span>
                </div>
            </a>

            <?php if ($headerIsLoggedIn): ?>
                <a class="inline-flex items-center gap-2 no-underline text-black font-bold text-[0.95rem] py-2 px-3 rounded-[25px] transition-all duration-200 hover:bg-[#f5f5f5]"
                    href="/account/manage">
                    <img class="w-8 h-8 rounded-full object-cover" src="<?= htmlspecialchars($headerProfilePicturePath); ?>"
                        alt="Account">
                    <span>Account</span>
                </a>
                <a href="/logout"
                    class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">Logout</a>
            <?php else: ?>
                <a href="/login"
                    class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">Login</a>
            <?php endif; ?>

            <?php if ($headerIsAdmin): ?>
                <a class="<?= getNavClass('/cms', $currentPath) ?>" href="/cms">CMS</a>
            <?php endif; ?>
        </nav>
    </div>
</header>