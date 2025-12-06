-- Schedule batches table
CREATE TABLE IF NOT EXISTS schedule_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    batch_id INT NOT NULL,
    faculty_id INT NULL,
    recurrence ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
    start_date DATE NULL,
    end_date DATE NULL,
    day_of_week TINYINT NULL COMMENT '0=Sun ... 6=Sat',
    day_of_month TINYINT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    subject_ids TEXT NULL COMMENT 'JSON array of subject ids',
    student_ids TEXT NULL COMMENT 'JSON array of student ids',
    notes VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
