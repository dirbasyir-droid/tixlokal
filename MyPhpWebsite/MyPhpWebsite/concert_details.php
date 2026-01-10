<?php
include 'config.php';

// Basic input hardening
$concert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($concert_id <= 0) { redirect('index.php'); }

// Fetch concert
$sql = "SELECT * FROM concerts WHERE id=$concert_id";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) { echo "Concert not found."; exit(); }
$concert = mysqli_fetch_assoc($result);

// Calculate availability
$sold_sql = "SELECT SUM(quantity) as sold FROM bookings WHERE concert_id=$concert_id AND status != 'rejected'";
$sold_res = mysqli_query($conn, $sold_sql);
$sold_data = mysqli_fetch_assoc($sold_res);
$sold_count = $sold_data['sold'] ?? 0;
$available = max(0, (int)$concert['capacity'] - (int)$sold_count);

$user_id = $_SESSION['user_id'] ?? null;

// Spotify Embed Logic (convert normal link to embed link)
$spotify_embed_url = '';
if (!empty($concert['spotify_url'])) {
    $spotify_url = trim($concert['spotify_url']);
    $spotify_embed_url = $spotify_url;

    // open.spotify.com/... -> open.spotify.com/embed/...
    if (strpos($spotify_url, 'open.spotify.com') !== false && strpos($spotify_url, '/embed/') === false) {
        $spotify_embed_url = str_replace('open.spotify.com/', 'open.spotify.com/embed/', $spotify_url);
    }

    // If it's already embed or a spotify URI, just try best effort
    if (strpos($spotify_embed_url, 'spotify:') === 0) {
        // Example: spotify:track:xxxx
        $parts = explode(':', $spotify_embed_url);
        if (count($parts) >= 3) {
            $spotify_embed_url = "https://open.spotify.com/embed/{$parts[1]}/{$parts[2]}";
        }
    }
}

