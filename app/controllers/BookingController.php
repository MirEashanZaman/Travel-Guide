<?php
require_once 'app/models/Booking.php';

function bookingCtrl($conn) {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?page=login');
        exit;
    }

    $action = $_GET['action'] ?? 'confirm';
    $userId = $_SESSION['user']['id'];
    $error = '';

    if ($action === 'confirm') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Payment process
            $postId        = intval($_POST['post_id'] ?? 0);
            $travelers     = max(1, intval($_POST['travelers'] ?? 1));
            $days          = max(1, intval($_POST['days'] ?? 7));
            $travelDate    = trim($_POST['travel_date'] ?? '');
            $billingName   = trim($_POST['billing_name'] ?? '');
            $billingEmail  = trim($_POST['billing_email'] ?? '');
            $paymentMethod = trim($_POST['payment_method'] ?? 'card');

            if ($postId <= 0 || empty($travelDate) || empty($billingName) || empty($billingEmail)) {
                $error = 'All fields (billing name, email, and travel date) are required.';
            } else {
                //Fetch destination details
                $post = getPost($conn, $postId);
                if (!$post) {
                    $error = 'Invalid destination selected.';
                } else {
                    $costInfo = getCostEstimate($conn, $postId);
                    if (!$costInfo) {
                        $mapping = ['low' => 500, 'medium' => 1500, 'high' => 3000];
                        $costInfo = ['base_cost' => $mapping[strtolower($post['cost_level'])] ?? 1500];
                    }

                    $baseCost = floatval($costInfo['base_cost']);
                    $totalCost = ($baseCost * $travelers * $days) / 7;
                    $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));

                    if (createBooking($conn, $userId, $postId, $travelers, $days, $totalCost, $travelDate, $billingName, $billingEmail, $paymentMethod, $transactionId)) {
                        $bookingId = mysqli_insert_id($conn);
                        header("Location: index.php?page=booking_success&id=" . $bookingId);
                        exit;
                    } else {
                        $error = 'Failed to process your booking. Please try again.';
                    }
                }
            }
        }

        //Display confirmation form (GET request or validation error)
        $postId    = intval($_GET['post_id'] ?? $_POST['post_id'] ?? 0);
        $travelers = max(1, intval($_GET['people'] ?? $_POST['travelers'] ?? 1));
        $days      = max(1, intval($_GET['days'] ?? $_POST['days'] ?? 7));

        $post = getPost($conn, $postId);
        if (!$post) {
            header('Location: index.php?page=user');
            exit;
        }

        $costInfo = getCostEstimate($conn, $postId);
        if (!$costInfo) {
            $mapping = ['low' => 500, 'medium' => 1500, 'high' => 3000];
            $costInfo = ['base_cost' => $mapping[strtolower($post['cost_level'])] ?? 1500];
        }

        $baseCost = floatval($costInfo['base_cost']);
        $totalCost = ($baseCost * $travelers * $days) / 7;

        require 'app/views/posts/confirm_booking.php';
        return;
    }
}

function bookingSuccessCtrl($conn) {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?page=login');
        exit;
    }

    $bookingId = intval($_GET['id'] ?? 0);
    $booking = getBooking($conn, $bookingId);

    if (!$booking || ($booking['user_id'] != $_SESSION['user']['id'] && $_SESSION['user']['role'] !== 'admin')) {
        header('Location: index.php?page=user');
        exit;
    }

    require 'app/views/posts/booking_success.php';
}

function myTravelsCtrl($conn) {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?page=login');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $bookings = getBookingsByUser($conn, $userId);

    require 'app/views/posts/my_travels.php';
}
?>
