<?php
declare(strict_types=1);

use App\Utils\AuthSessionData;

$auth = AuthSessionData::read();
$userName = $auth['userName'] ?? 'Guest';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful – Haarlem Festival</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-white font-[system-ui,'Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-neutral-800 leading-relaxed">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="relative min-h-[620px] overflow-hidden bg-[#f7f7f7] px-5 pb-20 pt-12">
        <canvas id="confetti-canvas" class="pointer-events-none absolute inset-0 z-[1] h-full w-full" aria-hidden="true"></canvas>

        <section class="relative z-[2] mx-auto max-w-[460px] pt-2 text-center">
            <div class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full bg-[#b8dfff] text-[26px] font-bold text-[#1b8fe8]" aria-hidden="true">&#10003;</div>
            <h1 class="mb-6 text-[49px] font-bold uppercase leading-tight text-black">Payment Successful!</h1>

            <a href="/program" class="mb-2.5 block w-full rounded-none bg-[#1f98eb] px-5 py-4 text-center text-[25px] font-bold uppercase leading-[30px] text-white no-underline transition duration-150 hover:-translate-y-px hover:brightness-[0.98]">View My Program</a>
            <a href="/" class="mb-[18px] block w-full rounded-none bg-black px-5 py-[7px] text-center text-[13px] font-bold uppercase leading-[19.5px] text-white no-underline transition duration-150 hover:-translate-y-px hover:brightness-[0.98]">Back to Home Page</a>

            <div class="rounded-md border border-[#c0d3ef] bg-[#d9e2f2] px-4 py-3.5 text-left text-[#4b5563]">
                <p class="mb-1 text-[13px] font-semibold leading-[19.5px] text-[#3f4754]"><span class="mr-2 font-bold text-[#1f98eb]">i</span>Confirmation Email Sent</p>
                <p class="m-0 text-[13px] font-normal leading-[19.5px] text-[#6b7280]">
                    A confirmation email with your tickets and QR codes has been sent to your email address, <?= htmlspecialchars($userName) ?>.
                    Please present these at the venue entrance.
                </p>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        (function () {
            const canvas = document.getElementById('confetti-canvas');
            if (!canvas || typeof confetti !== 'function') return;

            const myConfetti = confetti.create(canvas, { resize: true, useWorker: true });
            const duration = 3200;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 25, spread: 360, ticks: 70, zIndex: 1, scalar: 0.95 };
            const colors = ['#1f98eb', '#1932f0', '#9ca3af', '#d1d5db'];

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function () {
                const timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    return;
                }

                myConfetti(Object.assign({}, defaults, {
                    particleCount: 42,
                    colors: colors,
                    origin: {
                        x: randomInRange(0.05, 0.95),
                        y: randomInRange(0.0, 0.18)
                    }
                }));
            }, 220);
        })();
    </script>
</body>

</html>
