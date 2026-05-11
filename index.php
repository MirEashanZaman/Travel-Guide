<?php
session_start();

require 'config/database.php';
require 'app/models/Post.php';
require 'app/models/admin_model.php';
require 'app/controllers/PostController.php';
require 'app/controllers/AdminController.php';

$page = $_GET['page'] ?? 'home';

//Logout
if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    setcookie('remember_user', '', time() - 3600, '/');
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
        
    } elseif ($type === 'posts' && $_SESSION['user']['role'] === 'user') {
        echo json_encode($q === '' ? getApprovedPosts($conn) : searchPosts($conn, $q));
        
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
    }
    exit;
}

//Auth gates
$publicPages = ['login', 'register', 'home'];

//If already logged in then skip login/register
if (in_array($page, ['login', 'register']) && isset($_SESSION['user'])) {
    header('Location: index.php?page=' . $_SESSION['user']['role']);
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
if ($page === 'user'  && $_SESSION['user']['role'] !== 'user')  { header('Location: index.php?page=login'); exit; }

//Dispatch
switch ($page) {
    case 'home':         homeCtrl($conn);         break;
    case 'login':        loginCtrl($conn);        break;
    case 'register':     registerCtrl($conn);     break;
    case 'admin':        adminCtrl($conn);        break;
    case 'scout':        scoutCtrl($conn);        break;
    case 'user':         postCtrl($conn);         break; 
    default:
        header('Location: index.php?page=home');
        exit;
}

mysqli_close($conn);
?>