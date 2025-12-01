<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');
$user_id = $_SESSION['user_id'];
$concert_id = $_GET['id'] ?? null;
$booking_id = $_GET['bid'] ?? null;
$error = '';

if (isset($_POST['confirm_booking']) && $concert_id) {
    mysqli_query($conn, "INSERT INTO bookings (user_id, concert_id, status) VALUES ('$user_id', '$concert_id', 'pending_payment')");
    $booking_id = mysqli_insert_id($conn);
    redirect("book.php?step=upload&bid=$booking_id");
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
if ($concert_id) {
    $result = mysqli_query($conn, "SELECT * FROM concerts WHERE id=$concert_id");
    $concert = mysqli_fetch_assoc($result);
} elseif ($booking_id) {
     $result = mysqli_query($conn, "SELECT c.* FROM concerts c JOIN bookings b ON c.id=b.concert_id WHERE b.id=$booking_id");
     $concert = mysqli_fetch_assoc($result);
}
if (!$concert) redirect('index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking | TixLokal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container-lg py-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
    <a href="index.php" class="navbar-brand text-white mb-4 text-center d-block">TixLokal</a>
    <div class="card p-4 shadow-lg w-100" style="max-width: 500px;">
        <?php if ($error) echo "<div class='alert alert-danger border-0 mb-4'>$error</div>"; ?>
        <?php if (isset($_GET['step']) && $_GET['step'] == 'upload'): ?>
            <div class="text-center mb-4"><h3 class="fw-bold mb-1 text-white">UPLOAD RECEIPT</h3><p class="text-light-muted small">Step 2 of 2</p></div>
            <div class="bg-black bg-opacity-25 p-3 rounded mb-4 text-center border border-secondary border-opacity-25">
                <p class="mb-1 text-light-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Transfer Amount</p>
                <h2 class="fw-bold text-white mb-3">RM <?php echo number_format($concert['price'], 2); ?></h2>
                <div class="text-start small text-light-muted">Bank: <strong class="text-white">Bank Islam</strong><br>Acc: <strong class="text-white">12345-67890</strong></div>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="bid" value="<?php echo $booking_id; ?>">
                <div class="mb-4"><label class="form-label text-light-muted small fw-bold text-uppercase">Receipt Image</label><input type="file" name="receipt" class="form-control" required></div>
                <button type="submit" name="upload" class="btn btn-primary w-100">Submit Verification</button>
            </form>
        <?php else: ?>
            <div class="text-center mb-4"><h3 class="fw-bold mb-1 text-white">CONFIRM ORDER</h3><p class="text-light-muted small">Step 1 of 2</p></div>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item d-flex justify-content-between"><span class="text-light-muted">Event</span><span class="text-white fw-bold text-end"><?php echo $concert['artist']; ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-light-muted">Date</span><span class="text-white text-end"><?php echo date('M d, Y', strtotime($concert['event_date'])); ?></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center border-0 pt-3"><span class="fs-5 fw-bold text-white">Total</span><span class="fs-4 fw-bold text-warning">RM <?php echo number_format($concert['price'], 2); ?></span></li>
            </ul>
            <form method="POST">
                <button type="submit" name="confirm_booking" class="btn btn-primary w-100">Confirm & Pay</button>
                <a href="index.php" class="btn btn-outline-light w-100 mt-2 border-0 small text-light-muted">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>