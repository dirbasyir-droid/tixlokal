<?php
include 'config.php';
if (($_SESSION['role'] ?? '') != 'admin') redirect('index.php');

// Housekeeping: release expired holds
@mysqli_query($conn, "UPDATE concert_seats 
  SET status='available', hold_until=NULL, held_by_user_id=NULL
  WHERE status='held' AND hold_until IS NOT NULL AND hold_until < NOW()");

// Ensure tables exist
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

@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS booking_seats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  seat_id INT NOT NULL,
  UNIQUE (booking_id, seat_id)
)");

$action_msg = '';
$concert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Actions
if (isset($_POST['action']) && $concert_id > 0) {
  $act = $_POST['action'];
  if ($act === 'release_held') {
    @mysqli_query($conn, "UPDATE concert_seats SET status='available', hold_until=NULL, held_by_user_id=NULL WHERE concert_id=$concert_id AND status='held'");
    $action_msg = "Released all held seats for this concert.";
  }
  if ($act === 'reset_all') {
    @mysqli_query($conn, "UPDATE concert_seats SET status='available', hold_until=NULL, held_by_user_id=NULL WHERE concert_id=$concert_id AND status!='booked'");
    $action_msg = "Reset all non-booked seats to available.";
  }
}

// Concert list
$clist = mysqli_query($conn, "SELECT id, artist, venue, event_date FROM concerts ORDER BY event_date DESC");

// If no id, pick first
if ($concert_id <= 0) {
  $first = mysqli_query($conn, "SELECT id FROM concerts ORDER BY event_date DESC LIMIT 1");
  if ($first && mysqli_num_rows($first) > 0) $concert_id = (int)mysqli_fetch_assoc($first)['id'];
}

$concert = null;
if ($concert_id > 0) {
  $cres = mysqli_query($conn, "SELECT * FROM concerts WHERE id=$concert_id");
  if ($cres && mysqli_num_rows($cres) > 0) $concert = mysqli_fetch_assoc($cres);
}

// Seats + stats
$stats = ['available'=>0,'held'=>0,'booked'=>0,'total'=>0];
$rows = [];
if ($concert) {
  $sres = mysqli_query($conn, "SELECT * FROM concert_seats WHERE concert_id=$concert_id ORDER BY seat_code ASC");
  while($s = mysqli_fetch_assoc($sres)) {
    $stats['total']++;
    if (isset($stats[$s['status']])) $stats[$s['status']]++;
    $r = strtoupper(substr($s['seat_code'],0,1));
    if (!isset($rows[$r])) $rows[$r]=[];
    $rows[$r][] = $s;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seat Dashboard • Admin</title>
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
    .seat{
      width:40px;height:40px;border-radius:12px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.05);
      display:grid;place-items:center;
      font-weight:900;font-size:12px;
      user-select:none;
      margin:4px;
    }
    .seat.av{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.28)}
    .seat.held{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.28)}
    .seat.booked{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.28)}
    .seat.vip.av{background:rgba(124,58,237,.16);border-color:rgba(124,58,237,.30)}
    .seatGrid{display:flex;flex-wrap:wrap}
    .rowWrap{display:flex;gap:10px;align-items:flex-start;margin:8px 0}
    .rowLabel{width:30px;text-align:center;font-weight:900;color:rgba(229,231,235,.9);padding-top:10px}
    .stage{border:1px dashed rgba(255,255,255,.22);background:rgba(255,255,255,.04);border-radius:14px;padding:10px;text-align:center;color:rgba(229,231,235,.72);font-weight:900}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark py-3">
  <div class="container">
    <span class="navbar-brand brand">TixLokal <span class="muted">ADMIN</span></span>
    <div class="ms-auto d-flex gap-2 flex-wrap">
      <a class="chip" href="admin.php">← Dashboard</a>
      <a class="chip" href="admin_verify.php">Verify Payments</a>
    </div>
  </div>
