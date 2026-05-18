<?php 
$user = $_SESSION['user']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Comments &mdash; <?= APP_NAME ?></title>
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
                <h1 class="page-title">Comment Moderation</h1>
                <p class="page-sub">Monitor and remove inappropriate comments across all posts</p>
            </div>
            <a href="index.php?page=admin" class="btn btn-ghost">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $messages = ['comment_deleted' => 'Comment has been permanently removed.'];
            $msg = $messages[$_GET['msg']] ?? null; 
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">Global Comment Feed</h3>
            <span class="badge"><?= count($comments) ?> total comments</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Post Title</th>
                        <th>Comment Content</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                        <tr><td colspan="5" class="empty">No comments found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($comments as $i => $c): ?>
                            <tr id="comment-row-<?= $c['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($c['user_name']) ?></strong></td>
                                <td><?= htmlspecialchars($c['post_title']) ?></td>
                                <td><?= htmlspecialchars($c['content']) ?></td>
                                <td class="text-right">
                                    <button class="btn-sm btn-delete" 
                                       onclick="deleteComment(<?= $c['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>



<script>
function deleteComment(id) {
    if (!confirm('Delete this comment permanently?')) return;

    fetch('index.php?page=admin&action=comments&delete=1&id=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('comment-row-' + id).remove();
        }
    });
}
</script>
</body>
</html>


