<?php
include 'config.php';
if ($_SESSION['role'] != 'admin') redirect('index.php');
if (isset($_POST['status'])) {
    $bid = $_POST['bid']; $pid = $_POST['pid']; $status = $_POST['status']; 
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify | TixLokal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container-lg py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="index.php" class="navbar-brand">TixLokal <span class="fs-6 fw-normal text-warning">VERIFY</span></a>
        <a href="admin.php" class="btn btn-outline-light fw-bold text-uppercase" style="font-size: 0.85rem;">Back to Dashboard</a>
    </div>
    <div class="row g-4">
        <?php
        $sql = "SELECT b.id as bid, p.id as pid, p.receipt_img, c.artist, c.price, u.name FROM bookings b JOIN payments p ON b.id = p.booking_id JOIN concerts c ON b.concert_id = c.id JOIN users u ON b.user_id = u.id WHERE b.status = 'verification_pending' ORDER BY b.booking_date ASC";
        $res = mysqli_query($conn, $sql);
        if (mysqli_num_rows($res) == 0) echo '<div class="col-12"><div class="alert alert-secondary bg-opacity-10 border-0 text-center text-white">Queue is empty.</div></div>';
        while ($row = mysqli_fetch_assoc($res)):
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div><h5 class="fw-bold mb-0 text-white"><?php echo $row['artist']; ?></h5><small class="text-light-muted d-block"><?php echo $row['name']; ?></small></div>
                    <span class="text-warning fw-bold">RM <?php echo number_format($row['price'], 2); ?></span>
                </div>
                <a href="uploads/<?php echo $row['receipt_img']; ?>" target="_blank" class="d-block mb-3 text-decoration-none"><img src="uploads/<?php echo $row['receipt_img']; ?>" class="receipt-preview"><small class="text-center d-block mt-2 text-white fw-bold" style="font-size: 0.75rem;">CLICK TO ZOOM RECEIPT</small></a>
                <form method="POST" class="d-flex gap-2 mt-auto">
                    <input type="hidden" name="bid" value="<?php echo $row['bid']; ?>">
                    <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                    <button type="submit" name="status" value="approved" class="btn btn-success flex-fill fw-bold" style="font-size: 0.85rem;">APPROVE</button>
                    <button type="submit" name="status" value="rejected" class="btn btn-danger flex-fill fw-bold" style="font-size: 0.85rem;">REJECT</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>