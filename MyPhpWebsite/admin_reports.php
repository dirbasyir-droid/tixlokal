<?php
include 'config.php';
if ($_SESSION['role'] != 'admin') redirect('index.php');

// Calculate Totals
$rev_res = mysqli_query($conn, "SELECT SUM(total_price) as t FROM bookings WHERE status='approved'");
$rev = mysqli_fetch_assoc($rev_res)['t'] ?? 0;

$tix_res = mysqli_query($conn, "SELECT SUM(quantity) as t FROM bookings WHERE status='approved'");
$tix = mysqli_fetch_assoc($tix_res)['t'] ?? 0;

// Detailed Query
$res = mysqli_query($conn, "SELECT c.artist, c.capacity, COUNT(b.id) as txns, SUM(IF(b.status='approved', b.quantity, 0)) as sold, SUM(IF(b.status='approved', b.total_price, 0)) as rev FROM concerts c LEFT JOIN bookings b ON c.id=b.concert_id GROUP BY c.id ORDER BY rev DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reports | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-black py-3">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1 fw-bold">TixLokal <span>ADMIN</span></span>
        <div>
            <a href="admin.php" class="btn btn-outline-light btn-sm">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-warning mb-0">Sales Reports</h4>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="admin-card text-center py-4">
                <h6 class="text-muted text-uppercase fw-bold">Total Revenue</h6>
                <h1 class="display-5 text-success fw-bold mb-0">RM <?php echo number_format($rev, 2); ?></h1>
            </div>
        </div>
        <div class="col-md-6">
            <div class="admin-card text-center py-4">
                <h6 class="text-muted text-uppercase fw-bold">Tickets Sold</h6>
                <h1 class="display-5 text-white fw-bold mb-0"><?php echo $tix; ?></h1>
            </div>
        </div>
    </div>

    <!-- DETAILED TABLE -->
    <div class="admin-card">
        <div class="admin-header">Performance by Event</div>
        <table class="table">
            <thead><tr><th>Event</th><th>Cap</th><th>Sold</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php while($r=mysqli_fetch_assoc($res)): 
                    $avail = $r['capacity'] - $r['sold'];
                    $color = $avail == 0 ? 'text-danger' : 'text-success';
                ?>
                <tr>
                    <td class="fw-bold text-white"><?php echo $r['artist']; ?></td>
                    <td><?php echo $r['capacity']; ?></td>
                    <td><?php echo $r['sold']; ?> <span class="small <?php echo $color; ?>">(<?php echo $avail; ?> left)</span></td>
                    <td class="text-warning fw-bold">RM <?php echo number_format($r['rev'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>