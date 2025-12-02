<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');

$booking_id = $_GET['id'];
$sql = "SELECT b.*, c.artist, c.venue, c.event_date, u.name as user_name 
        FROM bookings b 
        JOIN concerts c ON b.concert_id = c.id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id='$booking_id' AND b.user_id='{$_SESSION['user_id']}' AND b.status='approved'";

$res = mysqli_query($conn, $sql);
if (mysqli_num_rows($res) == 0) redirect('my_tickets.php');
$ticket = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html>
<head>
    <title>E-Ticket</title>
</head>
<body style="text-align: center; font-family: monospace;">

    <div style="border: 2px dashed #000; width: 300px; margin: 50px auto; padding: 20px;">
        <h2>TixLokal E-TICKET</h2>
        <hr>
        <h3><?php echo $ticket['artist']; ?></h3>
        <p><?php echo $ticket['venue']; ?></p>
        <p><?php echo $ticket['event_date']; ?></p>
        
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($ticket['qr_code_data']); ?>">
        
        <p><strong>Holder:</strong> <?php echo $ticket['user_name']; ?></p>
        <p><strong>Admit:</strong> <?php echo $ticket['quantity']; ?></p>
    </div>
    
    <a href="my_tickets.php">Close</a>

</body>
</html>