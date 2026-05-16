<?php
 //Fetch all posts that have been approved by the admin
function getApprovedPosts($conn) {
    $r = mysqli_query($conn, "SELECT * FROM posts WHERE status = 'approved' ORDER BY created_at DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

//Fetch all approved posts submitted by a specific scout
function getPostsByScout($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE scout_id = ? AND status = 'approved' ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Fetch a single post by its ID for the detail page
function getPost($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ? AND status = 'approved'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

//AJAX Search: Find approved posts by title or country
function searchPosts($conn, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn, 
        "SELECT * FROM posts 
         WHERE (title LIKE ? OR country LIKE ?) 
         AND status = 'approved' 
         ORDER BY id DESC"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Add a new comment to a post
function addComment($conn, $postId, $userId, $content) {
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iis', $postId, $userId, $content);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Fetch all comments for a post, including the user name
function getComments($conn, $postId) {
    $stmt = mysqli_prepare($conn, 
        "SELECT comments.*, users.name as user_name 
         FROM comments 
         JOIN users ON comments.user_id = users.id 
         WHERE comments.post_id = ? 
         ORDER BY comments.created_at ASC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Delete a comment (only if it belongs to the logged-in user)
function deleteComment($conn, $commentId, $userId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $commentId, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Fetch cost estimate for a specific post
function getCostEstimate($conn, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM cost_estimates WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
function filterPosts($conn, $params) {
    $sql = "SELECT * FROM posts WHERE status = 'approved'";
    $types = "";
    $args = [];

    if (!empty($params['q'])) {
        $sql .= " AND (title LIKE ? OR country LIKE ?)";
        $q = "%" . $params['q'] . "%";
        $args[] = $q; $args[] = $q;
        $types .= "ss";
    }

    if (!empty($params['country'])) {
        $sql .= " AND country = ?";
        $args[] = $params['country'];
        $types .= "s";
    }

    if (!empty($params['cost_level'])) {
        $sql .= " AND cost_level = ?";
        $args[] = $params['cost_level'];
        $types .= "s";
    }

    if (!empty($params['genres'])) {
        $genreList = $params['genres'];
        $placeholders = implode(',', array_fill(0, count($genreList), '?'));
        $sql .= " AND genre IN ($placeholders)";
        foreach ($genreList as $g) {
            $args[] = $g;
            $types .= "s";
        }
    }

    $sql .= " ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($args)) {
        mysqli_stmt_bind_param($stmt, $types, ...$args);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

function getAllCountries($conn) {
    $r = mysqli_query($conn, "SELECT DISTINCT country FROM posts WHERE status = 'approved' ORDER BY country ASC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}
?>
