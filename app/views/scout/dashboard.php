<?php 
$user = $_SESSION['user']; 
$isEdit = !empty($editing);
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
        <form method="POST" action="index.php?page=scout&action=<?= $isEdit ? 'update' : 'add' ?>" class="form" enctype="multipart/form-data">
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
                    <label>Expected Cost Level</label>
                    <select name="cost_level">
                        <option value="low" <?= ($editing['cost_level'] ?? '') == 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($editing['cost_level'] ?? '') == 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= ($editing['cost_level'] ?? '') == 'high' ? 'selected' : '' ?>>High</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>Travel Medium Info</label>
                <input type="text" name="travel_medium_info" value="<?= htmlspecialchars($editing['travel_medium_info'] ?? '') ?>" required>
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


