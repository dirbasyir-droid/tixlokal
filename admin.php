<?php
include 'config.php';
if ($_SESSION['role'] != 'admin') redirect('index.php');
$error = ''; $success = '';

if (isset($_POST['add'])) {
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $date = $_POST['date'];
    $price = $_POST['price'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image_filename = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_filename = uniqid('img_', true) . '.' . $file_ext;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_filename)) $error = "Error uploading image.";
    }
    if (!$error) {
        $sql = "INSERT INTO concerts (artist, venue, event_date, price, description, image_url) VALUES ('$artist', '$venue', '$date', '$price', '$description', '$image_filename')";
        if (mysqli_query($conn, $sql)) $success = "Concert published."; else $error = "Database Error: " . mysqli_error($conn);
    }
}
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE p FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE b.concert_id = '$id'");
    mysqli_query($conn, "DELETE FROM bookings WHERE concert_id='$id'");
    mysqli_query($conn, "DELETE FROM concerts WHERE id='$id'");
    redirect('admin.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | TixLokal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container-lg py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="index.php" class="navbar-brand">TixLokal <span class="fs-6 fw-normal text-warning">ADMIN</span></a>
        <div class="d-flex gap-2"><a href="admin_verify.php" class="btn btn-warning fw-bold text-uppercase" style="font-size: 0.85rem;">Verify Payments</a><a href="index.php" class="btn btn-outline-light fw-bold text-uppercase" style="font-size: 0.85rem;">Back to Site</a></div>
    </div>
    <?php if ($error) echo "<div class='alert alert-danger bg-danger text-white border-0'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success bg-success text-white border-0'>$success</div>"; ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h5 class="fw-bold mb-4 text-white">PUBLISH NEW EVENT</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3"><label class="form-label">Artist Name</label><input type="text" name="artist" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Venue</label><input type="text" name="venue" class="form-control" required></div>
                    <div class="row g-2 mb-3">
                        <div class="col-7"><label class="form-label">Date</label><input type="datetime-local" name="date" class="form-control" required></div>
                        <div class="col-5"><label class="form-label">Price (RM)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Cover Image</label><input type="file" name="image" class="form-control" required></div>
                    <div class="mb-4"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" required></textarea></div>
                    <button type="submit" name="add" class="btn btn-primary w-100 py-2">Publish Event</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card p-4 h-100">
                <h5 class="fw-bold mb-4 text-white">LIVE EVENTS</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Artist</th><th>Date</th><th>Price</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            <?php $res = mysqli_query($conn, "SELECT id, artist, event_date, price FROM concerts ORDER BY event_date DESC"); while($row = mysqli_fetch_assoc($res)): ?>
                            <tr><td class="fw-bold text-white"><?php echo $row['artist']; ?></td><td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td><td>RM <?php echo number_format($row['price'], 2); ?></td><td class="text-end"><a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this event?');">DELETE</a></td></tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>