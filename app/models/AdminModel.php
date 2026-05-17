<?php
//USER MANAGEMENT
function getAllUsers($conn) {
    $r = mysqli_query($conn, "SELECT id, name, email, role, is_verified FROM users ORDER BY id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function verifyUser($conn, $userId, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_verified = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $status, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteUser($conn, $userId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function adminAddUser($conn, $name, $email, $password, $role, $verified) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, role, is_verified) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $hashed, $role, $verified);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUserRole($conn, $userId, $role) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $role, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//MODERATION
function getPendingRequests($conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE status = 'pending' ORDER BY requested_at DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Approve a Request
function approvePostRequest($conn, $requestId) {
    //Get the request data
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $requestId);
    mysqli_stmt_execute($stmt);
    $req = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$req) return false;

    //Decode the JSON data
    $data = json_decode($req['post_data'], true);
    $scoutId = $req['scout_id'];
    $originalPostId = $req['original_post_id'];