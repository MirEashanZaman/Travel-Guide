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
}

if (calcPeople && calcDays) {
    calcPeople.addEventListener('input', updateCost);
    calcDays.addEventListener('input', updateCost);
}

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


