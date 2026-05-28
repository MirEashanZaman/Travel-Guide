<?php 
$user = $_SESSION['user']; 
$isGeneralUser = ($user['role'] === 'user' && $user['is_verified'] == 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/posts.css">
    <link rel="stylesheet" href="css/comment.css">
</head>

<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title"><?= htmlspecialchars($post['title']) ?></h1>
                <p class="page-sub">Detailed guide and traveler reviews</p>
            </div>
            <div class="header-actions">
                <?php if ($isGeneralUser): ?>
                    <button id="wishlistBtn" 
                            class="btn <?= $inWishlist ? 'btn-ghost' : 'btn-primary' ?>"
                            onclick="toggleWishlist(<?= $post['id'] ?>)">
                        <?= $inWishlist ? '&#128148; Remove Wishlist' : '&#10084; Add to Wishlist' ?>
                    </button>
                <?php endif; ?>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'scout' && $_SESSION['user']['id'] == $post['scout_id']): ?>
                    <a href="index.php?page=scout&action=request_change&id=<?= $post['id'] ?>" class="btn btn-primary">✏️ Request Edit</a>
                <?php endif; ?>
                <a href="index.php?page=user" class="btn btn-ghost">&larr; Back to Explore</a>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $messages = ['added' => 'Your comment was posted successfully!', 'deleted' => 'Comment removed.'];
            $msg = $messages[$_GET['msg']] ?? null; 
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <?php 
        $imgPath = $post['image_path'] ?? $post['image'] ?? null;
        if (!empty($imgPath)): 
    ?>
        <div class="card detail-hero-card">
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($post['title']) ?>" 
                 class="detail-hero-img">
        </div>
    <?php endif; ?>

    <div class="card">
        <h3 class="card-title">&#128204; Destination Information</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <label>Country</label>
                <div class="val"><?= htmlspecialchars($post['country']) ?></div>
            </div>
            <div class="detail-item">
                <label>Genre</label>
                <div class="val"><?= htmlspecialchars($post['genre']) ?></div>
            </div>
            <div class="detail-item">
                <label>Cost Level</label>
                <div class="val">
                    <span class="cost-badge <?= strtolower($post['cost_level']) ?>">
                        <?= ucfirst($post['cost_level']) ?>
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <label>Travel Medium</label>
                <div class="val"><?= htmlspecialchars($post['travel_medium_info']) ?></div>
            </div>
        </div>
        <div class="detail-history">
            <label>About this place</label>
            <p><?= nl2br(htmlspecialchars($post['short_history'])) ?></p>
        </div>
    </div>

    <!-- INTERACTIVE TRAVEL ITINERARY TIMELINE -->
    <div class="card timeline-card">
        <h3 class="card-title">&#128197; Interactive Travel Itinerary</h3>
        <p class="page-sub" style="margin-top: -10px; margin-bottom: 20px; font-size: 13.5px;">Click on any day below to expand and discover your day-by-day customized travel activities!</p>
        
        <div class="timeline-container">
            <?php 
            // Group itinerary items by day number
            $groupedItinerary = [];
            foreach ($itinerary as $item) {
                $groupedItinerary[$item['day_number']][] = $item;
            }
            ksort($groupedItinerary);
            
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
            
            foreach ($groupedItinerary as $day => $items): 
                $theme = $dayThemes[$day] ?? 'Cultural Discoveries';
                $isFirst = ($day === 1);
            ?>
                <div class="timeline-day-header <?= $isFirst ? 'active-header' : '' ?>" onclick="toggleDay(<?= $day ?>)" id="day-header-<?= $day ?>">
                    <h3>📅 Day <?= $day ?>: <?= $theme ?></h3>
                    <span class="chevron">&#9662;</span>
                </div>
                <div class="timeline-content <?= $isFirst ? 'active' : '' ?>" id="day-content-<?= $day ?>">
                    <div class="timeline-items-list">
                        <?php foreach ($items as $item): 
                            $time = strtolower($item['time_of_day']);
                            $icon = '☀️';
                            if ($time === 'morning') $icon = '🌅';
                            elseif ($time === 'evening') $icon = '🌙';
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-icon <?= $time ?>"><?= $icon ?></div>
                                <div class="timeline-details">
                                    <div class="timeline-details-header">
                                        <h4><?= htmlspecialchars($item['activity_title']) ?></h4>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <span class="itinerary-time-tag <?= $time ?>"><?= $item['time_of_day'] ?></span>
                                        </div>
                                    </div>
                                    <p><?= nl2br(htmlspecialchars($item['activity_description'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title">&#128176; Probable Cost Estimate</h3>
        <div class="cost-calc-wrap">
            <div class="calc-info">
                <p>Base Cost: <strong>$<?= number_format($costInfo['base_cost'] ?? 0, 2) ?></strong> (per person/week)</p>
                <p class="muted">
                    Budget Mapping: 
                    <?php $cl = strtolower($post['cost_level']); ?>
                    <span class="cost-badge <?= $cl === 'low' ? 'low' : 'badge-inactive' ?>">LOW ($500)</span> 
                    <span class="cost-badge <?= $cl === 'medium' ? 'medium' : 'badge-inactive' ?>">MEDIUM ($1500)</span> 
                    <span class="cost-badge <?= $cl === 'high' ? 'high' : 'badge-inactive' ?>">HIGH ($3000)</span>
                </p>
            </div>
            <?php if ($isGeneralUser): ?>
                <div class="calc-form">
                    <div class="field-row">
                        <div class="field">
                            <label>Travelers</label>
                            <input type="number" id="calc_people" value="1" min="1" class="calc-input">
                        </div>
                        <div class="field">
                            <label>Days</label>
                            <input type="number" id="calc_days" value="7" min="1" class="calc-input">
                        </div>
                    </div>
                    <div class="calc-result">
                        <span>Estimated Total:</span>
                        <strong id="total_cost">$<?= number_format($costInfo['base_cost'] ?? 0, 2) ?></strong>
                    </div>
                </div>
            <?php elseif (!isset($_SESSION['user'])): ?>
                <div class="calc-form muted calc-gate-notice">
                    <em>The interactive cost calculator is available only for verified General Users.</em>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card packing-assistant-card">
        <h3 class="card-title">&#129523; Dynamic Travel Packing Assistant</h3>
        <p class="page-sub" style="margin-top: -10px; margin-bottom: 20px; font-size: 13.5px;">Customized automatically based on your destination's <strong><?= ucfirst(htmlspecialchars($post['genre'])) ?></strong> genre and your selected trip duration!</p>
        
        <div class="packing-assistant-wrap" style="background: var(--paletton-6); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px; margin-bottom: 15px;">
                <span style="font-weight: 700; font-size: 14px; color: var(--text-main);">🎒 Your Recommended packing checklist:</span>
                <span class="badge" id="packing_tier_badge" style="font-size: 11px; font-weight: 700; background: var(--primary); color: white;">Weekender Pack</span>
            </div>
            
            <div id="packingListContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px;">
            </div>
            
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed var(--border-color); display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                <span class="muted" id="packing_progress_text">0 of 0 items packed</span>
                <button class="btn-sm btn-ghost" onclick="resetPackingChecklist()" style="font-size: 11px; font-weight: 600; padding: 4px 10px;">Reset List</button>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title">&#128172; Traveler Comments</h3>
        
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <p class="empty">No comments yet.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" id="comment-<?= $comment['id'] ?>">
                        <div class="comment-meta">
                            <strong><?= htmlspecialchars($comment['user_name']) ?></strong>
                            <span class="date"><?= date('M d, Y', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <div class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                        
                        <?php if ($comment['user_id'] == $user['id']): ?>
                            <div class="comment-actions">
                                <button class="btn-sm btn-delete" 
                                   onclick="deleteComment(<?= $comment['id'] ?>, <?= $post['id'] ?>)">Delete</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($isGeneralUser): ?>
            <div class="comment-form-wrap">
                <form id="commentForm" method="POST" action="index.php?page=user&action=add_comment" class="form">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <div class="field">
                        <label>Comment as</label>
                        <input type="text" value="<?= htmlspecialchars($user['name']) ?>" disabled class="calc-input calc-disabled-input">

                    </div>
                    <div class="field">
                        <label for="content">Your Comment</label>
                        <textarea id="commentContent" name="content" placeholder="Write a comment..." required rows="3" maxlength="500"></textarea>
                        <span class="muted comment-info-text">Max 500 characters</span>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </div>
                </form>
            </div>
        <?php elseif (!isset($_SESSION['user'])): ?>
            <div class="comment-form-wrap muted comment-gate-notice">
                <p>Please log in as a verified <strong>General User</strong> to post comments.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleDay(dayNum) {
    var header = document.getElementById('day-header-' + dayNum);
    var content = document.getElementById('day-content-' + dayNum);
    if (!header || !content) return;
    
    var isActive = content.classList.contains('active');
    
    if (isActive) {
        content.classList.remove('active');
        header.classList.remove('active-header');
    } else {
        content.classList.add('active');
        header.classList.add('active-header');
    }
}

//Cost Calculator Logic
var baseCost = <?= floatval($costInfo['base_cost']) ?>;
var calcPeople = document.getElementById('calc_people');
var calcDays = document.getElementById('calc_days');
var totalDisplay = document.getElementById('total_cost');

function updateCost() {
    var people = Math.max(1, Math.abs(parseInt(calcPeople.value)) || 1);
    var days = Math.max(1, Math.abs(parseInt(calcDays.value)) || 7);
    
    calcPeople.value = people;
    calcDays.value = days;

    var total = (baseCost * people * days) / 7;
    totalDisplay.innerHTML = '$' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    if (typeof generatePackingChecklist === 'function') {
        generatePackingChecklist();
    }
}

if (calcPeople && calcDays) {
    calcPeople.addEventListener('input', updateCost);
    calcDays.addEventListener('input', updateCost);
}

var genre = <?= json_encode(strtolower($post['genre'])) ?>;

var genreItems = {
    'beach': [
        { name: '👙 Swimwear & Beach clothes', desc: 'Light and quick-dry apparel' },
        { name: '🧴 Sunscreen (SPF 50+)', desc: 'Water-resistant protection' },
        { name: '🕶️ Polarized Sunglasses', desc: 'Protects eyes from sea glare' },
        { name: '🩴 Waterproof Sandals/Sand shoes', desc: 'For walking on hot sand' },
        { name: '👒 Wide-brimmed Sun Hat', desc: 'Keeps face shaded' },
        { name: '🏖️ Compact Microfiber Beach Towel', desc: 'Highly absorbent, fast-drying' }
    ],
    'mountain': [
        { name: '🧥 Heavy Windproof/Warm Jacket', desc: 'Temps drop fast at higher altitudes' },
        { name: '🥾 Sturdy Waterproof Hiking Boots', desc: 'Ankle support for rugged trails' },
        { name: '🦯 Telescopic Trekking Poles', desc: 'Reduces impact on knees and back' },
        { name: '🧴 Insect Repellent (DEET 30%+)', desc: 'Guards against ticks & mosquitos' },
        { name: '🔦 Reliable headlamp / Flashlight', desc: 'For early starts or delayed returns' },
        { name: '💧 Insulated Thermal Water Flask', desc: 'Keeps beverages warm or cold' }
    ],
    'city': [
        { name: '👟 Comfortable Walking Sneakers', desc: 'Essential for exploring city streets' },
        { name: '🔋 High-capacity Power Bank', desc: 'Keeps phone charged for maps & photos' },
        { name: '🌂 Compact Pocket Umbrella', desc: 'Prepares for sudden metropolitan showers' },
        { name: '🎒 Lightweight Theft-proof Daypack', desc: 'Securely holds daily essentials' },
        { name: '📸 Camera / Lens wipes', desc: 'For capturing monuments clearly' },
        { name: '💳 Cardholders & Cash stash', desc: 'Quick contactless metro & shop payments' }
    ],
    'historical': [
        { name: '🧣 Cultural Shawl / Light Scarf', desc: 'For covering shoulders/knees at holy sites' },
        { name: '🥿 Comfortable Slip-on Shoes', desc: 'Easy off/on at temples and shrines' },
        { name: '📓 Small Travel Journal & Pen', desc: 'For sketching or noting guide stories' },
        { name: '🧴 Sun Hat & Sunscreen protection', desc: 'Many ruins have little to no shade' },
        { name: '🕶️ Anti-reflective Sunglasses', desc: 'For reading signs in bright sun' },
        { name: '🗺️ Historical Map / Audio guide app', desc: 'Enriches walking tours around ruins' }
    ],
    'nature': [
        { name: '🔭 Compact Travel Binoculars', desc: 'For viewing birds and wildlife closely' },
        { name: '🧴 Insect Repellent & Mosquito Spray', desc: 'Must-have for deep woods and reserves' },
        { name: '🥾 Sturdy Trail Boots', desc: 'Traction on muddy forest paths' },
        { name: '🧥 Waterproof Rain Poncho', desc: 'Folds tiny, protects from storms' },
        { name: '🩹 Outdoor First-Aid kit', desc: 'Bandages, antiseptic wipes, blister pads' },
        { name: '🔦 Flashlight & Extra batteries', desc: 'Safe navigating through forest trails' }
    ]
};

var baseUniversalItems = [
    { name: '🔌 Universal Power Adapter', desc: 'Fits local plug configurations' },
    { name: '👝 Passport Wallet & Travel docs', desc: 'Keeps identity papers safe' },
    { name: '🧼 Travel-sized Toiletries Kit', desc: 'Shampoo, toothpaste, toothbrushes' },
    { name: '💊 Personal Medical kit', desc: 'Painkillers, allergy pills, prescriptions' }
];

function generatePackingChecklist() {
    var calcDaysInput = document.getElementById('calc_days');
    var days = calcDaysInput ? Math.max(1, parseInt(calcDaysInput.value) || 7) : 7;
    
    var container = document.getElementById('packingListContainer');
    var tierBadge = document.getElementById('packing_tier_badge');
    if (!container) return;
    
    var quantityText = '';
    var multiplier = 1;
    var luggageTier = 'Weekender Pack';
    
    if (days <= 3) {
        luggageTier = 'Weekender Pack';
        multiplier = 3;
        quantityText = '3x pairs of ';
    } else if (days <= 7) {
        luggageTier = 'Standard Explorer Pack';
        multiplier = 7;
        quantityText = '7x pairs of ';
    } else {
        luggageTier = 'Grand Voyager Pack';
        multiplier = 10;
        quantityText = '10x pairs of ';
    }
    
    if (tierBadge) {
        tierBadge.textContent = luggageTier;
    }
    
    var list = [];
    
    list.push({
        name: '🧦 ' + quantityText + 'Socks & Underwear',
        desc: 'Sized perfectly for your ' + days + '-day journey'
    });
    
    var genreSpecific = genreItems[genre] || genreItems['city'];
    genreSpecific.forEach(function(item) {
        list.push(item);
    });
    
    baseUniversalItems.forEach(function(item) {
        list.push(item);
    });
    
    container.innerHTML = '';
    
    var storageKey = 'packed_list_post_' + <?= intval($post['id']) ?>;
    var packedStates = {};
    try {
        packedStates = JSON.parse(localStorage.getItem(storageKey)) || {};
    } catch(e) {
        packedStates = {};
    }
    
    list.forEach(function(item, index) {
        var isChecked = packedStates[item.name] ? 'checked' : '';
        
        var html = 
            '<div class="packing-item" style="background: var(--white); border: 1px solid var(--border-color); border-radius: 6px; padding: 12px 15px; display: flex; align-items: flex-start; gap: 10px; transition: transform 0.2s, opacity 0.2s; ' + (isChecked ? 'opacity: 0.75;' : '') + '">' +
                '<input type="checkbox" class="packing-checkbox" onchange="togglePackingItem(this, \'' + item.name.replace(/'/g, "\\'") + '\')" ' + isChecked + ' style="margin-top: 3px; cursor: pointer; accent-color: var(--primary);">' +
                '<div style="flex: 1;">' +
                    '<label style="display: block; font-size: 13.5px; font-weight: 700; color: var(--text-main); margin: 0; text-decoration: ' + (isChecked ? 'line-through' : 'none') + ';">' + item.name + '</label>' +
                    '<small style="display: block; font-size: 11px; color: var(--text-muted); margin-top: 2px;">' + item.desc + '</small>' +
                '</div>' +
            '</div>';
            
        container.insertAdjacentHTML('beforeend', html);
    });
    
    updatePackingProgress();
}

function togglePackingItem(checkbox, name) {
    var parent = checkbox.closest('.packing-item');
    var label = parent ? parent.querySelector('label') : null;
    
    if (checkbox.checked) {
        if (parent) parent.style.opacity = '0.75';
        if (label) label.style.textDecoration = 'line-through';
    } else {
        if (parent) parent.style.opacity = '1';
        if (label) label.style.textDecoration = 'none';
    }
    
    var storageKey = 'packed_list_post_' + <?= intval($post['id']) ?>;
    var packedStates = {};
    try {
        packedStates = JSON.parse(localStorage.getItem(storageKey)) || {};
    } catch(e) {
        packedStates = {};
    }
    
    packedStates[name] = checkbox.checked;
    localStorage.setItem(storageKey, JSON.stringify(packedStates));
    
    updatePackingProgress();
}

function updatePackingProgress() {
    var container = document.getElementById('packingListContainer');
    if (!container) return;
    
    var checkboxes = container.querySelectorAll('.packing-checkbox');
    var total = checkboxes.length;
    var checked = 0;
    checkboxes.forEach(function(cb) {
        if (cb.checked) checked++;
    });
    
    var progressText = document.getElementById('packing_progress_text');
    if (progressText) {
        progressText.innerHTML = '<strong>' + checked + '</strong> of <strong>' + total + '</strong> items packed';
    }
}

function resetPackingChecklist() {
    if (!confirm('Reset your packing checklist?')) return;
    
    var storageKey = 'packed_list_post_' + <?= intval($post['id']) ?>;
    localStorage.removeItem(storageKey);
    
    generatePackingChecklist();
}

document.addEventListener('DOMContentLoaded', function() {
    generatePackingChecklist();
});

function toggleWishlist(postId) {
    var btn = document.getElementById('wishlistBtn');
    if (!btn) return;
    var isAdding = btn.classList.contains('btn-primary');
    var action = isAdding ? 'add' : 'remove';
    
    var fd = new FormData();
    fd.append('post_id', postId);

    fetch('index.php?page=wishlist&action=' + action, {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (isAdding) {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-ghost');
                btn.innerHTML = '&#128148; Remove Wishlist';
            } else {
                btn.classList.remove('btn-ghost');
                btn.classList.add('btn-primary');
                btn.innerHTML = '&#10084; Add to Wishlist';
            }
        } else {
            alert(data.message);
        }
    });
}
function deleteComment(commentId, postId) {
    if (!confirm('Delete your comment?')) return;
    
    fetch('index.php?page=user&action=delete_comment&comment_id=' + commentId + '&post_id=' + postId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            var el = document.getElementById('comment-' + commentId);
            if (el) el.remove();
        }
    });
}

//AJAX Add Comment
var commentForm = document.getElementById('commentForm');
if (commentForm) {
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var fd = new FormData(this);
        var content = document.getElementById('commentContent').value.trim();
        if (!content) return;
        if (content.length > 500) {
            alert('Comment is too long (Max 500 characters).');
            return;
        }

        fetch(this.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                var list = document.querySelector('.comments-list');
                var empty = list.querySelector('.empty');
                if (empty) empty.remove();

                var c = data.comment;
                var html = `
                    <div class="comment-item" id="comment-${c.id}">
                        <div class="comment-meta">
                            <strong>${c.user_name}</strong>
                            <span class="date">${c.date}</span>
                        </div>
                        <div class="comment-text">${c.content.replace(/\n/g, '<br>')}</div>
                        <div class="comment-actions">
                            <button class="btn-sm btn-delete" onclick="deleteComment(${c.id}, ${<?= $post['id'] ?>})">Delete</button>
                        </div>
                    </div>`;
                list.insertAdjacentHTML('beforeend', html);
                document.getElementById('commentContent').value = '';
            }
        });
    });
}
</script>

</body>
</html>


