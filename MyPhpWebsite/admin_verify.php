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
        // Mark seats as booked (Seat Selection feature)
        @mysqli_query($conn, "UPDATE concert_seats cs JOIN booking_seats bs ON cs.id = bs.seat_id SET cs.status='booked', cs.hold_until=NULL, cs.held_by_user_id=NULL WHERE bs.booking_id='$bid'");
        mysqli_query($conn, "UPDATE payments SET status='valid' WHERE id='$pid'");
    } else {
        mysqli_query($conn, "UPDATE bookings SET status='rejected' WHERE id='$bid'");
        // Release seats + restore capacity (so the event availability stays correct)
        @mysqli_query($conn, "UPDATE concerts c JOIN bookings b ON c.id=b.concert_id SET c.capacity = c.capacity + b.quantity WHERE b.id='$bid'");
        @mysqli_query($conn, "UPDATE concert_seats cs JOIN booking_seats bs ON cs.id = bs.seat_id SET cs.status='available', cs.hold_until=NULL, cs.held_by_user_id=NULL WHERE bs.booking_id='$bid'");
        mysqli_query($conn, "UPDATE payments SET status='invalid' WHERE id='$pid'");
    }
    redirect('admin_verify.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verify Payments • Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{
      background:
        radial-gradient(900px 500px at 15% 10%, rgba(124,58,237,.30), transparent 60%),
        radial-gradient(900px 500px at 90% 20%, rgba(6,182,212,.22), transparent 60%),
        linear-gradient(180deg, #0b1020, #071824);
      color:#e5e7eb; min-height:100vh;
    }
    .glass{
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      border-radius:18px;
      box-shadow: 0 18px 40px rgba(0,0,0,.45);
      backdrop-filter: blur(14px);
    }
    .muted{color: rgba(229,231,235,.72);}
    .brand{font-weight:900; letter-spacing:.2px;}
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      padding:7px 12px; border-radius:999px;
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      color: rgba(229,231,235,.9);
      font-size: 13px;
      text-decoration:none;
    }
    .btn-approve{
      background: linear-gradient(135deg, #16a34a, #22c55e);
      border:0; font-weight:800;
    }
    .btn-reject{
      background: rgba(239,68,68,.14);
      border:1px solid rgba(239,68,68,.45);
      color:#fff; font-weight:800;
    }
    .thumb{
      width: 220px; max-width: 100%;
      border-radius: 14px;
      border:1px solid rgba(255,255,255,.14);
      box-shadow: 0 10px 26px rgba(0,0,0,.35);
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark py-3">
  <div class="container">
    <span class="navbar-brand brand">TixLokal <span class="muted">ADMIN</span></span>
    <div class="ms-auto d-flex gap-2 flex-wrap">
      <a class="chip" href="admin.php">← Dashboard</a>
      <a class="chip" href="admin_reports.php">Sales Reports</a>
    </div>
  </div>
</nav>

<div class="container pb-5">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="h3 mb-1 fw-bold">Verify Payments</h1>
      <div class="muted">Review uploaded receipts and approve or reject bookings.</div>
    </div>
    <div class="chip">🧾 Pending receipts</div>
  </div>

  <?php
  $sql = "SELECT b.id as bid, b.total_price, b.quantity, p.id as pid, p.receipt_img, c.artist, u.name 
          FROM bookings b 
          JOIN payments p ON b.id = p.booking_id 
          JOIN concerts c ON b.concert_id = c.id 
          JOIN users u ON b.user_id = u.id 
          WHERE b.status = 'verification_pending' 
          ORDER BY b.booking_date ASC";
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) == 0): ?>
    <div class="glass p-4">
      <div class="fw-bold">No pending approvals.</div>
      <div class="muted mt-1">Once users upload receipts, they will appear here for verification.</div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
  <?php while ($row = mysqli_fetch_assoc($res)): ?>
    <div class="col-lg-6">
      <div class="glass p-4 h-100">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <div class="h5 fw-bold mb-1"><?php echo $row['artist']; ?></div>
            <div class="muted">User: <span class="text-white fw-semibold"><?php echo $row['name']; ?></span></div>
          </div>
          <span class="chip">RM <?php echo number_format((float)$row['total_price'], 2); ?></span>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2">
          <span class="chip">Qty: <?php echo (int)$row['quantity']; ?></span>
          <span class="chip">Booking ID: <?php echo (int)$row['bid']; ?></span>
        </div>

        <div class="mt-3">
          <div class="muted mb-2">Receipt preview</div>
          <a href="uploads/<?php echo $row['receipt_img']; ?>" target="_blank" class="text-decoration-none">
            <img class="thumb" src="uploads/<?php echo $row['receipt_img']; ?>" alt="Receipt">
          </a>
          <div class="muted mt-2">Tip: Click the image to view full size.</div>
        </div>

        <form method="POST" class="mt-4 d-flex gap-2 flex-wrap">
          <input type="hidden" name="bid" value="<?php echo $row['bid']; ?>">
          <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
          <button type="submit" name="status" value="approved" class="btn btn-approve px-4 py-2">Approve</button>
          <button type="submit" name="status" value="rejected" class="btn btn-reject px-4 py-2">Reject</button>
        </form>
      </div>
    </div>
  <?php endwhile; ?>
  </div>

</div>
</body>
</html>
