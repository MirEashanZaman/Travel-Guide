<?php
session_start();

require_once 'database.php';

define('ROOT_URL', 'http://localhost/travel-guide/');
define('APP_NAME', 'Travel Guide');

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($location) {
    header("Location: " . ROOT_URL . $location);
    exit;
}
?>