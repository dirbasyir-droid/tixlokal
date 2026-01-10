-- Email Verification Migration for TixLokal (concert_db)
-- Run this in phpMyAdmin / MySQL console.

ALTER TABLE users
  ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN verify_token VARCHAR(64) NULL,
  ADD COLUMN verify_expires DATETIME NULL;

-- Optional: for quicker lookups
CREATE INDEX idx_users_verify_token ON users (verify_token);
