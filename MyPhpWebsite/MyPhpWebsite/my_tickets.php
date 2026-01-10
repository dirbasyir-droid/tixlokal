<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');
$user_id = (int)$_SESSION['user_id'];
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
    .top{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
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

    .panel{
      border:1px solid var(--border);
      background:var(--glass);
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,.35);
      overflow:hidden;
    }

    .controls{
      display:flex;gap:10px;flex-wrap:wrap;align-items:center;
      padding:14px;
      border-bottom:1px solid rgba(255,255,255,.08);
      background:rgba(255,255,255,.03);
    }
    .field{
      display:flex;align-items:center;gap:10px;
      padding:10px 12px;border-radius:14px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.06);
      flex:1;min-width:220px;
    }
    .field input{
      width:100%;
      border:0;outline:0;background:transparent;
      color:var(--text);font-weight:700;
    }
    .select{
      padding:10px 12px;border-radius:14px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.06);
      color:var(--text);font-weight:800;
      outline:0;
      min-width:170px;
    }
    .btn{
      appearance:none;border:none;cursor:pointer;
      padding:10px 14px;border-radius:12px;
      font-weight:900; text-decoration:none;
      color:var(--text);
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.14);
      display:inline-flex;align-items:center;gap:10px;
      white-space:nowrap;
    }
    .btn:hover{filter:brightness(1.08)}
    .btn.primary{
      background:linear-gradient(135deg,var(--primary1),var(--primary2));
      border:0;
    }
    .btn.ghost{background:transparent}

    .tableWrap{width:100%;overflow:auto;}
    table{width:100%;border-collapse:separate;border-spacing:0;}
    thead th{
      text-align:left;
      font-size:12px;
      letter-spacing:.08em;
      text-transform:uppercase;
      color:rgba(229,231,235,.72);
      padding:14px;
      background:rgba(255,255,255,.04);
      border-bottom:1px solid rgba(255,255,255,.08);
      white-space:nowrap;
    }
    tbody td{
      padding:14px;
      border-bottom:1px solid rgba(255,255,255,.08);
      vertical-align:middle;
      white-space:nowrap;
    }
    tbody tr:hover{background:rgba(255,255,255,.04);}
    tbody tr:last-child td{border-bottom:none;}
    .eventCell{white-space:normal;min-width:280px;}
    .cellTitle{font-weight:950;}
    .cellSub{margin-top:4px;font-size:12px;color:var(--muted);}

    .badge{
      display:inline-flex;align-items:center;
      padding:7px 10px;border-radius:999px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.06);
      font-size:12px;font-weight:900;
    }
    .badge.ok{border-color:rgba(34,197,94,.45);background:rgba(34,197,94,.12)}
    .badge.warn{border-color:rgba(245,158,11,.45);background:rgba(245,158,11,.12)}
    .badge.bad{border-color:rgba(239,68,68,.45);background:rgba(239,68,68,.12)}

    .empty{
      border:1px dashed rgba(255,255,255,.22);
      background:rgba(255,255,255,.04);
      border-radius:18px;
      padding:22px;
      color:var(--muted);
    }
    .footer-note{margin-top:16px;color:var(--muted);font-size:13px}

    @media (max-width:760px){
      thead{display:none;}
      tbody tr{display:block;border-bottom:1px solid rgba(255,255,255,.08);}
      tbody td{
        display:flex;justify-content:space-between;gap:12px;
        white-space:normal;
      }
      tbody td::before{
        content:attr(data-label);
        color:var(--muted);
        font-size:12px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.06em;
      }
      .eventCell{min-width:unset;}
      .controls .field{min-width:100%;}
      .controls .select{flex:1;min-width:unset;}
    }
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

<?php
$sql = "SELECT b.*, c.artist, c.venue, c.event_date
        FROM bookings b
        JOIN concerts c ON b.concert_id = c.id
        WHERE b.user_id={$user_id}
        ORDER BY b.booking_date DESC";
$res = mysqli_query($conn, $sql);
$hasRows = ($res && mysqli_num_rows($res) > 0);

