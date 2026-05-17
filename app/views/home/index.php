<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <?php if (!isset($_SESSION['user'])): ?>
        <!-- VIEW FOR NON-REGISTERED USERS -->
        <div class="page-header">
            <h1 class="page-title">Explore the World with Us</h1>
            <p class="page-sub">Get the best travel suggestions, cost estimates, and verified reviews.</p>
            <div class="hero-actions home-actions">
                <a href="index.php?page=registration" class="btn btn-primary">Join Now</a>
                <a href="index.php?page=login" class="btn btn-ghost">Sign In</a>
            </div>
        </div>

    <?php elseif ($_SESSION['user']['is_verified'] == 0): ?>
        <!-- VIEW FOR LOGGED-IN BUT NOT VERIFIED USERS -->
        <div class="page-header">
            <div class="card">
                <h1 class="page-title">Verification Pending</h1>
                <p class="page-sub">Your account is currently being reviewed by our administrators.</p>
                <div class="alert alert-warning">
                    <strong>Notice:</strong> Your account is pending admin approval. Please wait for access to detailed site features.
                </div>
                <a href="index.php?page=logout" class="btn btn-ghost">Logout</a>
            </div>
        </div>

    <?php else: ?>
        <!-- VIEW FOR VERIFIED USERS -->
        <div class="page-header">
            <h1 class="page-title">Welcome Back, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h1>
            <p class="page-sub">Here are some trending destinations for you.</p>
        </div>

        <div class="profile-grid">
            <?php if (empty($posts)): ?>
                <div class="card">
                    <p>No travel destinations available yet. Check back later!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card" onclick="location.href='index.php?page=user&action=detail&id=<?= $post['id'] ?>'">
                        <h3 class="card-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <p><strong>Country:</strong> <?= htmlspecialchars($post['country']) ?></p>
                        <p><strong>Genre:</strong> <?= htmlspecialchars($post['genre']) ?></p>
                        <div class="card-footer">
                            <?php 
                                $vMap = ['low' => '$500', 'medium' => '$1500', 'high' => '$3000'];
                                $v = $vMap[strtolower($post['cost_level'])] ?? '';
                            ?>
                            <span class="cost-badge <?= strtolower($post['cost_level']) ?>">
                                <?= ucfirst($post['cost_level']) ?> (<?= $v ?>)
                            </span>
                            <a href="index.php?page=user&action=detail&id=<?= $post['id'] ?>" class="btn btn-ghost">Read More</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="home-footer-actions">
            <a href="index.php?page=user" class="btn btn-primary">Browse All Destinations</a>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
