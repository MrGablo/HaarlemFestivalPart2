<?php
$danceFooterMargin = empty($danceFooter) ? 'mt-[50px]' : 'mt-0';
?>
<footer class="bg-white py-[60px] <?= $danceFooterMargin ?> font-sans">
    <div class="max-w-[1200px] mx-auto px-5 flex flex-wrap justify-between items-start gap-10">
        
        <div class="flex-1 min-w-[200px] flex flex-col gap-5 max-w-[300px]">
            <img src="/assets/svg/logo.svg" alt="Haarlem Festival Logo" class="w-[60px]">
            
            <div class="flex gap-[15px]">
                <a href="#">
                    <img src="/assets/img/headerfooter/facebook.svg" alt="Facebook" class="w-6 h-6 opacity-60 hover:opacity-100 transition-opacity duration-200">
                </a>
                <a href="#">
                    <img src="/assets/img/headerfooter/yt.svg" alt="YouTube" class="w-6 h-6 opacity-60 hover:opacity-100 transition-opacity duration-200">
                </a>
                <a href="#">
                    <img src="/assets/img/headerfooter/insta.svg" alt="Instagram" class="w-6 h-6 opacity-60 hover:opacity-100 transition-opacity duration-200">
                </a>
            </div>

            <p class="text-[0.9rem] text-gray-500 mt-[30px]">@2025 Haarlem Festival</p>
        </div>

        <div class="flex-1 min-w-[200px]">
            <h3 class="text-[1.2rem] font-extrabold mb-[25px] text-black">Support</h3>
            <ul class="list-none p-0 m-0">
                <li class="mb-3"><a href="#" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Contact Us</a></li>
                <li class="mb-3"><a href="#" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Tickets Refund</a></li>
                <li class="mb-3"><a href="#" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Terms & Conditions</a></li>
                <li class="mb-3"><a href="#" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Privacy Policy</a></li>
            </ul>
        </div>

        <div class="flex-1 min-w-[200px]">
            <h3 class="text-[1.2rem] font-extrabold mb-[25px] text-black">Quick Menu</h3>
            <ul class="list-none p-0 m-0">
                <li class="mb-3"><a href="/" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Home</a></li>
                <li class="mb-3"><a href="/dance" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Dance!</a></li>
                <li class="mb-3"><a href="/jazz" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Haarlem Jazz</a></li>
                <li class="mb-3"><a href="/yummy" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Yummy!</a></li>
                <li class="mb-3"><a href="/stories" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">Stories in Haarlem</a></li>
                <li class="mb-3"><a href="/history" class="no-underline text-gray-800 text-base font-normal transition-colors duration-200 hover:text-[#2F80ED] hover:underline">A Stroll through History</a></li>
            </ul>
        </div>

    </div>
</footer>