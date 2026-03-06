<?php
declare(strict_types=1);

/** @var int $totalEvents */
/** @var float $subtotal */
$totalEvents = (int)($totalEvents ?? 0);
$subtotal = (float)($subtotal ?? 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Program – Haarlem Festival</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #333;
            line-height: 1.6;
        }
        .program-page { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .program-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 40px;
        }
        .program-title-block { flex: 1; min-width: 280px; }
        .program-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            line-height: 1.2;
        }
        .program-title .word-my { color: #000; }
        .program-title .word-program { color: #2F80ED; }
        .program-subtitle {
            font-size: 1rem;
            color: #555;
            margin-bottom: 4px;
        }
        .program-events-box {
            flex-shrink: 0;
            border: 2px solid #2F80ED;
            border-radius: 12px;
            padding: 24px 32px;
            text-align: center;
            min-width: 180px;
        }
        .program-events-box .icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            display: block;
        }
        .program-events-box .number {
            font-size: 2rem;
            font-weight: 800;
            color: #000;
        }
        .program-events-box .label {
            font-size: 0.9rem;
            color: #555;
        }
        .program-subtotal {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 28px;
        }
        .program-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 48px;
        }
        .program-btn {
            display: inline-block;
            padding: 14px 28px;
            background-color: #2F80ED;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .program-btn:hover { background-color: #1c6ddb; }
        .program-info { margin-top: 32px; }
        .program-info h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #000;
        }
        .program-info p {
            font-size: 1rem;
            color: #555;
            margin-bottom: 24px;
        }
        .program-info a {
            color: #2F80ED;
            text-decoration: none;
        }
        .program-info a:hover { text-decoration: underline; }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="program-page">
        <div class="program-header">
            <div class="program-title-block">
                <h1 class="program-title">
                    <span class="word-my">MY</span> <span class="word-program">PROGRAM</span>
                </h1>
                <p class="program-subtitle">Your personal festival schedule and reservations</p>
                <p class="program-subtitle">For the bought tickets check the mobile Wallet app!</p>
            </div>
            <div class="program-events-box">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="#2F80ED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <div class="number"><?= $totalEvents ?></div>
                <div class="label">Total Events</div>
            </div>
        </div>

        <p class="program-subtotal">Subtotal: €<?= number_format($subtotal, 2, '.', '') ?></p>

        <div class="program-actions">
            <a href="/jazz" class="program-btn">Add more events</a>
            <a href="#" class="program-btn" id="shareProgramBtn">Share my program</a>
            <a href="#" class="program-btn">Proceed with booking</a>
        </div>

        <section class="program-info">
            <h3>Ticket Collection</h3>
            <p>All tickets are digital. Check your email for QR codes to present at venue entrances.</p>

            <h3>Cancellation Policy</h3>
            <p>Free cancellation up to 24 hours before each event. Refunds processed within 5–7 business days.</p>

            <h3>Need Help?</h3>
            <p>Contact our support team at <a href="mailto:support@haarlemfestival.nl">support@haarlemfestival.nl</a> or call <a href="tel:+31231234567">+31 23 123 4567</a>.</p>
        </section>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
        document.getElementById('shareProgramBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (navigator.share) {
                navigator.share({
                    title: 'My Haarlem Festival Program',
                    text: 'Check out my festival program!',
                    url: window.location.href
                }).catch(function() {});
            } else {
                window.prompt('Copy link:', window.location.href);
            }
        });
    </script>
</body>

</html>
