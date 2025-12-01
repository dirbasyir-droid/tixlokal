<?php
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$action = $_GET['action'] ?? 'home';
$error = '';
$success = '';

// Calendar Logic
$cal_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$cal_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$current_date_obj = mktime(0, 0, 0, $cal_month, 1, $cal_year);
$month_name = date('F', $current_date_obj);
$prev_link = "?month=" . date('m', strtotime('-1 month', $current_date_obj)) . "&year=" . date('Y', strtotime('-1 month', $current_date_obj));
$next_link = "?month=" . date('m', strtotime('+1 month', $current_date_obj)) . "&year=" . date('Y', strtotime('+1 month', $current_date_obj));

// Search/Sort Logic
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
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    if (mysqli_num_rows($res) == 1) {
        $user = mysqli_fetch_assoc($res);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        redirect('index.php');
    } else { $error = "Invalid credentials."; $action = 'login'; }
}
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) { $error = "Email taken."; $action = 'register'; }
    else { mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')"); $success = "Registered! Login now."; $action = 'login'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TixLokal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-lg">
        <a class="navbar-brand" href="index.php">TixLokal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Concerts</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Learn More</a></li>
            </ul>
        </div>
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-globe fs-5 text-secondary"></i>
            <?php if ($user_id): ?>
                <span class="text-white small fw-bold d-none d-md-block">Hi, <?php echo $_SESSION['name']; ?></span>
                <a href="my_tickets.php" class="btn btn-sm btn-outline-light fw-bold">TICKETS</a>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php" class="btn btn-sm btn-warning fw-bold text-dark">ADMIN</a>
                <?php endif; ?>
                <a href="?action=logout" class="btn btn-sm btn-danger fw-bold">LOGOUT</a>
            <?php else: ?>
                <a href="?action=login" class="btn btn-secondary fw-bold px-3">Log In</a>
                <a href="?action=register" class="btn btn-secondary fw-bold px-3">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container-lg py-4">
    <?php if ($error): ?> <div class="alert alert-danger bg-danger text-white border-0 mb-4"><?php echo $error; ?></div> <?php endif; ?>
    <?php if ($success): ?> <div class="alert alert-success bg-success text-white border-0 mb-4"><?php echo $success; ?></div> <?php endif; ?>

    <?php if (!$user_id && ($action == 'login' || $action == 'register')): ?>
        <div class="row justify-content-center mt-5">
            <div class="col-md-5">
                <div class="card p-4 shadow-lg">
                    <h2 class="text-center fw-bold mb-4 text-white"><?php echo ($action == 'login' ? 'SIGN IN' : 'CREATE ACCOUNT'); ?></h2>
                    <form method="POST">
                        <?php if ($action == 'register'): ?>
                        <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
                        <?php endif; ?>
                        <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                        <div class="mb-4"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                        <button type="submit" name="<?php echo $action; ?>" class="btn btn-light w-100 fw-bold"><?php echo ($action == 'login' ? 'ENTER' : 'REGISTER'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>

        <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
             <div class="carousel-inner">
                 <?php
                 $banner_res = mysqli_query($conn, "SELECT * FROM concerts ORDER BY id DESC LIMIT 3");
                 $active = true;
                 if (mysqli_num_rows($banner_res) > 0) {
                     while ($banner = mysqli_fetch_assoc($banner_res)):
                 ?>
                     <div class="carousel-item <?php echo $active ? 'active' : ''; ?>">
                         <img src="uploads/<?php echo $banner['image_url'] ?? 'placeholder.png'; ?>" class="hero-img" alt="...">
                         <div class="carousel-caption hero-caption">
                             <h5 class="text-warning text-uppercase mb-0"><?php echo $banner['venue']; ?></h5>
                             <h1 class="hero-title"><?php echo $banner['artist']; ?></h1>
                             <a href="concert_details.php?id=<?php echo $banner['id']; ?>" class="btn btn-light fw-bold mt-2">GET TICKETS</a>
                         </div>
                     </div>
                 <?php $active = false; endwhile; 
                 } else { echo '<div class="carousel-item active"><div class="d-flex h-100 align-items-center justify-content-center"><h2 class="text-muted">No featured events yet</h2></div></div>'; } ?>
             </div>
             <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
             <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>

        <form method="GET" class="search-container mb-5">
            <div class="row g-0">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Search event or keyword" value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select border-start-0" name="sort" onchange="this.form.submit()">
                        <option value="date_asc" <?php if($sort_option=='date_asc') echo 'selected'; ?>>Date: Sooner</option>
                        <option value="date_desc" <?php if($sort_option=='date_desc') echo 'selected'; ?>>Date: Later</option>
                        <option value="price_asc" <?php if($sort_option=='price_asc') echo 'selected'; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php if($sort_option=='price_desc') echo 'selected'; ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <a href="index.php" class="filter-tab active text-decoration-none">Entertainment</a>
                <span class="text-light-muted ms-2 small">Sort by Relevance above</span>
            </div>
        </form>

        <div class="row">
            <div class="col-lg-8">
                <div class="row g-4">
                    <?php
                    $res = mysqli_query($conn, $sql_base);
                    if (mysqli_num_rows($res) == 0) echo '<div class="col-12 text-center text-muted py-5">No events found.</div>';
                    while ($row = mysqli_fetch_assoc($res)):
                    ?>
                    <div class="col-md-6">
                        <div class="card concert-card">
                            <img src="uploads/<?php echo $row['image_url'] ?? 'placeholder.png'; ?>" class="card-img-top" alt="Concert">
                            <div class="card-body">
                                <h5 class="fw-bold text-white mb-1"><?php echo $row['artist']; ?></h5>
                                <p class="text-light-muted small mb-3"><?php echo $row['venue']; ?></p>
                                <div class="d-flex justify-content-between align-items-end">
                                    <div class="text-light-muted small">
                                        <div class="mb-1"><i class="bi bi-clock me-1"></i> <?php echo date('h:i A', strtotime($row['event_date'])); ?></div>
                                        <div><i class="bi bi-tag me-1"></i> RM <?php echo $row['price']; ?></div>
                                    </div>
                                    <a href="concert_details.php?id=<?php echo $row['id']; ?>" class="btn btn-buy">BUY NOW</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-widget ms-lg-3">
                    <div class="sidebar-header">
                        <span class="fs-5 text-dark"><?php echo $month_name . " " . $cal_year; ?></span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-normal text-muted lh-1 text-end" style="font-size: 0.7rem;">Events this<br>month</span>
                            <a href="index.php<?php echo $prev_link; ?>" class="text-dark"><i class="bi bi-caret-left-fill fs-5"></i></a>
                            <a href="index.php<?php echo $next_link; ?>" class="text-dark"><i class="bi bi-caret-right-fill fs-5"></i></a>
                        </div>
                    </div>
                    <div class="p-2 bg-dark text-white text-center"><span class="badge bg-light text-dark">Upcoming</span></div>
                    <?php
                    $sidebar_sql = "SELECT artist, venue, event_date FROM concerts WHERE MONTH(event_date) = '$cal_month' AND YEAR(event_date) = '$cal_year' ORDER BY event_date ASC";
                    $sidebar_res = mysqli_query($conn, $sidebar_sql);
                    if (mysqli_num_rows($sidebar_res) == 0) { echo '<div class="p-3 text-center text-muted small">No events scheduled.</div>'; }
                    while ($s_row = mysqli_fetch_assoc($sidebar_res)):
                    ?>
                    <div class="event-list-item">
                        <div class="event-date-box text-dark">
                            <div><?php echo date('D', strtotime($s_row['event_date'])); ?></div>
                            <div class="fs-4"><?php echo date('d', strtotime($s_row['event_date'])); ?></div>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 1rem;"><?php echo $s_row['artist']; ?></div>
                            <div class="text-secondary small">
                                <i class="bi bi-geo-alt-fill me-1"></i><?php echo $s_row['venue']; ?><br>
                                <i class="bi bi-clock me-1"></i><?php echo date('h:i A', strtotime($s_row['event_date'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>