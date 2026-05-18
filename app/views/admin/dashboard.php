<?php 
$user = $_SESSION['user']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/posts.css">
<link rel="stylesheet" href="css/admin.css">

</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Administrative Control Panel</h1>
        <p class="page-sub">Welcome back, Administrator. Overview of system health and pending tasks.</p>
    </div>

    <!-- Stats Grid (Stacked Vertically) -->
    <div class="admin-stack">
        <div class="card stat-card">
            <h3 class="card-title">User Community</h3>
            <div class="stat-value">
                <?= $stats['total_users'] ?>
            </div>
            <div class="user-role-breakdown">
                <?php 
                $roles = [];
                foreach ($stats['users_by_role'] as $roleStat) {
                    $roles[] = "<strong>" . ucfirst($roleStat['role']) . ":</strong> " . $roleStat['count'];
                }
                echo implode(' | ', $roles);
                ?>
            </div>
            <a href="index.php?page=admin&action=users" class="btn btn-primary btn-full">Manage Users</a>
        </div>

        <div class="card stat-card">
            <h3 class="card-title">Content Moderation</h3>
            <div class="stat-value warning">
                <?= $stats['pending_requests'] ?>
            </div>
            <p class="muted">Pending Approval Requests</p>
            <a href="index.php?page=admin&action=posts" class="btn btn-primary btn-full">Review Requests</a>
        </div>

        <div class="card stat-card">
            <h3 class="card-title">Engagement Stats</h3>
            <div class="engagement-row">
                <div class="stat-item">
                    <div class="stat-sub-value"><?= $stats['total_posts'] ?></div>
                    <div class="stat-label">LIVE POSTS</div>
                </div>
                <div class="stat-item">
                    <div class="stat-sub-value"><?= $stats['total_comments'] ?></div>
                    <div class="stat-label">COMMENTS</div>
                </div>
            </div>
            <a href="index.php?page=admin&action=comments" class="btn btn-primary btn-full">Moderate Feed</a>
        </div>
    </div>
</main>



</body>
</html>