</nav>

<div class="container pb-5">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="h3 mb-1 fw-bold">Seat Dashboard</h1>
      <div class="muted">Monitor seat availability, holds, and bookings.</div>
    </div>
    <div class="chip">🪑 Seat management</div>
  </div>

  <?php if ($action_msg): ?>
    <div class="alert alert-success glass border-0"><?php echo htmlspecialchars($action_msg); ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="glass p-4">
        <div class="fw-bold mb-2">Select Concert</div>
        <form method="GET" class="d-grid gap-2">
          <select class="form-select" name="id" onchange="this.form.submit()">
            <?php while($c = mysqli_fetch_assoc($clist)): ?>
              <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$c['id']===$concert_id)?'selected':''; ?>>
                #<?php echo (int)$c['id']; ?> • <?php echo htmlspecialchars($c['artist']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </form>

        <hr class="border-white border-opacity-10 my-3">

        <div class="d-flex flex-wrap gap-2">
          <span class="chip">✅ Available: <?php echo (int)$stats['available']; ?></span>
          <span class="chip">⏳ Held: <?php echo (int)$stats['held']; ?></span>
          <span class="chip">🎟️ Booked: <?php echo (int)$stats['booked']; ?></span>
          <span class="chip">🧮 Total: <?php echo (int)$stats['total']; ?></span>
        </div>

        <hr class="border-white border-opacity-10 my-3">

        <form method="POST" class="d-flex gap-2 flex-wrap">
          <button class="btn btn-warning fw-bold" name="action" value="release_held" type="submit">Release Held</button>
          <button class="btn btn-outline-light fw-bold" name="action" value="reset_all" type="submit">Reset Non‑Booked</button>
        </form>

        <div class="muted mt-3" style="font-size:13px">
          Tip: In viva, explain held seats as “temporary reservation” to prevent double booking.
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="glass p-4">
        <div class="d-flex justify-content-between gap-2 flex-wrap">
          <div>
            <div class="fw-bold">Seat Map</div>
            <?php if($concert): ?>
              <div class="muted" style="font-size:13px">
                <?php echo htmlspecialchars($concert['artist']); ?> • <?php echo htmlspecialchars($concert['venue']); ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="chip"><span style="width:10px;height:10px;border-radius:3px;background:rgba(34,197,94,.45);display:inline-block"></span> Available</span>
            <span class="chip"><span style="width:10px;height:10px;border-radius:3px;background:rgba(245,158,11,.45);display:inline-block"></span> Held</span>
            <span class="chip"><span style="width:10px;height:10px;border-radius:3px;background:rgba(239,68,68,.45);display:inline-block"></span> Booked</span>
            <span class="chip"><span style="width:10px;height:10px;border-radius:3px;background:rgba(124,58,237,.45);display:inline-block"></span> VIP</span>
          </div>
        </div>

        <div class="stage my-3">STAGE</div>

        <?php if(!$concert): ?>
          <div class="muted">No concerts found.</div>
        <?php else: ?>
          <?php foreach($rows as $r => $list): ?>
            <div class="rowWrap">
              <div class="rowLabel"><?php echo htmlspecialchars($r); ?></div>
              <div class="seatGrid">
                <?php foreach($list as $s):
                  $st = $s['status'];
                  $cls = ($st==='booked')?'booked':(($st==='held')?'held':'av');
                  $vip = ($s['seat_type']==='VIP')?'vip':'';
                  $tip = $s['seat_code']." • ".$s['seat_type']." • RM ".number_format((float)$s['seat_price'],2)." • ".$st;
                  if ($st==='held' && !empty($s['hold_until'])) $tip .= " (until ".$s['hold_until'].")";
                ?>
                  <div class="seat <?php echo $cls.' '.$vip; ?>" title="<?php echo htmlspecialchars($tip); ?>">
                    <?php echo htmlspecialchars($s['seat_code']); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
