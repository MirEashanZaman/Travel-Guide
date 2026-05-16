<?php

function homeCtrl($conn) {
    $user = $_SESSION['user'] ?? null;
    $posts = [];

    //Logic for Verified Users
    if ($user && isset($user['is_verified']) && $user['is_verified'] == 1) {
        $allPosts = getApprovedPosts($conn);
        
        //Only take the first 3-6 posts for the home page summary
        $posts = array_slice($allPosts, 0, 6);
    }

    require 'app/views/home/index.php';
}
?>
