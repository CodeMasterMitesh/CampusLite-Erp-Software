-- Tuition360 Seeder SQL
-- Run this after importing schema.sql to populate minimal demo data


INSERT INTO company (name, address, phone, email) VALUES
  ('Tuition360 Pvt Ltd', 'Corporate HQ, Gujarat', '1010101010', 'info@tuition360.com');

INSERT INTO branches (company_id, name, address, phone, email) VALUES
  (1, 'Ahmedabad Branch', 'Ahmedabad, Gujarat', '1234567890', 'ahm@tuition360.com'),
  (1, 'Surat Branch', 'Surat, Gujarat', '9876543210', 'surat@tuition360.com');

INSERT INTO users (branch_id, role, name, email, password, mobile, is_part_time, status) VALUES
  (NULL, 'super_admin', 'Super Admin', 'admin@company.com', '$2y$10$n5XBd7leYIllA218eHHEjOmjvvFBczkmJbaLQfyHFCfY/W9KKeUoK', '9999999999', 0, 1),
  (1, 'branch_admin', 'Branch Admin', 'branch1@company.com', '$2y$10$examplehashforbranchadmin', '8888888888', 0, 1),
  (2, 'branch_admin', 'Branch Admin 2', 'branch2@company.com', '$2y$10$examplehashforbranchadmin2', '7777777777', 0, 1),
  (1, 'faculty', 'John Faculty', 'faculty1@company.com', '$2y$10$examplehashforfaculty', '6666666666', 1, 1),
  (1, 'employee', 'Jane Employee', 'employee1@company.com', '$2y$10$examplehashforemployee', '5555555555', 0, 1);

INSERT INTO subjects (branch_id, code, title, description) VALUES
  (NULL, 'HTML', 'HTML', 'HTML basics'),
  (NULL, 'CSS', 'CSS', 'CSS basics'),
  (NULL, 'JS', 'JavaScript', 'JavaScript basics'),
  (NULL, 'PHP', 'PHP', 'PHP basics'),
  (NULL, 'NODE', 'Node.js', 'Node.js basics');

INSERT INTO courses (branch_id, title, description, total_fee, duration_months) VALUES
  (1, 'Web Development', 'Full stack web dev', 15000, 6),
  (1, 'Frontend Basics', 'HTML, CSS, JS', 8000, 3),
  (2, 'Backend Basics', 'PHP, Node.js', 9000, 3);

INSERT INTO course_subjects (course_id, subject_id, sequence) VALUES
  (1, 1, 1), (1, 2, 2), (1, 3, 3), (1, 4, 4), (1, 5, 5),
  (2, 1, 1), (2, 2, 2), (2, 3, 3),
  (3, 4, 1), (3, 5, 2);

INSERT INTO students (branch_id, name, email, mobile, dob, father_name, address) VALUES
  (1, 'Student One', 'student1@company.com', '1111111111', '2005-01-01', 'Father One', 'Ahmedabad'),
  (1, 'Student Two', 'student2@company.com', '2222222222', '2006-02-02', 'Father Two', 'Ahmedabad'),
  (2, 'Student Three', 'student3@company.com', '3333333333', '2005-03-03', 'Father Three', 'Surat');

INSERT INTO batches (branch_id, course_id, title, start_date, end_date, days_of_week, time_slot, capacity, status) VALUES
  (1, 1, 'Batch A', '2025-01-01', '2025-06-30', 'Mon,Wed,Fri', '10:00-12:00', 30, 'running'),
  (1, 2, 'Batch B', '2025-02-01', '2025-04-30', 'Tue,Thu', '14:00-16:00', 25, 'planned'),
  (2, 3, 'Batch C', '2025-03-01', '2025-05-31', 'Sat,Sun', '09:00-11:00', 20, 'planned');

INSERT INTO enrollments (student_id, batch_id, enroll_date, fee_paid, status) VALUES
  (1, 1, '2025-01-01', 5000, 'active'),
  (2, 2, '2025-02-01', 3000, 'active'),
  (3, 3, '2025-03-01', 4000, 'active');


-- Attendance
INSERT INTO attendance (branch_id, entity_type, entity_id, date, status, note, recorded_by) VALUES
  (1, 'student', 1, '2025-01-02', 'present', 'On time', 2),
  (1, 'student', 2, '2025-01-02', 'absent', '', 2),
  (2, 'student', 3, '2025-03-02', 'present', '', 3),
  (1, 'faculty', 4, '2025-01-02', 'present', '', 2),
  (1, 'employee', 5, '2025-01-02', 'present', '', 2);

-- Fees
INSERT INTO fees (branch_id, student_id, enrollment_id, amount, payment_date, payment_mode, receipt_no, created_by) VALUES
  (1, 1, 1, 5000, '2025-01-05', 'cash', 'R001', 2),
  (1, 2, 2, 3000, '2025-02-05', 'card', 'R002', 2),
  (2, 3, 3, 4000, '2025-03-05', 'upi', 'R003', 3);

-- Ledgers
INSERT INTO ledgers (branch_id, ref_type, ref_id, amount, dr_cr, date, description) VALUES
  (1, 'fee', 1, 5000, 'CR', '2025-01-05', 'Fee received from Student One'),
  (1, 'fee', 2, 3000, 'CR', '2025-02-05', 'Fee received from Student Two'),
  (2, 'fee', 3, 4000, 'CR', '2025-03-05', 'Fee received from Student Three');

-- Salaries
INSERT INTO salaries (branch_id, user_id, salary_month, gross_amount, deductions, net_amount, paid_on, status) VALUES
  (1, 4, '2025-01-01', 10000, 1000, 9000, '2025-01-31', 'paid'),
  (1, 5, '2025-01-01', 12000, 1200, 10800, '2025-01-31', 'paid');

-- Leaves
INSERT INTO leaves (user_id, branch_id, leave_type, from_date, to_date, reason, status, applied_on, decided_by, decided_on) VALUES
  (4, 1, 'Sick', '2025-01-10', '2025-01-12', 'Fever', 'approved', '2025-01-09 10:00:00', 2, '2025-01-09 12:00:00'),
  (5, 1, 'Casual', '2025-01-15', '2025-01-16', 'Personal', 'applied', '2025-01-14 09:00:00', NULL, NULL);

-- Course Completion
INSERT INTO course_completion (enrollment_id, completion_date, status, remarks) VALUES
  (1, '2025-06-30', 'completed', 'Completed successfully'),
  (2, NULL, 'in_progress', 'Ongoing');

-- Activity Logs
INSERT INTO activity_logs (user_id, action, meta) VALUES
  (2, 'login', '{"ip":"127.0.0.1"}'),
  (3, 'enroll', '{"student_id":3,"batch_id":3}');