if (!$hasRows) {
?>
  <div class="empty">
    <div style="font-weight:900;color:var(--text);font-size:16px;">No tickets yet</div>
    <div style="margin-top:6px;">Browse events and book your first concert ticket.</div>
    <div style="margin-top:14px;">
      <a class="btn primary" href="index.php">🎫 Explore Events</a>
    </div>
  </div>
<?php
} else {
?>
  <div class="panel">
    <div class="controls">
      <div class="field" title="Search event / venue / seats">
        🔎 <input id="q" type="text" placeholder="Search event / venue / seats..." autocomplete="off" />
      </div>

      <select id="status" class="select" title="Filter status">
        <option value="all">All Status</option>
        <option value="approved">Approved</option>
        <option value="pending_payment">Pending Payment</option>
        <option value="rejected">Rejected</option>
      </select>

      <select id="sort" class="select" title="Sort">
        <option value="date_desc">Sort: Date (Newest)</option>
        <option value="date_asc">Sort: Date (Oldest)</option>
        <option value="total_desc">Sort: Total (High)</option>
        <option value="total_asc">Sort: Total (Low)</option>
      </select>

      <button class="btn" id="reset" type="button">Reset</button>
    </div>

    <div class="tableWrap">
      <table id="tixTable">
        <thead>
          <tr>
            <th>Event</th>
            <th>Date</th>
            <th>Qty</th>
            <th>Seats</th>
            <th>Total Paid</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
<?php
  while ($row = mysqli_fetch_assoc($res)) {
    $booking_id = (int)$row['id'];
    $status = (string)$row['status'];

    $badgeClass = 'bad';
    if ($status === 'approved') $badgeClass = 'ok';
    elseif ($status === 'pending_payment') $badgeClass = 'warn';

    // Seat list (optional)
    $seat_codes = [];
    $sres = @mysqli_query($conn, "SELECT cs.seat_code
                                  FROM booking_seats bs
                                  JOIN concert_seats cs ON cs.id=bs.seat_id
                                  WHERE bs.booking_id={$booking_id}
                                  ORDER BY cs.seat_code ASC");
    if ($sres) {
      while ($sr = mysqli_fetch_assoc($sres)) { $seat_codes[] = $sr['seat_code']; }
    }
    $seats_text = !empty($seat_codes) ? implode(', ', $seat_codes) : '—';

    // Total
    $total = isset($row['total_price']) ? (float)$row['total_price'] : 0.0;

    // Date sortable (use event_date if valid)
    $event_date_raw = $row['event_date'] ?? '';
    $event_ts = strtotime($event_date_raw);
    $event_ts = $event_ts ? $event_ts : 0;

    $search_blob = strtolower(($row['artist'] ?? '').' '.($row['venue'] ?? '').' '.$event_date_raw.' '.$seats_text.' '.$status);
?>
          <tr class="tixRow"
              data-status="<?php echo htmlspecialchars($status); ?>"
              data-total="<?php echo htmlspecialchars(number_format($total, 2, '.', '')); ?>"
              data-date="<?php echo (int)$event_ts; ?>"
              data-search="<?php echo htmlspecialchars($search_blob); ?>">
            <td class="eventCell" data-label="Event">
              <div class="cellTitle"><?php echo htmlspecialchars($row['artist'] ?? ''); ?></div>
              <div class="cellSub">📍 <?php echo htmlspecialchars($row['venue'] ?? ''); ?></div>
            </td>
            <td data-label="Date"><?php echo htmlspecialchars($event_date_raw); ?></td>
            <td data-label="Qty" style="text-align:right;"><?php echo (int)($row['quantity'] ?? 1); ?></td>
            <td data-label="Seats"><?php echo htmlspecialchars($seats_text); ?></td>
            <td data-label="Total Paid"><b>RM <?php echo number_format($total, 2); ?></b></td>
            <td data-label="Status">
              <span class="badge <?php echo $badgeClass; ?>"><?php echo strtoupper(str_replace('_',' ',htmlspecialchars($status))); ?></span>
            </td>
            <td data-label="Action">
              <?php if ($status === 'approved') { ?>
                <a class="btn primary" href="view_ticket.php?id=<?php echo $booking_id; ?>">🎟️ View E‑Ticket</a>
              <?php } elseif ($status === 'pending_payment') { ?>
                <a class="btn primary" href="upload_receipt.php?booking_id=<?php echo $booking_id; ?>">⬆️ Upload Receipt</a>
              <?php } else { ?>
                <a class="btn ghost" href="concert_details.php?id=<?php echo (int)$row['concert_id']; ?>">View Event</a>
              <?php } ?>
            </td>
          </tr>
<?php
  } // end while
?>
        </tbody>
      </table>
    </div>
  </div>
<?php
} // end hasRows
?>
  <div class="footer-note">
    Tip: Use search + filter to quickly find bookings. Approved tickets will show an e‑ticket button.
  </div>
</div>

<script>
(function(){
  const q = document.getElementById('q');
  const status = document.getElementById('status');
  const sort = document.getElementById('sort');
  const reset = document.getElementById('reset');
  const tbody = document.querySelector('#tixTable tbody');

  function apply(){
    const query = (q.value || '').trim().toLowerCase();
    const st = status.value;

    // filter
    const rows = Array.from(tbody.querySelectorAll('.tixRow'));
    rows.forEach(r => {
      const okStatus = (st === 'all') || (r.dataset.status === st);
      const okQuery = !query || (r.dataset.search && r.dataset.search.indexOf(query) !== -1);
      r.style.display = (okStatus && okQuery) ? '' : 'none';
    });

    // sort (only visible rows)
    const visible = rows.filter(r => r.style.display !== 'none');
    const mode = sort.value;

    visible.sort((a,b)=>{
      if(mode === 'date_desc') return (parseInt(b.dataset.date||'0',10) - parseInt(a.dataset.date||'0',10));
      if(mode === 'date_asc')  return (parseInt(a.dataset.date||'0',10) - parseInt(b.dataset.date||'0',10));
      if(mode === 'total_desc') return (parseFloat(b.dataset.total||'0') - parseFloat(a.dataset.total||'0'));
      if(mode === 'total_asc')  return (parseFloat(a.dataset.total||'0') - parseFloat(b.dataset.total||'0'));
      return 0;
    });

    // append in new order (keep hidden rows at bottom unchanged)
    visible.forEach(r => tbody.appendChild(r));
  }

  ['input','change'].forEach(ev=>{
    q.addEventListener(ev, apply);
  });
  status.addEventListener('change', apply);
  sort.addEventListener('change', apply);

  reset.addEventListener('click', ()=>{
    q.value = '';
    status.value = 'all';
    sort.value = 'date_desc';
    apply();
  });

  apply();
})();
</script>
</body>
</html>
