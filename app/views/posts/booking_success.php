<?php 
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/posts.css">
    <link rel="stylesheet" href="css/booking_success.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="success-hero">
        <span class="success-icon">&check;</span>
        <h1>Payment Completed Successfully!</h1>
        <p>Your travel destination booking is securely confirmed and finalized.</p>
    </div>

    <!-- DIGITAL INVOICE RECEIPT -->
    <div class="receipt-card">
        <div class="receipt-header">
            <div class="receipt-logo">🌐</div>
            <h3 class="receipt-title"><?= APP_NAME ?> Receipt</h3>
            <p class="muted" style="margin: 0; font-size: 13px;">Transaction Successful</p>
        </div>

        <div class="receipt-grid">
            <div class="receipt-item">
                <label>Transaction ID</label>
                <span><?= htmlspecialchars($booking['transaction_id']) ?></span>
            </div>
            <div class="receipt-item">
                <label>Payment Method</label>
                <span><?= strtoupper(htmlspecialchars($booking['payment_method'])) ?></span>
            </div>
            <div class="receipt-item">
                <label>Billing Name</label>
                <span><?= htmlspecialchars($booking['billing_name']) ?></span>
            </div>
            <div class="receipt-item">
                <label>Billing Email</label>
                <span><?= htmlspecialchars($booking['billing_email']) ?></span>
            </div>
            <div class="receipt-item">
                <label>Destination</label>
                <span><?= htmlspecialchars($booking['post_title']) ?> (<?= htmlspecialchars($booking['post_country']) ?>)</span>
            </div>
            <div class="receipt-item">
                <label>Travel Journey Date</label>
                <span><?= date('F d, Y', strtotime($booking['travel_date'])) ?></span>
            </div>
        </div>

        <div class="receipt-totals">
            <div>
                <span>Travelers:</span>
                <strong><?= $booking['travelers'] ?> Person(s)</strong>
            </div>
            <div>
                <span>Duration:</span>
                <strong><?= $booking['days'] ?> Day(s)</strong>
            </div>
            <div class="grand-total">
                <span>Grand Total Paid:</span>
                <span>$<?= number_format($booking['total_cost'], 2) ?></span>
            </div>
        </div>
    </div>

    <div class="action-bar">
        <button class="btn btn-ghost" onclick="window.print()">Print Receipt</button>
        <button class="btn btn-ghost" onclick="window.open('index.php?page=print_brochure&id=<?= $booking['post_id'] ?>', '_blank')">Export Brochure (PDF)</button>
        <a href="index.php?page=user" class="btn btn-primary">Return to Destinations</a>
    </div>
</main>

</body>
</html>
