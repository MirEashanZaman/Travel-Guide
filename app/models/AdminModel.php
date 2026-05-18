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

    if ($originalPostId) {
        //Update existing post
        $sql = "UPDATE posts SET title = ?, short_history = ?, country = ?, genre = ?, cost_level = ?, travel_medium_info = ?, image_path = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssssi', 
            $data['title'], 
            $data['short_history'], 
            $data['country'], 
            $data['genre'], 
            $data['cost_level'], 
            $data['travel_medium_info'],
            $data['image'],
            $originalPostId
        );
    } else {
        //Insert into the 'posts' table
        $sql = "INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, image_path, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'isssssss', 
            $scoutId, 
            $data['title'], 
            $data['short_history'], 
            $data['country'], 
            $data['genre'], 
            $data['cost_level'], 
            $data['travel_medium_info'],
            $data['image']
        );
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        //Mark the request as approved so it doesn't show in pending list
        $stmt = mysqli_prepare($conn, "UPDATE post_requests SET status = 'approved' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $requestId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

function rejectPostRequest($conn, $requestId) {
    $stmt = mysqli_prepare($conn, "UPDATE post_requests SET status = 'rejected' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $requestId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//COMMENT
function getAllComments($conn) {
    $stmt = mysqli_prepare($conn, "SELECT comments.*, users.name as user_name, posts.title as post_title 
                                  FROM comments 
                                  JOIN users ON comments.user_id = users.id 
                                  JOIN posts ON comments.post_id = posts.id 
                                  ORDER BY comments.created_at DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function deleteCommentByAdmin($conn, $commentId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $commentId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Global Post Management
function getAllPostsAdmin($conn) {
    $r = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function deletePostAdmin($conn, $postId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updatePostAdmin($conn, $postId, $data) {
    $sql = "UPDATE posts SET title = ?, country = ?, short_history = ?, genre = ?, cost_level = ?, travel_medium_info = ?, image_path = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssssi', 
        $data['title'], 
        $data['country'], 
        $data['short_history'], 
        $data['genre'], 
        $data['cost_level'], 
        $data['travel_medium_info'], 
        $data['image_path'],
        $postId
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Get comprehensive dashboard statistics
function getDashboardStats($conn) {
    $stats = [];
    
    // User counts by role
    $r = mysqli_query($conn, "SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stats['users_by_role'] = mysqli_fetch_all($r, MYSQLI_ASSOC);
    
    // Total users
    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = mysqli_fetch_assoc($r)['count'];
    
    // Pending requests
    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM post_requests WHERE status = 'pending'");
    $stats['pending_requests'] = mysqli_fetch_assoc($r)['count'];
    
    // Total posts
    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts WHERE status = 'approved'");
    $stats['total_posts'] = mysqli_fetch_assoc($r)['count'];
    
    // Total comments
    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments");
    $stats['total_comments'] = mysqli_fetch_assoc($r)['count'];
    
    return $stats;
}
?>