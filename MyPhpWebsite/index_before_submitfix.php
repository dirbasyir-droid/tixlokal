<?php
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$action = $_GET['action'] ?? 'home';
$error = ''; $success = '';

// Calendar & Search Logic
$cal_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$cal_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$current_date_obj = mktime(0, 0, 0, $cal_month, 1, $cal_year);
$month_name = date('F', $current_date_obj);
$prev_link = "?month=" . date('m', strtotime('-1 month', $current_date_obj)) . "&year=" . date('Y', strtotime('-1 month', $current_date_obj));
$next_link = "?month=" . date('m', strtotime('+1 month', $current_date_obj)) . "&year=" . date('Y', strtotime('+1 month', $current_date_obj));

$search_query = $_GET['q'] ?? '';
$sort_option = $_GET['sort'] ?? 'date_asc';
$sql_base = "SELECT * FROM concerts WHERE artist LIKE '%$search_query%' OR venue LIKE '%$search_query%'";
switch ($sort_option) {
    case 'price_asc': $sql_base .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql_base .= " ORDER BY price DESC"; break;
    case 'date_desc': $sql_base .= " ORDER BY event_date DESC"; break;
    default: $sql_base .= " ORDER BY event_date ASC"; 
}

// Auth Logic
if ($action == 'logout') { session_destroy(); redirect('index.php'); }
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($res) == 1) {
        $user = mysqli_fetch_assoc($res);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; $_SESSION['role'] = $user['role']; $_SESSION['name'] = $user['name']; redirect('index.php');
        } else { $error = "Invalid password."; $action = 'login'; }
    } else { $error = "User not found."; $action = 'login'; }
}
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']); $email = mysqli_real_escape_string($conn, $_POST['email']); $password = $_POST['password'];
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) { $error = "Email taken."; $action = 'register'; }
    else { $hashed = password_hash($password, PASSWORD_DEFAULT); mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed')"); $success = "Registered!"; $action = 'login'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TixLokal - Home</title>
    <style>
:root{
  --bg1:#0b1020;
  --bg2:#0f172a;
  --card:rgba(255,255,255,.06);
  --card2:rgba(255,255,255,.10);
  --text:#e5e7eb;
  --muted:rgba(229,231,235,.75);
  --border:rgba(255,255,255,.12);
  --accent:#7c3aed;
  --accent2:#06b6d4;
  --good:#22c55e;
  --warn:#f59e0b;
  --bad:#ef4444;
}

*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,Arial,"Apple Color Emoji","Segoe UI Emoji";
  background:
    radial-gradient(1200px 600px at 10% 0%, rgba(124,58,237,.35), transparent 60%),
    radial-gradient(900px 500px at 90% 10%, rgba(6,182,212,.25), transparent 55%),
    linear-gradient(180deg,var(--bg1),var(--bg2));
  color:var(--text);
}

a{color:inherit}
.container{max-width:1100px;margin:0 auto;padding:18px}

.topbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 16px;
  background:rgba(255,255,255,.06);
  border:1px solid var(--border);
  border-radius:16px;
  backdrop-filter: blur(10px);
  position:sticky;top:12px;z-index:50;
}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.brand-badge{
  width:34px;height:34px;border-radius:10px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  box-shadow:0 10px 30px rgba(124,58,237,.25);
}
.brand strong{letter-spacing:.2px}
.nav-links{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.nav-links a{opacity:.9;text-decoration:none}
.nav-links a:hover{opacity:1;text-decoration:underline}

.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  border:1px solid var(--border);
  background:rgba(255,255,255,.08);
  padding:10px 12px;border-radius:12px;
  text-decoration:none;cursor:pointer;
}
.btn:hover{background:rgba(255,255,255,.12)}
.btn-primary{
  border:none;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  color:white;
}
.btn-primary:hover{filter:brightness(1.05)}
.pill{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--border);
  background:rgba(255,255,255,.06);
  color:var(--muted);
  font-size:13px;
}

.hero{
  margin-top:18px;
  padding:20px;
  border:1px solid var(--border);
  background:linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.05));
  border-radius:20px;
  display:flex;gap:18px;align-items:flex-start;justify-content:space-between;
}
.hero h1{margin:0;font-size:28px;line-height:1.15}
.hero p{margin:8px 0 0;color:var(--muted)}
.hero-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}

