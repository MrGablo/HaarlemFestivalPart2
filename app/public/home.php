<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['title'] ?? 'Home'); ?></title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .hero { background: #f4f4f4; padding: 40px; text-align: center; border-radius: 8px; }
        .buttons button { padding: 10px 20px; margin: 5px; cursor: pointer; background: #333; color: white; border: none; }
        .content-section { margin-top: 30px; border-bottom: 1px solid #ddd; padding-bottom: 20px; }
    </style>
</head>
<body>

    <header class="hero">
        <h1><?php echo htmlspecialchars($content['title']); ?></h1>
        <p><?php echo htmlspecialchars($content['hero_text']); ?></p>
        
        <?php if (!empty($content['buttons'])): ?>
            <div class="buttons">
                <?php foreach($content['buttons'] as $btn): ?>
                    <button><?php echo htmlspecialchars($btn); ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </header>

    <main>
        <?php if (!empty($content['sections'])): ?>
            <?php foreach($content['sections'] as $section): ?>
                <div class="content-section">
                    <h2><?php echo htmlspecialchars($section['headline']); ?></h2>
                    <p><?php echo htmlspecialchars($section['text']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</body>
</html>