// Helpers
$img = 'uploads/' . ($concert['image_url'] ?? 'placeholder.png');
$title = $concert['artist'] ?? 'Concert Details';
$venue = $concert['venue'] ?? '-';
$price = number_format((float)($concert['price'] ?? 0), 2);
$event_date_raw = $concert['event_date'] ?? '';
$event_date = $event_date_raw;
try {
    if (!empty($event_date_raw)) {
        $dt = new DateTime($event_date_raw);
        $event_date = $dt->format('D, d M Y • h:i A');
    }
} catch (Exception $e) {
    $event_date = $event_date_raw;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($title); ?> - Details</title>

  <!-- Google Font (optional, looks nicer for lecturer) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Icons (optional) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root{
      --bg: #0b1220;
      --card: rgba(255,255,255,0.06);
      --card2: rgba(255,255,255,0.10);
      --text: rgba(255,255,255,0.92);
      --muted: rgba(255,255,255,0.70);
      --line: rgba(255,255,255,0.10);
      --accent: #7c3aed; /* purple */
      --accent2: #22c55e; /* green */
      --danger: #ef4444;
      --shadow: 0 18px 45px rgba(0,0,0,.40);
      --radius: 18px;
    }
    * { box-sizing: border-box; }
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: radial-gradient(1100px 500px at 10% 0%, rgba(124,58,237,.35), transparent 60%),
                  radial-gradient(900px 500px at 90% 20%, rgba(34,197,94,.22), transparent 55%),
                  var(--bg);
      color: var(--text);
      min-height: 100vh;
    }
    a{ color: inherit; text-decoration: none; }
    .wrap{
      max-width: 1100px;
      margin: 0 auto;
      padding: 24px 16px 80px;
    }
    .topbar{
      display:flex;
      align-items:center;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 0 18px;
    }
    .crumb{
      display:flex;
      align-items:center;
      gap: 10px;
      color: var(--muted);
      font-size: 14px;
    }
    .crumb a{
      color: var(--muted);
      padding: 8px 10px;
      border: 1px solid var(--line);
      border-radius: 999px;
      background: rgba(255,255,255,0.04);
      transition: .2s;
    }
    .crumb a:hover{ border-color: rgba(255,255,255,0.22); transform: translateY(-1px); }

    .grid{
      display:grid;
      grid-template-columns: 1.6fr 1fr;
      gap: 18px;
      align-items:start;
    }
    @media (max-width: 900px){
      .grid{ grid-template-columns: 1fr; }
    }

    .hero{
      border: 1px solid var(--line);
      background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04));
      border-radius: var(--radius);
      overflow:hidden;
      box-shadow: var(--shadow);
    }
    .heroTop{
      display:grid;
      grid-template-columns: 260px 1fr;
      gap: 18px;
      padding: 18px;
    }
    @media (max-width: 650px){
      .heroTop{ grid-template-columns: 1fr; }
    }

    .poster{
      width: 100%;
      aspect-ratio: 3 / 4;
      border-radius: 14px;
      overflow:hidden;
      border: 1px solid var(--line);
      background: rgba(0,0,0,.25);
    }
    .poster img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display:block;
      transform: scale(1.01);
    }

    .title{
      margin: 0;
      font-size: 30px;
      line-height: 1.15;
      letter-spacing: -0.02em;
    }
    .sub{
      margin: 10px 0 0;
      color: var(--muted);
      display:flex;
      flex-wrap: wrap;
      gap: 10px 14px;
      font-size: 14px;
    }
    .pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 10px;
      border-radius: 999px;
      background: rgba(255,255,255,0.06);
      border: 1px solid var(--line);
    }
    .badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 10px;
      border-radius: 999px;
      border: 1px solid var(--line);
      background: rgba(255,255,255,0.05);
      font-weight: 600;
      font-size: 13px;
    }
    .badge.ok{ border-color: rgba(34,197,94,.35); background: rgba(34,197,94,.12); }
    .badge.bad{ border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.12); }

    .section{
      padding: 16px 18px 18px;
      border-top: 1px solid var(--line);
    }
    .section h3{
      margin: 0 0 10px;
      font-size: 16px;
      letter-spacing: -0.01em;
    }
    .desc{
      color: rgba(255,255,255,0.82);
      line-height: 1.7;
      font-size: 14px;
      white-space: pre-line;
    }

    .side{
      position: sticky;
      top: 16px;
    }
    .card{
      border: 1px solid var(--line);
      background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04));
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 16px;
    }
    .priceRow{
      display:flex;
      align-items: baseline;
      justify-content: space-between;
      gap: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid var(--line);
      margin-bottom: 12px;
    }
    .price{
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -0.02em;
    }
    .small{
      color: var(--muted);
      font-size: 13px;
    }

    .qty{
      display:flex;
      align-items:center;
      justify-content: space-between;
      gap: 10px;
      margin: 12px 0;
      padding: 12px;
      border: 1px solid var(--line);
      border-radius: 14px;
      background: rgba(255,255,255,0.04);
    }
    .qtyBtns{
      display:flex;
      align-items:center;
      gap: 8px;
    }
    .btnIcon{
      width: 40px;
      height: 40px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: rgba(255,255,255,0.06);
      color: var(--text);
      cursor:pointer;
      transition: .15s;
      display:inline-flex;
      align-items:center;
      justify-content:center;
    }
    .btnIcon:hover{ transform: translateY(-1px); border-color: rgba(255,255,255,0.20); }
    .qty input{
      width: 56px;
      height: 40px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: rgba(0,0,0,0.25);
      color: var(--text);
      text-align:center;
      font-weight: 700;
      outline:none;
    }

    .btn{
      width: 100%;
      padding: 12px 14px;
      border-radius: 14px;
      border: 1px solid rgba(124,58,237,.35);
      background: linear-gradient(180deg, rgba(124,58,237,0.95), rgba(124,58,237,0.80));
      color: white;
      font-weight: 700;
      cursor:pointer;
      transition: .15s;
      box-shadow: 0 12px 26px rgba(124,58,237,.25);
    }
    .btn:hover{ transform: translateY(-1px); filter: brightness(1.03); }
    .btn:disabled{
      opacity: .55;
      cursor: not-allowed;
      box-shadow: none;
    }
    .btn.secondary{
      background: rgba(255,255,255,0.06);
      border-color: var(--line);
      box-shadow:none;
    }

    .spotify{
      border: 1px solid var(--line);
      border-radius: 16px;
      overflow:hidden;
      background: rgba(0,0,0,0.25);
    }
    iframe{ width:100%; height: 152px; border:0; }
    .toast{
      margin-top: 10px;
      font-size: 13px;
      color: var(--muted);
    }
  .brand{display:flex;align-items:center;gap:10px;font-weight:900;letter-spacing:.2px;text-decoration:none}
