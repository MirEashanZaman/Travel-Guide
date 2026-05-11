<?php
function postCtrl($conn) {
    $action = $_GET['action'] ?? 'browse';
    $error = '';
    $editing = null;

    //Add Comment
    if ($action === 'add_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $postId  = intval($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($content === '') {
            $error = 'Comment cannot be empty.';
            $action = 'detail'; 
            $_GET['id'] = $postId;
        } else {
            $userId = $_SESSION['user']['id'];
            if (addComment($conn, $postId, $userId, htmlspecialchars($content, ENT_QUOTES))) {
                header("Location: index.php?page=user&action=detail&id=$postId&msg=added");
                exit;
            }
            $error = 'Failed to add comment.';
            $action = 'detail';
            $_GET['id'] = $postId;
        }
    }

    //Delete Comment
    if ($action === 'delete_comment') {
        $commentId = intval($_GET['comment_id'] ?? 0);
        $postId    = intval($_GET['post_id'] ?? 0);
        $userId    = $_SESSION['user']['id'];

        if ($commentId > 0) deleteComment($conn, $commentId, $userId);
        header("Location: index.php?page=user&action=detail&id=$postId&msg=deleted");
        exit;
    }

    //Detail View
    if ($action === 'detail') {
        $id = intval($_GET['id'] ?? 0);
        $post = getPost($conn, $id);

        if (!$post) {
            header('Location: index.php?page=user');
            exit;
        }

        $comments = getComments($conn, $id);
        $costInfo = getCostEstimate($conn, $id);
        require 'views/posts/detail.php';
        return;
    }

    //Browse (Default)
    $posts = getApprovedPosts($conn);
    require 'views/posts/browse.php';
}
?>