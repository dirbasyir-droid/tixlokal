<?php
include 'config.php';
$concert_id = $_GET['id'] ?? redirect('index.php');

$sql = "SELECT * FROM concerts WHERE id=$concert_id";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) { echo "Concert not found."; exit(); }
$concert = mysqli_fetch_assoc($result);

// Calculate Availability
$sold_sql = "SELECT SUM(quantity) as sold FROM bookings WHERE concert_id=$concert_id AND status != 'rejected'";
$sold_res = mysqli_query($conn, $sold_sql);
$sold_data = mysqli_fetch_assoc($sold_res);
$sold_count = $sold_data['sold'] ?? 0;
$available = $concert['capacity'] - $sold_count;

$user_id = $_SESSION['user_id'] ?? null;

// Spotify Embed Logic
$spotify_embed_url = '';
if (!empty($concert['spotify_url'])) {
    // Convert standard link to embed link if necessary
    // Replaces 'open.spotify.com/' with 'open.spotify.com/embed/'
    $spotify_embed_url = str_replace('open.spotify.com/', 'open.spotify.com/embed/', $concert['spotify_url']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $concert['artist']; ?> - Details</title>
    <script>
        function updateQty(change) {
            let input = document.getElementById('qty');
            let currentVal = parseInt(input.value);
            let newVal = currentVal + change;
            
            if (newVal < 1) newVal = 1;
            if (newVal > 99) newVal = 99;
            
            let maxAvailable = <?php echo $available; ?>;
            if (newVal > maxAvailable) {
                alert("Only " + maxAvailable + " tickets left!");
                newVal = maxAvailable;
            }
            input.value = newVal;
        }
    </script>
</head>
<body>

<a href="index.php">&larr; Back to Home</a>
<hr>

<h1><?php echo $concert['artist']; ?></h1>
<h3>Venue: <?php echo $concert['venue']; ?></h3>

<img src="uploads/<?php echo $concert['image_url'] ?? 'placeholder.png'; ?>" width="300"><br><br>

<p><strong>Date:</strong> <?php echo $concert['event_date']; ?></p>
<p><strong>Price:</strong> RM <?php echo $concert['price']; ?></p>
<p><strong>Availability:</strong> 
    <?php if($available > 0): ?>
        <span style="color:green; font-weight:bold;"><?php echo $available; ?> tickets left</span>
    <?php else: ?>
        <span style="color:red; font-weight:bold;">SOLD OUT</span>
    <?php endif; ?>
</p>

<h4>Description:</h4>
<p><?php echo nl2br($concert['description']); ?></p>

<hr>

<!-- DYNAMIC SPOTIFY EMBED -->
<?php if ($spotify_embed_url): ?>
    <h3>Featured Playlist</h3>
    <iframe style="border-radius:12px" src="<?php echo $spotify_embed_url; ?>" width="100%" height="152" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
    <br><br>
    <hr>
<?php endif; ?>

<?php if ($user_id): ?>
    <h3>Book Tickets</h3>
    <?php if($available > 0): ?>
        <form action="book.php" method="GET">
            <input type="hidden" name="id" value="<?php echo $concert['id']; ?>">
            
            <label>Select Quantity:</label><br>
            <button type="button" onclick="updateQty(-1)" style="padding: 5px 10px; cursor: pointer;">-</button>
            <input type="number" id="qty" name="qty" value="1" min="1" max="99" readonly style="width: 50px; text-align: center;">
            <button type="button" onclick="updateQty(1)" style="padding: 5px 10px; cursor: pointer;">+</button>
            <br><br>

            <button type="submit">Proceed to Booking</button>
        </form>
    <?php else: ?>
        <button disabled>Event Sold Out</button>
    <?php endif; ?>
<?php else: ?>
    <p>Please <a href="index.php?action=login">Login</a> to book tickets.</p>
<?php endif; ?>

</body>
</html>