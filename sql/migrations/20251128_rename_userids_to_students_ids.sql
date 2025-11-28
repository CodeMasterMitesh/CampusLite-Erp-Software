-- Migration: rename user_ids column to students_ids in batch_assignments
-- Run this if you previously added `user_ids` and want to rename it to `students_ids`.

ALTER TABLE batch_assignments
  CHANGE COLUMN user_ids students_ids TEXT NULL;

-- Verify with:
-- SHOW COLUMNS FROM batch_assignments;
