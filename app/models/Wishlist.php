<?php
//Add a post to the user's wishlist
function addToWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO wishlist (user_id, post_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
//Remove a specific post from the user's wishlist
function removeFromWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE user_id = ? AND post_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Get all posts saved in a user's wishlist
function getUserWishlist($conn, $userId) {
    $stmt = mysqli_prepare($conn, 
        "SELECT posts.*, wishlist.added_at 
         FROM wishlist 
         JOIN posts ON wishlist.post_id = posts.id 
         WHERE wishlist.user_id = ? 
         ORDER BY wishlist.added_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Check if a specific post is already in the user's wishlist
function isPostInWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM wishlist WHERE user_id = ? AND post_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

//Get all post IDs saved in a user's wishlist
function getUserWishlistPostIds($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT post_id FROM wishlist WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = intval($row['post_id']);
    }
    mysqli_stmt_close($stmt);
    return $ids;
}
?>
