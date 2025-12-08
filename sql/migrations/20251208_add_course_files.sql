-- Add file storage columns to courses table
ALTER TABLE courses 
ADD COLUMN IF NOT EXISTS file_path VARCHAR(500) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS file_name VARCHAR(255) DEFAULT NULL;
