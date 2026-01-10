Seat Selection (Clean Implementation)

1) Import DB schema:
   - Open phpMyAdmin -> select your database (concert_db) -> SQL tab
   - Run seat_schema.sql

2) Copy/replace these files into your project:
   - concert_details.php (button now goes to select_seat.php)
   - select_seat.php (new)
   - book.php (now supports seat-based booking)
   - my_tickets.php (shows seat codes)
   - admin.php (auto-generates seats when you add a concert)
   - admin_verify.php (books seats on approval, releases seats+restores capacity on rejection)
   - admin_seats.php (new admin seat dashboard)

3) How it works (flow):
   concert_details.php -> Select Seats -> select_seat.php
   -> redirects to book.php (qty auto = number of seats)
   -> upload receipt -> admin approves -> seats become BOOKED.

Notes:
- Holds expire after 15 minutes.
- VIP seats = first 20% of capacity, price = base * 1.5 (you can change it anytime).
