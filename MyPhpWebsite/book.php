<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');

$user_id = $_SESSION['user_id'];
$concert_id = $_GET['id'] ?? null;
$booking_id = $_GET['bid'] ?? null;
$qty = $_GET['qty'] ?? 1;
$error = '';

if (isset($_POST['confirm_booking']) && $concert_id) {
    // 1. Fetch Price and Capacity
    $c_res = mysqli_query($conn, "SELECT price, capacity FROM concerts WHERE id=$concert_id");
    $c_data = mysqli_fetch_assoc($c_res);
    
    // 2. Check Availability (Double check backend)
    $s_res = mysqli_query($conn, "SELECT SUM(quantity) as sold FROM bookings WHERE concert_id=$concert_id AND status != 'rejected'");
    $s_data = mysqli_fetch_assoc($s_res);
    $sold = $s_data['sold'] ?? 0;
    $available = $c_data['capacity'] - $sold;

    if ($qty > $available) {
        $error = "Sorry! Only $available tickets remaining.";
    } else {
        $total_price = $c_data['price'] * $qty;
        $sql = "INSERT INTO bookings (user_id, concert_id, quantity, total_price, status) VALUES ('$user_id', '$concert_id', '$qty', '$total_price', 'pending_payment')";
        if (mysqli_query($conn, $sql)) {
            $booking_id = mysqli_insert_id($conn);
            redirect("book.php?step=upload&bid=$booking_id");
        } else { $error = "Booking failed: " . mysqli_error($conn); }
    }
}

if (isset($_POST['upload']) && $booking_id) {
    $target_dir = "uploads/";
    $file_ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('receipt_', true) . '.' . $file_ext;
    if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_dir . $filename)) {
        mysqli_query($conn, "INSERT INTO payments (booking_id, receipt_img) VALUES ('$booking_id', '$filename')");
        mysqli_query($conn, "UPDATE bookings SET status='verification_pending' WHERE id='$booking_id'");
        redirect('my_tickets.php');
    } else { $error = "Upload failed."; }
}

$concert = null;
$total_display = 0;
if ($concert_id) {
    $result = mysqli_query($conn, "SELECT * FROM concerts WHERE id=$concert_id");
    $concert = mysqli_fetch_assoc($result);
    $total_display = $concert['price'] * $qty;
} elseif ($booking_id) {
     $result = mysqli_query($conn, "SELECT c.*, b.total_price, b.quantity FROM concerts c JOIN bookings b ON c.id=b.concert_id WHERE b.id=$booking_id");
     $concert = mysqli_fetch_assoc($result);
     $qty = $concert['quantity'];
     $total_display = $concert['total_price'];
}
if (!$concert) redirect('index.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Process</title>
</head>
<body>

<h1>Booking: <?php echo $concert['artist']; ?></h1>
<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<?php if (isset($_GET['step']) && $_GET['step'] == 'upload'): ?>
    <!-- STEP 2 -->
    <h3>Step 2: Payment</h3>
    <p>Please transfer <strong>RM <?php echo number_format($total_display, 2); ?></strong> to:</p>
    <ul>
        <li>Bank: Bank Islam</li>
        <li>Account: 12345-67890</li>
    </ul>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="bid" value="<?php echo $booking_id; ?>">
        <label>Upload Receipt:</label><br>
        <input type="file" name="receipt" required><br><br>
        <button type="submit" name="upload">Submit Verification</button>
    </form>
<?php else: ?>
    <!-- STEP 1 -->
    <h3>Step 1: Confirm Order</h3>
    <table border="1" cellpadding="10">
        <tr><td>Event</td><td><?php echo $concert['artist']; ?></td></tr>
        <tr><td>Date</td><td><?php echo $concert['event_date']; ?></td></tr>
        <tr><td>Quantity</td><td><?php echo $qty; ?></td></tr>
        <tr><td><strong>Total</strong></td><td><strong>RM <?php echo number_format($total_display, 2); ?></strong></td></tr>
    </table>
    <br>
    <?php if(!$error): ?>
    <form method="POST">
        <button type="submit" name="confirm_booking">Confirm & Pay</button>
        <a href="index.php">Cancel</a>
    </form>
    <?php else: ?>
        <a href="concert_details.php?id=<?php echo $concert_id; ?>">Go Back</a>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>