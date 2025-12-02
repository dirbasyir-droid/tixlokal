<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Tickets</title>
</head>
<body>

<a href="index.php">&larr; Back to Home</a>
<h1>My Tickets</h1>

<?php
$sql = "SELECT b.*, c.artist, c.venue, c.event_date FROM bookings b JOIN concerts c ON b.concert_id = c.id WHERE b.user_id='$user_id' ORDER BY b.booking_date DESC";
$res = mysqli_query($conn, $sql);
if (mysqli_num_rows($res) == 0) echo '<p>No bookings yet.</p>';

while($row = mysqli_fetch_assoc($res)):
    $status = $row['status'];
?>
    <div style="border: 1px solid #999; margin-bottom: 15px; padding: 10px;">
        <h3><?php echo $row['artist']; ?></h3>
        <p>Venue: <?php echo $row['venue']; ?></p>
        <p>Date: <?php echo $row['event_date']; ?></p>
        <p>Status: <strong><?php echo $status; ?></strong></p>
        <p>Quantity: <?php echo $row['quantity']; ?> | Paid: RM <?php echo $row['total_price']; ?></p>

        <?php if ($status == 'approved'): ?>
            <a href="view_ticket.php?id=<?php echo $row['id']; ?>">VIEW E-TICKET</a>
        <?php elseif ($status == 'pending_payment'): ?>
             <a href="book.php?step=upload&bid=<?php echo $row['id']; ?>">UPLOAD RECEIPT</a>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

</body>
</html>