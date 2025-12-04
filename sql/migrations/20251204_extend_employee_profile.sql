-- Add profile and HR fields to users (employees)
ALTER TABLE `users`
  ADD COLUMN `profile_photo` VARCHAR(255) NULL AFTER `email`,
  ADD COLUMN `dob` DATE NULL AFTER `profile_photo`,
  ADD COLUMN `gender` ENUM('male','female','other') NULL AFTER `dob`,
  ADD COLUMN `marital_status` ENUM('single','married','divorced','widowed') NULL AFTER `gender`,
  ADD COLUMN `joining_date` DATE NULL AFTER `marital_status`,
  ADD COLUMN `resign_date` DATE NULL AFTER `joining_date`,
  ADD COLUMN `in_time` TIME NULL AFTER `resign_date`,
  ADD COLUMN `out_time` TIME NULL AFTER `in_time`,
  ADD COLUMN `address` VARCHAR(255) NULL AFTER `out_time`,
  ADD COLUMN `area` VARCHAR(100) NULL AFTER `address`,
  ADD COLUMN `city` VARCHAR(100) NULL AFTER `area`,
  ADD COLUMN `pincode` VARCHAR(20) NULL AFTER `city`,
  ADD COLUMN `state` VARCHAR(100) NULL AFTER `pincode`,
  ADD COLUMN `country` VARCHAR(100) NULL AFTER `state`,
  ADD COLUMN `aadhar_card` VARCHAR(20) NULL AFTER `country`,
  ADD COLUMN `pan_card` VARCHAR(20) NULL AFTER `aadhar_card`,
  ADD COLUMN `passport` VARCHAR(20) NULL AFTER `pan_card`;

-- Education details table
CREATE TABLE IF NOT EXISTS `employee_education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `degree` VARCHAR(120) NOT NULL,
  `institute` VARCHAR(180) NOT NULL,
  `from_date` DATE NULL,
  `to_date` DATE NULL,
  `grade` VARCHAR(50) NULL,
  `specialization` VARCHAR(120) NULL,
  CONSTRAINT `fk_emp_edu_user` FOREIGN KEY (`employee_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Employment details table
CREATE TABLE IF NOT EXISTS `employee_employment` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `organisation` VARCHAR(180) NOT NULL,
  `designation` VARCHAR(120) NOT NULL,
  `from_date` DATE NULL,
  `to_date` DATE NULL,
  `annual_ctc` DECIMAL(12,2) NULL,
  CONSTRAINT `fk_emp_emp_user` FOREIGN KEY (`employee_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);