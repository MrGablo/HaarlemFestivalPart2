<?php
use App\Config;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['hero']['title'] ?? 'Haarlem Festival'); ?></title>
    <style>
        /* BASIC RESET */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #fff;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        img {
            max-width: 100%;
            display: block;
        }

        /* HERO SECTION */
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 80px 0;
            gap: 20px;
            overflow: visible;
        }

        /* 1. TEXT STYLING */
        .hero-text {
            flex: 0 0 45%;
            max-width: 500px;
        }

        .hero-text h1 {
            font-size: 4.5rem;
            line-height: 0.95;
            margin-bottom: 25px;
            color: #000;
            font-weight: 800;
        }

        .hero-text h1 span {
            color: #3FA9F5;
            /* Haarlem Blue */
            display: block;
        }

        .hero-text p {
            font-size: 1.1rem;
            color: #333;
            max-width: 400px;
            font-weight: 500;
        }

        /* 2. IMAGE COLLAGE STYLING */
        .hero-visuals {
            flex: 0 0 50%;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 60px;
        }

        .hero-bg-shape {
            position: absolute;
            width: 105%;
            height: 75%;
            background-color: #3FA9F5;
            border-radius: 40px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        /* GRID SYSTEM */
        .image-grid {
            display: grid;
            grid-template-columns: 1fr 1.6fr 1fr;
            gap: 15px;
            position: relative;
            z-index: 1;
            width: 100%;
            align-items: center;
        }

        /* WRAPPER FOR IMG + CAPTION */
        .img-wrapper {
            display: flex;
            flex-direction: column;
            width: fit-content;
        }

        /* CAPTION STYLING - FIXED SIZE */
        .img-caption {
            font-style: italic;
            font-size: 0.75rem;
            /* Smaller text */
            color: #555;
            margin-top: 6px;
            font-weight: 500;
            line-height: 1.2;
            white-space: nowrap;
            /* Keeps short captions on one line */
        }

        /* SHARED IMAGE STYLES (Radius & Shadow fix) */
        .img-vertical,
        .img-wide,
        .img-main {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            object-fit: cover;
            display: block;
        }

        /* Specific Sizes */
        .img-main {
            height: 420px;
            width: 100%;
            border-radius: 20px;
            /* Slightly rounder for main image */
        }

        .img-vertical {
            height: 180px;
            width: 100%;
        }

        .img-wide {
            height: 200px;
            width: 100%;
        }

        /* COLUMN ALIGNMENT */
        .col-left {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: flex-end;
            /* Pushes wrappers to right edge */
        }

        /* Left Column Text Align */
        .col-left .img-wrapper {
            align-items: flex-end;
            text-align: right;
        }

        .col-right {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
            /* Pushes wrappers to left edge */
        }

        /* Right Column Text Align */
        .col-right .img-wrapper,
        .img-main+.img-caption {
            align-items: flex-start;
            text-align: left;
        }

        /* Center caption padding */
        .img-main+.img-caption {
            padding-left: 5px;
        }


        /* --- REST OF THE PAGE STYLES (UNCHANGED) --- */

        /* INTRO & STATS */
        .intro {
            padding: 80px 0;
            display: flex;
            gap: 60px;
            align-items: center;
        }

        .intro-text {
            flex: 1.2;
        }

        .intro-text h2 {
            font-size: 2.5rem;
            margin-bottom: 25px;
            font-weight: 800;
        }

        .intro-text p {
            margin-bottom: 20px;
            color: #444;
            font-size: 1.05rem;
            line-height: 1.8;
            white-space: pre-line;
        }

        .stats {
            flex: 0.8;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .stat-item {
            display: flex;
            align-items: baseline;
            gap: 15px;
        }

        .stat-number {
            font-size: 4.5rem;
            font-weight: 900;
            color: #000;
            line-height: 1;
        }

        .stat-label {
            font-size: 2.2rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
            line-height: 1;
        }

        .stat-label::after {
            content: '';
            position: absolute;
            left: -5px;
            right: -5px;
            bottom: 12px;
            height: 6px;
            z-index: -1;
            opacity: 0.8;
        }

        .stat-item:nth-child(1) .stat-label::after {
            background-color: #F8C3D6;
        }

        .stat-item:nth-child(2) .stat-label::after {
            background-color: #3FA9F5;
        }

        .stat-item:nth-child(3) .stat-label::after {
            background-color: #E9C46A;
        }

        .stat-item:nth-child(4) .stat-label::after {
            background-color: #E63946;
        }

        /* HIGHLIGHTED EVENTS */
        .events-section {
            padding: 60px 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 35px;
            color: #000;
        }

        .events-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 30px;
        }

        .event-card {
            display: flex;
            flex-direction: column;
        }

        .event-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .event-card:hover .event-img {
            transform: translateY(-5px);
        }

        .event-details {
            padding: 0 5px;
        }

        .event-title {
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 5px;
            color: #000;
        }

        .event-meta {
            color: #888;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .event-card:nth-child(1) {
            grid-area: 1 / 1 / 2 / 2;
        }

        .event-card:nth-child(2) {
            grid-area: 1 / 2 / 2 / 3;
        }

        .event-card:nth-child(3) {
            grid-area: 2 / 1 / 3 / 2;
        }

        .event-card:nth-child(4) {
            grid-area: 2 / 2 / 3 / 3;
        }

        .event-card:nth-child(6) {
            grid-area: 1 / 3 / 3 / 4;
            height: 100%;
        }

        .event-card:nth-child(6) .event-img {
            height: 100%;
            min-height: 520px;
        }

        .event-card:nth-child(5) {
            display: none;
        }

        /* CATEGORIES */
        .categories-section {
            padding: 60px 0;
        }

        .categories-flex {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .category-svg {
            flex: 1;
            min-width: 220px;
            height: 260px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .category-svg:hover {
            transform: scale(1.05);
        }

        /* NEWSLETTER */
        .newsletter {
            background: #fff;
            padding: 80px 0;
            border-top: 1px solid #eee;
        }

        .newsletter-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 60px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .newsletter-logo {
            flex: 0 0 250px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .newsletter-logo img {
            width: 150%;
            max-width: 250px;
            margin-top: 100px;
        }

        .newsletter-content {
            flex: 1;
            text-align: left;
        }

        .newsletter-content h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .newsletter-content p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 25px;
            max-width: 500px;
            line-height: 1.6;
        }

        .preferences-label {
            font-weight: 700;
            margin-bottom: 12px;
            display: block;
            color: #000;
        }

        .preferences-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 15px;
        }

        .pref-chip {
            background: #F0F5FA;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .pref-chip:hover {
            background: #e2e8f0;
        }

        .select-all {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #333;
            cursor: pointer;
        }

        .subscribe-form {
            display: flex;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        .subscribe-form input {
            flex: 1;
            padding: 18px 25px;
            border: 1px solid #eee;
            border-radius: 8px 0 0 8px;
            font-size: 1rem;
            outline: none;
        }

        .subscribe-form button {
            background: #000;
            color: white;
            padding: 0 40px;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            font-weight: 800;
            font-size: 1rem;
        }

        .privacy-text {
            font-size: 0.85rem;
            color: #888;
            margin-top: 15px;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container">

        <header class="hero">
            <div class="hero-text">
                <h1>
                    Discover <br>
                    <span>Haarlem</span>
                    <span>Festival</span>
                </h1>
                <p><?php echo htmlspecialchars($content['hero']['subtitle']); ?></p>
            </div>

            <div class="hero-visuals">
                <div class="hero-bg-shape"></div>

                <div class="image-grid">
                    <div class="col-left">
                        <div class="img-wrapper">
                            <img src="<?php echo htmlspecialchars($content['hero']['images'][0] ?? ''); ?>"
                                class="img-vertical" alt="Dance">
                            <p class="img-caption">Haarlemmerhout, Bevrijdingspop</p>
                        </div>

                        <div class="img-wrapper">
                            <img src="<?php echo htmlspecialchars($content['hero']['images'][4] ?? ''); ?>"
                                class="img-wide" alt="Crowd">
                            <p class="img-caption">Haarlemmerhout, Bevrijdingspop</p>
                        </div>
                    </div>

                    <div class="img-wrapper">
                        <img src="<?php echo htmlspecialchars($content['hero']['images'][3] ?? ''); ?>" class="img-main"
                            alt="Church">
                        <p class="img-caption">Grote markt, Haarlem Jazz</p>
                    </div>

                    <div class="col-right">
                        <div class="img-wrapper">
                            <img src="<?php echo htmlspecialchars($content['hero']['images'][2] ?? ''); ?>"
                                class="img-wide" alt="Fireworks">
                            <p class="img-caption">Grote markt, Haarlem Jazz</p>
                        </div>

                        <div class="img-wrapper">
                            <img src="<?php echo htmlspecialchars($content['hero']['images'][1] ?? ''); ?>"
                                class="img-vertical" alt="City">
                            <p class="img-caption">Amsterdamse Poort, Haarlem</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="intro">
            <div class="intro-text">
                <h2><?php echo htmlspecialchars($content['introduction']['title']); ?></h2>
                <p><?php echo htmlspecialchars($content['introduction']['text']); ?></p>
            </div>

            <div class="stats">
                <?php foreach ($content['introduction']['statistics'] as $stat): ?>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo htmlspecialchars($stat['value']); ?></div>
                        <div class="stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="events-section">
            <div class="section-title">Highlighted events</div>
            <div class="events-grid">
                <?php foreach ($content['highlighted_events'] as $event): ?>
                    <div class="event-card">
                        <img src="<?php echo htmlspecialchars($event['image'] ?? 'assets/img/homepage/placeholder.jpg'); ?>"
                            class="event-img" alt="<?php echo htmlspecialchars($event['title']); ?>">

                        <div class="event-details">
                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                            <div class="event-meta">
                                <?php echo htmlspecialchars($event['date']); ?>,
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="categories-section">
            <div class="section-title">All categories</div>
            <div class="categories-flex">
                <?php foreach ($content['categories'] as $cat): ?>
                    <img src="<?php echo htmlspecialchars($cat['image']); ?>"
                        alt="<?php echo htmlspecialchars($cat['name']); ?>" class="category-svg">
                <?php endforeach; ?>
            </div>
        </section>

        <section class="newsletter">
            <div class="newsletter-wrapper">

                <div class="newsletter-logo">
                    <img src="<?php echo htmlspecialchars($content['newsletter']['logo'] ?? '/assets/svg/logo.svg'); ?>"
                        alt="Haarlem Festival Logo">
                </div>

                <div class="newsletter-content">
                    <h2>Stay Updated ✉️</h2>
                    <p><?php echo htmlspecialchars($content['newsletter']['description']); ?></p>

                    <label class="preferences-label">Notify me about:</label>

                    <div class="preferences-grid">
                        <?php foreach ($content['newsletter']['preferences'] as $pref): ?>
                            <div class="pref-chip">
                                <?php
                                $icon = '🎵'; // Default
                                if (str_contains($pref, 'Dance'))
                                    $icon = '🎵';
                                if (str_contains($pref, 'Jazz'))
                                    $icon = '🎷';
                                if (str_contains($pref, 'Food') || str_contains($pref, 'Restaurant'))
                                    $icon = '🍴';
                                if (str_contains($pref, 'History'))
                                    $icon = '📖';
                                if (str_contains($pref, 'Stories'))
                                    $icon = '✒️';
                                ?>
                                <span><?php echo $icon; ?></span>
                                <?php echo htmlspecialchars($pref); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="select-all">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll" style="cursor:pointer;">Select all</label>
                    </div>

                    <form class="subscribe-form">
                        <input type="email" placeholder="Enter your email address" required>
                        <button type="submit">Subscribe</button>
                    </form>

                    <p class="privacy-text"><?php echo htmlspecialchars($content['newsletter']['privacy_text']); ?></p>
                </div>

            </div>
        </section>

    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>

</html>