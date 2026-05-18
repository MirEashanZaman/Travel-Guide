<?php 
$userId = $_SESSION['user']['id']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/profile.css">

</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">My Account Profile</h1>
                <p class="page-sub">Manage your personal information and account security</p>
            </div>
            <a href="index.php?page=user" class="btn btn-ghost">&larr; Back to Explore</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $messages = [
                'updated' => 'Profile updated successfully',
                'password_changed' => 'Password has been changed successfully',
                'pic_updated' => 'Profile picture updated'
            ];
            $msg = $messages[$_GET['msg']] ?? null; 
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        
        <div class="card">
            <h3 class="card-title">Profile Summary</h3>
            <div class="profile-summary-center">
                <div class="profile-pic-wrap">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture">
                    <?php else: ?>
                        <div class="user-avatar-large">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h2><?= htmlspecialchars($user['name']) ?></h2>
                <p class="muted"><?= htmlspecialchars($user['email']) ?></p>
                
                <form method="POST" action="index.php?page=profile&action=upload_pic" enctype="multipart/form-data">
                    <label class="btn btn-ghost">
                        <span>Change photo</span>
                        <input type="file" name="profile_pic" style="display:none" onchange="this.form.submit()">
                    </label>
                </form>
                <?php if (!empty($user['profile_picture'])): ?>
                    <a href="index.php?page=profile&action=delete_pic" class="btn btn-ghost" style="margin-top: 10px; color: var(--paletton-4);" onclick="return confirm('Are you sure you want to delete your photo?');">Remove photo</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="settings-area">

            <div class="card">
                <h3 class="card-title">Account Details</h3>
                <form method="POST" action="index.php?page=profile&action=update" class="form">
                    <div class="field-row">
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="field">
                            <label>Email Address</label>
                            <input type="email" id="profile_email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            
            <div class="card">
                <h3 class="card-title">Security & Password</h3>
                <form method="POST" action="index.php?page=profile&action=change_password" class="form">
                    <div class="field">
                        <label>Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter Current Password" required>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label>New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Minimum 8 characters" required>
                        </div>
                        <div class="field">
                            <label>Confirm New Password</label>
                            <input type="password" id="confirm_new_password" name="confirm_password" placeholder="Type New Password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</main>



<script>
// Validate Account Details Form
document.querySelector('form[action*="update"]').addEventListener('submit', function(e) {
    const email = document.getElementById('profile_email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        e.preventDefault();
    }
});

// Validate Password Change Form
document.querySelector('form[action*="change_password"]').addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_new_password').value;
    
    if (newPass.length < 8) {
        alert('New password must be at least 8 characters long.');
        e.preventDefault();
        return;
    }
    
    if (newPass !== confirmPass) {
        alert('New passwords do not match.');
        e.preventDefault();
    }
});
</script>
</body>
</html>


