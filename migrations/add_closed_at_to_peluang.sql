-- Add closed_at column to peluang table to track when a post is closed
-- Run this SQL to enable the close post feature

ALTER TABLE peluang ADD COLUMN closed_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;
