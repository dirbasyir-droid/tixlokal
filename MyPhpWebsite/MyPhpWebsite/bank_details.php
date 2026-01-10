<?php
// bank_details.php (Dummy Bank Payment page)
// DB-compatible: adapts to different bookings schema (qty/quantity, concert_id/event_id, total/total_price)
// Requires: config.php (mysqli $conn), existing bookings + concerts tables
session_start();
require_once __DIR__ . '/config.php';

if (!isset($conn) || !$conn) {
  die("Database connection not found. Check config.php.");
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$bid = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
if ($bid <= 0) { die("Invalid booking id."); }

// --- helper: fetch columns for a table
function table_columns($conn, $table) {
  $cols = [];
  $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
  if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
      $cols[] = $row['Field'];
    }
    mysqli_free_result($res);
  }
  return $cols;
}

$bookingCols = table_columns($conn, "bookings");
if (empty($bookingCols)) { die("Cannot read bookings table columns."); }

// Map likely column names
$colBookingId   = in_array('id', $bookingCols) ? 'id' : (in_array('booking_id', $bookingCols) ? 'booking_id' : null);
$colUserId      = in_array('user_id', $bookingCols) ? 'user_id' : (in_array('uid', $bookingCols) ? 'uid' : null);
$colConcertId   = in_array('concert_id', $bookingCols) ? 'concert_id' : (in_array('event_id', $bookingCols) ? 'event_id' : (in_array('concert', $bookingCols) ? 'concert' : null));
$colQty         = in_array('qty', $bookingCols) ? 'qty' : (in_array('quantity', $bookingCols) ? 'quantity' : (in_array('ticket_qty', $bookingCols) ? 'ticket_qty' : null));
$colTotal       = in_array('total_price', $bookingCols) ? 'total_price' : (in_array('total', $bookingCols) ? 'total' : (in_array('amount', $bookingCols) ? 'amount' : null));
$colStatus      = in_array('status', $bookingCols) ? 'status' : null;
$colReceipt     = in_array('receipt', $bookingCols) ? 'receipt' : (in_array('receipt_img', $bookingCols) ? 'receipt_img' : (in_array('receipt_image', $bookingCols) ? 'receipt_image' : null));

if (!$colBookingId) { die("Cannot find booking id column in bookings table."); }
if (!$colConcertId) { die("Cannot find concert_id/event_id column in bookings table."); }

// Fetch booking row
$stmt = $conn->prepare("SELECT * FROM bookings WHERE `$colBookingId` = ? LIMIT 1");
$stmt->bind_param("i", $bid);
$stmt->execute();
$bookingRes = $stmt->get_result();
$booking = $bookingRes ? $bookingRes->fetch_assoc() : null;
$stmt->close();

if (!$booking) { die("Booking not found."); }

// If user_id exists and session has user_id, optionally block other users (safe)
if ($colUserId && isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0) {
  if (intval($booking[$colUserId]) !== intval($_SESSION['user_id'])) {
    die("Unauthorized access to booking.");
  }
}

$concertId = intval($booking[$colConcertId]);
$qty = $colQty ? intval($booking[$colQty]) : 1;
$total = $colTotal ? floatval($booking[$colTotal]) : 0.0;
$status = $colStatus ? (string)$booking[$colStatus] : '';

// Fetch concert info (best-effort)
$concert = null;
$concertCols = table_columns($conn, "concerts");
if (!empty($concertCols) && $concertId > 0) {
  $cIdCol = in_array('id', $concertCols) ? 'id' : (in_array('concert_id', $concertCols) ? 'concert_id' : null);
  if ($cIdCol) {
    $stmt = $conn->prepare("SELECT * FROM concerts WHERE `$cIdCol` = ? LIMIT 1");
    $stmt->bind_param("i", $concertId);
    $stmt->execute();
    $cres = $stmt->get_result();
    $concert = $cres ? $cres->fetch_assoc() : null;
    $stmt->close();
  }
}

// Pull common concert fields with fallbacks
function pick($row, $keys, $default='') {
  if (!$row) return $default;
  foreach ($keys as $k) {
    if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') return $row[$k];
  }
  return $default;
}

$eventName = pick($concert, ['artist','name','title','event','event_name'], 'Concert');
$venue     = pick($concert, ['venue','location','place'], '');
$dateStr   = pick($concert, ['date','event_date','datetime','start_time'], '');
$price     = floatval(pick($concert, ['price','ticket_price','cost'], 0));

