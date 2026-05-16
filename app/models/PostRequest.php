<?php

//Create a new post request
function createPostRequest($conn, $scoutId, $postDataJson, $originalPostId = null) {
    $stmt = mysqli_prepare($conn, "INSERT INTO post_requests (scout_id, original_post_id, post_data, status) VALUES (?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, 'iis', $scoutId, $originalPostId, $postDataJson);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Fetch all requests made by a specific scout
function getRequestsByScout($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE scout_id = ? ORDER BY requested_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Update a pending request
function updatePostRequest($conn, $requestId, $postDataJson) {
    //Only allow updates if the status is still 'pending'
    $stmt = mysqli_prepare($conn, "UPDATE post_requests SET post_data = ? WHERE id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'si', $postDataJson, $requestId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Delete a request (only if it's still pending)
function deletePostRequest($conn, $requestId, $scoutId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM post_requests WHERE id = ? AND scout_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'ii', $requestId, $scoutId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Get a single request's data to pre-fill the edit form
function getPostRequestById($conn, $requestId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $requestId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
?>
