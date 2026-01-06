SOLD OUT FIX (Seat-based availability)

What changed:
- Homepage availability now computed from concert_seats (status='available') instead of relying on concerts.availability.
- Featured carousel query also updated for consistent Sold Out badges.

Install:
1) Replace your MyPhpWebsite/index.php with the patched index.php.
2) Hard refresh browser (Ctrl+F5).

Requires:
- Table concert_seats exists (seat selection feature).
