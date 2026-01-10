<?php
include 'config.php';
$user_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Help & Support</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
        .nav-bar { background: #eee; padding: 10px; margin-bottom: 20px; border-bottom: 1px solid #ccc; }
        h3 { border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 30px; }
    </style>
</head>
<body>

<!-- Navigation -->
<div class="nav-bar">
    <strong>TixLokal</strong> | <a href="index.php">Home</a>
</div>

<h1>Help & Support</h1>

<h3>ALL SALES FINAL</h3>
<p>All sales are final. Event tickets are non-refundable except where required by law. Review your order before checkout.</p>

<h3>BOOKING TICKETS</h3>
<p>Tickets are sold on a first-come, first-served basis. You'll receive QR code(s) within minutes of purchase (after approval). Present valid ID matching the ticket holder name at entry. Lost or stolen tickets cannot be replaced.</p>

<h3>PAYMENT</h3>
<p>All prices are in MYR. Payment are by bank transfer and the receipt need to be uploaded. Payments are reviewed by our admins. If payment fails, your order is not confirmed.</p>

<h3>SUPPORT</h3>
<p>For order or ticket issues, reach us via the official contact links on tixlokal and include your order number and details of the issue.</p>

</body>
</html>