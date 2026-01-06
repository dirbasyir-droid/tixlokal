-- Seat hold upgrade migration
-- Run this in phpMyAdmin on your existing database

ALTER TABLE concert_seats
  MODIFY status ENUM('available','held','booked') DEFAULT 'available';

ALTER TABLE concert_seats
  ADD COLUMN hold_until DATETIME NULL,
  ADD COLUMN held_by_user_id INT NULL;

-- Helpful indexes (optional)
CREATE INDEX idx_concert_seats_concert ON concert_seats(concert_id);
CREATE INDEX idx_concert_seats_status ON concert_seats(status);
CREATE INDEX idx_concert_seats_hold_until ON concert_seats(hold_until);
