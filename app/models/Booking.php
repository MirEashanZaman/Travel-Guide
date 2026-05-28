<?php

// Create a new booking entry
function createBooking($conn, $userId, $postId, $travelers, $days, $totalCost, $travelDate, $billingName, $billingEmail, $paymentMethod, $transactionId) {
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO bookings (user_id, post_id, travelers, days, total_cost, travel_date, billing_name, billing_email, payment_method, transaction_id, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid')"
    );
    mysqli_stmt_bind_param($stmt, 'iiiidsssss', $userId, $postId, $travelers, $days, $totalCost, $travelDate, $billingName, $billingEmail, $paymentMethod, $transactionId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Fetch a single booking record by ID with destination details
function getBooking($conn, $id) {
    $stmt = mysqli_prepare($conn, 
        "SELECT bookings.*, posts.title as post_title, posts.country as post_country, posts.genre as post_genre, posts.image_path as post_image 
         FROM bookings 
         JOIN posts ON bookings.post_id = posts.id 
         WHERE bookings.id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

// Fetch all bookings for a specific user
function getBookingsByUser($conn, $userId) {
    $stmt = mysqli_prepare($conn, 
        "SELECT bookings.*, posts.title as post_title, posts.country as post_country 
         FROM bookings 
         JOIN posts ON bookings.post_id = posts.id 
         WHERE bookings.user_id = ? 
         ORDER BY bookings.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

// Check if a specific user has booked a specific destination
function hasUserBookedPost($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM bookings WHERE user_id = ? AND post_id = ? AND status = 'paid' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $hasBooked = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $hasBooked;
}
?>
