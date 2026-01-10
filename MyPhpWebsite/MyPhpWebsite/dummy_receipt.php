<?php
// dummy_receipt.php - Generates a printable dummy receipt (demo only)
session_start();
require_once __DIR__ . '/config.php';
if (!isset($conn) || !$conn) { die("DB connection missing."); }

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$bid = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
if ($bid <= 0) { die("Invalid booking id."); }

function table_columns($conn, $table) {
  $cols = [];
  $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
  if ($res) {
    while ($row = mysqli_fetch_assoc($res)) $cols[] = $row['Field'];
    mysqli_free_result($res);
  }
  return $cols;
}
$bookingCols = table_columns($conn, "bookings");
$colBookingId = in_array('id',$bookingCols)?'id':(in_array('booking_id',$bookingCols)?'booking_id':null);
$colConcertId = in_array('concert_id',$bookingCols)?'concert_id':(in_array('event_id',$bookingCols)?'event_id':null);
$colQty = in_array('qty',$bookingCols)?'qty':(in_array('quantity',$bookingCols)?'quantity':null);
$colTotal = in_array('total_price',$bookingCols)?'total_price':(in_array('total',$bookingCols)?'total':null);

$stmt = $conn->prepare("SELECT * FROM bookings WHERE `$colBookingId`=? LIMIT 1");
$stmt->bind_param("i",$bid);
$stmt->execute();
$res = $stmt->get_result();
$booking = $res ? $res->fetch_assoc() : null;
$stmt->close();
if(!$booking){ die("Booking not found."); }

$concertId = $colConcertId ? intval($booking[$colConcertId]) : 0;
$qty = $colQty ? intval($booking[$colQty]) : 1;
$total = $colTotal ? floatval($booking[$colTotal]) : 0.0;

$concert = null;
$concertCols = table_columns($conn, "concerts");
if(!empty($concertCols) && $concertId>0){
  $cIdCol = in_array('id',$concertCols)?'id':(in_array('concert_id',$concertCols)?'concert_id':null);
  if($cIdCol){
    $stmt = $conn->prepare("SELECT * FROM concerts WHERE `$cIdCol`=? LIMIT 1");
    $stmt->bind_param("i",$concertId);
    $stmt->execute();
    $cres = $stmt->get_result();
    $concert = $cres ? $cres->fetch_assoc() : null;
    $stmt->close();
  }
}
function pick($row,$keys,$default=''){
  if(!$row) return $default;
  foreach($keys as $k){ if(isset($row[$k]) && $row[$k]!=='' && $row[$k]!==null) return $row[$k]; }
  return $default;
}

$eventName = pick($concert,['artist','name','title','event_name'],'Concert');
$venue = pick($concert,['venue','location'],'');
$dateStr = pick($concert,['date','event_date','datetime'],'');
$refCode = "BOOKING-" . $bid;

$today = date("d/m/Y");
$time = date("H:i:s");
$receiptNo = "TXL-" . date("Ymd") . "-" . str_pad((string)$bid, 5, "0", STR_PAD_LEFT);
$amountFmt = "RM " . number_format($total, 2);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dummy Receipt | TixLokal</title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial; background:#f2f4f8; margin:0; padding:20px;}
  .paper{max-width:820px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 12px 30px rgba(0,0,0,.08); overflow:hidden;}
  .top{padding:18px 20px; background:linear-gradient(90deg,#7c3aed,#06b6d4); color:#fff; display:flex; justify-content:space-between; align-items:center;}
  .top h1{margin:0; font-size:18px; letter-spacing:.3px;}
  .top .small{opacity:.9; font-size:12px;}
  .body{padding:20px;}
  .row{display:flex; gap:16px; flex-wrap:wrap;}
  .box{flex:1; min-width:250px; border:1px solid #eef2f7; border-radius:12px; padding:14px;}
  .label{font-size:12px; color:#64748b;}
  .value{font-weight:800; margin-top:4px;}
  table{width:100%; border-collapse:collapse; margin-top:14px;}
  th,td{padding:10px; border-bottom:1px solid #eef2f7; text-align:left;}
  th{font-size:12px; color:#64748b; font-weight:800;}
  .total{font-size:18px; font-weight:900;}
  .actions{display:flex; gap:10px; justify-content:flex-end; padding:14px 20px; border-top:1px solid #eef2f7; background:#fafafa;}
  button,a{border:none; background:#111827; color:#fff; padding:10px 12px; border-radius:10px; font-weight:800; cursor:pointer; text-decoration:none;}
  a{background:#334155;}
  .muted{color:#64748b; font-size:12px; margin-top:10px;}
  @media print{
    body{background:#fff; padding:0;}
    .actions{display:none;}
    .paper{box-shadow:none; border:none; border-radius:0;}
  }
</style>
</head>
<body>
  <div class="paper">
    <div class="top">
      <div>
        <h1>TixLokal — Bank Transfer Receipt (Demo)</h1>
        <div class="small">This receipt is generated for demonstration only.</div>
      </div>
      <div class="small">Receipt No: <b><?php echo h($receiptNo); ?></b></div>
    </div>

    <div class="body">
      <div class="row">
        <div class="box">
          <div class="label">Transfer Date / Time</div>
          <div class="value"><?php echo h($today . " " . $time); ?></div>
          <div class="label" style="margin-top:10px;">Bank</div>
          <div class="value">Bank Islam (Demo)</div>
        </div>
        <div class="box">
          <div class="label">Beneficiary</div>
          <div class="value">TixLokal Sdn Bhd</div>
          <div class="label" style="margin-top:10px;">Account No</div>
          <div class="value">1234-5678-90</div>
        </div>
      </div>

      <table>
        <thead>
          <tr><th>Item</th><th>Details</th><th style="text-align:right;">Amount</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><b><?php echo h($eventName); ?></b></td>
            <td>
              <?php echo $venue ? h($venue) . "<br>" : ""; ?>
              <?php echo $dateStr ? h($dateStr) . "<br>" : ""; ?>
              Qty: <?php echo intval($qty); ?><br>
              Ref: <span style="font-family:ui-monospace,monospace;"><?php echo h($refCode); ?></span>
            </td>
            <td style="text-align:right;"><b><?php echo h($amountFmt); ?></b></td>
          </tr>
          <tr>
            <td colspan="2" style="text-align:right;"><span class="total">Total</span></td>
            <td style="text-align:right;"><span class="total"><?php echo h($amountFmt); ?></span></td>
          </tr>
        </tbody>
      </table>

      <div class="muted">
        Note: This is a dummy receipt generated by the system for FYP demonstration. Upload this PDF or take screenshot and upload as receipt.
      </div>
    </div>

    <div class="actions">
      <a href="bank_details.php?bid=<?php echo intval($bid); ?>">Back to Bank Details</a>
      <button onclick="window.print()">Print / Save as PDF</button>
    </div>
  </div>
</body>
</html>
