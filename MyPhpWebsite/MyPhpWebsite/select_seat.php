<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php?action=login');

$user_id = (int)$_SESSION['user_id'];
$concert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($concert_id <= 0) redirect('index.php');

// Housekeeping: release expired holds
@mysqli_query($conn, "UPDATE concert_seats 
  SET status='available', hold_until=NULL, held_by_user_id=NULL
  WHERE status='held' AND hold_until IS NOT NULL AND hold_until < NOW()");

// Fetch concert
$cres = mysqli_query($conn, "SELECT * FROM concerts WHERE id=$concert_id");
if (!$cres || mysqli_num_rows($cres) == 0) { echo "Concert not found."; exit; }
$concert = mysqli_fetch_assoc($cres);

// Ensure tables exist (safe)
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

// Auto-generate seats if not exist (based on capacity)
$seat_count_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM concert_seats WHERE concert_id=$concert_id");
$seat_count = (int)(mysqli_fetch_assoc($seat_count_res)['c'] ?? 0);

if ($seat_count === 0) {
  $cap = (int)($concert['capacity'] ?? 0);
  $base_price = (float)($concert['price'] ?? 0);
  $per_row = 10;
  $vip_count = (int)ceil($cap * 0.2);

  $created = 0;
  $seat_index = 0;
  while ($created < $cap && $seat_index < 2600) {
    $row = chr(ord('A') + intdiv($seat_index, $per_row));
    $num = ($seat_index % $per_row) + 1;
    $code = $row . $num;

    $type = ($created < $vip_count) ? 'VIP' : 'REGULAR';
    $seat_price = ($type === 'VIP') ? ($base_price * 1.5) : $base_price;

    @mysqli_query($conn, "INSERT IGNORE INTO concert_seats (concert_id, seat_code, seat_type, seat_price)
      VALUES ('".$concert_id."', '".$code."', '".$type."', '".number_format($seat_price, 2, '.', '')."')");
    $created++;
    $seat_index++;
  }
}

// Handle seat selection submit
$error = '';
if (isset($_POST['continue'])) {
  $ids = $_POST['seats'] ?? [];
  if (!is_array($ids) || count($ids) < 1) {
    $error = "Please select at least 1 seat.";
  } else {
    $safe_ids = array_map('intval', $ids);
    $id_list = implode(',', $safe_ids);

    mysqli_begin_transaction($conn);
    try {
      // Lock selected seats
      $sres = mysqli_query($conn, "SELECT id, status, held_by_user_id, hold_until FROM concert_seats
        WHERE concert_id=$concert_id AND id IN ($id_list) FOR UPDATE");
      $rows = [];
      while($r = mysqli_fetch_assoc($sres)) $rows[] = $r;

      if (count($rows) !== count($safe_ids)) throw new Exception("Some seats are invalid.");

      foreach ($rows as $s) {
        if ($s['status'] === 'booked') throw new Exception("Some seats are already booked.");
        if ($s['status'] === 'held') {
          $held_by = (int)($s['held_by_user_id'] ?? 0);
          $exp = !empty($s['hold_until']) ? strtotime($s['hold_until']) : null;
          if ($held_by !== $user_id) throw new Exception("Some seats are currently held by another user.");
          if ($exp && $exp < time()) throw new Exception("Your seat hold expired. Please pick again.");
        }
      }

      // Hold them for 15 minutes
      mysqli_query($conn, "UPDATE concert_seats
        SET status='held', held_by_user_id=$user_id, hold_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
        WHERE concert_id=$concert_id AND id IN ($id_list)");

      mysqli_commit($conn);

      $_SESSION['selected_seat_ids'] = $safe_ids;
      $_SESSION['selected_seat_concert_id'] = $concert_id;

      redirect("book.php?id=$concert_id&qty=".count($safe_ids));
    } catch (Exception $ex) {
      mysqli_rollback($conn);
      $error = $ex->getMessage();
    }
  }
}

// Fetch seats
$res = mysqli_query($conn, "SELECT * FROM concert_seats WHERE concert_id=$concert_id ORDER BY seat_code ASC");
$seats = [];
while($row = mysqli_fetch_assoc($res)) $seats[] = $row;

// Group by row letter
$rows = [];
foreach ($seats as $s) {
  $r = strtoupper(substr($s['seat_code'], 0, 1));
  if (!isset($rows[$r])) $rows[$r] = [];
  $rows[$r][] = $s;
}

// For UI
$title = $concert['artist'] ?? 'Select Seats';
$venue = $concert['venue'] ?? '';
$event_date = $concert['event_date'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Select Seats • <?php echo htmlspecialchars($title); ?></title>
  <style>
    :root{
      --bg1:#0b1020; --bg2:#071a26;
      --glass:rgba(255,255,255,.06); --border:rgba(255,255,255,.12);
      --text:#e5e7eb; --muted:rgba(229,231,235,.72);
      --p1:#7c3aed; --p2:#06b6d4;
      --ok:#22c55e; --warn:#f59e0b; --bad:#ef4444;
    }
    *{box-sizing:border-box}
    body{
      margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;
      color:var(--text);
      background:
        radial-gradient(900px 520px at 20% 10%, rgba(124,58,237,.35), transparent 60%),
        radial-gradient(820px 520px at 90% 20%, rgba(6,182,212,.25), transparent 60%),
        linear-gradient(180deg, var(--bg1), var(--bg2));
      min-height:100vh;
    }
    a{color:inherit}
    .wrap{max-width:1100px;margin:34px auto;padding:0 18px;}
    .top{
      display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;
      margin-bottom:14px;
    }
    .brand{font-weight:950;letter-spacing:.2px}
    .h1{font-size:28px;font-weight:950;margin:6px 0 0}
    .sub{color:var(--muted);margin-top:6px;font-size:14px;line-height:1.45}
    .back{
      display:inline-flex;align-items:center;gap:10px;
      padding:10px 14px;border-radius:12px;
      text-decoration:none;border:1px solid var(--border);
      background:rgba(255,255,255,.05);
    }
    .back:hover{filter:brightness(1.08)}
    .grid{display:grid;grid-template-columns:1.25fr .75fr;gap:16px;align-items:start;}
    @media (max-width:900px){.grid{grid-template-columns:1fr;}}
    .card{
      border:1px solid var(--border);
      background:var(--glass);
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,.35);
      overflow:hidden;
    }
    .card-h{padding:16px 16px 10px;border-bottom:1px solid rgba(255,255,255,.08)}
    .card-b{padding:16px}
    .pill{
      display:inline-flex;align-items:center;gap:8px;
      padding:8px 12px;border-radius:999px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.05);
      font-size:13px;font-weight:800;color:rgba(229,231,235,.9);
    }
    .legend{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
    .key{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)}
    .dot{width:12px;height:12px;border-radius:4px;border:1px solid rgba(255,255,255,.18)}
    .dot.av{background:rgba(34,197,94,.45)}
    .dot.held{background:rgba(245,158,11,.45)}
    .dot.book{background:rgba(239,68,68,.45)}
    .stage{
      border:1px dashed rgba(255,255,255,.22);
      background:rgba(255,255,255,.04);
      border-radius:14px;
      padding:10px 12px;
      text-align:center;
      color:var(--muted);
      font-weight:900;
      margin-bottom:14px;
    }
    .seatRow{display:flex;gap:10px;align-items:center;margin:10px 0}
    .rowLabel{
      width:34px;flex:0 0 auto;
      color:rgba(229,231,235,.85);
      font-weight:950;
      text-align:center;
    }
    .seats{display:flex;flex-wrap:wrap;gap:8px}
    .seat{
      width:40px;height:40px;border-radius:12px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.05);
      display:grid;place-items:center;
      font-weight:950;font-size:12px;
      cursor:pointer;
      user-select:none;
      transition:.15s transform, .15s filter;
      position:relative;
    }
    .seat:hover{transform:translateY(-1px);filter:brightness(1.08)}
    .seat input{display:none}
    .seat.av{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.25)}
    .seat.vip.av{background:rgba(124,58,237,.16);border-color:rgba(124,58,237,.30)}
    .seat.held{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.30);cursor:not-allowed;opacity:.85}
    .seat.booked{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.30);cursor:not-allowed;opacity:.85}
    .seat.sel{outline:2px solid rgba(6,182,212,.85); box-shadow:0 0 0 4px rgba(6,182,212,.18)}
    .hint{color:var(--muted);font-size:13px;line-height:1.45}
    .alert{
      padding:10px 12px;border-radius:14px;margin-top:12px;
      border:1px solid rgba(239,68,68,.35);
      background:rgba(239,68,68,.12);
      color:rgba(255,255,255,.92);
      font-size:14px;
    }
    .btnRow{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
    .btn{
      appearance:none;border:none;cursor:pointer;
      padding:11px 14px;border-radius:12px;
      font-weight:950;color:var(--text);
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.14);
      text-decoration:none;display:inline-flex;align-items:center;gap:10px;
    }
    .btn:hover{filter:brightness(1.08)}
    .btnPrimary{background:linear-gradient(135deg,var(--p1),var(--p2));border:0}
    .sum{
      display:grid;gap:10px;
      border:1px solid rgba(255,255,255,.12);
      background:rgba(255,255,255,.04);
      border-radius:14px;
      padding:12px;
      margin-top:12px;
    }
    .kv{display:flex;justify-content:space-between;gap:10px}
    .k{color:var(--muted);font-size:13px}
    .v{font-weight:950}
    .price{font-size:20px}
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <div class="brand">TixLokal</div>
      <div class="h1">Select Seats</div>
      <div class="sub">
        <div style="font-weight:900;color:rgba(229,231,235,.92)"><?php echo htmlspecialchars($title); ?></div>
        <div><?php echo htmlspecialchars($venue); ?> • <?php echo htmlspecialchars($event_date); ?></div>
        <div class="hint">Seats are held for <b>15 minutes</b> during checkout to prevent double-booking.</div>
      </div>
    </div>
    <a class="back" href="concert_details.php?id=<?php echo (int)$concert_id; ?>">← Back</a>
  </div>

  <div class="grid">
    <div class="card">
      <div class="card-h">
        <span class="pill">🎟️ Choose your seats</span>
        <div class="legend">
          <span class="key"><span class="dot av"></span> Available</span>
          <span class="key"><span class="dot held"></span> Held</span>
          <span class="key"><span class="dot book"></span> Booked</span>
          <span class="key"><span class="dot" style="background:rgba(124,58,237,.45)"></span> VIP</span>
        </div>
        <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      </div>
      <div class="card-b">
        <div class="stage">STAGE</div>

        <form method="POST" id="seatForm">
          <?php foreach ($rows as $r => $list): ?>
            <div class="seatRow">
              <div class="rowLabel"><?php echo htmlspecialchars($r); ?></div>
              <div class="seats">
                <?php foreach ($list as $s):
                  $status = $s['status'];
                  $held_by = (int)($s['held_by_user_id'] ?? 0);
                  $isMine = ($status === 'held' && $held_by === $user_id);
                  $disabled = ($status === 'booked') || ($status === 'held' && !$isMine);
                  $cls = ($status === 'booked') ? 'booked' : (($status === 'held' && !$isMine) ? 'held' : 'av');
                  $vip = ($s['seat_type'] === 'VIP') ? 'vip' : '';
                  $titleTip = $s['seat_code']." • ".$s['seat_type']." • RM ".number_format((float)$s['seat_price'],2);
                  if ($status === 'held' && !$isMine) $titleTip .= " • Held";
                  if ($status === 'booked') $titleTip .= " • Booked";
                ?>
                  <label class="seat <?php echo $cls.' '.$vip; ?>" title="<?php echo htmlspecialchars($titleTip); ?>">
                    <input <?php echo $disabled ? 'disabled' : ''; ?> type="checkbox" name="seats[]" value="<?php echo (int)$s['id']; ?>" data-code="<?php echo htmlspecialchars($s['seat_code']); ?>" data-price="<?php echo htmlspecialchars($s['seat_price']); ?>" data-type="<?php echo htmlspecialchars($s['seat_type']); ?>">
                    <?php echo htmlspecialchars($s['seat_code']); ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="btnRow">
            <button class="btn btnPrimary" type="submit" name="continue">Continue to Booking</button>
            <a class="btn" href="concert_details.php?id=<?php echo (int)$concert_id; ?>">Cancel</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-h">
        <div style="font-weight:950;font-size:16px;">Summary</div>
        <div class="hint">Your selected seats & total price.</div>
      </div>
      <div class="card-b">
        <div class="sum">
          <div class="kv"><div class="k">Selected seats</div><div class="v" id="selSeats">—</div></div>
          <div class="kv"><div class="k">Quantity</div><div class="v" id="selQty">0</div></div>
          <div class="kv"><div class="k">Total</div><div class="v price">RM <span id="selTotal">0.00</span></div></div>
          <div class="hint">VIP seats are priced higher (1.5× base price) to demonstrate tiered pricing in your FYP.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const form = document.getElementById('seatForm');
  const seats = Array.from(form.querySelectorAll('input[type="checkbox"][name="seats[]"]'));
  const seatLabels = seats.map(s => s.closest('label.seat'));

  function refreshSummary(){
    const selected = seats.filter(s => s.checked);
    const codes = selected.map(s => s.dataset.code);
    const total = selected.reduce((sum, s) => sum + parseFloat(s.dataset.price || "0"), 0);
    document.getElementById('selSeats').textContent = codes.length ? codes.join(', ') : '—';
    document.getElementById('selQty').textContent = String(selected.length);
    document.getElementById('selTotal').textContent = total.toFixed(2);
  }

  seats.forEach((chk, i) => {
    const lab = seatLabels[i];
    // Label click toggles checkbox; add visual class
    chk.addEventListener('change', () => {
      lab.classList.toggle('sel', chk.checked);
      refreshSummary();
    });
  });

  // Initial paint
  refreshSummary();

  // Prevent submitting with 0 seats
  form.addEventListener('submit', (e) => {
    if (!seats.some(s => s.checked)) {
      e.preventDefault();
      alert('Please select at least 1 seat.');
    }
  });
</script>
</body>
</html>
