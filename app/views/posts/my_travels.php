<?php 
$user = $_SESSION['user']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Travels &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/posts.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">My Travel Logs</h1>
                <p class="page-sub">Review all your booked journeys, payment summaries, and download receipts</p>
            </div>
            <a href="index.php?page=user" class="btn btn-ghost">Explore More Places</a>
        </div>
    </div>

    <!-- BOOKINGS LOG CARD -->
    <div class="card">
        <div class="card-toolbar" style="margin-bottom: 25px;">
            <h3 class="card-title" style="margin: 0; border: none; padding: 0;">Reservation History</h3>
            <span class="badge" style="font-weight: 600;"><?= count($bookings) ?> completed trips</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Destination</th>
                        <th>Travel Date</th>
                        <th>Details</th>
                        <th>Total Paid</th>
                        <th>Transaction ID</th>
                        <th class="text-right" style="width: 150px;">Receipts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="empty" style="text-align: center; padding: 40px 20px;">
                                <div style="font-size: 40px; margin-bottom: 10px;"></div>
                                <p style="margin: 0 0 15px 0; font-size: 15px; color: var(--text-muted);">You haven't booked any travel destinations yet!</p>
                                <a href="index.php?page=user" class="btn btn-primary">Start Planning Your First Trip</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $i => $book): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong style="color: var(--text-main); font-size: 15px; display: block;"><?= htmlspecialchars($book['post_title']) ?></strong>
                                    <span style="font-size: 12px; color: var(--text-muted);"> <?= htmlspecialchars($book['post_country']) ?></span>
                                </td>
                                <td>
                                    <span style="font-weight: 500;"><?= date('M d, Y', strtotime($book['travel_date'])) ?></span>
                                </td>
                                <td>
                                    <span style="font-size: 13px; display: block;"><?= $book['travelers'] ?> Traveler(s)</span>
                                    <span style="font-size: 12px; color: var(--text-muted);"><?= $book['days'] ?> Day(s) duration</span>
                                </td>
                                <td>
                                    <strong style="color: var(--primary); font-size: 15px;">$<?= number_format($book['total_cost'], 2) ?></strong>
                                </td>
                                <td>
                                    <code style="background: var(--paletton-6); padding: 3px 8px; border-radius: 4px; font-size: 12px; font-family: monospace; font-weight: bold; color: var(--text-main); border: 1px solid var(--border-color);"><?= htmlspecialchars($book['transaction_id']) ?></code>
                                </td>
                                <td class="text-right">
                                    <a class="btn-sm btn-edit" style="font-weight: 600; padding: 6px 12px;" href="index.php?page=booking_success&id=<?= $book['id'] ?>">
                                        View Slip
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
