<?php 
$user = $_SESSION['user']; 
$imgPath = $post['image_path'] ?? $post['image'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/posts.css">
    <link rel="stylesheet" href="css/confirm_booking.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Secure Travel Checkout</h1>
        <p class="page-sub">Verify details, choose billing, and complete your reservation</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="booking-container">
        <!-- LEFT: BILLING & PAYMENT FORM -->
        <div class="card">
            <h3 class="card-title">Billing & Traveler Details</h3>
            <form id="checkoutForm" method="POST" action="index.php?page=booking&action=confirm" class="form">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="hidden" name="travelers" value="<?= $travelers ?>">
                <input type="hidden" name="days" value="<?= $days ?>">
                <input type="hidden" id="selected_method" name="payment_method" value="card">

                <div class="field">
                    <label>Billing Name</label>
                    <input type="text" name="billing_name" value="<?= htmlspecialchars($user['name']) ?>" required placeholder="Full Name">
                </div>

                <div class="field">
                    <label>Billing Email</label>
                    <input type="email" name="billing_email" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="example@email.com">
                </div>

                <div class="field">
                    <label>Travel Journey Date</label>
                    <input type="date" name="travel_date" required min="<?= date('Y-m-d') ?>">
                </div>

                <h3 class="card-title" style="margin-top: 30px;">Select Payment Method</h3>
                <div class="payment-method-selector">
                    <div class="pay-tab active" data-target="card">Card</div>
                    <div class="pay-tab" data-target="paypal">PayPal</div>
                </div>

                <div class="form-actions" style="margin-top: 30px;">
                    <a href="index.php?page=user&action=detail&id=<?= $post['id'] ?>" class="btn btn-ghost">Cancel Checkout</a>
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 15px; font-weight: 600;">Confirm & Pay $<?= number_format($totalCost, 2) ?></button>
                </div>
            </form>
        </div>

        <!-- RIGHT: RESERVATION SUMMARY -->
        <div class="card" style="align-self: start;">
            <h3 class="card-title">Booking Summary</h3>
            <?php if (!empty($imgPath)): ?>
                <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="summary-post-img">
            <?php endif; ?>
            
            <h2 style="margin: 0 0 5px 0; font-size: 20px;"><?= htmlspecialchars($post['title']) ?></h2>
            <p class="muted" style="margin: 0 0 15px 0; font-size: 13px;"> <?= htmlspecialchars($post['country']) ?> &middot; <?= ucfirst($post['genre']) ?></p>

            <ul class="pricing-list">
                <li>
                    <span>Base rate (per week):</span>
                    <strong>$<?= number_format($baseCost, 2) ?></strong>
                </li>
                <li>
                    <span>Total travelers:</span>
                    <strong><?= $travelers ?> Person(s)</strong>
                </li>
                <li>
                    <span>Booking duration:</span>
                    <strong><?= $days ?> Day(s)</strong>
                </li>
                <li class="grand-total">
                    <span>Grand Total:</span>
                    <strong>$<?= number_format($totalCost, 2) ?></strong>
                </li>
            </ul>
        </div>
    </div>
</main>

<!-- CARD GATEWAY MODAL -->
<div id="cardModal" class="modal-overlay">
    <div class="modal-box">
        <h3 class="modal-title">Credit / Debit Card Checkout</h3>
        <div class="form">
            <div class="field">
                <label>Cardholder Name</label>
                <input type="text" id="card_name" value="<?= htmlspecialchars($user['name']) ?>" class="calc-input">
            </div>
            <div class="field">
                <label>Card Number</label>
                <input type="text" id="card_num" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19" class="calc-input">
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Expiry (MM/YY)</label>
                    <input type="text" id="card_expiry" placeholder="MM/YY" maxlength="5" class="calc-input">
                </div>
                <div class="field">
                    <label>CVV</label>
                    <input type="password" id="card_cvv" placeholder="123" maxlength="3" class="calc-input">
                </div>
                <div class="field">
                    <label>Card PIN</label>
                    <input type="password" id="card_pin" placeholder="••••" maxlength="4" class="calc-input">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitSimulatedPayment()">Complete Transaction</button>
            </div>
        </div>
    </div>
</div>



<!-- PAYPAL MODAL -->
<div id="paypalModal" class="modal-overlay">
    <div class="modal-box">
        <h3 class="modal-title">PayPal Account Login</h3>
        <div class="form">
            <div class="field">
                <label>Email Address</label>
                <input type="email" id="paypal_email" value="<?= htmlspecialchars($user['email']) ?>" class="calc-input">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" id="paypal_pass" placeholder="••••••••" class="calc-input">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitSimulatedPayment()">Authorize Payment</button>
            </div>
        </div>
    </div>
</div>

<script>
    var currentMethod = 'card';
    var tabs = document.querySelectorAll('.pay-tab');
    var inputMethod = document.getElementById('selected_method');

    // Handle payment tab selections
    tabs.forEach(function(t) {
        t.addEventListener('click', function() {
            tabs.forEach(x => x.classList.remove('active'));
            t.classList.add('active');
            currentMethod = t.getAttribute('data-target');
            inputMethod.value = currentMethod;
        });
    });

    // Form submit listener
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Input validations
        var dateField = this.querySelector('input[name="travel_date"]');
        if(!dateField.value) {
            alert("Please choose a travel journey date.");
            return;
        }

        // Open specific gateway modal
        if (currentMethod === 'card') {
            document.getElementById('cardModal').style.display = 'flex';
        } else if (currentMethod === 'paypal') {
            document.getElementById('paypalModal').style.display = 'flex';
        }
    });

    function closeModals() {
        var overlays = document.querySelectorAll('.modal-overlay');
        overlays.forEach(o => o.style.display = 'none');
    }

    // Submit the real PHP form on simulated gateway success
    function submitSimulatedPayment() {
        // Validation checks
        if (currentMethod === 'card') {
            var name = document.getElementById('card_name').value.trim();
            var num = document.getElementById('card_num').value.trim();
            var expiry = document.getElementById('card_expiry').value.trim();
            var cvv = document.getElementById('card_cvv').value.trim();
            var pin = document.getElementById('card_pin').value.trim();

            if (!name || !num || !expiry || !cvv || !pin) {
                alert("Please fill in all credit card fields (Name, Number, Expiry, CVV, and PIN).");
                return;
            }
            if (num.length < 15) {
                alert("Please enter a valid credit card number.");
                return;
            }
            if (cvv.length < 3) {
                alert("Please enter a valid 3-digit CVV.");
                return;
            }
            if (pin.length < 4) {
                alert("Please enter a valid 4-digit Card PIN.");
                return;
            }
        } else if (currentMethod === 'paypal') {
            var email = document.getElementById('paypal_email').value.trim();
            var pass = document.getElementById('paypal_pass').value.trim();

            if (!email || !pass) {
                alert("Please fill in your PayPal Email and Password.");
                return;
            }
        }

        // Mock loading visual feedback
        var btn = event.target;
        btn.innerText = "Verifying Credentials...";
        btn.disabled = true;

        setTimeout(function() {
            closeModals();
            document.getElementById('checkoutForm').submit();
        }, 1500);
    }
</script>

</body>
</html>
