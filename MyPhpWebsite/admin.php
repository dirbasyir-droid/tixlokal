<?php
include 'config.php';
if ($_SESSION['role'] != 'admin') redirect('index.php');
$error = ''; $success = '';

if (isset($_POST['add'])) {
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $date = $_POST['date'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $spotify_url = mysqli_real_escape_string($conn, $_POST['spotify_url']); // New Field
    $image_filename = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_filename = uniqid('img_', true) . '.' . $file_ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_filename);
    }
    
    // Insert with Spotify URL
    $sql = "INSERT INTO concerts (artist, venue, event_date, price, capacity, description, spotify_url, image_url) VALUES ('$artist', '$venue', '$date', '$price', '$capacity', '$description', '$spotify_url', '$image_filename')";
    if (mysqli_query($conn, $sql)) {
    $success = "Concert published.";

    // ---- Auto-generate seats for this concert (Seat Selection feature) ----
    $concert_id_new = mysqli_insert_id($conn);
    $cap = (int)$capacity;
    $base_price = (float)$price;

    // Create a simple seat map: rows A..Z, 10 seats per row (A1..A10, B1..B10, ...)
    $per_row = 10;
    $vip_count = (int)ceil($cap * 0.2); // first 20% as VIP

    // Ensure seat table exists (if migration not run yet, this will just fail silently)
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS concert_seats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        concert_id INT NOT NULL,
        seat_code VARCHAR(10) NOT NULL,
        seat_type ENUM('VIP','REGULAR') DEFAULT 'REGULAR',
        seat_price DECIMAL(10,2) NOT NULL,
        status ENUM('available','held','booked') DEFAULT 'available',
        hold_until DATETIME NULL,
        held_by_user_id INT NULL,
        UNIQUE (concert_id, seat_code)
    )");

    $created = 0;
    $seat_index = 0;
    while ($created < $cap && $seat_index < 2600) { // safety guard
        $row = chr(ord('A') + intdiv($seat_index, $per_row));
        $num = ($seat_index % $per_row) + 1;
        $code = $row . $num;

        $type = ($created < $vip_count) ? 'VIP' : 'REGULAR';
        $seat_price = ($type === 'VIP') ? ($base_price * 1.5) : $base_price;

        @mysqli_query($conn, "INSERT IGNORE INTO concert_seats (concert_id, seat_code, seat_type, seat_price)
            VALUES ('".$concert_id_new."', '".$code."', '".$type."', '".number_format($seat_price, 2, '.', '')."')");

        $created++;
        $seat_index++;
    }
    // ---------------------------------------------------------------
} else {
    $error = "Database Error: " . mysqli_error($conn);
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard • TixLokal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{
      background:
        radial-gradient(900px 500px at 15% 10%, rgba(124,58,237,.30), transparent 60%),
        radial-gradient(900px 500px at 90% 20%, rgba(6,182,212,.22), transparent 60%),
        linear-gradient(180deg, #0b1020, #071824);
      color:#e5e7eb;
      min-height:100vh;
    }
    .glass{
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      border-radius:18px;
      box-shadow: 0 18px 40px rgba(0,0,0,.45);
      backdrop-filter: blur(14px);
    }
    .muted{color: rgba(229,231,235,.72);}
    .brand{display:flex;align-items:center;gap:10px;font-weight:900;letter-spacing:.2px;text-decoration:none}
.brandLogo{width:34px;height:34px;border-radius:10px;object-fit:contain;box-shadow:0 10px 30px rgba(0,0,0,.35);}
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      padding:7px 12px; border-radius:999px;
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      color: rgba(229,231,235,.9);
      font-size: 13px;
    }
    .btn-grad{
      background: linear-gradient(135deg, #7c3aed, #06b6d4);
      border:0;
      color:#fff;
      font-weight:800;
    }
    .table-darkish{
      --bs-table-bg: rgba(255,255,255,.03);
      --bs-table-striped-bg: rgba(255,255,255,.04);
      --bs-table-border-color: rgba(255,255,255,.10);
      color:#e5e7eb;
    }
    .form-control, .form-select{
      background: rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.14);
      color:#e5e7eb;
    }
    .form-control:focus, .form-select:focus{
      background: rgba(255,255,255,.07);
      border-color: rgba(124,58,237,.65);
      box-shadow: 0 0 0 .2rem rgba(124,58,237,.20);
      color:#e5e7eb;
    }
    .form-control::placeholder{color: rgba(229,231,235,.55);}
    a{color: rgba(229,231,235,.9);}
    a:hover{color:#fff;}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark py-3">
  <div class="container">
    <a class="navbar-brand brand d-flex align-items-center gap-2" href="admin.php"><img src="assets/tixlokal_logo_badge.png" class="brandLogo" alt="TixLokal logo" style="width:30px;height:30px;border-radius:10px;"><span>TixLokal <span class="muted">ADMIN</span></span></a>
    <div class="ms-auto d-flex gap-2 flex-wrap">
      <a class="chip text-decoration-none" href="index.php">← Back to Website</a>
      <a class="chip text-decoration-none" href="admin_verify.php">Verify Payments</a>
      <a class="chip text-decoration-none" href="admin_reports.php">Sales Reports</a>
    </div>
  </div>
</nav>

<div class="container pb-5">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="h3 mb-1 fw-bold">Dashboard</h1>
      <div class="muted">Create events, manage listings, and monitor operations.</div>
    </div>
    <div class="chip">🔒 Admin access</div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger glass border-0" role="alert"><?php echo $error; ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success glass border-0" role="alert"><?php echo $success; ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="glass p-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h2 class="h5 fw-bold mb-0">Add New Concert</h2>
          <span class="chip">🗓️ Publish</span>
        </div>
        <div class="muted mb-3">Fill in the details to publish an event on the website.</div>

        <form method="POST" enctype="multipart/form-data" class="row g-3">
          <div class="col-12">
            <label class="form-label">Artist</label>
            <input type="text" name="artist" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">Venue</label>
            <input type="text" name="venue" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">Date & Time</label>
            <input type="datetime-local" name="date" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Price (RM)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Total Ticket Capacity</label>
            <input type="number" name="capacity" value="100" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">Spotify Playlist (Optional)</label>
            <input type="text" name="spotify_url" class="form-control" placeholder="https://open.spotify.com/playlist/...">
          </div>
          <div class="col-12">
            <label class="form-label">Poster Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
            <div class="form-text muted">Tip: Use a landscape poster for better card display.</div>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control" required></textarea>
          </div>

          <div class="col-12 d-flex gap-2 flex-wrap mt-2">
            <button type="submit" name="add" class="btn btn-grad px-4 py-2">Publish Event</button>
            <a href="admin_reports.php" class="btn btn-outline-light px-4 py-2">View Sales</a>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="glass p-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h2 class="h5 fw-bold mb-0">Manage Events</h2>
          <span class="chip">🧾 Listings</span>
        </div>
        <div class="muted mb-3">Delete outdated events to keep listings clean.</div>

        <div class="table-responsive">
          <table class="table table-darkish table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Artist</th>
                <th>Date</th>
                <th>Capacity</th>
                <th style="width:120px">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $res = mysqli_query($conn, "SELECT id, artist, event_date, capacity FROM concerts ORDER BY event_date DESC"); 
              while($row = mysqli_fetch_assoc($res)): ?>
              <tr>
                <td class="fw-bold"><?php echo $row['artist']; ?></td>
                <td><?php echo $row['event_date']; ?></td>
                <td><?php echo $row['capacity']; ?> Pax</td>
                <td>
                  <a class="btn btn-sm btn-outline-danger"
                     href="?delete=<?php echo $row['id']; ?>"
                     onclick="return confirm('Delete this event?');">Delete</a>
                </td>
              </tr>
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