// If total missing, estimate from price * qty
if ($total <= 0 && $price > 0 && $qty > 0) {
  $total = $price * $qty;
}

$refCode = "BOOKING-" . $bid;

// Handle "Pay with Dummy Bank" (demo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demo_pay']) && $_POST['demo_pay'] === '1') {
  $newStatus = 'verification_pending';
  if ($colStatus) {
    if ($colReceipt) {
      $stmt = $conn->prepare("UPDATE bookings SET `$colStatus`=?, `$colReceipt`=? WHERE `$colBookingId`=?");
      $demoReceipt = 'DEMO_RECEIPT';
      $stmt->bind_param("ssi", $newStatus, $demoReceipt, $bid);
    } else {
      $stmt = $conn->prepare("UPDATE bookings SET `$colStatus`=? WHERE `$colBookingId`=?");
      $stmt->bind_param("si", $newStatus, $bid);
    }
    $stmt->execute();
    $stmt->close();
  }
  header("Location: book.php?step=upload&bid=" . $bid);
  exit;
}

$amountFmt = "RM " . number_format($total, 2);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Bank Details (Demo) | TixLokal</title>
  <style>
    :root{
      --bg1:#240a47; --bg2:#061d2a;
      --card: rgba(255,255,255,.08);
      --card2: rgba(255,255,255,.06);
      --text: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.70);
      --line: rgba(255,255,255,.12);
      --accent1:#7c3aed; --accent2:#06b6d4;
      --danger:#ef4444; --ok:#22c55e;
    }
    *{box-sizing:border-box}
    body{
      margin:0; color:var(--text);
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      background: radial-gradient(1200px 700px at 20% 10%, rgba(124,58,237,.45), transparent 55%),
                  radial-gradient(900px 600px at 85% 20%, rgba(6,182,212,.35), transparent 50%),
                  linear-gradient(160deg, var(--bg1), var(--bg2));
      min-height:100vh;
      padding: 36px 18px;
    }
    .wrap{max-width: 980px; margin:0 auto;}
    .topbar{display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px;}
    .brand{display:flex; align-items:center; gap:10px; font-weight:800; letter-spacing:.2px;}
    .brand img{width:34px; height:34px; border-radius:10px; object-fit:cover; box-shadow:0 10px 30px rgba(0,0,0,.25);}
    .pill{
      padding:9px 12px; border:1px solid var(--line); border-radius:999px;
      background: rgba(255,255,255,.06); color:var(--text); text-decoration:none;
    }
    .grid{display:grid; grid-template-columns: 1.25fr .75fr; gap:16px;}
    @media (max-width: 880px){ .grid{grid-template-columns:1fr;} }
    .card{
      background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.06));
      border:1px solid var(--line);
      border-radius: 18px;
      box-shadow: 0 30px 80px rgba(0,0,0,.35);
      overflow:hidden;
    }
    .card .hd{padding:18px 18px 10px;}
    .card .hd h1{margin:0; font-size:24px;}
    .card .hd p{margin:6px 0 0; color:var(--muted);}
    .section{padding: 12px 18px 18px;}
    .table{
      width:100%; border-collapse:collapse; overflow:hidden;
      background: rgba(0,0,0,.12);
      border:1px solid var(--line);
      border-radius: 14px;
    }
    .table td{padding:12px 12px; border-bottom:1px solid rgba(255,255,255,.08); color:var(--text);}
    .table tr:last-child td{border-bottom:none;}
    .k{color:var(--muted); width:38%}
    .v{font-weight:700}
    .badge{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 12px; border-radius:999px;
      border:1px solid var(--line); background: rgba(255,255,255,.06);
      color:var(--muted); font-weight:700; font-size:12px;
    }
    .badge strong{color:var(--text)}
    .actions{display:flex; flex-wrap:wrap; gap:10px; margin-top:14px;}
    .btn{
      border:1px solid var(--line);
      background: rgba(255,255,255,.06);
      color:var(--text);
      padding:11px 14px;
      border-radius: 12px;
      font-weight:800;
      text-decoration:none;
      cursor:pointer;
      transition: transform .08s ease, background .2s ease;
    }
    .btn:hover{transform: translateY(-1px); background: rgba(255,255,255,.09);}
    .btn.primary{
      border:none;
      background: linear-gradient(90deg, var(--accent1), var(--accent2));
      box-shadow: 0 18px 40px rgba(124,58,237,.25);
    }
    .btn.danger{border-color: rgba(239,68,68,.35); color: #ffd6d6;}
    .side .box{padding:16px 16px 18px; border-top:1px solid var(--line);}
    .note{font-size:13px; color:var(--muted); line-height:1.5;}
    .copyrow{display:flex; gap:10px; align-items:center; justify-content:space-between;}
    .mono{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;}
    .small{font-size:12px; color:var(--muted);}
  </style>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <div class="brand">
      <?php
        $logoPath = 'assets/tixlokal_logo_badge.png';
        if (file_exists(__DIR__ . '/' . $logoPath)) {
          echo '<img src="' . h($logoPath) . '" alt="TixLokal logo">';
        }
      ?>
      <div>TixLokal <span class="small">/ Dummy Bank</span></div>
    </div>
    <a class="pill" href="book.php?id=<?php echo $concertId; ?>&qty=<?php echo $qty; ?>">← Back</a>
  </div>

  <div class="grid">
    <div class="card">
      <div class="hd">
        <h1>Bank Details (Demo)</h1>
        <p>Transfer manually for demo, then upload receipt for verification.</p>
      </div>
      <div class="section">
        <div class="badge">Reference: <strong class="mono"><?php echo h($refCode); ?></strong></div>
        <div style="height:12px"></div>

        <table class="table">
          <tr><td class="k">Event</td><td class="v"><?php echo h($eventName); ?></td></tr>
          <?php if ($venue !== ''): ?>
          <tr><td class="k">Venue</td><td class="v"><?php echo h($venue); ?></td></tr>
          <?php endif; ?>
          <?php if ($dateStr !== ''): ?>
          <tr><td class="k">Date</td><td class="v"><?php echo h($dateStr); ?></td></tr>
          <?php endif; ?>
          <tr><td class="k">Quantity</td><td class="v"><?php echo intval($qty); ?></td></tr>
          <tr><td class="k">Amount</td><td class="v"><?php echo h($amountFmt); ?></td></tr>
        </table>

        <div style="height:14px"></div>

        <table class="table">
          <tr><td class="k">Bank</td><td class="v">Bank Islam (Demo)</td></tr>
          <tr><td class="k">Account Name</td><td class="v">TixLokal Sdn Bhd</td></tr>
          <tr>
            <td class="k">Account No</td>
            <td class="v">
              <div class="copyrow">
                <span class="mono" id="acc">1234-5678-90</span>
                <button class="btn" type="button" onclick="copyText('1234-5678-90')">Copy</button>
              </div>
            </td>
          </tr>
        </table>

        <div class="actions">
          <a class="btn" href="dummy_receipt.php?bid=<?php echo $bid; ?>" target="_blank">Generate Dummy Receipt</a>
          <a class="btn" href="book.php?step=upload&bid=<?php echo $bid; ?>">Continue to Upload Receipt</a>
          <form method="post" style="display:inline;">
            <input type="hidden" name="demo_pay" value="1">
            <button class="btn primary" type="submit" onclick="return confirm('Simulate payment success (Demo)?');">
              Pay with Dummy Bank (Demo)
            </button>
          </form>
          <a class="btn danger" href="index.php">Cancel</a>
        </div>

        <div style="height:10px"></div>
        <div class="note">
          <b>Demo note:</b> This is a simulated transfer. Upload any image/PDF as receipt, then admin will approve.
        </div>
      </div>
    </div>

    <div class="card side">
      <div class="hd">
        <h1>How it works</h1>
        <p>Realistic flow without real banking.</p>
      </div>
      <div class="box">
        <div class="note">
          <ol style="margin:0; padding-left:18px">
            <li>Copy the account number & reference.</li>
            <li>Generate a dummy receipt (PDF) and save it.</li>
            <li>Upload receipt on the next step.</li>
            <li>Admin verifies → e-ticket is generated.</li>
          </ol>
        </div>
      </div>
      <div class="box">
        <div class="note">
          <b>Current booking status:</b><br>
          <span class="mono"><?php echo h($status ?: 'unknown'); ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function copyText(t){
  try{
    navigator.clipboard.writeText(t);
    alert("Copied: " + t);
  }catch(e){
    // fallback
    const el=document.createElement('textarea');
    el.value=t; document.body.appendChild(el);
    el.select(); document.execCommand('copy');
    document.body.removeChild(el);
    alert("Copied: " + t);
  }
}
</script>
</body>
</html>
