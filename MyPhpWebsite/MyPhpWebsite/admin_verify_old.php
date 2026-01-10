<?php
include 'config.php';
if ($_SESSION['role'] != 'admin') redirect('index.php');

if (isset($_POST['status'])) {
    $bid = $_POST['bid']; 
    $pid = $_POST['pid']; 
    $status = $_POST['status']; 
    if ($status == 'approved') {
        $qr = "TICKET-" . $bid . "-" . uniqid();
        mysqli_query($conn, "UPDATE bookings SET status='approved', qr_code_data='$qr' WHERE id='$bid'");
        mysqli_query($conn, "UPDATE payments SET status='valid' WHERE id='$pid'");
    } else {
        mysqli_query($conn, "UPDATE bookings SET status='rejected' WHERE id='$bid'");
        mysqli_query($conn, "UPDATE payments SET status='invalid' WHERE id='$pid'");
    }
    redirect('admin_verify.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Payments</title>
</head>
<body>

<a href="admin.php">&larr; Back to Dashboard</a>
<h1>Pending Verifications</h1>

<?php
$sql = "SELECT b.id as bid, b.total_price, b.quantity, p.id as pid, p.receipt_img, c.artist, u.name 
        FROM bookings b 
        JOIN payments p ON b.id = p.booking_id 
        JOIN concerts c ON b.concert_id = c.id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.status = 'verification_pending' 
        ORDER BY b.booking_date ASC";
$res = mysqli_query($conn, $sql);

if (mysqli_num_rows($res) == 0) echo "<p>No pending approvals.</p>";

while ($row = mysqli_fetch_assoc($res)):
?>
    <div style="border: 1px solid #000; margin-bottom: 20px; padding: 15px;">
        <h3><?php echo $row['artist']; ?></h3>
        <p>User: <?php echo $row['name']; ?></p>
        <p>Quantity: <?php echo $row['quantity']; ?> | <strong>Total: RM <?php echo $row['total_price']; ?></strong></p>
        
        <p>Receipt:</p>
        <a href="uploads/<?php echo $row['receipt_img']; ?>" target="_blank">
            <img src="uploads/<?php echo $row['receipt_img']; ?>" width="200" border="1">
        </a>
        <br><br>
        
        <form method="POST">
            <input type="hidden" name="bid" value="<?php echo $row['bid']; ?>">
            <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
            <button type="submit" name="status" value="approved" style="color:green;">Approve</button>
            <button type="submit" name="status" value="rejected" style="color:red;">Reject</button>
        </form>
    </div>
<?php endwhile; ?>

</body>
</html>