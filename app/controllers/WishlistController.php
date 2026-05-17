<?php
function wishlistCtrl($conn) {
    $userId = $_SESSION['user']['id'];
    $action = $_GET['action'] ?? 'list';

    //AJAX ACTION: Add to Wishlist
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        $postId = intval($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Post ID']);
            exit;
        }

        if (addToWishlist($conn, $userId, $postId)) {
            echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
        }
        exit;
    }

    //AJAX ACTION: Remove from Wishlist
    if ($action === 'remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        $postId = intval($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Post ID']);
            exit;
        }

        if (removeFromWishlist($conn, $userId, $postId)) {
            echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
        }
        exit;
    }

    //View Wishlist Page
    $wishlistItems = getUserWishlist($conn, $userId);
    
    require 'app/views/wishlist/index.php';
}
?>
