<?php
define('ROOT_URL', 'http://localhost/travel_guide/Travel-Guide/');
define('APP_NAME', 'Travel Guide');

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($location) {
    header("Location: " . ROOT_URL . $location);
    exit;
}

?>