.brandLogo{width:34px;height:34px;border-radius:10px;object-fit:contain;box-shadow:0 10px 30px rgba(0,0,0,.35);}
</style>

  <script>
    function updateQty(change) {
      const input = document.getElementById('qty');
      const maxAvailable = <?php echo (int)$available; ?>;
      let currentVal = parseInt(input.value || "1");
      let newVal = currentVal + change;

      if (newVal < 1) newVal = 1;
      if (newVal > 99) newVal = 99;
      if (newVal > maxAvailable) newVal = maxAvailable;

      input.value = newVal;
      document.getElementById('total').innerText = (newVal * <?php echo (float)$concert['price']; ?>).toFixed(2);
    }

    window.addEventListener('DOMContentLoaded', () => {
      // init total price
      const q = parseInt(document.getElementById('qty')?.value || "1");
      const total = (q * <?php echo (float)$concert['price']; ?>).toFixed(2);
      const el = document.getElementById('total');
      if (el) el.innerText = total;
    });
  </script>
</head>

<body>
  <div class="wrap">
    <div class="topbar">
      <div class="crumb">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <span>/</span>
        <span>Concert Details</span>
      </div>

      <a class="brand" href="index.php"><img src="assets/tixlokal_logo_badge.png" class="brandLogo" alt="TixLokal logo"><span>TixLokal</span></a>
    </div>

    <div class="grid">
      <!-- LEFT: Details -->
      <div class="hero">
        <div class="heroTop">
          <div class="poster">
            <img src="<?php echo htmlspecialchars($img); ?>" alt="Concert Poster">
          </div>

          <div>
            <h1 class="title"><?php echo htmlspecialchars($title); ?></h1>

            <div class="sub">
              <span class="pill"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($venue); ?></span>
              <span class="pill"><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($event_date); ?></span>
              <?php if($available > 0): ?>
                <span class="badge ok"><i class="fa-solid fa-circle-check"></i> <?php echo (int)$available; ?> left</span>
              <?php else: ?>
                <span class="badge bad"><i class="fa-solid fa-circle-xmark"></i> Sold out</span>
              <?php endif; ?>
            </div>

            <div class="section" style="padding-left:0; padding-right:0; border-top: none; margin-top: 12px;">
              <h3>About this concert</h3>
              <div class="desc"><?php echo nl2br(htmlspecialchars($concert['description'] ?? '')); ?></div>
            </div>
          </div>
        </div>

        <?php if ($spotify_embed_url): ?>
          <div class="section">
            <h3>Featured Playlist</h3>
            <div class="spotify">
              <iframe
                src="<?php echo htmlspecialchars($spotify_embed_url); ?>"
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                loading="lazy"></iframe>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: Booking card -->
      <div class="side">
        <div class="card">
          <div class="priceRow">
            <div>
              <div class="small">Ticket price</div>
              <div class="price">RM <?php echo $price; ?></div>
            </div>
            <div style="text-align:right;">
              <div class="small">Estimated total</div>
              <div style="font-weight:800; font-size:18px;">RM <span id="total">0.00</span></div>
            </div>
          </div>

          <?php if ($user_id): ?>
            <?php if($available > 0): ?>
              <form action="select_seat.php" method="GET">
  <input type="hidden" name="id" value="<?php echo (int)$concert['id']; ?>">
  <div class="small" style="margin-bottom:8px;">Seat selection</div>

  <div class="toast" style="margin-top:0;">
    Pick your seats (VIP / Regular) like a real ticketing system. Your seats will be held for a short time while you proceed.
  </div>

  <button class="btn" type="submit"><i class="fa-solid fa-chair"></i> Select Seats</button>
  <div class="toast">After seat selection, you’ll continue to booking & receipt upload.</div>
</form>
            <?php else: ?>
              <button class="btn" disabled><i class="fa-solid fa-ban"></i> Event Sold Out</button>
              <button class="btn secondary" onclick="location.href='index.php'"><i class="fa-solid fa-house"></i> Browse other concerts</button>
            <?php endif; ?>
          <?php else: ?>
            <button class="btn secondary" onclick="location.href='index.php?action=login'">
              <i class="fa-solid fa-right-to-bracket"></i> Login to book tickets
            </button>
            <div class="toast">Login required to continue booking.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
