<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Tickets</title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
      :root{
        --bg1:#0b1020; --bg2:#071827;
        --glass:rgba(255,255,255,.06);
        --border:rgba(255,255,255,.12);
        --text:#e5e7eb; --muted:rgba(229,231,235,.72);
        --primary1:#7c3aed; --primary2:#06b6d4;
        --ok:#22c55e; --warn:#f59e0b; --bad:#ef4444;
      }
      body{
        margin:0;
        font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;
        color:var(--text);
        background:
          radial-gradient(900px 500px at 20% 10%, rgba(124,58,237,.35), transparent 60%),
          radial-gradient(800px 500px at 90% 20%, rgba(6,182,212,.22), transparent 60%),
          linear-gradient(180deg, var(--bg1), var(--bg2));
        min-height:100vh;
      }
      a{color:inherit}
      .wrap{max-width:1100px;margin:34px auto;padding:0 18px;}
      .top{
        display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;
        margin-bottom:16px;
      }
      .brand{font-weight:900;letter-spacing:.2px}
      .back{
        display:inline-flex;align-items:center;gap:10px;
        padding:10px 14px;border-radius:12px;
        text-decoration:none;
        border:1px solid var(--border);
        background:rgba(255,255,255,.05);
      }
      .back:hover{filter:brightness(1.08)}
      .h1{font-size:28px;font-weight:950;margin:6px 0 0}
      .sub{color:var(--muted);margin-top:6px;font-size:14px}
      .grid{
        display:grid;grid-template-columns:repeat(12,1fr);gap:14px;
      }
      .card{
        grid-column:span 6;
        border:1px solid var(--border);
        background:var(--glass);
        border-radius:18px;
        box-shadow:0 12px 30px rgba(0,0,0,.35);
        overflow:hidden;
      }
      @media (max-width: 900px){.card{grid-column:span 12;}}
      .card-h{
        padding:16px 16px 10px;
        border-bottom:1px solid rgba(255,255,255,.08);
        display:flex;justify-content:space-between;gap:12px;align-items:flex-start;
      }
      .title{font-weight:900;font-size:18px;margin:0;line-height:1.2}
      .meta{color:var(--muted);font-size:13px;margin-top:6px;line-height:1.35}
      .badge{
        display:inline-flex;align-items:center;gap:8px;
        padding:8px 12px;border-radius:999px;
        border:1px solid rgba(255,255,255,.14);
        background:rgba(255,255,255,.05);
        font-size:12px;font-weight:800;white-space:nowrap;
      }
      .badge.ok{border-color:rgba(34,197,94,.45);background:rgba(34,197,94,.12)}
      .badge.warn{border-color:rgba(245,158,11,.45);background:rgba(245,158,11,.12)}
      .badge.bad{border-color:rgba(239,68,68,.45);background:rgba(239,68,68,.12)}
      .card-b{padding:16px;}
      .row{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.08)}
      .row:last-child{border-bottom:0}
      .k{color:var(--muted);font-size:13px}
      .v{font-weight:800}
      .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
      .btn{
        appearance:none;border:none;cursor:pointer;
        padding:10px 14px;border-radius:12px;
        font-weight:900; text-decoration:none;
        color:var(--text);
        background:rgba(255,255,255,.08);
        border:1px solid rgba(255,255,255,.14);
        display:inline-flex;align-items:center;gap:10px;
      }
      .btn:hover{filter:brightness(1.08)}
      .btn.primary{
        background:linear-gradient(135deg,var(--primary1),var(--primary2));
        border:0;
      }
      .btn.ghost{background:transparent}
      .empty{
        border:1px dashed rgba(255,255,255,.22);
        background:rgba(255,255,255,.04);
        border-radius:18px;
        padding:22px;
        color:var(--muted);
      }
      .footer-note{margin-top:16px;color:var(--muted);font-size:13px}
    </style>

</head>
<body>

<div class="wrap">
  <div class="top">
    <div>
      <div class="brand">TixLokal</div>
      <div class="h1">My Tickets</div>
      <div class="sub">Track your bookings, upload receipts, and view your e‑tickets.</div>
    </div>
    <a class="back" href="index.php">← Back to Home</a>
  </div>

  <div class="grid">

<?php
$sql = "SELECT b.*, c.artist, c.venue, c.event_date FROM bookings b JOIN concerts c ON b.concert_id = c.id WHERE b.user_id='$user_id' ORDER BY b.booking_date DESC";
$res = mysqli_query($conn, $sql);
$hasRows = (mysqli_num_rows($res) > 0);

if(!$hasRows): ?>
    <div class="empty">
      <div style="font-weight:900;color:var(--text);font-size:16px;">No tickets yet</div>
      <div style="margin-top:6px;">Browse events and book your first concert ticket.</div>
      <div class="actions" style="margin-top:14px;">
        <a class="btn primary" href="index.php">🎫 Explore Events</a>
      </div>
    </div>
<?php else:
while($row = mysqli_fetch_assoc($res)):
    $status = $row['status'];
?>
    <div class="card">
      <div class="card-h">
        <div>
          <h3 class="title"><?php echo htmlspecialchars($row['artist']); ?></h3>
          <div class="meta">📍 <?php echo htmlspecialchars($row['venue']); ?> &nbsp; • &nbsp; 🗓️ <?php echo htmlspecialchars($row['event_date']); ?></div>
        </div>
        <?php
          $status = $row['status'];
          $badgeClass = ($status == 'approved') ? 'ok' : (($status == 'pending_payment') ? 'warn' : 'bad');
          $statusLabel = str_replace('_', ' ', $status);
        ?>
        <div class="badge <?php echo $badgeClass; ?>">
          <?php echo strtoupper(htmlspecialchars($statusLabel)); ?>
        </div>
      </div>
      <div class="card-b">
        <div class="row"><div class="k">Quantity</div><div class="v"><?php echo (int)$row['quantity']; ?></div></div>

<?php
  // Seat list (if Seat Selection is used)
  $seat_codes = [];
  $sid = (int)$row['id'];
  $sres = @mysqli_query($conn, "SELECT cs.seat_code FROM booking_seats bs JOIN concert_seats cs ON cs.id=bs.seat_id WHERE bs.booking_id=$sid ORDER BY cs.seat_code ASC");
  if ($sres) {
    while($sr = mysqli_fetch_assoc($sres)) { $seat_codes[] = $sr['seat_code']; }
  }
?>
<?php if (!empty($seat_codes)): ?>
  <div class="row"><div class="k">Seats</div><div class="v"><?php echo htmlspecialchars(implode(', ', $seat_codes)); ?></div></div>
<?php endif; ?>
        <div class="row"><div class="k">Total Paid</div><div class="v">RM <?php echo htmlspecialchars($row['total_price']); ?></div></div>
        <div class="actions">
          <?php if ($status == 'approved'): ?>
            <a class="btn primary" href="view_ticket.php?id=<?php echo (int)$row['id']; ?>">🎟️ View E‑Ticket</a>
          <?php elseif ($status == 'pending_payment'): ?>
            <a class="btn primary" href="book.php?step=upload&bid=<?php echo (int)$row['id']; ?>">⬆️ Upload Receipt</a>
          <?php else: ?>
            <a class="btn ghost" href="concert_details.php?id=<?php echo (int)$row['concert_id']; ?>">View Event</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
<?php endwhile; ?>
<?php endif; ?>

  </div>

  <div class="footer-note">
    Tip: Pending payment tickets require a receipt upload. Approved tickets will show an e‑ticket button.
  </div>
</div>
</body>
</html>