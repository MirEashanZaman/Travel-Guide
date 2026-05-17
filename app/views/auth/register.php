<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">

</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127760;</div>
        <h1>Join Us</h1>
        <p>Create an account to start exploring the world and contributing your own travel findings</p>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="muted">Join the community of explorers</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=registration" class="form">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" 
                           placeholder="Enter your name" required>
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" 
                           placeholder="example@email.com" required>
                </div>
                <div class="field">
                    <label for="role">I want to be a...</label>
                    <select name="role" id="role">
                        <option value="user" <?= ($old['role'] == 'user') ? 'selected' : '' ?>>General User</option>
                        <option value="scout" <?= ($old['role'] == 'scout') ? 'selected' : '' ?>>Travel Scout</option>
                        <option value="admin" <?= ($old['role'] == 'admin') ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Type your password again" required>
                    </div>
                </div>
                <div class="field">
                    <button type="submit" class="btn btn-primary">Register Account</button>
                </div>
            </form>

            <p class="auth-foot">Already have an account? <a href="index.php?page=login">Sign in</a></p>
        </div>
    </div>
</div>

<script>
document.querySelector('.form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const email = document.getElementById('email').value;
    
    if (password.length < 8) {
        alert('Password must be at least 8 characters long.');
        e.preventDefault();
        return;
    }
    
    if (password !== confirm) {
        alert('Passwords do not match.');
        e.preventDefault();
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        e.preventDefault();
        return;
    }
});
</script>
</body>
</html>
