<?php
//Register a new user
function addUser($conn, $name, $email, $password, $role) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, role, is_verified) VALUES (?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hash, $role);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Authenticate user by email and password
function authUser($conn, $email, $password) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, password_hash, role, is_verified, profile_picture FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($row && password_verify($password, $row['password_hash'])) {
        return $row;
    }
    return false;
}

//Check if email already exists (to prevent duplicates during registration)
function emailExists($conn, $email, $excludeId = null) {
    if ($excludeId) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

//Fetch user profile by ID
function getUserById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, is_verified, profile_picture FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

//Fetch a user's password hash by ID
function getPasswordHashById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ? $row['password_hash'] : null;
}


//Update user profile information
function updateUserInfo($conn, $id, $name, $email) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $name, $email, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


//Update user password
function updatePassword($conn, $id, $newPassword) {
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Upload/Update profile picture path
function updateProfilePic($conn, $id, $picPath) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET profile_picture = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $picPath, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Update remember token
function updateRememberToken($conn, $id, $token) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $token, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Get user by remember token
function getUserByRememberToken($conn, $token) {
    $hashed_token = hash('sha256', $token);
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, is_verified, profile_picture FROM users WHERE remember_token = ?");
    mysqli_stmt_bind_param($stmt, 's', $hashed_token);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
?>
