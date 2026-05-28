<?php 
$user = $_SESSION['user']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Posts &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/posts.css">
<link rel="stylesheet" href="css/admin.css">

</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">Post Moderation</h1>
                <p class="page-sub">Review, approve, or reject destination requests from Scouts</p>
            </div>
            <a href="index.php?page=admin" class="btn btn-ghost">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $messages = [
                'post_approved' => 'Request approved and published to site!',
                'post_rejected' => 'Request has been rejected.',
                'post_updated' => 'Post details updated successfully.',
                'post_deleted' => 'Post has been permanently removed.'
            ];
            $msg = $messages[$_GET['msg']] ?? null; 
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">All Published & Rejected Posts</h3>
            <span class="badge"><?= count($allPosts) ?> total entries</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allPosts)): ?>
                        <tr><td colspan="5" class="empty">No posts exist in the main directory.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allPosts as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($p['image_path'])): ?>
                                        <img src="<?= $p['image_path'] ?>" class="post-thumbnail">
                                    <?php else: ?>
                                        <span class="muted text-tiny">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                                <td><?= htmlspecialchars($p['country']) ?></td>
                                <td>
                                    <span class="cost-badge <?= $p['status'] === 'approved' ? 'low' : 'high' ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button class="btn-sm btn-edit" onclick="openEditPost(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
                                    <a class="btn-sm btn-delete" 
                                       href="index.php?page=admin&action=posts&delete_post=1&id=<?= $p['id'] ?>" 
                                       onclick="return confirm('Permanently delete this post and its comments?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Post -->
    <div id="editModal" class="edit-modal">
        <div class="card edit-modal-content">
            <h3 class="card-title">Edit Post Details</h3>
            <form method="POST" action="index.php?page=admin&action=posts" class="form">
                <input type="hidden" name="edit_post" value="1">
                <input type="hidden" name="post_id" id="edit_post_id">
                <input type="hidden" name="image_path" id="edit_image_path">
                
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="field">
                    <label>Country</label>
                    <input type="text" name="country" id="edit_country" required>
                </div>
                <div class="field">
                    <label>Short History</label>
                    <textarea name="short_history" id="edit_history" rows="3"></textarea>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label>Genre</label>
                        <select name="genre" id="edit_genre">
                            <option value="beach">Beach</option>
                            <option value="mountain">Mountain</option>
                            <option value="city">City</option>
                            <option value="historical">Historical</option>
                            <option value="nature">Nature</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Base Cost (USD)</label>
                        <input type="number" name="base_cost" id="edit_base_cost" min="1" step="1" required placeholder="e.g. 1500" oninput="updateEditCostLevel()">
                    </div>
                </div>
                <div class="field">
                    <label>Budget Tier (Derived)</label>
                    <select name="cost_level_disabled" id="edit_cost" disabled style="background-color: var(--paletton-7); cursor: not-allowed; opacity: 0.8;">
                        <option value="low">Budget (Under $1,000)</option>
                        <option value="medium">Mid-Range ($1,000 - $2,500)</option>
                        <option value="high">Luxury (Over $2,500)</option>
                    </select>
                    <input type="hidden" name="cost_level" id="edit_cost_hidden">
                </div>
                <div class="field">
                    <label>Travel Info</label>
                    <input type="text" name="travel_medium_info" id="edit_travel">
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function updateEditCostLevel() {
        var baseCostVal = parseFloat(document.getElementById('edit_base_cost').value) || 0;
        var select = document.getElementById('edit_cost');
        var hidden = document.getElementById('edit_cost_hidden');
        var tier = 'medium';
        if (baseCostVal < 1000) {
            tier = 'low';
        } else if (baseCostVal <= 2500) {
            tier = 'medium';
        } else {
            tier = 'high';
        }
        select.value = tier;
        hidden.value = tier;
    }

    function openEditPost(post) {
        document.getElementById('edit_post_id').value = post.id;
        document.getElementById('edit_title').value = post.title;
        document.getElementById('edit_country').value = post.country;
        document.getElementById('edit_history').value = post.short_history;
        document.getElementById('edit_genre').value = post.genre;
        
        var mapping = {'low': 500, 'medium': 1500, 'high': 3000};
        var costVal = post.base_cost !== null && post.base_cost !== undefined ? parseFloat(post.base_cost) : (mapping[post.cost_level.toLowerCase()] || 1500);
        document.getElementById('edit_base_cost').value = costVal;
        
        document.getElementById('edit_cost').value = post.cost_level;
        document.getElementById('edit_cost_hidden').value = post.cost_level;
        document.getElementById('edit_travel').value = post.travel_medium_info;
        document.getElementById('edit_image_path').value = post.image_path || '';
        document.getElementById('editModal').style.display = 'flex';
    }

    function moderateRequest(id, action) {
        if (action === 'reject' && !confirm('Are you sure you want to reject this post?')) return;

        fetch('index.php?page=admin&action=posts&' + action + '=1&id=' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    </script>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">Pending Post Requests</h3>
            <span class="badge"><?= count($pendingRequests) ?> awaiting review</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Destination</th>
                        <th>Country</th>
                        <th>Scout ID</th>
                        <th class="text-right">Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingRequests)): ?>
                        <tr><td colspan="5" class="empty">No pending requests to review.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pendingRequests as $i => $req): ?>
                            <?php $data = json_decode($req['post_data'], true); ?>
                            <tr id="req-row-<?= $req['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($data['image'])): ?>
                                        <a href="<?= $data['image'] ?>" target="_blank" class="badge badge-photo">View Photo</a>
                                    <?php else: ?>
                                        <span class="muted text-tiny">No Photo</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($data['title'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($data['country'] ?? 'N/A') ?></td>
                                <td>#<?= $req['scout_id'] ?></td>
                                <td class="text-right">
                                    <button class="btn-sm btn-edit btn-approve-green" 
                                       onclick="moderateRequest(<?= $req['id'] ?>, 'approve')">Approve</button>
                                    <button class="btn-sm btn-delete" 
                                       onclick="moderateRequest(<?= $req['id'] ?>, 'reject')">Reject</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>



</body>
</html>


