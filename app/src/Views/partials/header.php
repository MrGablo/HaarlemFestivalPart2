<?php
use \App\Utils\Session;
use \App\Utils\AuthSessionData;

Session::ensureStarted();
$authPayload = AuthSessionData::read();

$headerIsLoggedIn = isset($isLoggedIn) ? (bool)$isLoggedIn : ($authPayload !== null);
$headerProfilePicturePath = (string)($profilePicturePath ?? ($authPayload['profilePicturePath'] ?? '/assets/img/default-user.png'));
$headerIsAdmin = strtolower((string)($authPayload['userRole'] ?? '')) === 'admin';
?>

<header class="bg-white border-b border-[#f0f0f0] py-[15px] font-sans">
    <div class="max-w-[1200px] mx-auto px-5 flex justify-between items-center">
        
        <a href="/" class="block">
            <img src="/assets/img/homepage/logo.svg" alt="Haarlem Festival" class="h-10 block">
        </a>

        <nav class="flex items-center gap-[15px]">
            <a href="/" class="no-underline text-white font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] bg-[#2F80ED] shadow-[0_4px_10px_rgba(47,128,237,0.3)] hover:bg-[#1c6ddb]">
                Home
            </a>

            <a href="/dance" class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">Dance</a>
            <a href="/jazz" class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">Jazz</a>
            <a href="/yummy" class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">Yummy</a>
            <a href="/stories" class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">Stories</a>
            <a href="/history" class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">History</a>
            
            <a href="/cart" class="flex items-center gap-2 no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]">
                Program
                <div class="relative flex items-center">
                    <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="w-6 h-6">
                    <span class="absolute -top-2 -right-2 bg-[#E63946] text-white text-[0.7rem] font-bold w-[18px] h-[18px] rounded-full flex items-center justify-center border-2 border-white">
                        0
                    </span>
                </div>
            </a>

            <?php if ($headerIsLoggedIn): ?>
                <a class="inline-flex items-center gap-2 no-underline text-black font-bold text-[0.95rem] py-2 px-3 rounded-[25px] transition-all duration-200 hover:bg-[#f5f5f5]" href="/account/manage" title="Manage account">
                    <img class="w-8 h-8 min-w-[32px] rounded-full object-cover block" 
                         src="<?php echo htmlspecialchars($headerProfilePicturePath); ?>"
                         onerror="this.onerror=null;this.src='/assets/img/default-user.png';" 
                         alt="Account">
                    <span>Account</span>
                </a>
                <a class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]" href="/logout">Logout</a>
            <?php else: ?>
                <a class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]" href="/login">Login</a>
            <?php endif; ?>

            <?php if ($headerIsAdmin): ?>
                <a class="no-underline text-black font-bold text-base py-2.5 px-[18px] transition-all duration-200 rounded-[25px] hover:bg-[#f5f5f5]" href="/cms">CMS</a>
            <?php endif; ?>
        </nav>
    </div>
</header>