<?php 
$user = $_SESSION['user']; 
$isEdit = !empty($editing);

$itinValues = [];
for ($day = 1; $day <= 15; $day++) {
    foreach (['morning', 'afternoon', 'evening'] as $time) {
        $itinValues[$day][$time] = [
            'activity_title' => '',
            'activity_description' => '',
            'estimated_cost' => ''
        ];
    }
}

$maxDay = 3;
if ($isEdit && !empty($editing['itinerary']) && is_array($editing['itinerary'])) {
    foreach ($editing['itinerary'] as $item) {
        $day = intval($item['day_number']);
        $time = $item['time_of_day'];
        if ($day >= 1 && $day <= 15 && in_array($time, ['morning', 'afternoon', 'evening'])) {
            $itinValues[$day][$time] = [
                'activity_title' => $item['activity_title'] ?? '',
                'activity_description' => $item['activity_description'] ?? '',
                'estimated_cost' => $item['estimated_cost'] ?? ''
            ];
            if ($day > $maxDay) {
                $maxDay = $day;
            }
        }
    }
}
$maxDay = min(15, max(1, $maxDay));

$phraseValues = [];
for ($i = 1; $i <= 5; $i++) {
    $phraseValues[$i] = [
        'original_phrase' => '',
        'translation' => '',
        'phonetic' => ''
    ];
}
if ($isEdit && !empty($editing['phrases']) && is_array($editing['phrases'])) {
    foreach ($editing['phrases'] as $p) {
        $no = intval($p['phrase_no']);
        if ($no >= 1 && $no <= 5) {
            $phraseValues[$no] = [
                'original_phrase' => $p['original_phrase'] ?? '',
                'translation' => $p['translation'] ?? '',
                'phonetic' => $p['phonetic'] ?? ''
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scout Dashboard &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="css/posts.css?v=2">
    <link rel="stylesheet" href="css/scout.css?v=2">
</head>

<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">Scout Dashboard</h1>
                <p class="page-sub">Submit new travel destinations and track your request status</p>
            </div>
            <a href="index.php?page=user" class="btn btn-ghost">Explore Destinations</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $messages = [
                'added' => 'Post request submitted successfully!',
                'updated' => 'Request updated successfully!',
                'deleted' => 'Request removed.',
                'error' => 'This request cannot be edited because it has already been processed.'
            ];
            $msg = $messages[$_GET['msg']] ?? null; 
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FORM SECTION -->
    <div class="card">
        <h3 class="card-title">
            <?php 
                if ($isEdit) {
                    echo !empty($editing['original_post_id']) ? 'Request Changes for Destination' : 'Edit Post Request (#' . intval($editing['id'] ?? '') . ')';
                } else {
                    echo 'Submit New Travel Place';
                }
            ?>
        </h3>
        <form method="POST" action="index.php?page=scout&action=<?= (!empty($editing['id'])) ? 'update' : 'add' ?>" class="form" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="request_id" value="<?= $editing['id'] ?? '' ?>">
                <input type="hidden" name="original_post_id" value="<?= $editing['original_post_id'] ?? '' ?>">
            <?php endif; ?>
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($editing['image_path'] ?? $editing['image'] ?? '') ?>">

            <div class="field-row">
                <div class="field">
                    <label>Destination Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Country & Cultural Significance</label>
                    <input type="text" name="country" value="<?= htmlspecialchars($editing['country'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field">
                <label>Image (Optional)</label>
                <input type="file" name="post_image" accept="image/*">
                <?php 
                    $currentImage = $editing['image_path'] ?? $editing['image'] ?? '';
                    if ($isEdit && !empty($currentImage)): 
                ?>
                    <p class="muted">Current image:</p>
                    <img src="<?= htmlspecialchars($currentImage) ?>" class="current-image-preview">
                <?php endif; ?>
            </div>

            <div class="field">
                <label>Short History & Significance</label>
                <textarea name="short_history" rows="3"><?= htmlspecialchars($editing['short_history'] ?? '') ?></textarea>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Genre</label>
                    <select name="genre">
                        <option value="beach" <?= ($editing['genre'] ?? '') == 'beach' ? 'selected' : '' ?>>Beach</option>
                        <option value="mountain" <?= ($editing['genre'] ?? '') == 'mountain' ? 'selected' : '' ?>>Mountain</option>
                        <option value="city" <?= ($editing['genre'] ?? '') == 'city' ? 'selected' : '' ?>>City</option>
                        <option value="historical" <?= ($editing['genre'] ?? '') == 'historical' ? 'selected' : '' ?>>Historical</option>
                        <option value="nature" <?= ($editing['genre'] ?? '') == 'nature' ? 'selected' : '' ?>>Nature</option>
                    </select>
                </div>
                <div class="field">
                    <label>Expected Cost (USD)</label>
                    <input type="number" name="expected_cost" min="1" step="1" value="<?= htmlspecialchars($editing['expected_cost'] ?? $editing['base_cost'] ?? 1500) ?>" required placeholder="e.g. 500">
                </div>
            </div>

            <div class="field">
                <label>Travel Medium Info</label>
                <input type="text" name="travel_medium_info" value="<?= htmlspecialchars($editing['travel_medium_info'] ?? '') ?>" required>
            </div>

            <!-- Interactive Travel Itinerary Builder (Max 15 Days) -->
            <div class="itinerary-section">
                <div class="itinerary-title-wrap" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <h3 class="itinerary-main-title">Custom Travel Itinerary <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">(Optional)</span></h3>
                        <p class="itinerary-sub-title" style="margin: 0;">Provide daily plans for Morning, Afternoon, and Evening activities (up to 15 days max).</p>
                    </div>
                    <div class="field" style="margin-bottom: 0; width: 180px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">Duration</label>
                        <select id="itinerary_days_count" onchange="updateItineraryDays()" style="height: 38px; padding: 0 10px;">
                            <?php for ($d = 1; $d <= 15; $d++): ?>
                                <option value="<?= $d ?>" <?= ($d == $maxDay) ? 'selected' : '' ?>><?= $d ?> Day<?= $d > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="itinerary-accordions">
                    <?php for ($day = 1; $day <= 15; $day++): 
                        $isOpen = ($day === 1); // Open Day 1 by default
                        $isActive = ($day <= $maxDay);
                    ?>
                        <div class="itinerary-day-wrapper day-accordion" id="day-wrapper-<?= $day ?>" style="display: <?= $isActive ? 'block' : 'none' ?>;">
                            <div class="itin-header" onclick="toggleItinDay(<?= $day ?>)" id="itin-header-<?= $day ?>">
                                <span class="itin-header-title">Day <?= $day ?> Plan</span>
                                <span class="itin-chevron" id="itin-chevron-<?= $day ?>" style="transform: <?= ($isOpen && $isActive) ? 'rotate(180deg)' : 'rotate(0deg)' ?>;">&#9662;</span>
                            </div>
                            
                            <div class="itin-body" id="itin-body-<?= $day ?>" style="max-height: <?= ($isOpen && $isActive) ? '2000px' : '0px' ?>;">
                                <div class="itin-body-content">
                                    <?php foreach (['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening'] as $time => $label): ?>
                                        <div class="time-block <?= $time ?>">
                                            <h4 class="time-block-title"><?= $label ?></h4>
                                            
                                            <div class="field-row">
                                                <div class="field" style="flex: 2;">
                                                    <label>Activity Title</label>
                                                    <input type="text" name="itinerary[<?= $day ?>][<?= $time ?>][activity_title]" 
                                                           value="<?= htmlspecialchars($itinValues[$day][$time]['activity_title']) ?>" 
                                                           placeholder="e.g. Sunrise Discovery Tour"
                                                           <?= !$isActive ? 'disabled' : '' ?>>
                                                </div>
                                                <div class="field" style="flex: 1;">
                                                    <label>Estimated Cost ($)</label>
                                                    <input type="number" name="itinerary[<?= $day ?>][<?= $time ?>][estimated_cost]" 
                                                           value="<?= htmlspecialchars($itinValues[$day][$time]['estimated_cost'] !== '' ? floatval($itinValues[$day][$time]['estimated_cost']) : '') ?>" 
                                                           min="0" step="0.01" placeholder="e.g. 15.00"
                                                           <?= !$isActive ? 'disabled' : '' ?>>
                                                </div>
                                            </div>
                                            
                                            <div class="field" style="margin-top: 10px;">
                                                <label>Description</label>
                                                <textarea name="itinerary[<?= $day ?>][<?= $time ?>][activity_description]" 
                                                          rows="2" placeholder="Describe the activity, locations to visit, local options, etc."
                                                          <?= !$isActive ? 'disabled' : '' ?>><?= htmlspecialchars($itinValues[$day][$time]['activity_description']) ?></textarea>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="phrasebook-section" style="margin-top: 30px; border-top: 1px dashed var(--border-color); padding-top: 25px;">
                <h3 class="itinerary-main-title">Local Expressions Phrasebook <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">(Optional)</span></h3>
                <p class="itinerary-sub-title" style="margin: 0 0 20px 0;">Submit up to 5 essential local expressions to help travelers interact with locals!</p>
                
                <div class="phrases-list" style="display: flex; flex-direction: column; gap: 15px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="phrase-row" style="background: var(--paletton-6); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 700; font-size: 13px; color: var(--primary); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <span>Expression #<?= $i ?></span>
                            </div>
                            <div class="field-row">
                                <div class="field">
                                    <label style="font-size: 11px;">Original Phrase</label>
                                    <input type="text" name="phrases[<?= $i ?>][original]" value="<?= htmlspecialchars($phraseValues[$i]['original_phrase']) ?>" placeholder="e.g. Bonjour" style="height: 38px;">
                                </div>
                                <div class="field">
                                    <label style="font-size: 11px;">English Translation</label>
                                    <input type="text" name="phrases[<?= $i ?>][translation]" value="<?= htmlspecialchars($phraseValues[$i]['translation']) ?>" placeholder="e.g. Hello" style="height: 38px;">
                                </div>
                                <div class="field">
                                    <label style="font-size: 11px;">Phonetic Pronunciation</label>
                                    <input type="text" name="phrases[<?= $i ?>][phonetic]" value="<?= htmlspecialchars($phraseValues[$i]['phonetic']) ?>" placeholder="e.g. bohn-zhoor" style="height: 38px;">
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=scout" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Request</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABLE SECTION -->
    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">My Submission History</h3>
            <span class="badge"><?= count($requests) ?> total requests</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Destination</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="requestBody">
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="5" class="empty">You haven't submitted any travel places yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $i => $req): ?>
                            <?php $data = $req['data']; ?>
                            <tr id="row-<?= $req['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($data['title']) ?></strong></td>
                                <td><?= htmlspecialchars($data['country']) ?></td>
                                <td>
                                    <span class="cost-badge <?= $req['status'] ?>">
                                        <?= ucfirst($req['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a class="btn-sm btn-edit" href="index.php?page=scout&action=edit&id=<?= $req['id'] ?>">Edit</a>
                                        <a class="btn-sm btn-delete" href="javascript:void(0)" 
                                           onclick="deleteRequest(<?= $req['id'] ?>)">Delete</a>
                                    <?php else: ?>
                                        <span class="muted">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- APPROVED POSTS SECTION -->
    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">My Approved Destinations</h3>
            <span class="badge"><?= count($approvedPosts) ?> published posts</span>
        </div>

        <div class="profile-grid">
            <?php if (empty($approvedPosts)): ?>
                <div class="field"><p class="muted">No approved posts yet. Once an admin approves your request, it will appear here.</p></div>
            <?php else: ?>
                <?php foreach ($approvedPosts as $post): ?>
                    <div class="card">
                        <h4 class="card-title approved-post-title"><?= htmlspecialchars($post['title']) ?></h4>
                        <p class="muted approved-post-meta"><?= htmlspecialchars($post['country']) ?> &middot; <?= ucfirst($post['genre']) ?></p>
                        <div class="approved-post-actions">
                            <a href="index.php?page=user&action=detail&id=<?= $post['id'] ?>" class="btn-sm btn-edit">View Page</a>
                            <a href="index.php?page=scout&action=request_change&id=<?= $post['id'] ?>" class="btn-sm btn-primary btn-request">Request Change</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>



<script>
function toggleItinDay(dayNum) {
    var header = document.getElementById('itin-header-' + dayNum);
    var body = document.getElementById('itin-body-' + dayNum);
    var chevron = document.getElementById('itin-chevron-' + dayNum);
    if (!header || !body) return;
    
    // Check if currently active
    var isActive = body.style.maxHeight !== '0px' && body.style.maxHeight !== '';
    
    if (isActive) {
        body.style.maxHeight = '0px';
        chevron.style.transform = 'rotate(0deg)';
        header.style.background = 'var(--paletton-7)';
    } else {
        body.style.maxHeight = '2000px';
        chevron.style.transform = 'rotate(180deg)';
        header.style.background = 'var(--paletton-6)';
    }
}

function updateItineraryDays() {
    var select = document.getElementById('itinerary_days_count');
    if (!select) return;
    var count = parseInt(select.value);
    
    for (var day = 1; day <= 15; day++) {
        var wrapper = document.getElementById('day-wrapper-' + day);
        if (!wrapper) continue;
        
        var inputs = wrapper.querySelectorAll('input, textarea');
        
        if (day <= count) {
            wrapper.style.display = 'block';
            inputs.forEach(function(input) {
                input.disabled = false;
            });
        } else {
            wrapper.style.display = 'none';
            inputs.forEach(function(input) {
                input.disabled = true;
            });
        }
    }
}

function deleteRequest(id) {
    if (!confirm('Are you sure you want to delete this request?')) return;

    fetch('index.php?page=scout&action=delete&id=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            var row = document.getElementById('row-' + id);
            if (row) {
                row.style.opacity = '0.5';
                row.style.backgroundColor = '#fee2e2';
                setTimeout(() => row.remove(), 300);
            }
        } else {
            alert(data.message || 'Failed to delete request.');
        }
    });
}

//AJAX Form Submission & Validation
document.querySelector('.form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    
    //JS Validation
    var imageInput = form.querySelector('input[type="file"]');
    if (imageInput && imageInput.files.length > 0) {
        var file = imageInput.files[0];
        var allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            alert('Invalid image type. Please use JPG, PNG, or WEBP.');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Image is too large. Max size is 2MB.');
            return;
        }
    }

    //Submit via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.href = 'index.php?page=scout'; // Reload to show new request
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Something went wrong. Please try again.');
    });
});
</script>
</body>
</html>


