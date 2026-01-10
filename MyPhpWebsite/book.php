<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');

$user_id = $_SESSION['user_id'];
$concert_id = $_GET['id'] ?? null;
$booking_id = $_GET['bid'] ?? null;
$qty = $_GET['qty'] ?? 1;
$error = '';

// ---- Payment method (demo): manual vs QR ----
$payment_method = $_GET['pm'] ?? ($_POST['pm'] ?? 'manual');
if (!in_array($payment_method, ['manual', 'qr'], true)) { $payment_method = 'manual'; }

/**
 * Generate a deterministic "dummy QR" SVG as a data URI (no external API).
 * This is ONLY for demo UI (not a real QR standard encoding).
 */
function tixlokal_dummy_qr_svg_data_uri(string $text): string {
    $hash = sha1($text);
    $size = 29; // modules
    $module = 6; // px
    $pad = 8;
    $w = $size * $module + $pad * 2;

    // Build modules using hash bits (deterministic pattern)
    $bits = '';
    for ($i = 0; $i < strlen($hash); $i++) {
        $bits .= str_pad(base_convert($hash[$i], 16, 2), 4, '0', STR_PAD_LEFT);
    }
    $bitLen = strlen($bits);
    $k = 0;

    $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}' height='{$w}' viewBox='0 0 {$w} {$w}'>"
         . "<rect width='100%' height='100%' fill='white'/>";

    // Finder-like corners (visual only)
    $finder = function($x,$y) use ($module,$pad,&$svg){
        $x = $pad + $x*$module; $y = $pad + $y*$module;
        $s = 7*$module;
        $svg .= "<rect x='{$x}' y='{$y}' width='{$s}' height='{$s}' fill='black'/>";
        $svg .= "<rect x='".($x+$module)."' y='".($y+$module)."' width='".(5*$module)."' height='".(5*$module)."' fill='white'/>";
        $svg .= "<rect x='".($x+2*$module)."' y='".($y+2*$module)."' width='".(3*$module)."' height='".(3*$module)."' fill='black'/>";
    };
    $finder(0,0); $finder($size-7,0); $finder(0,$size-7);

    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            // keep finder areas empty (already drawn)
            $inFinder = ($x < 7 && $y < 7) || ($x >= $size-7 && $y < 7) || ($x < 7 && $y >= $size-7);
            if ($inFinder) continue;

            $bit = $bits[$k % $bitLen];
            $k++;
            if ($bit === '1') {
                $rx = $pad + $x*$module;
                $ry = $pad + $y*$module;
                $svg .= "<rect x='{$rx}' y='{$ry}' width='{$module}' height='{$module}' fill='black'/>";
            }
        }
    }
    $svg .= "</svg>";
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}



