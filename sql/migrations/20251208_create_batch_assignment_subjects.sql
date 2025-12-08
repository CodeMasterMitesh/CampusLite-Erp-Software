-- Migration: create junction table for batch assignments -> subjects
-- Creates `batch_assignment_subjects` to normalize many-to-many relationship
CREATE TABLE IF NOT EXISTS `batch_assignment_subjects` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `assignment_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_assignment` (`assignment_id`),
  KEY `idx_subject` (`subject_id`),
  UNIQUE KEY `unique_assignment_subject` (`assignment_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE: If you have existing data in batch_assignments.subjects column,
-- you may need to migrate it to this table using a script.
-- The subjects column was previously TEXT storing JSON or comma-separated values.

-- Optional: If you want to remove the old subjects column after migration:
-- ALTER TABLE batch_assignments DROP COLUMN subjects;
