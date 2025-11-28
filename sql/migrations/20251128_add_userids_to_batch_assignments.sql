-- Migration: add user_ids column to batch_assignments to store multiple student IDs as JSON
ALTER TABLE batch_assignments
  ADD COLUMN user_ids TEXT NULL AFTER user_id;

-- Verify with:
-- SHOW COLUMNS FROM batch_assignments;
