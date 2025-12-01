<?php
include 'config.php';
$concert_id = $_GET['id'] ?? redirect('index.php');

$sql = "SELECT * FROM concerts WHERE id=$concert_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "Concert not found.";
    exit();
}
$concert = mysqli_fetch_assoc($result);
$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $concert['artist']; ?> | TixLokal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .hero-img-container { width: 100%; overflow: hidden; border-radius: 8px; border: 1px solid #334155; }
        .hero-img { width: 100%; aspect-ratio: 1080 / 1350; object-fit: cover; display: block; }
        h1 { font-weight: 800; letter-spacing: -1px; font-size: 2rem; color: #fff; margin-bottom: 0.2rem; }
        h3 { font-weight: 600; color: #94a3b8; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .description-content p { color: #cbd5e1; line-height: 1.5; font-size: 0.9rem; margin-bottom: 1rem; }
        .price-tag { font-size: 1.5rem; font-weight: 800; color: #fbbf24; }
        .btn-primary { background-color: #fff; color: #0f172a; font-weight: 700; text-transform: uppercase; padding: 10px 0; font-size: 0.9rem; border: none; transition: all 0.2s ease; }
        .btn-primary:hover { background-color: #fbbf24; color: #000; }
        .list-group-item { background: transparent; border-color: #334155; color: #cbd5e1; padding: 0.5rem 0; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container-lg py-4">
    <div class="row">
        <div class="col-12 mb-3">
            <a href="index.php" class="text-decoration-none text-secondary fw-bold small">&larr; BACK TO CONCERTS</a>
        </div>
        <div class="col-md-5 mb-4">
            <div class="hero-img-container">
                <img src="uploads/<?php echo $concert['image_url'] ?? 'placeholder.png'; ?>" class="hero-img" alt="<?php echo $concert['artist']; ?>">
            </div>
        </div>
        <div class="col-md-7">
            <div class="ps-md-4 h-100 d-flex flex-column">
                <p class="text-secondary mb-0 fw-bold text-uppercase tracking-wider small">Live Concert</p>
                <h1><?php echo $concert['artist']; ?></h1>
                <p class="fs-6 text-white mb-0"><?php echo $concert['venue']; ?></p>
                
                <h3>About the Event</h3>
                <div class="description-content">
                    <p><?php echo nl2br($concert['description']); ?></p>
                </div>

                <ul class="list-group list-group-flush mt-2 mb-4">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Date</span>
                        <span class="text-white fw-bold"><?php echo date('F j, Y', strtotime($concert['event_date'])); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Time</span>
                        <span class="text-white fw-bold"><?php echo date('h:i A', strtotime($concert['event_date'])); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                        <span>Price</span>
                        <span class="price-tag">RM <?php echo number_format($concert['price'], 2); ?></span>
                    </li>
                </ul>

                <?php if ($user_id): ?>
                    <a href="book.php?id=<?php echo $concert['id']; ?>" class="btn btn-primary w-100">Secure Ticket</a>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn btn-outline-light w-100 py-2 fw-bold">Login to Buy</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>