.controls{
  margin-top:14px;
  display:flex;gap:10px;flex-wrap:wrap;align-items:center;
}
.input{
  display:flex;align-items:center;gap:10px;
  padding:10px 12px;border-radius:14px;
  border:1px solid var(--border);
  background:rgba(255,255,255,.06);
}
.input input,.input select{
  border:none;outline:none;background:transparent;color:var(--text);
  min-width:220px;
}
.input select{min-width:180px}
.input input::placeholder{color:rgba(229,231,235,.55)}
.small{font-size:13px;color:var(--muted)}

.grid{
  margin-top:18px;
  display:grid;
  grid-template-columns: 1fr 320px;
  gap:16px;
}
@media (max-width: 900px){
  .grid{grid-template-columns:1fr}
  .topbar{position:static}
}
.card{
  border:1px solid var(--border);
  background:rgba(255,255,255,.06);
  border-radius:18px;
  overflow:hidden;
}
.card-header{padding:14px 14px 0}
.card-header h2{margin:0;font-size:18px}
.card-body{padding:14px}
.card-body.no-pad{padding:0}

.events{
  display:grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap:14px;
}
@media (max-width: 760px){
  .events{grid-template-columns:1fr}
}

.event{
  border:1px solid var(--border);
  background:rgba(255,255,255,.05);
  border-radius:18px;
  overflow:hidden;
  transition: transform .15s ease, background .15s ease;
}
.event:hover{transform:translateY(-2px);background:rgba(255,255,255,.08)}
.event-img{
  width:100%;height:170px;object-fit:cover;display:block;
  background:rgba(255,255,255,.04);
}
.event-content{padding:12px}
.event-title{margin:0 0 8px;font-size:16px;line-height:1.25}
.meta{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px}
.badge{
  display:inline-flex;align-items:center;gap:6px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--border);
  background:rgba(0,0,0,.12);
  font-size:13px;color:var(--muted);
}
.badge.good{border-color:rgba(34,197,94,.35);color:#bbf7d0;background:rgba(34,197,94,.10)}
.badge.warn{border-color:rgba(245,158,11,.35);color:#fde68a;background:rgba(245,158,11,.10)}
.badge.bad{border-color:rgba(239,68,68,.35);color:#fecaca;background:rgba(239,68,68,.10)}
.event-bottom{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:10px}
.price{font-weight:700}

.carousel{
  margin-top:18px;
  border:1px solid var(--border);
  background:rgba(255,255,255,.06);
  border-radius:20px;
  overflow:hidden;
  position:relative;
}
.slide{display:none;position:relative}
.slide.active{display:block}
.slide img{width:100%;height:270px;object-fit:cover;display:block;filter:saturate(1.05)}
.caption{
  position:absolute;left:0;right:0;bottom:0;
  padding:16px;
  background:linear-gradient(180deg, transparent, rgba(0,0,0,.75));
}
.caption h3{margin:0 0 6px;font-size:20px}
.caption .row{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center}
.carousel-nav{
  position:absolute;top:50%;transform:translateY(-50%);
  width:40px;height:40px;border-radius:999px;
  border:1px solid var(--border);
  background:rgba(0,0,0,.35);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;
}
.carousel-nav:hover{background:rgba(0,0,0,.5)}
.carousel-nav.prev{left:12px}
.carousel-nav.next{right:12px}

.sidebar-item{padding:12px;border-bottom:1px solid rgba(255,255,255,.08)}
.sidebar-item:last-child{border-bottom:none}
.sidebar-item strong{display:block}
.sidebar-links{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:10px}
.sidebar-links a{text-decoration:none;opacity:.9}
.sidebar-links a:hover{text-decoration:underline;opacity:1}

.alert{
  margin-top:12px;
  padding:12px 12px;
  border-radius:14px;
  border:1px solid var(--border);
  background:rgba(255,255,255,.06);
}
.alert.error{border-color:rgba(239,68,68,.35);background:rgba(239,68,68,.10);color:#fecaca}
.alert.success{border-color:rgba(34,197,94,.35);background:rgba(34,197,94,.10);color:#bbf7d0}

/* Auth pages */
.auth-wrap{
  max-width: 980px;
  margin: 38px auto 0;
  padding: 0 18px;
}
.auth-shell{
  display:grid;
  grid-template-columns: 1.05fr .95fr;
  gap: 16px;
  align-items: stretch;
}
@media (max-width: 860px){
  .auth-shell{grid-template-columns:1fr}
}
.auth-hero{
  border:1px solid var(--border);
  background: linear-gradient(180deg, rgba(124,58,237,.20), rgba(6,182,212,.12));
  border-radius:22px;
  padding:18px;
  min-height: 320px;
}
.auth-hero h1{margin:0 0 8px;font-size:28px}
.auth-hero p{margin:0;color:var(--muted)}
.auth-card{
  border:1px solid var(--border);
  background:rgba(255,255,255,.06);
  border-radius:22px;
  padding:18px;
}
.form-row{margin-top:12px}
.label{display:block;font-size:13px;color:var(--muted);margin-bottom:6px}
.field{
  display:flex;align-items:center;gap:10px;
  padding:10px 12px;border-radius:14px;
  border:1px solid rgba(255,255,255,.14);
  background:rgba(255,255,255,.06);
}
.field input{
  width:100%;
  border:none;outline:none;background:transparent;color:var(--text);
}
.field input::placeholder{color:rgba(229,231,235,.55)}
.form-actions{margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.full{width:100%}

/* Footer */
.footer{
  margin: 28px 0 18px;
  padding: 14px 0;
  color: rgba(229,231,235,.75);
  display:flex;flex-wrap:wrap;gap:12px;justify-content:center;
}
.footer a{color:rgba(229,231,235,.8);text-decoration:none}
.footer a:hover{text-decoration:underline;color:rgba(229,231,235,1)}

</style>
</head>
<body>
<?php
// Simple route-based rendering: login/register pages or homepage
?>
<?php if ($action === 'login'): ?>
  <div class="container">
    <div class="auth-wrap">
      <div class="auth-hero">
        <div class="brand">TixLokal</div>
        <h1>Welcome back</h1>
        <p>Login to manage your bookings and access your e‑tickets.</p>
        <div class="small" style="margin-top:10px">Tip: Use a real email format for better demo credibility.</div>
      </div>

      <div class="auth-card">
        <h2 style="margin:0 0 6px">Login</h2>
        <div class="small">Don’t have an account? <a href="index.php?action=register">Create one</a>.</div>

        <?php if (!empty($error)): ?>
          <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?action=login" style="margin-top:12px">
          <div class="form-row">
            <label class="label">Email</label>
            <div class="field">
              <span class="ico">✉</span>
              <input type="email" name="email" placeholder="name@example.com" required>
            </div>
          </div>

          <div class="form-row">
            <label class="label">Password</label>
            <div class="field">
              <span class="ico">🔒</span>
              <input id="loginPass" type="password" name="password" placeholder="••••••••" required>
              <button class="btn" type="button" onclick="togglePass('loginPass', this)" style="padding:8px 12px">Show</button>
            </div>
          </div>

          <div class="form-row">
            <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Sign in</button>
          </div>

          <div class="small" style="margin-top:10px">
            <a href="index.php">← Back to Home</a>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php elseif ($action === 'register'): ?>
  <div class="container">
    <div class="auth-wrap">
      <div class="auth-hero">
        <div class="brand">TixLokal</div>
        <h1>Create your account</h1>
        <p>Register once, then book tickets faster with stored details.</p>
      </div>

      <div class="auth-card">
        <h2 style="margin:0 0 6px">Register</h2>
        <div class="small">Already have an account? <a href="index.php?action=login">Login</a>.</div>

        <?php if (!empty($error)): ?>
          <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?action=register" style="margin-top:12px">
          <div class="form-row">
            <label class="label">Name</label>
            <div class="field">
              <span class="ico">👤</span>
              <input type="text" name="name" placeholder="Your full name" required>
            </div>
          </div>

          <div class="form-row">
            <label class="label">Email</label>
            <div class="field">
              <span class="ico">✉</span>
              <input type="email" name="email" placeholder="name@example.com" required>
            </div>
          </div>

          <div class="form-row">
            <label class="label">Password</label>
            <div class="field">
              <span class="ico">🔒</span>
              <input id="regPass" type="password" name="password" placeholder="Min 6 characters" required>
              <button class="btn" type="button" onclick="togglePass('regPass', this)" style="padding:8px 12px">Show</button>
            </div>
          </div>

          <div class="form-row">
            <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Create account</button>
          </div>

          <div class="small" style="margin-top:10px">
            <a href="index.php">← Back to Home</a>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php else: ?>


<!-- Navigation -->
<div class="container">
  <div class="topbar">
    <a class="brand" href="index.php">
      <span class="brand-badge" aria-hidden="true"></span>
      <strong>TixLokal</strong>
    </a>

    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="help.php">Help</a>
      <a href="faq.php">FAQ</a>
      <a href="terms.php">Terms</a>
      <a href="about.php">About</a>
      <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="admin.php">Admin</a>
      <?php endif; ?>
      <span style="opacity:.45">|</span>

      <?php if ($user_id): ?>
        <span class="pill">Hi, <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></span>
        <a class="btn" href="my_tickets.php">My Tickets</a>
        <a class="btn" href="?action=logout">Logout</a>
      <?php else: ?>
        <a class="btn" href="index.php?action=login">Login</a>
        <a class="btn btn-primary" href="index.php?action=register">Register</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="container">
  <div class="hero">
    <div>
      <h1>Discover concerts near you</h1>
      <p>Browse upcoming events, compare prices, and secure your tickets in seconds.</p>

      <div class="controls">
        <form method="get" action="index.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
          <div class="input" title="Search by artist or venue">
            <span aria-hidden="true">🔎</span>
            <input type="text" name="q" placeholder="Search artist / venue..." value="<?php echo htmlspecialchars($search_query); ?>">
          </div>

          <div class="input" title="Sort events">
            <span aria-hidden="true">↕️</span>
            <select name="sort">
              <option value="date_asc" <?php echo ($sort_option == 'date_asc' ? 'selected' : ''); ?>>Date (Soonest)</option>
              <option value="date_desc" <?php echo ($sort_option == 'date_desc' ? 'selected' : ''); ?>>Date (Latest)</option>
              <option value="price_asc" <?php echo ($sort_option == 'price_asc' ? 'selected' : ''); ?>>Price (Low → High)</option>
              <option value="price_desc" <?php echo ($sort_option == 'price_desc' ? 'selected' : ''); ?>>Price (High → Low)</option>
            </select>
          </div>

          <button class="btn btn-primary" type="submit">Search</button>
          <a class="btn" href="index.php">Reset</a>
        </form>
        <div class="small">Tip: Try “Vox Live” or “53 Universe”.</div>
      </div>
    </div>

    <div class="hero-actions">
      <span class="pill">Secure checkout</span>
      <span class="pill">QR e-ticket</span>
      <span class="pill">Real-time availability</span>
    </div>
  </div>

  <!-- Featured carousel -->
  <div class="carousel" aria-label="Featured concerts">
    <?php
      $banner_res = mysqli_query($conn, "SELECT * FROM concerts ORDER BY id DESC LIMIT 3");
      $count = 0;
      while ($banner = mysqli_fetch_assoc($banner_res)):
        $count++;
        $banner_title = ($banner['artist'] ?? 'Concert');
        $banner_date = !empty($banner['event_date']) ? date('D, d M Y • g:ia', strtotime($banner['event_date'])) : '';
        $banner_venue = $banner['venue'] ?? '';
    ?>
      <div class="slide <?php echo $count === 1 ? 'active' : ''; ?>">
        <img src="uploads/<?php echo htmlspecialchars($banner['image_url'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($banner_title); ?>">
        <div class="caption">
          <h3><?php echo htmlspecialchars($banner_title); ?></h3>
          <div class="row">
            <span class="pill"><?php echo htmlspecialchars(trim($banner_date . ($banner_venue ? " • " . $banner_venue : ""))); ?></span>
            <a class="btn btn-primary" href="concert_details.php?id=<?php echo (int)$banner['id']; ?>">Get Tickets →</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>

    <button class="carousel-nav prev" type="button" aria-label="Previous">‹</button>
    <button class="carousel-nav next" type="button" aria-label="Next">›</button>
  </div>

  <div class="grid">
    <!-- Main events -->
    <div class="card">
      <div class="card-header">
        <h2>Upcoming Events</h2>
      </div>

      <div class="card-body">
        <?php
          $res = mysqli_query($conn, $sql_base);
          if (!$res || mysqli_num_rows($res) === 0):
        ?>
          <div class="alert">No events found. Try a different keyword.</div>
        <?php else: ?>
          <div class="events">
            <?php while ($row = mysqli_fetch_assoc($res)): 
              $title = $row['artist'] ?? 'Concert';
              $venue = $row['venue'] ?? '-';
              $date  = !empty($row['event_date']) ? date('D, d M Y • g:ia', strtotime($row['event_date'])) : '-';
              $price = isset($row['price']) ? number_format((float)$row['price'], 2) : '0.00';
              $avail = (int)($row['availability'] ?? 0);

              if ($avail <= 0) { $badge_class='bad'; $badge_text='Sold Out'; }
              else if ($avail <= 20) { $badge_class='warn'; $badge_text=$avail.' left'; }
              else { $badge_class='good'; $badge_text=$avail.' left'; }
            ?>
              <div class="event">
                <img class="event-img" src="uploads/<?php echo htmlspecialchars($row['image_url'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($title); ?>">
                <div class="event-content">
                  <h3 class="event-title"><?php echo htmlspecialchars($title); ?></h3>

                  <div class="meta">
                    <span class="badge"><?php echo htmlspecialchars($venue); ?></span>
                    <span class="badge"><?php echo htmlspecialchars($date); ?></span>
                    <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($badge_text); ?></span>
                  </div>

                  <div class="event-bottom">
                    <div class="price">RM <?php echo $price; ?></div>
                    <a class="btn btn-primary" href="concert_details.php?id=<?php echo (int)$row['id']; ?>">View Details</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Sidebar calendar -->
    <div class="card">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
          <div>
            <div class="small">Calendar</div>
            <div style="font-weight:800;font-size:18px;"><?php echo htmlspecialchars($month_name . " " . $cal_year); ?></div>
          </div>
          <div class="sidebar-links">
            <a class="btn" href="index.php<?php echo $prev_link; ?>">‹ Prev</a>
            <a class="btn" href="index.php<?php echo $next_link; ?>">Next ›</a>
          </div>
        </div>

        <div style="margin-top:12px;border-top:1px solid rgba(255,255,255,.10)"></div>

        <?php
          $sidebar_sql = "SELECT artist, venue, event_date FROM concerts WHERE MONTH(event_date) = '$cal_month' AND YEAR(event_date) = '$cal_year' ORDER BY event_date ASC";
          $side_res = mysqli_query($conn, $sidebar_sql);
          if (!$side_res || mysqli_num_rows($side_res) === 0):
        ?>
          <div class="sidebar-item">
            <div class="small">No events in this month.</div>
          </div>
        <?php else: ?>
          <?php while ($s_row = mysqli_fetch_assoc($side_res)): ?>
            <div class="sidebar-item">
              <strong><?php echo date('d M', strtotime($s_row['event_date'])); ?></strong>
              <div class="small"><?php echo htmlspecialchars($s_row['artist']); ?></div>
              <div class="small"><?php echo htmlspecialchars($s_row['venue']); ?></div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- FOOTER LINKS -->

    <div class="footer">
        <a href="help.php">Help & Support</a> |
        <a href="about.php">About Us</a> |
        <a href="terms.php">Terms of Service</a> |
        <a href="faq.php">FAQ</a>
    </div>


<?php endif; ?>


<script>
  function togglePass(id, btn){
    const el = document.getElementById(id);
    if(!el) return;
    if(el.type === 'password'){ el.type='text'; btn.textContent='Hide'; }
    else { el.type='password'; btn.textContent='Show'; }
  }
</script>

<script>
  (function(){
    const slides = Array.from(document.querySelectorAll('.carousel .slide'));
    const prevBtn = document.querySelector('.carousel .carousel-nav.prev');
    const nextBtn = document.querySelector('.carousel .carousel-nav.next');
    if (!slides.length) return;

    let idx = 0;
    const show = (n) => {
      slides.forEach((s,i)=>s.classList.toggle('active', i===n));
      idx = n;
    };
    const next = () => show((idx + 1) % slides.length);
    const prev = () => show((idx - 1 + slides.length) % slides.length);

    nextBtn && nextBtn.addEventListener('click', next);
    prevBtn && prevBtn.addEventListener('click', prev);

    // Auto-slide
    setInterval(next, 5500);
  })();
</script>

</body>
</html>