<?php
function postCtrl($conn) {
    $action = $_GET['action'] ?? 'browse';
    $error = '';
    $editing = null;

    //Add Comment
    if ($action === 'add_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $postId  = intval($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($content === '' || mb_strlen($content) > 500) {
            $error = 'Review must be 1-500 characters';
            $action = 'detail'; 
            $_GET['id'] = $postId;
        } elseif ($_SESSION['user']['is_verified'] != 1) {
            $error = 'Your account need to be verified to post reviews';
            $action = 'detail';
            $_GET['id'] = $postId;
        } elseif ($_SESSION['user']['role'] === 'user' && !hasUserBookedPost($conn, $_SESSION['user']['id'], $postId)) {
            $error = 'You can only review destinations you have booked.';
            $action = 'detail';
            $_GET['id'] = $postId;
        } else {
            $userId = $_SESSION['user']['id'];
            $rating = intval($_POST['rating'] ?? 5);
            if (addComment($conn, $postId, $userId, htmlspecialchars($content, ENT_QUOTES), $rating)) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    $newId = mysqli_insert_id($conn);
                    echo json_encode([
                        'success' => true, 
                        'comment' => [
                            'id' => $newId,
                            'user_name' => $_SESSION['user']['name'],
                            'content' => $content,
                            'rating' => $rating,
                            'date' => date('M d, Y')
                        ]
                    ]);
                    exit;
                }
                header("Location: index.php?page=user&action=detail&id=$postId&msg=added");
                exit;
            }
            $error = 'Failed to add comment';
            $action = 'detail';
            $_GET['id'] = $postId;
        }
    }

    //Delete Comment
    if ($action === 'delete_comment') {
        $commentId = intval($_GET['comment_id'] ?? 0);
        $postId    = intval($_GET['post_id'] ?? 0);
        $userId    = $_SESSION['user']['id'];

        if ($commentId > 0 && deleteComment($conn, $commentId, $userId)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['success' => true]);
                exit;
            }
        }
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
        
        //Fallback mapping if no estimate exists
        if (!$costInfo) {
            $mapping = ['low' => 500, 'medium' => 1500, 'high' => 3000];
            $costInfo = ['base_cost' => $mapping[strtolower($post['cost_level'])] ?? 1500];
        }

        $inWishlist = isPostInWishlist($conn, $_SESSION['user']['id'], $id);
        $hasBooked = false;
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user') {
            $hasBooked = hasUserBookedPost($conn, $_SESSION['user']['id'], $id);
        }

        //Fetch daily itinerary plan
        $itinerary = getItinerary($conn, $id);
        if (empty($itinerary)) {
            seedDefaultItinerary($conn, $id);
            $itinerary = getItinerary($conn, $id);
        }
        
        require 'app/views/posts/detail.php'; 
        return;
    }

    //Browse
    $posts = getApprovedPosts($conn);
    $countries = getAllCountries($conn);
    $wishlistIds = [];
    if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user') {
        $wishlistIds = getUserWishlistPostIds($conn, $_SESSION['user']['id']);
    }
    require 'app/views/posts/browse.php'; 
}

function printBrochureCtrl($conn) {
    $id = intval($_GET['id'] ?? 0);
    $post = getPost($conn, $id);

    if (!$post) {
        header('Location: index.php?page=user');
        exit;
    }

    $itinerary = getItinerary($conn, $id);
    if (empty($itinerary)) {
        seedDefaultItinerary($conn, $id);
        $itinerary = getItinerary($conn, $id);
    }

    $phrases = getLocalPhrases($conn, $id);
    if (empty($phrases)) {
        seedDefaultLocalPhrases($conn, $id);
        $phrases = getLocalPhrases($conn, $id);
    }

    require 'app/views/posts/print_brochure.php';
}
?>
