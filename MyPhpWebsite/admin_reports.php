<?php
include 'config.php';
if ($_SESSION['role'] != 'admin') redirect('index.php');

$total_rev_res = mysqli_query($conn, "SELECT SUM(total_price) as total FROM bookings WHERE status='approved'");
$total_revenue = mysqli_fetch_assoc($total_rev_res)['total'] ?? 0;

$total_tix_res = mysqli_query($conn, "SELECT SUM(quantity) as total FROM bookings WHERE status='approved'");
$total_tickets = mysqli_fetch_assoc($total_tix_res)['total'] ?? 0;

// Update SQL to include Capacity and calculate remaining
$sales_sql = "SELECT c.artist, c.venue, c.capacity, 
                     COUNT(b.id) as bookings_count, 
                     SUM(CASE WHEN b.status != 'rejected' THEN b.quantity ELSE 0 END) as tickets_sold_or_pending,
                     SUM(CASE WHEN b.status = 'approved' THEN b.total_price ELSE 0 END) as revenue 
              FROM concerts c 
              LEFT JOIN bookings b ON c.id = b.concert_id 
              GROUP BY c.id
              ORDER BY revenue DESC";
$sales_res = mysqli_query($conn, $sales_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Reports</title>
</head>
<body>

<a href="admin.php">&larr; Back to Dashboard</a>
<h1>Sales Reports</h1>

<p><strong>Total Revenue:</strong> RM <?php echo number_format($total_revenue, 2); ?></p>
<p><strong>Total Tickets Sold (Approved):</strong> <?php echo $total_tickets; ?></p>

<hr>

<h3>Inventory & Sales by Event</h3>
<table border="1" cellpadding="10" width="100%">
    <thead>
        <tr>
            <th>Event</th>
            <th>Venue</th>
            <th>Total Capacity</th>
            <th>Sold/Reserved</th>
            <th>Available</th>
            <th>Revenue (Approved)</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($sales_res)): 
            $sold_reserved = $row['tickets_sold_or_pending'] ?? 0;
            $available = $row['capacity'] - $sold_reserved;
        ?>
        <tr>
            <td><?php echo $row['artist']; ?></td>
            <td><?php echo $row['venue']; ?></td>
            <td><?php echo $row['capacity']; ?></td>
            <td><?php echo $sold_reserved; ?></td>
            
            <!-- Highlight availability -->
            <td>
                <?php if($available == 0): ?>
                    <strong style="color:red;">SOLD OUT</strong>
                <?php else: ?>
                    <strong style="color:green;"><?php echo $available; ?></strong>
                <?php endif; ?>
            </td>
            
            <td>RM <?php echo number_format($row['revenue'] ?? 0, 2); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>