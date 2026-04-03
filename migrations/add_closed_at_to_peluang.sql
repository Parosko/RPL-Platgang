-- Add closed_at column to peluang table to track when a post is closed
-- This is needed for the close/reopen post feature
-- Run this SQL to enable the close post feature

ALTER TABLE peluang ADD COLUMN closed_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;

-- If the column already exists, you can check with:
-- SHOW COLUMNS FROM peluang LIKE 'closed_at';

-- To run this migration:
-- mysql -u root platform_karir_kampus < migrations/add_closed_at_to_peluang.sql
-- Or run manually in phpMyAdmin
