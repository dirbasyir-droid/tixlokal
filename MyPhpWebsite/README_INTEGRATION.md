# Integration steps (minimal, safe)

## 0) Run the SQL migration
Open phpMyAdmin -> SQL and run `seat_hold_migration.sql`.

This updates `concert_seats` to support:
- status: available / held / booked
- hold_until (DATETIME)
- held_by_user_id (INT)

## 1) Link from concert_details.php to select_seat.php
Replace your "Book Now" link/button with:

    <a href="select_seat.php?id=<?php echo (int)$concert_id; ?>" class="btn btn-primary">Select Seats</a>

(Keep your existing details page logic.)

## 2) Ensure sessions are enabled
In your config.php (or at the top of select_seat.php/book.php), ensure:

    if (session_status() === PHP_SESSION_NONE) session_start();

## 3) Update book.php to accept seats from SESSION
This upgrade stores selected seat IDs in:
    $_SESSION['selected_seat_ids']

So in book.php, before calculating total, do something like:

    $seat_ids = $_SESSION['selected_seat_ids'] ?? ($_POST['seats'] ?? []);
    if (!is_array($seat_ids)) $seat_ids = [];

Then compute total from `concert_seats` and, after payment/receipt success, mark them booked.

## 4) Admin seat map
Place `admin_seats.php` in the same folder as admin pages.
Add a link in admin navbar/menu:

    <a href="admin_seats.php">Seats</a>

admin_seats.php expects you already have an admin session/guard; add your existing guard at the top if needed.

## Suggested marking logic
- When booking is CONFIRMED (or when admin approves receipt), set seats to 'booked'.
- If user cancels, or hold expires, seats return to 'available'.

If you upload your latest `book.php` and the admin receipt-approve file, I can wire this
exactly into your approval step (best practice).
