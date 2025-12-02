<?php
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$action = $_GET['action'] ?? 'home';
$error = '';
$success = '';

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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            redirect('index.php');
        } else { $error = "Invalid password."; $action = 'login'; }
    } else { $error = "User not found."; $action = 'login'; }
}

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) { $error = "Email taken."; $action = 'register'; }
    else { 
        $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_pwd')"); 
        $success = "Registered! Login now."; $action = 'login'; 
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TixLokal - Plain</title>
</head>
<body>

<!-- Navigation -->
<div style="background: #eee; padding: 10px; border-bottom: 1px solid #ccc;">
    <strong>TixLokal</strong> | 
    <a href="index.php">Home</a>
    
    <div style="float: right;">
        <?php if ($user_id): ?>
            Logged in as: <strong><?php echo $_SESSION['name']; ?></strong> |
            <a href="my_tickets.php">My Tickets</a> | 
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="admin.php">Admin Panel</a> | 
            <?php endif; ?>
            <a href="?action=logout">Logout</a>
        <?php else: ?>
            <a href="?action=login">Login</a> | 
            <a href="?action=register">Register</a>
        <?php endif; ?>
    </div>
    <div style="clear: both;"></div>
</div>

<hr>

<!-- Messages -->
<?php if ($error): ?> <p style="color: red;"><?php echo $error; ?></p> <?php endif; ?>
<?php if ($success): ?> <p style="color: green;"><?php echo $success; ?></p> <?php endif; ?>

<!-- Auth Forms -->
<?php if (!$user_id && ($action == 'login' || $action == 'register')): ?>
    <div style="border: 1px solid #000; padding: 20px; width: 300px; margin: 20px auto;">
        <h2><?php echo ($action == 'login' ? 'Login' : 'Register'); ?></h2>
        <form method="POST">
            <?php if ($action == 'register'): ?>
                <label>Name:</label><br><input type="text" name="name" required><br><br>
            <?php endif; ?>
            <label>Email:</label><br><input type="email" name="email" required><br><br>
            <label>Password:</label><br><input type="password" name="password" required><br><br>
            <button type="submit" name="<?php echo $action; ?>">Submit</button>
        </form>
    </div>
<?php else: ?>

    <!-- Featured Events (Replaced Carousel with simple list) -->
    <h3>Featured Events</h3>
    <?php
    $banner_res = mysqli_query($conn, "SELECT * FROM concerts ORDER BY id DESC LIMIT 3");
    while ($banner = mysqli_fetch_assoc($banner_res)):
    ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <h4><?php echo $banner['artist']; ?> at <?php echo $banner['venue']; ?></h4>
            <a href="concert_details.php?id=<?php echo $banner['id']; ?>">Get Tickets</a>
        </div>
    <?php endwhile; ?>

    <hr>

    <!-- Search & Sort -->
    <form method="GET">
        <input type="text" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($search_query); ?>">
        <select name="sort" onchange="this.form.submit()">
            <option value="date_asc" <?php if($sort_option=='date_asc') echo 'selected'; ?>>Date: Sooner</option>
            <option value="date_desc" <?php if($sort_option=='date_desc') echo 'selected'; ?>>Date: Later</option>
            <option value="price_asc" <?php if($sort_option=='price_asc') echo 'selected'; ?>>Price: Low to High</option>
            <option value="price_desc" <?php if($sort_option=='price_desc') echo 'selected'; ?>>Price: High to Low</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <hr>

    <!-- Main Layout: Events List & Sidebar -->
    <table width="100%" border="0">
        <tr valign="top">
            <!-- Event List -->
            <td width="70%">
                <h3>All Events</h3>
                <?php
                $res = mysqli_query($conn, $sql_base);
                if (mysqli_num_rows($res) == 0) echo '<p>No events found.</p>';
                while ($row = mysqli_fetch_assoc($res)):
                ?>
                <div style="border: 1px solid #000; padding: 15px; margin-bottom: 15px;">
                    <img src="uploads/<?php echo $row['image_url'] ?? 'placeholder.png'; ?>" width="100" style="float: left; margin-right: 15px;">
                    <h4><?php echo $row['artist']; ?></h4>
                    <p>Venue: <?php echo $row['venue']; ?></p>
                    <p>Date: <?php echo $row['event_date']; ?> | Price: RM <?php echo $row['price']; ?></p>
                    <a href="concert_details.php?id=<?php echo $row['id']; ?>">Buy Ticket</a>
                    <div style="clear: both;"></div>
                </div>
                <?php endwhile; ?>
            </td>
            
            <!-- Sidebar -->
            <td width="30%" style="padding-left: 20px;">
                <div style="border: 1px solid #ccc; padding: 10px;">
                    <h4>Calendar: <?php echo $month_name . " " . $cal_year; ?></h4>
                    <a href="index.php<?php echo $prev_link; ?>">&lt; Prev</a> | 
                    <a href="index.php<?php echo $next_link; ?>">Next &gt;</a>
                    <ul>
                        <?php
                        $sidebar_sql = "SELECT artist, venue, event_date FROM concerts WHERE MONTH(event_date) = '$cal_month' AND YEAR(event_date) = '$cal_year' ORDER BY event_date ASC";
                        $sidebar_res = mysqli_query($conn, $sidebar_sql);
                        if (mysqli_num_rows($sidebar_res) == 0) echo '<li>No events.</li>';
                        while ($s_row = mysqli_fetch_assoc($sidebar_res)):
                        ?>
                            <li>
                                <strong><?php echo date('d M', strtotime($s_row['event_date'])); ?></strong>: 
                                <?php echo $s_row['artist']; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </td>
        </tr>
    </table>

<?php endif; ?>

</body>
</html>