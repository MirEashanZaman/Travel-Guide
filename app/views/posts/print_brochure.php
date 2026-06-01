<?php
$title = htmlspecialchars($post['title']);
$country = htmlspecialchars($post['country']);
$genre = htmlspecialchars($post['genre']);
$costLevel = htmlspecialchars($post['cost_level']);
$history = htmlspecialchars($post['short_history']);
$medium = htmlspecialchars($post['travel_medium_info']);
$imgPath = $post['image_path'] ?? $post['image'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Brochure - <?= $title ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print_brochure.css">
</head>
<body>

<div class="brochure-container">
    <div class="cover-page">
        <?php if (!empty($imgPath)): ?>
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= $title ?>" class="cover-image">
        <?php endif; ?>
        <h1 class="cover-title"><?= $title ?></h1>
        <div class="cover-subtitle">Ready-to-Print Travel Brochure</div>
        
        <div class="cover-meta">
            <div class="meta-box">
                <label>Country Destination</label>
                <span><?= $country ?></span>
            </div>
            <div class="meta-box">
                <label>Travel Genre</label>
                <span><?= ucfirst($genre) ?></span>
            </div>
            <div class="meta-box">
                <label>Budget Level</label>
                <span><?= ucfirst($costLevel) ?></span>
            </div>
            <div class="meta-box">
                <label>Travel Medium</label>
                <span><?= $medium ?></span>
            </div>
        </div>

        <div class="cover-intro">
            <strong>About this destination:</strong><br>
            <?= nl2br($history) ?>
        </div>
    </div>

    <h2 class="section-title first">Complete Travel Itinerary Plan</h2>
    
    <?php 
    $grouped = [];
    foreach ($itinerary as $item) {
        $grouped[$item['day_number']][] = $item;
    }
    ksort($grouped);

    $dayThemes = [
        1 => 'Historical Wonders & Arrival',
        2 => 'Scenic Exploration & Local Life',
        3 => 'Hidden Gems & Skyline Views',
        4 => 'Adventure Trails & Nature Escapes',
        5 => 'Culinary Discoveries & Local Markets',
        6 => 'Art, Heritage & Museums Tour',
        7 => 'Coastal Vibes & Water Sports',
        8 => 'Traditional Crafts & Workshops',
        9 => 'Leisure, Spa & Botanical Gardens',
        10 => 'Historic Neighborhoods Walking Tour',
        11 => 'Mountain Vantage Points & Cable Cars',
        12 => 'Ancient Castles & Fortresses',
        13 => 'Eco-Tourism & Wilderness Wonders',
        14 => 'Local Festivals & Music Nightlife',
        15 => 'Souvenir Shopping & Departure'
    ];

    foreach ($grouped as $day => $items): 
        $theme = $dayThemes[$day] ?? 'Cultural Discoveries';
    ?>
        <div class="day-header">Day <?= $day ?>: <?= $theme ?></div>
        <div class="print-timeline-container">
            <?php foreach ($items as $item): 
                $time = strtolower($item['time_of_day']);
            ?>
                <div class="print-timeline-item">
                    <div class="print-item-header">
                        <h4 class="print-item-title"><?= htmlspecialchars($item['activity_title']) ?></h4>
                        <span class="print-time-tag <?= $time ?>"><?= $item['time_of_day'] ?></span>
                    </div>
                    <p class="print-item-desc"><?= nl2br(htmlspecialchars($item['activity_description'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <h2 class="section-title">Local Phrasebook & Translation Guide</h2>
    <div class="print-phrase-grid">
        <?php foreach ($phrases as $phrase): ?>
            <div class="print-phrase-card">
                <div class="print-phrase-orig"><?= htmlspecialchars($phrase['original_phrase']) ?></div>
                <div class="print-phrase-trans">"<?= htmlspecialchars($phrase['translation']) ?>"</div>
                <div class="print-phrase-phon">Pronounced: <?= htmlspecialchars($phrase['phonetic']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
window.onload = function() {
    window.print();
    setTimeout(function() {
        window.close();
    }, 500);
};
</script>

</body>
</html>