// ---- Seat Selection Integration (clean implementation) ----
// Release expired seat holds (simple housekeeping)
@mysqli_query($conn, "UPDATE concert_seats 
    SET status='available', hold_until=NULL, held_by_user_id=NULL 
    WHERE status='held' AND hold_until IS NOT NULL AND hold_until < NOW()");

$seat_ids = $_SESSION['selected_seat_ids'] ?? [];
$seat_concert_id = $_SESSION['selected_seat_concert_id'] ?? null;

// If user came from seat selection, override qty for UI display
if ($concert_id && $seat_concert_id && (int)$seat_concert_id === (int)$concert_id && is_array($seat_ids) && count($seat_ids) > 0) {
    $qty = count($seat_ids);
}
// ----------------------------------------------------------

    if (isset($_POST['confirm_booking']) && $concert_id) {
        // If user selected seats, we book based on seats (VIP/Regular pricing)
        $use_seats = ($seat_concert_id && (int)$seat_concert_id === (int)$concert_id && is_array($seat_ids) && count($seat_ids) > 0);

        // Fetch Capacity
        $c_res = mysqli_query($conn, "SELECT price, capacity FROM concerts WHERE id=$concert_id");
        $c_data = mysqli_fetch_assoc($c_res);

        if (!$c_data) {
            $error = "Concert not found.";
        } else {
            $final_qty = $qty;
            $final_total = 0;

            // Basic capacity guard (keeps your existing capacity system intact)
            if ((int)$c_data['capacity'] < (int)$final_qty) {
                $error = "Not enough tickets available.";
            } else {

                // Transaction helps prevent double-booking seats
                mysqli_begin_transaction($conn);

                try {
                    // Release expired holds inside transaction as well
                    mysqli_query($conn, "UPDATE concert_seats 
                        SET status='available', hold_until=NULL, held_by_user_id=NULL 
                        WHERE status='held' AND hold_until IS NOT NULL AND hold_until < NOW()");

                    if ($use_seats) {
                        // Validate seats belong to this concert and are available/held by this user
                        $safe_ids = array_map('intval', $seat_ids);
                        $id_list = implode(',', $safe_ids);

                        $seat_res = mysqli_query($conn, "
                            SELECT id, seat_price, status, held_by_user_id, hold_until
                            FROM concert_seats
                            WHERE concert_id=".(int)$concert_id." AND id IN ($id_list)
                            FOR UPDATE
                        ");

                        $rows = [];
                        while ($r = mysqli_fetch_assoc($seat_res)) { $rows[] = $r; }

                        if (count($rows) !== count($safe_ids)) {
                            throw new Exception("Some seats are invalid.");
                        }

                        $now_ok = true;
                        foreach ($rows as $s) {
                            $st = $s['status'];
                            $held_by = (int)($s['held_by_user_id'] ?? 0);

                            if ($st === 'booked') $now_ok = false;

                            if ($st === 'held') {
                                // must be held by this user and not expired
                                if ($held_by !== (int)$user_id) $now_ok = false;
                                if (!empty($s['hold_until']) && strtotime($s['hold_until']) < time()) $now_ok = false;
                            }
                        }

                        if (!$now_ok) {
                            throw new Exception("Selected seats are no longer available. Please pick again.");
                        }

                        // Extend hold for checkout window (15 mins)
                        mysqli_query($conn, "
                            UPDATE concert_seats
                            SET status='held',
                                held_by_user_id=".(int)$user_id.",
                                hold_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                            WHERE concert_id=".(int)$concert_id." AND id IN ($id_list)
                        ");

                        // Calculate total from seat prices
                        $final_total = 0;
                        foreach ($rows as $s) { $final_total += (float)$s['seat_price']; }
                        $final_qty = count($safe_ids);
                    } else {
                        // Legacy qty-based total
                        $final_total = (float)$c_data['price'] * (int)$final_qty;
                    }

                    // Create booking

// First buyer discount (10%) - first booking for this concert
$is_first_buyer = 0;
$discount_amount = 0;

$chk_first = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM bookings WHERE concert_id=".(int)$concert_id." FOR UPDATE");
if ($chk_first) {
    $r_first = mysqli_fetch_assoc($chk_first);
    if ((int)($r_first['cnt'] ?? 0) === 0) {
        $is_first_buyer = 1;
        $discount_amount = round(((float)$final_total) * 0.10, 2);
        $final_total = ((float)$final_total) - $discount_amount;
    }
}

                    mysqli_query($conn, "INSERT INTO bookings (user_id, concert_id, quantity, total_price, is_first_buyer, status) 
                        VALUES ('".(int)$user_id."', '".(int)$concert_id."', '".(int)$final_qty."', '".(float)$final_total."', '".(int)$is_first_buyer."', 'pending_payment')");
                    $new_booking_id = mysqli_insert_id($conn);

                    // Map seats to booking (if used)
                    if ($use_seats) {
                        $safe_ids = array_map('intval', $seat_ids);
                        foreach ($safe_ids as $sid) {
                            mysqli_query($conn, "INSERT INTO booking_seats (booking_id, seat_id) VALUES ('".(int)$new_booking_id."', '".(int)$sid."')");
                        }
                    }

                    // Reduce capacity (your existing mechanism)
                    mysqli_query($conn, "UPDATE concerts SET capacity = capacity - ".(int)$final_qty." WHERE id=".(int)$concert_id);

                    mysqli_commit($conn);

                    // Prevent accidental reuse
                    unset($_SESSION['selected_seat_ids'], $_SESSION['selected_seat_concert_id']);

                    redirect("book.php?step=upload&bid=$new_booking_id");
                } catch (Exception $ex) {
                    mysqli_rollback($conn);
                    $error = $ex->getMessage();
                }
            }
        }
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
$total_display = 0;
if ($concert_id) {
    $result = mysqli_query($conn, "SELECT * FROM concerts WHERE id=$concert_id");
    $concert = mysqli_fetch_assoc($result);
    $total_display = $concert['price'] * $qty;

// First buyer preview (UI only)
$is_first_buyer_preview = 0;
$discount_preview = 0;
$subtotal_display = $total_display;
$final_total_display = $total_display;

$chk = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM bookings WHERE concert_id=".(int)$concert_id);
if ($chk) {
    $r = mysqli_fetch_assoc($chk);
    if ((int)($r['cnt'] ?? 0) === 0) {
        $is_first_buyer_preview = 1;
        $discount_preview = round($subtotal_display * 0.10, 2);
        $final_total_display = $subtotal_display - $discount_preview;
    }
}
} elseif ($booking_id) {
     $result = mysqli_query($conn, "SELECT c.*, b.total_price, b.quantity FROM concerts c JOIN bookings b ON c.id=b.concert_id WHERE b.id=$booking_id");
     $concert = mysqli_fetch_assoc($result);
     $qty = $concert['quantity'];
     $total_display = $concert['total_price'];

$is_first_buyer_preview = 0;
$discount_preview = 0;
$subtotal_display = $total_display;
$final_total_display = $total_display;
}
if (!$concert) redirect('index.php');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Booking Process</title>
  <style>
    :root{
      --bg1:#0b1020; --bg2:#0a2a3a; --glass:rgba(255,255,255,.06);
      --border:rgba(255,255,255,.12); --text:#e5e7eb; --muted:rgba(229,231,235,.72);
      --primary1:#7c3aed; --primary2:#06b6d4; --danger:#ef4444;
    }
    *{box-sizing:border-box}
    body{
      margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;
      color:var(--text);
      background:
        radial-gradient(900px 520px at 20% 10%, rgba(124,58,237,.35), transparent 60%),
        radial-gradient(820px 520px at 90% 20%, rgba(6,182,212,.25), transparent 60%),
        linear-gradient(180deg, var(--bg1), var(--bg2));
      min-height:100vh;
    }
    .wrap{max-width:1050px;margin:34px auto;padding:0 18px;}
    .topbar{
      display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;
      margin-bottom:14px;
    }
    .brand{font-weight:900;letter-spacing:.2px}
    .crumb a{color:rgba(229,231,235,.88);text-decoration:none}
    .crumb a:hover{text-decoration:underline}
    .grid{display:grid;grid-template-columns:1.25fr .75fr;gap:16px;align-items:start;}
    @media (max-width:900px){.grid{grid-template-columns:1fr;}}
    .card{
      border:1px solid var(--border);
      background:var(--glass);
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,.35);
      overflow:hidden;
    }
    .card-h{
      padding:16px 16px 10px;
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    .title{font-size:22px;font-weight:900;margin:0}
    .sub{color:var(--muted);margin:6px 0 0}
    .card-b{padding:16px;}
    .steps{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
    .step{
      display:flex;align-items:center;gap:8px;
      padding:8px 12px;border-radius:999px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.05);
      color:rgba(229,231,235,.9);font-size:13px;
    }
    .step .dot{
      width:18px;height:18px;border-radius:999px;
      display:grid;place-items:center;
      font-weight:800;font-size:12px;
    }
    .dot.active{background:rgba(124,58,237,.35);border:1px solid rgba(124,58,237,.55);}
    .dot.inactive{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);color:rgba(229,231,235,.85)}
    .alert{
      padding:10px 12px;border-radius:14px;margin-top:12px;
      border:1px solid rgba(239,68,68,.35);
      background:rgba(239,68,68,.12);
      color:rgba(255,255,255,.92);
      font-size:14px;
    }
    .table{
      width:100%;
      border-collapse:separate;
      border-spacing:0;
      overflow:hidden;
      border:1px solid rgba(255,255,255,.12);
      border-radius:14px;
    }
    .table td{
      padding:12px 12px;
      border-bottom:1px solid rgba(255,255,255,.08);
      vertical-align:top;
    }
    .table tr:last-child td{border-bottom:none;}
    .k{color:var(--muted);width:38%;font-size:13px}
    .v{font-weight:800}
    .price{font-size:22px;font-weight:950}
    .badge{
      display:inline-flex;align-items:center;gap:8px;
      padding:8px 12px;border-radius:999px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.05);
      color:rgba(229,231,235,.9); font-size:13px;
    }
    .btnRow{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
    .btn{
      appearance:none;border:none;cursor:pointer;
      padding:11px 14px;border-radius:12px;
      font-weight:900; color:var(--text);
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.14);
      text-decoration:none; display:inline-flex;align-items:center;gap:10px;
    }
    .btn:hover{filter:brightness(1.08)}
    .btnPrimary{
      background:linear-gradient(135deg,var(--primary1),var(--primary2));
      border:0;
    }
    .btnDanger{
      background:rgba(239,68,68,.12);
      border:1px solid rgba(239,68,68,.35);
    }
    .note{color:var(--muted);font-size:13px;margin-top:10px;line-height:1.45}
    .divider{height:1px;background:rgba(255,255,255,.08);margin:12px 0}
    .field{
      display:flex;align-items:center;gap:10px;
      padding:10px 12px;border-radius:14px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.06);
      width:100%;
    }
    input[type="file"]{width:100%;color:rgba(229,231,235,.85)}
    .mini{color:var(--muted);font-size:13px;line-height:1.45}
    .bank{margin:10px 0 0; padding-left:18px; color:rgba(229,231,235,.9)}
    .bank li{margin:6px 0}
  
/* ---- Payment method tabs (Manual / QR) ---- */
.payTabs{display:flex;gap:10px;flex-wrap:wrap;margin-top:4px;margin-bottom:10px}
.payTab{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:999px;
  text-decoration:none;color:#eaf0ff;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
  transition:transform .08s ease, background .15s ease, border-color .15s ease;font-weight:700;font-size:13px}
.payTab:hover{transform:translateY(-1px);background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.2)}
.payTab.active{background:linear-gradient(135deg, rgba(124,92,255,.8), rgba(0,216,255,.55));border-color:rgba(255,255,255,.22)}
.copyBtn{margin-left:8px;padding:6px 10px;border-radius:10px;border:1px solid rgba(255,255,255,.15);
  background:rgba(255,255,255,.08);color:#eaf0ff;font-weight:700;cursor:pointer}
.copyBtn:hover{background:rgba(255,255,255,.12)}
.qrBox{display:grid;grid-template-columns:220px 1fr;gap:16px;align-items:start;
  padding:14px;border-radius:16px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.18);margin-top:12px}
.qrImg{width:200px;height:200px;border-radius:14px;background:#fff;padding:10px}
.qrHint{margin-top:8px;font-size:12px;opacity:.8}
.qrRow{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.08)}
.qrRow:last-child{border-bottom:none}
.qrRow span{opacity:.8}
.qrActions{margin-top:12px}
@media (max-width: 780px){
  .qrBox{grid-template-columns:1fr}
  .qrImg{width:100%;height:auto}
}
</style>
</head>
<body>
<?php
  $title = $concert['artist'] ?? 'Concert';
  $venue = $concert['venue'] ?? '';
  $date_raw = $concert['event_date'] ?? '';
  $pretty_date = $date_raw ? date('D, d M Y • g:ia', strtotime($date_raw)) : '-';
  $is_upload = (isset($_GET['step']) && $_GET['step'] === 'upload');
?>
  <div class="wrap">
    <div class="topbar">
      <div class="brand">TixLokal</div>
      <div class="crumb">
        <a href="index.php">Home</a> / <a href="concert_details.php?id=<?php echo (int)($concert['id'] ?? $concert_id); ?>">Concert</a> / <span style="color:rgba(229,231,235,.65)">Booking</span>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <div class="card-h">
          <h1 class="title"><?php echo $is_upload ? 'Payment Verification' : 'Confirm Your Booking'; ?></h1>
          <div class="sub"><?php echo htmlspecialchars($title . ($venue ? " • $venue" : "")); ?></div>

          <div class="steps">
            <div class="step"><span class="dot <?php echo $is_upload ? 'inactive' : 'active'; ?>">1</span> Confirm</div>
            <div class="step"><span class="dot <?php echo $is_upload ? 'active' : 'inactive'; ?>">2</span> Payment</div>
            <div class="step" style="opacity:.65"><span class="dot inactive">3</span> E‑Ticket</div>
          </div>

          <?php if (!empty($error)): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
        </div>

        <div class="card-b">
          <?php if ($is_upload): ?>
            <div class="payTabs">
            <a class="payTab <?= ($payment_method==='manual'?'active':'') ?>" href="book.php?step=upload&bid=<?= (int)$booking_id ?>&pm=manual">🏦 Manual Transfer</a>
            <a class="payTab <?= ($payment_method==='qr'?'active':'') ?>" href="book.php?step=upload&bid=<?= (int)$booking_id ?>&pm=qr">📱 QR Payment</a>
          </div>

          <?php
            $refText = 'BOOKING-' . (int)$booking_id;
            $amountText = number_format((float)$final_total_display, 2);
            $qrPayload = "TIXLOKAL|REF={$refText}|AMOUNT={$amountText}";
            $dummyQr = tixlokal_dummy_qr_svg_data_uri($qrPayload);
          ?>

          <?php if ($payment_method === 'manual'): ?>
            <div class="badge">🏦 Manual Transfer (Demo)</div>
            <div class="note" style="margin-top:10px">
              <p>Please transfer <strong>RM <?php echo $amountText; ?></strong> to the account below, then upload your receipt for verification.</p>
              <ul style="margin:10px 0 0 18px; line-height:1.8">
                <li><strong>Bank:</strong> Bank Islam (Demo)</li>
                <li><strong>Account Name:</strong> TixLokal Sdn Bhd</li>
                <li>
                  <strong>Account No:</strong> 1234-5678-90
                  <button type="button" class="miniBtn" onclick="navigator.clipboard.writeText('1234-5678-90')">Copy</button>
                </li>
                <li>
                  <strong>Reference:</strong> <?php echo htmlspecialchars($refText); ?>
                  <button type="button" class="miniBtn" onclick="navigator.clipboard.writeText('<?php echo addslashes($refText); ?>')">Copy</button>
                </li>
              </ul>
            </div>
          <?php else: ?>
            <div class="badge">📱 QR Payment (Demo)</div>
            <div class="note" style="margin-top:10px">
              <p>Scan this dummy QR with any QR app (demo), then upload your receipt for verification.</p>
            </div>

            <div class="qrWrap">
              <div class="qrBox">
                <img src="<?php echo $dummyQr; ?>" alt="Dummy QR" class="qrImg">
              </div>
              <div class="qrMeta">
                <div class="qrRow"><span>Amount</span><strong>RM <?php echo $amountText; ?></strong></div>
                <div class="qrRow"><span>Merchant</span><strong>TixLokal Sdn Bhd</strong></div>
                <div class="qrRow"><span>Reference</span><strong><?php echo htmlspecialchars($refText); ?></strong></div>
                <div class="qrRow"><span>Bank</span><strong>QRPay (Demo)</strong></div>
                <div class="qrActions">
                  <a class="btn ghost" href="dummy_receipt.php?bid=<?php echo (int)$booking_id; ?>&pm=qr" target="_blank">Generate Dummy Receipt</a>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="divider"></div>

            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="bid" value="<?php echo (int)$booking_id; ?>">
              <div class="mini" style="margin-bottom:8px;font-weight:800;color:rgba(229,231,235,.9)">Upload Receipt</div>
              <div class="field">
                <span aria-hidden="true">📎</span>
                <input type="file" name="receipt" required>
              </div>

              <div class="btnRow">
                <button class="btn btnPrimary" type="submit" name="upload">Submit Verification</button>
                <a class="btn btnDanger" href="index.php">Cancel</a>
              </div>

              <div class="note">
                After submission, your booking status will be set to <strong>verification_pending</strong> and will appear in “My Tickets”.
              </div>
            </form>
          <?php else: ?>
            <table class="table">
              <tr>
                <td class="k">Event</td>
                <td class="v"><?php echo htmlspecialchars($title); ?></td>
              </tr>
              <tr>
                <td class="k">Date</td>
                <td class="v"><?php echo htmlspecialchars($pretty_date); ?></td>
              </tr>
              <tr>
                <td class="k">Quantity</td>
                <td class="v"><?php echo (int)$qty; ?></td>
              </tr>
              <?php if (!empty($is_first_buyer_preview)): ?>
<tr>
  <td class="k">Subtotal</td>
  <td class="v">RM <?php echo number_format((float)$subtotal_display, 2); ?></td>
</tr>
<tr>
  <td class="k">First Buyer Discount (10%)</td>
  <td class="v">- RM <?php echo number_format((float)$discount_preview, 2); ?></td>
</tr>
<tr>
  <td class="k">Total</td>
  <td class="v"><span class="price">RM <?php echo number_format((float)$final_total_display, 2); ?></span></td>
</tr>
<?php else: ?>
<tr>
  <td class="k">Total</td>
  <td class="v"><span class="price">RM <?php echo number_format((float)$total_display, 2); ?></span></td>
</tr>
<?php endif; ?>
            </table>

            <?php if (empty($error)): ?>
              <form method="POST" style="margin:0">
                <div class="btnRow">
                  <button class="btn btnPrimary" type="submit" name="confirm_booking">Confirm & Pay</button>
                  <a class="btn btnDanger" href="concert_details.php?id=<?php echo (int)$concert_id; ?>">Cancel</a>
                </div>
              </form>
              <div class="note">
                You will proceed to the payment step after confirmation. Ticket availability is validated again on the server to prevent overbooking.
              </div>
            <?php else: ?>
              <div class="btnRow">
                <a class="btn" href="concert_details.php?id=<?php echo (int)$concert_id; ?>">Go Back</a>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <div style="display:grid;gap:12px">
        <div class="card">
          <div class="card-b">
            <div class="badge">🔒 Secure checkout</div>
            <div class="divider"></div>
            <div class="mini">Your booking is tied to your account. E‑ticket will be generated after payment verification.</div>
            <div class="mini" style="margin-top:8px">✅ Real-time capacity check • ✅ Receipt upload • ✅ QR e‑ticket</div>
          </div>
        </div>

        <div class="card">
          <div class="card-b">
            <div style="font-weight:900">Need help?</div>
            <div class="mini" style="margin-top:6px">If something fails during demo, you can cancel and retry booking from concert details.</div>
            <div class="btnRow" style="margin-top:12px">
              <a class="btn" href="help.php">Help</a>
              <a class="btn" href="faq.php">FAQ</a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('.copyBtn');
  if(!btn) return;
  const text = btn.getAttribute('data-copy') || '';
  if(!text) return;
  if(navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(text).then(()=>{ btn.textContent='Copied'; setTimeout(()=>btn.textContent='Copy',1200); });
  } else {
    const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select();
    try{ document.execCommand('copy'); btn.textContent='Copied'; setTimeout(()=>btn.textContent='Copy',1200); }catch(_){}
    document.body.removeChild(ta);
  }
});
</script>
</body>
</html>