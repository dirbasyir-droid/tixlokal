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
        /* Minimal Reset */
        body { font-family: sans-serif; margin: 0; padding: 20px; padding-bottom: 60px; position: relative; min-height: 100vh; box-sizing: border-box; }
        
        /* CAROUSEL CSS START */
        .carousel-container {
            max-width: 1200px;
            height: 500px; /* Fixed Height */
            margin: 0 auto 30px auto;
            position: relative;
            overflow: hidden;
            background: #000;
            border-radius: 8px;
        }
        .carousel-slide {
            display: none;
            width: 100%;
            height: 100%;
            position: relative;
        }
        .carousel-slide.active {
            display: block;
            animation: fadeEffect 1s;
        }
        .carousel-img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures image fills box without stretching */
            opacity: 0.8;
        }
        .carousel-caption {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: #fff;
            background: rgba(0, 0, 0, 0.6);
            padding: 15px;
            border-radius: 4px;
            max-width: 50%;
        }
        .carousel-caption h2 { margin: 0 0 5px 0; font-size: 2rem; }
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.3);
            color: white;
            border: none;
            padding: 15px;
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }
        .carousel-btn:hover { background: rgba(255, 255, 255, 0.8); color: black; }
        .prev { left: 0; }
        .next { right: 0; }

        @keyframes fadeEffect {
            from {opacity: .4} 
            to {opacity: 1}
        }
        /* CAROUSEL CSS END */

        /* Basic Layout Helpers */
        .layout-table { width: 100%; border-collapse: collapse; }
        .layout-table td { vertical-align: top; }
        .sidebar { background: #f4f4f4; padding: 15px; border: 1px solid #ddd; }
        .event-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .nav-bar { background: #eee; padding: 10px; margin-bottom: 20px; border-bottom: 1px solid #ccc; }
        
        /* Footer */
        .footer {
            margin-top: 50px;
            padding: 20px;
            background: #eee;
            border-top: 1px solid #ccc;
            text-align: center;
        }
        .footer a {
            margin: 0 10px;
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<!-- Plain Navigation -->
<div class="nav-bar">
    <strong>TixLokal</strong> | <a href="index.php">Home</a>
    <div style="float: right;">
        <?php if ($user_id): ?>
            User: <strong><?php echo $_SESSION['name']; ?></strong> |
            <a href="my_tickets.php">My Tickets</a> | 
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="admin.php">Admin Panel</a> | 
            <?php endif; ?>
            <a href="?action=logout">Logout</a>
        <?php else: ?>
            <a href="?action=login">Login</a> | <a href="?action=register">Register</a>
        <?php endif; ?>
    </div>
    <div style="clear: both;"></div>
</div>

<?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:green;"><?php echo $success; ?></p><?php endif; ?>

<?php if (!$user_id && ($action == 'login' || $action == 'register')): ?>
    <!-- Login/Register Form -->
    <div style="border: 1px solid #ccc; padding: 20px; width: 300px; margin: 50px auto;">
        <h2><?php echo ($action == 'login' ? 'Login' : 'Register'); ?></h2>
        <form method="POST">
            <?php if ($action == 'register'): ?>
                <label>Name:</label><br><input type="text" name="name" style="width: 100%;" required><br><br>
            <?php endif; ?>
            <label>Email:</label><br><input type="email" name="email" style="width: 100%;" required><br><br>
            <label>Password:</label><br><input type="password" name="password" style="width: 100%;" required><br><br>
            <button type="submit" name="<?php echo $action; ?>">Submit</button>
        </form>
    </div>
<?php else: ?>

    <!-- SLIDING CAROUSEL -->
    <div class="carousel-container">
        <?php
        $banner_res = mysqli_query($conn, "SELECT * FROM concerts ORDER BY id DESC LIMIT 3");
        $count = 0;
        while ($banner = mysqli_fetch_assoc($banner_res)):
            $count++;
        ?>
        <div class="carousel-slide <?php echo $count === 1 ? 'active' : ''; ?>">
            <img src="uploads/<?php echo $banner['image_url'] ?? 'placeholder.png'; ?>" class="carousel-img">
            <div class="carousel-caption">
                <h2><?php echo $banner['artist']; ?></h2>
                <p><?php echo $banner['venue']; ?> • <?php echo date('d M Y', strtotime($banner['event_date'])); ?></p>
                <a href="concert_details.php?id=<?php echo $banner['id']; ?>" style="color: #fbbf24; font-weight: bold; text-decoration: none;">GET TICKETS &rarr;</a>
            </div>
        </div>
        <?php endwhile; ?>
        
        <button class="carousel-btn prev" onclick="moveSlide(-1)">&#10094;</button>
        <button class="carousel-btn next" onclick="moveSlide(1)">&#10095;</button>
    </div>

    <!-- Search & Filter -->
    <div style="max-width: 1200px; margin: 0 auto;">
        <form method="GET" style="margin-bottom: 20px;">
            <input type="text" name="q" placeholder="Search events..." value="<?php echo htmlspecialchars($search_query); ?>" style="padding: 5px; width: 300px;">
            <select name="sort" onchange="this.form.submit()" style="padding: 5px;">
                <option value="date_asc" <?php if($sort_option=='date_asc') echo 'selected'; ?>>Date: Sooner</option>
                <option value="price_asc" <?php if($sort_option=='price_asc') echo 'selected'; ?>>Price: Low to High</option>
            </select>
            <button type="submit" style="padding: 5px 10px;">Search</button>
        </form>

        <!-- Main Layout Table -->
        <table class="layout-table">
            <tr>
                <!-- Events Column -->
                <td style="padding-right: 20px;">
                    <h3>Upcoming Events</h3>
                    <?php
                    $res = mysqli_query($conn, $sql_base);
                    if (mysqli_num_rows($res) == 0) echo '<p>No events found.</p>';
                    while ($row = mysqli_fetch_assoc($res)):
                    ?>
                    <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; display: flex; gap: 15px;">
                        <img src="uploads/<?php echo $row['image_url'] ?? 'placeholder.png'; ?>" style="width: 120px; height: 160px; object-fit: cover;">
                        <div>
                            <h3 style="margin-top: 0;"><?php echo $row['artist']; ?></h3>
                            <p style="color: #666;"><?php echo $row['venue']; ?></p>
                            <p><strong>RM <?php echo number_format($row['price'], 2); ?></strong> | <?php echo date('d M Y, h:i A', strtotime($row['event_date'])); ?></p>
                            <a href="concert_details.php?id=<?php echo $row['id']; ?>">View Details</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </td>

                <!-- Sidebar Column -->
                <td width="300">
                    <div class="sidebar">
                        <h4><?php echo $month_name . " " . $cal_year; ?></h4>
                        <div style="margin-bottom: 10px;">
                            <a href="index.php<?php echo $prev_link; ?>">&lt; Prev</a> | 
                            <a href="index.php<?php echo $next_link; ?>">Next &gt;</a>
                        </div>
                        <hr>
                        <?php
                        $sidebar_sql = "SELECT artist, venue, event_date FROM concerts WHERE MONTH(event_date) = '$cal_month' AND YEAR(event_date) = '$cal_year' ORDER BY event_date ASC";
                        $sidebar_res = mysqli_query($conn, $sidebar_sql);
                        if (mysqli_num_rows($sidebar_res) == 0) echo '<p>No events.</p>';
                        while ($s_row = mysqli_fetch_assoc($sidebar_res)):
                        ?>
                        <div style="margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                            <strong><?php echo date('d M', strtotime($s_row['event_date'])); ?></strong><br>
                            <?php echo $s_row['artist']; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </td>
            </tr>
        </table>
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
    // Vanilla JS Carousel Logic
    let slideIndex = 1;
    showSlides(slideIndex);
    
    // Auto slide every 5 seconds
    let autoSlide = setInterval(() => { moveSlide(1) }, 5000);

    function moveSlide(n) {
        clearInterval(autoSlide); // Reset timer on manual click
        showSlides(slideIndex += n);
        autoSlide = setInterval(() => { moveSlide(1) }, 5000);
    }

    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("carousel-slide");
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
            slides[i].classList.remove("active");
        }
        slides[slideIndex-1].style.display = "block";
        slides[slideIndex-1].classList.add("active");
    }
</script>

</body>
</html>