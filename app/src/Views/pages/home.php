<?php
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

        .topbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 10px 0;
        }

        .topbar-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #111;
            font-weight: 700;
            font-size: 1rem;
        }

        .topbar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            background: #f3f4f6;
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
            overflow: hidden;
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

        /* The Blue Background Shape */
        .hero-bg-shape {
            position: absolute;
            width: 105%;
            height: 75%;
            background-color: #3FA9F5;
            border-radius: 40px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            /* Centered and straight */
            z-index: 0;
        }

        /* The Grid Container */
        .image-grid {
            display: grid;
            grid-template-columns: 1fr 1.6fr 1fr;
            gap: 15px;
            position: relative;
            z-index: 1;
            width: 100%;
            align-items: center;
        }

        /* COLUMN ALIGNMENT */

        .col-left {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: flex-end;
        }

        .col-right {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        /* Common Image Styles */
        .grid-img {
            background-color: #ccc;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            object-fit: cover;
        }

        /* SIZE CLASSES  */

        .img-main {
            height: 420px;
            width: 100%;
            border-radius: 20px;
        }

        .img-vertical {
            height: 180px;
            width: 100%;
        }

        .img-wide {
            height: 130px;
            width: 140%;
            max-width: none;
        }

        /* INTRO & STATS */
        .intro {
            padding: 80px 0;
            display: flex;
            gap: 60px;
            align-items: center;
        }

        /* Left Side: Text */
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

        /* Right Side: Statistics */
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

        /* The Colored Line - Thinner & Centered */
        .stat-label::after {
            content: '';
            position: absolute;
            left: -5px;
            /* Extend slightly left */
            right: -5px;
            /* Extend slightly right */

            bottom: 12px;
            /* ⚡ MOVED UP to the middle */
            height: 6px;
            /* ⚡ THINNER line */

            z-index: -1;
            opacity: 0.8;
        }

        /* Colors */
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

        /* HIGHLIGHTED EVENTS - REVISED */
        .events-section {
            padding: 60px 0;
        }

        /* 1. Heading Fix: Make it big and bold again */
        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 35px;
            color: #000;
        }

        /* Grid Layout */
        .events-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            /* 3 Columns */
            grid-template-rows: auto auto;
            gap: 30px;
        }

        /* 2. Container Structure: No Background, No Shadow */
        .event-card {
            display: flex;
            flex-direction: column;
            /* Removed background: #fff and padding */
        }

        /* 3. Image Styling: THIS gets the "Card" look */
        .event-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            /* Shadow on image only */
            margin-bottom: 15px;
            /* Push text down */
            transition: transform 0.3s ease;
        }

        .event-card:hover .event-img {
            transform: translateY(-5px);
            /* Only the image moves up */
        }

        /* 4. Text Styling: Plain text below image */
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

        /* GRID LAYOUT*/

        /* Standard Items */
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

        /* Tall Item (Mister Anansi) */
        .event-card:nth-child(6) {
            grid-area: 1 / 3 / 3 / 4;
            height: 100%;
            /* Stretch to fill the grid height */
        }

        .event-card:nth-child(6) .event-img {
            height: 100%;
            /* Fill the entire column height */
            min-height: 520px;
            /* Force it to be tall */
        }

        /* Hide Extra Item */
        .event-card:nth-child(5) {
            display: none;
        }

        /* CATEGORIES */
        .categories-section {
            padding: 60px 0;
        }

        .categories-flex {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .category-card {
            flex: 1;
            min-width: 150px;
            height: 180px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding: 20px;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            background: linear-gradient(135deg, #FF9966, #FF5E62);
        }

        /* NEWSLETTER SECTION - REDESIGNED */
        .newsletter {
            background: #fff;
            padding: 80px 0;
            border-top: 1px solid #eee;
        }

        /* Wrapper to split Logo (Left) and Text (Right) */
        .newsletter-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 60px;
            max-width: 1100px;
            /* Restrict width to keep elements close */
            margin: 0 auto;
        }

        /* LEFT COLUMN: The Big H Logo */
        .newsletter-logo {
            flex: 0 0 250px;
            /* Fixed width for the logo */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .newsletter-logo img {
            width: 100%;
            max-width: 250px;
        }

        /* RIGHT COLUMN: The Form */
        .newsletter-content {
            flex: 1;
            text-align: left;
            /* Align everything to the left */
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

        /* Preferences Chips */
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
            /* Light Blue-Grey */
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            /* Space for icon */
            transition: background 0.2s;
        }

        .pref-chip:hover {
            background: #e2e8f0;
        }

        /* Checkbox Styling */
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

        /* Form Input & Button */
        .subscribe-form {
            display: flex;
            max-width: 100%;
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

        .subscribe-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            gap: 10px;
        }

        .subscribe-form input {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .subscribe-form button {
            background: #000;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
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
                        <img src="img/<?php echo $content['hero']['images'][0] ?? ''; ?>" class="grid-img img-vertical"
                            alt="Dance">
                        <img src="img/<?php echo $content['hero']['images'][1] ?? ''; ?>" class="grid-img img-wide"
                            alt="Crowd">
                    </div>

                    <img src="img/<?php echo $content['hero']['images'][3] ?? ''; ?>" class="grid-img img-main"
                        alt="Church">

                    <div class="col-right">
                        <img src="img/<?php echo $content['hero']['images'][2] ?? ''; ?>" class="grid-img img-wide"
                            alt="Fireworks">
                        <img src="img/<?php echo $content['hero']['images'][4] ?? ''; ?>" class="grid-img img-vertical"
                            alt="City">
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
                        <img src="img/<?php echo htmlspecialchars($event['image'] ?? 'placeholder.jpg'); ?>"
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
                <?php
                $colors = ['#E63946', '#F4A261', '#E9C46A', '#D62828', '#8D99AE'];
                $i = 0;
                ?>
                <?php foreach ($content['categories'] as $cat): ?>
                    <div class="category-card" style="background: <?php echo $colors[$i++ % count($colors)]; ?>;">
                        <span>Icon</span> <span><?php echo htmlspecialchars($cat['name']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="newsletter">
            <div class="newsletter-wrapper">

                <div class="newsletter-logo">
                    <img src="img/logo_h.png" alt="Haarlem Festival Logo">
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