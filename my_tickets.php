<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets | TixLokal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container-lg py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="index.php" class="navbar-brand">TixLokal <span class="fs-6 fw-normal text-warning">TICKETS</span></a>
        <a href="index.php" class="text-decoration-none text-light-muted fw-bold small">&larr; HOME</a>
    </div>
    <div class="row row-cols-1 g-4">
        <?php
        $sql = "SELECT b.*, c.artist, c.venue, c.event_date, c.price FROM bookings b JOIN concerts c ON b.concert_id = c.id WHERE b.user_id='$user_id' ORDER BY b.booking_date DESC";
        $res = mysqli_query($conn, $sql);
        if (mysqli_num_rows($res) == 0) echo '<div class="alert alert-secondary border-0 bg-opacity-10 text-center text-white">No bookings found.</div>';
        while($row = mysqli_fetch_assoc($res)):
            $status = $row['status'];
        ?>
        <div class="col">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fw-bold mb-1 text-white"><?php echo $row['artist']; ?></h3>
                        <p class="text-light-muted mb-2 small text-uppercase fw-bold"><?php echo $row['venue']; ?> • <?php echo date('M d, Y', strtotime($row['event_date'])); ?></p>
                        <div class="mt-3"><span class="status-badge status-<?php echo $status; ?>"><?php echo str_replace('_', ' ', $status); ?></span></div>
                    </div>
                    <?php if ($status == 'approved'): ?>
                        <div class="bg-white p-2 rounded text-center" style="width: 100px;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($row['qr_code_data']); ?>" class="img-fluid">
                            <div class="small fw-bold text-dark mt-1" style="font-size: 0.6rem;">ENTRY CODE</div>
                        </div>
                    <?php elseif ($status == 'pending_payment'): ?>
                         <a href="book.php?step=upload&bid=<?php echo $row['id']; ?>" class="btn btn-upload btn-sm px-3 py-2">UPLOAD RECEIPT</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>