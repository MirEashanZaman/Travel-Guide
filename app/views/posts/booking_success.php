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
    <style>
        .success-hero {
            text-align: center;
            padding: 40px 20px;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .success-icon {
            font-size: 64px;
            color: #10b981;
            margin-bottom: 20px;
            display: inline-block;
            animation: bounceIn 0.8s cubic-bezier(0.3, 2.4, 0.6, 1);
        }
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        .success-hero h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            color: var(--text-main);
        }
        .success-hero p {
            margin: 0;
            color: var(--text-muted);
            font-size: 16px;
        }
        .receipt-card {
            background: #ffffff;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            max-width: 650px;
            margin: 0 auto 30px auto;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed var(--border-color);
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .receipt-logo {
            font-size: 32px;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 5px 0;
        }
        .receipt-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .receipt-item label {
            display: block;
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .receipt-item span {
            font-weight: 600;
            color: var(--text-main);
        }
        .receipt-totals {
            background: var(--paletton-6);
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .receipt-totals div {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 6px 0;
        }
        .receipt-totals div.grand-total {
            border-top: 1px solid var(--border-color);
            margin-top: 8px;
            padding-top: 12px;
            font-size: 20px;
            font-weight: bold;
            color: var(--primary);
        }
        .action-bar {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        /* Print Styles */
        @media print {
            .navbar, .success-icon, .success-hero, .action-bar, .alert {
                display: none !important;
            }
            body {
                background: #ffffff !important;
            }
            .receipt-card {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="success-hero">
        <span class="success-icon">✔️</span>
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
        <button class="btn btn-ghost" onclick="window.print()">🖨️ Print Receipt</button>
        <a href="index.php?page=user" class="btn btn-primary">Return to Destinations</a>
    </div>
</main>

</body>
</html>
