<?php
include 'config.php';
if ($_SESSION['role'] != 'admin') redirect('index.php');
$error = ''; $success = '';

if (isset($_POST['add'])) {
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $date = $_POST['date'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $spotify_url = mysqli_real_escape_string($conn, $_POST['spotify_url']); // New Field
    $image_filename = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_filename = uniqid('img_', true) . '.' . $file_ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_filename);
    }
    
    // Insert with Spotify URL
    $sql = "INSERT INTO concerts (artist, venue, event_date, price, capacity, description, spotify_url, image_url) VALUES ('$artist', '$venue', '$date', '$price', '$capacity', '$description', '$spotify_url', '$image_filename')";
    if (mysqli_query($conn, $sql)) $success = "Concert published."; else $error = "Database Error: " . mysqli_error($conn);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE p FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE b.concert_id = '$id'");
    mysqli_query($conn, "DELETE FROM bookings WHERE concert_id='$id'");
    mysqli_query($conn, "DELETE FROM concerts WHERE id='$id'");
    redirect('admin.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>

<div style="background: #ddd; padding: 10px;">
    <strong>ADMIN PANEL</strong> | 
    <a href="index.php">Back to Website</a> | 
    <a href="admin_verify.php">Verify Payments</a> | 
    <a href="admin_reports.php">Sales Reports</a>
</div>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green'>$success</p>"; ?>

<h3>Add New Concert</h3>
<form method="POST" enctype="multipart/form-data" style="border:1px solid #000; padding:15px; width:400px;">
    <label>Artist:</label><br><input type="text" name="artist" required><br><br>
    <label>Venue:</label><br><input type="text" name="venue" required><br><br>
    <label>Date:</label><br><input type="datetime-local" name="date" required><br><br>
    <label>Price (RM):</label><br><input type="number" step="0.01" name="price" required><br><br>
    <label>Total Ticket Capacity:</label><br><input type="number" name="capacity" value="100" required><br><br>
    
    <!-- New Spotify Field -->
    <label>Spotify Playlist Link (Optional):</label><br>
    <input type="text" name="spotify_url" placeholder="https://open.spotify.com/playlist/..." style="width: 100%;"><br><br>
    
    <label>Image:</label><br><input type="file" name="image" required><br><br>
    <label>Description:</label><br><textarea name="description" rows="4" cols="40" required></textarea><br><br>
    <button type="submit" name="add">Publish Event</button>
</form>

<hr>

<h3>Manage Events</h3>
<table border="1" cellpadding="10" width="100%">
    <thead>
        <tr>
            <th>Artist</th>
            <th>Date</th>
            <th>Capacity</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $res = mysqli_query($conn, "SELECT id, artist, event_date, capacity FROM concerts ORDER BY event_date DESC"); 
        while($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?php echo $row['artist']; ?></td>
            <td><?php echo $row['event_date']; ?></td>
            <td><?php echo $row['capacity']; ?> Pax</td>
            <td>
                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>