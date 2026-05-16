<?php
session_start();

require 'config/database.php';
require 'config/app.php';
require 'app/models/Post.php';
require 'app/controllers/PostController.php';
require 'app/models/User.php';
require 'app/controllers/AuthController.php';
require 'app/controllers/ProfileController.php';
require 'app/models/Wishlist.php';
require 'app/controllers/WishlistController.php';
require 'app/controllers/HomeController.php';
require 'app/models/PostRequest.php';
require 'app/controllers/ScoutController.php';
require 'app/models/AdminModel.php';
require 'app/controllers/AdminController.php';



$page = $_GET['page'] ?? 'home';

//Reinstate session from Remember Me cookie if not logged in
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $token_user = getUserByRememberToken($conn, $_COOKIE['remember_token']);
    if ($token_user) {
        $_SESSION['user'] = [
            'id'    => $token_user['id'],
            'name'  => $token_user['name'],
            'email' => $token_user['email'],
            'role'  => $token_user['role'],
            'is_verified' => $token_user['is_verified'],
            'profile_picture' => $token_user['profile_picture'] ?? ''
        ];
        $_SESSION['user_id'] = $token_user['id'];
        $_SESSION['name']    = $token_user['name'];
        $_SESSION['role']    = $token_user['role'];
    }
}

//Logout
if ($page === 'logout') {
    if (isset($_SESSION['user'])) {
        updateRememberToken($conn, $_SESSION['user']['id'], NULL);
    }
    $_SESSION = [];
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}

//AJAX search endpoint
if ($page === 'ajax') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $type = $_GET['type'] ?? '';
    $q    = trim($_GET['q'] ?? '');

    if ($type === 'users' && $_SESSION['user']['role'] === 'admin') {
        echo json_encode($q === '' ? getAllUsers($conn) : searchUsers($conn, $q));
        
    } elseif ($type === 'posts') {
        echo json_encode($q === '' ? getApprovedPosts($conn) : searchPosts($conn, $q));
        
    } elseif ($type === 'filter') {
        $params = [
            'q' => $q,
            'country' => $_GET['country'] ?? '',
            'cost_level' => $_GET['cost'] ?? '',
            'genres' => !empty($_GET['genre']) ? explode(',', $_GET['genre']) : []
        ];
        echo json_encode(filterPosts($conn, $params));

    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
    }
    exit;
}

//Auth gates
$publicPages = ['login', 'registration', 'home'];

//If already logged in and verified then skip login/registration/home
if (in_array($page, ['login', 'registration', 'home']) && isset($_SESSION['user']) && $_SESSION['user']['is_verified'] == 1) {
    $redirect = $_SESSION['user']['role'];
    header('Location: index.php?page=' . $redirect);
    exit;
}

//Protected pages require login
if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

//Role gate
if ($page === 'admin' && $_SESSION['user']['role'] !== 'admin') { header('Location: index.php?page=login'); exit; }
if ($page === 'scout' && $_SESSION['user']['role'] !== 'scout') { header('Location: index.php?page=login'); exit; }
if ($page === 'user'  && !in_array($_SESSION['user']['role'], ['user', 'scout', 'admin']))  { header('Location: index.php?page=login'); exit; }
if ($page === 'wishlist' && $_SESSION['user']['role'] !== 'user') { header('Location: index.php?page=login'); exit; }

// Verification gate: Non-verified users can only access home and logout
if (isset($_SESSION['user']) && $_SESSION['user']['is_verified'] == 0 && !in_array($page, ['home', 'logout'])) {
    header('Location: index.php?page=home');
    exit;
}

//Dispatch
switch ($page) {
    case 'home':     homeCtrl($conn);     break;
    case 'login':    loginCtrl($conn);    break;
    case 'registration': registerCtrl($conn); break;
    case 'profile':  profileCtrl($conn);  break;
    case 'scout':    scoutCtrl($conn);    break;
    case 'user':     postCtrl($conn);     break;
    case 'wishlist':  wishlistCtrl($conn);  break; 
    case 'admin': adminCtrl($conn); break;
    default:
        header('Location: index.php?page=home');
        exit;
}


mysqli_close($conn);
?>
