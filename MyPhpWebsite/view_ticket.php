<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) redirect('index.php');

$bid = $_GET['id'];
$res = mysqli_query($conn, "SELECT b.*, c.artist, c.venue, c.event_date, u.name FROM bookings b JOIN concerts c ON b.concert_id=c.id JOIN users u ON b.user_id=u.id WHERE b.id='$bid' AND b.user_id='{$_SESSION['user_id']}' AND b.status='approved'");
if(mysqli_num_rows($res)==0) redirect('my_tickets.php');
$t = mysqli_fetch_assoc($res);

// Using a reliable QR code API (goqr.me is generally reliable and CORS friendly)
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($t['qr_code_data']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ticket #<?php echo $bid; ?></title>
    <!-- Load PDF generation library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; background: #eee; text-align: center; padding: 20px; }
        
        /* The Ticket Look */
        #ticket-content {
            background: #fff;
            width: 350px;
            margin: 0 auto;
            border: 1px solid #000;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden; 
            padding-bottom: 20px;
        }
        .header { background: #000; color: #fff; padding: 20px; }
        .body { padding: 20px; }
        .qr { margin: 15px auto; display: block; } /* Centered QR */
        h2 { margin: 0; text-transform: uppercase; }
        h3 { margin-top: 0; }
        .cut-line { 
            border-top: 2px dashed #ccc; 
            margin: 20px 0; 
            width: 100%;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<a href="my_tickets.php" style="text-decoration: none; font-weight: bold; color: #333;">&larr; Close</a>
<br><br>

<!-- Download Button -->
<button onclick="saveAsPDF()" style="padding: 10px 20px; font-weight: bold; cursor: pointer; font-size: 16px; background-color: #fbbf24; border: none; border-radius: 5px;">Download PDF Ticket</button>
<br><br>

<!-- This specific DIV is what gets converted to PDF -->
<div id="ticket-content">
    <div class="header">
        <h2>ADMIT ONE</h2>
        <small>TixLokal E-Ticket</small>
    </div>
    <div class="body">
        <h3><?php echo $t['artist']; ?></h3>
        <p><?php echo $t['venue']; ?></p>
        <p><strong><?php echo date('d M Y, h:i A', strtotime($t['event_date'])); ?></strong></p>
        
        <!-- QR Code Image with explicit dimensions and CORS attribute -->
        <img src="<?php echo $qr_url; ?>" class="qr" width="150" height="150" alt="QR Code" crossorigin="anonymous">
        
        <div class="cut-line"></div>
        
        <p>Holder: <strong><?php echo $t['name']; ?></strong></p>
        <p>Quantity: <strong><?php echo $t['quantity']; ?></strong></p>
        <small style="color:#666;">ID: <?php echo $t['qr_code_data']; ?></small>
    </div>
</div>

<script>
    function saveAsPDF() {
        // 1. Get the ticket element
        var element = document.getElementById('ticket-content');
        
        // 2. Set PDF options
        var opt = {
            margin:       10,
            filename:     'TixLokal-Ticket-<?php echo $bid; ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true }, // Enable CORS for images
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        // 3. Generate and Download
        html2pdf().set(opt).from(element).save();
    }
</script>

</body>
</html>