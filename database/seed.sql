-- Campus360 Multi-Tenant ERP Seed Script
USE `college_erp_db`;

-- Seed Tenants
INSERT INTO `tenants` (`id`, `name`, `code`, `domain`, `status`) VALUES
(1, 'Campus360 Main College Institution', 'CAMPUS_360', 'campus360.edu', 'active'),
(2, 'St. Mark Higher Education Academy', 'ST_MARK', 'stmark.edu', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Seed Roles
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`) VALUES
(1, 'super_admin', 'Super Administrator', 'Global platform management and tenant provisioning'),
(2, 'school_admin', 'School Administrator', 'Institution setup, academic structure, and user creation'),
(3, 'principal', 'Principal / Super Administrator', 'Overall institutional access to all ERP modules and high-level approvals'),
(4, 'teacher', 'Teaching Staff / Teacher', 'Daily attendance, marks entry, timetable, exams, gradebook & student MIS'),
(5, 'student', 'Student', 'Personal student portal for timetable, attendance, marks, fees & library'),
(6, 'parent', 'Parent / Guardian', 'Parent portal to monitor linked child attendance, marks, fees & notices'),
(7, 'accountant', 'Accountant / Accounts Executive', 'Fees collection, accounts ledger, payroll finance & financial reports'),
(8, 'librarian', 'Librarian / Library Assistant', 'Book catalogue, book issue/return tracking, members & overdue fines'),
(9, 'hr_executive', 'HR Executive / HR Administrator', 'Staff records, staff attendance, leave management, payroll & recruitment'),
(10, 'admin_officer', 'Administrative Officer / Office Administrator', 'Enquiry handling, admission process, ID cards, certificates & visitor mgmt'),
(11, 'transport_hostel_admin', 'Transport & Hostel Administrator', 'Transport routes, vehicles, hostel rooms, occupancy & day boarding'),
(12, 'inventory_manager', 'Inventory / Store & Asset Manager', 'Stock inventory, office supplies, equipment & campus asset tracking'),
(13, 'receptionist', 'Receptionist / Front Office Executive', 'Front office visitors, enquiry desk, badges & administrative support'),
(14, 'erp_admin', 'ERP Administrator / System Administrator', 'Role & Permission configuration, app analysis, user account management & tech support')
ON DUPLICATE KEY UPDATE `display_name` = VALUES(`display_name`), `description` = VALUES(`description`);

-- Seed Default Users (Password: Admin@123)
-- Hash generated via BCRYPT ($2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK)
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`) VALUES (9, 'hr', 'HR Executive / HR Administrator', 'Legacy alias for HR Executive') ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `users` (`id`, `tenant_id`, `username`, `email`, `password_hash`, `role_id`, `status`) VALUES
(1, NULL, 'admin', 'admin@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 1, 'active'),
(2, 1, 'schooladmin', 'schooladmin@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 2, 'active'),
(3, 1, 'dr_soap', 'soap@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 4, 'active'),
(4, 1, 'dr_cipeng', 'cipeng@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 4, 'active'),
(5, 1, 'accountant_mary', 'mary@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 7, 'active'),
(6, 1, 'john_student', 'john@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 5, 'active'),
(7, 1, 'robert_parent', 'robert@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 6, 'active'),
(8, 1, 'principal_john', 'principal@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 3, 'active'),
(9, 1, 'librarian_lisa', 'lisa@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 8, 'active'),
(10, 1, 'hr_david', 'david@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 9, 'active'),
(11, 1, 'admin_officer_james', 'officer@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 10, 'active'),
(12, 1, 'transport_admin_alex', 'transport@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 11, 'active'),
(13, 1, 'inventory_mgr_steve', 'inventory@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 12, 'active'),
(14, 1, 'receptionist_sarah', 'receptionist@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 13, 'active'),
(15, 1, 'erp_admin_tech', 'erpadmin@campus360.edu', '$2y$10$Q/Taz4Q1ymYBUPqLHJh.heGwFJtqV9bmn4F0ccmsrlOmubHPnMKaK', 14, 'active')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`), `password_hash` = VALUES(`password_hash`), `role_id` = VALUES(`role_id`);


-- Seed Departments for Tenant 1
-- Seed Departments for Tenant 1
INSERT INTO `departments` (`id`, `tenant_id`, `name`, `code`) VALUES
(1, 1, 'B.Sc. Computer Science', 'BSC-CS'),
(2, 1, 'B.Sc. IT', 'BSC-IT'),
(3, 1, 'B.Com', 'BCOM'),
(4, 1, 'BCA', 'BCA')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `code` = VALUES(`code`);

-- Seed Classes (Year Levels: 1st Year, 2nd Year, 3rd Year)
INSERT INTO `classes` (`id`, `tenant_id`, `name`, `code`) VALUES
(1, 1, '1st Year', 'YR1'),
(2, 1, '2nd Year', 'YR2'),
(3, 1, '3rd Year', 'YR3')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `code` = VALUES(`code`);

-- Seed Semesters for Tenant 1
INSERT INTO `semesters` (`id`, `tenant_id`, `course_id`, `name`, `semester_number`) VALUES
(1, 1, 1, 'Semester 1 (1st Year)', 1),
(2, 1, 1, 'Semester 2 (1st Year)', 2),
(3, 1, 1, 'Semester 3 (2nd Year)', 3),
(4, 1, 1, 'Semester 4 (2nd Year)', 4),
(5, 1, 1, 'Semester 5 (3rd Year)', 5),
(6, 1, 1, 'Semester 6 (3rd Year)', 6)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Seed Classrooms for Tenant 1
INSERT INTO `classrooms` (`id`, `tenant_id`, `room_number`, `building_block`, `capacity`) VALUES
(1, 1, 'Block 101', 'Main Academic Building', 60),
(2, 1, 'Block 102', 'Main Academic Building', 60),
(3, 1, 'Lab 201', 'Science & IT Wing', 40),
(4, 1, 'Auditorium A', 'Central Block', 200)
ON DUPLICATE KEY UPDATE `room_number` = VALUES(`room_number`);

INSERT INTO `sections` (`id`, `tenant_id`, `class_id`, `name`) VALUES
(1, 1, 1, 'Division A'),
(2, 1, 1, 'Division B')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `subjects` (`id`, `tenant_id`, `class_id`, `name`, `code`, `type`) VALUES
(1, 1, 1, 'Data Structures & Algorithms', 'CS101', 'theory'),
(2, 1, 1, 'Database Management System', 'DBMS501', 'theory'),
(3, 1, 1, 'Web Technology', 'WT301', 'theory'),
(4, 1, 1, 'Financial Accounting', 'ACC101', 'theory'),
(5, 1, 1, 'Java Programming', 'BCA201', 'theory')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Seed Courses & Batches for Tenant 1
INSERT INTO `courses` (`id`, `tenant_id`, `department_id`, `name`, `code`, `duration_years`) VALUES
(1, 1, 1, 'B.Sc. Computer Science', 'BSC-CS', 3),
(2, 1, 2, 'B.Sc. IT', 'BSC-IT', 3),
(3, 1, 3, 'B.Com', 'BCOM', 3),
(4, 1, 4, 'BCA', 'BCA', 3)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `batches` (`id`, `tenant_id`, `course_id`, `batch_name`, `start_year`, `end_year`) VALUES
(1, 1, 1, 'CSE Batch 2024-2028', 2024, 2028),
(2, 1, 2, 'B.Sc IT Sem 5 Div A Batch', 2024, 2027),
(3, 1, 3, 'B.Com Sem 5 Div A Batch', 2024, 2027)
ON DUPLICATE KEY UPDATE `batch_name` = VALUES(`batch_name`);

-- Seed Staff for Tenant 1
INSERT INTO `staff` (`id`, `tenant_id`, `user_id`, `emp_id`, `first_name`, `last_name`, `designation`, `department_id`, `phone`, `email`, `joining_date`, `salary`) VALUES
(1, 1, 3, 'EMP-001', 'Prof. Khan', 'Mactavish', 'Senior Professor', 2, '+1 555-0192', 'soap@campus360.edu', '2020-01-15', 85000.00),
(2, 1, 4, 'EMP-002', 'Dr. Cipeng', 'Widjaja', 'Associate Professor', 2, '+1 555-0193', 'cipeng@campus360.edu', '2021-03-10', 72000.00)
ON DUPLICATE KEY UPDATE `first_name` = VALUES(`first_name`);

-- Seed Student for Tenant 1
INSERT INTO `students` (`id`, `tenant_id`, `user_id`, `admission_no`, `roll_no`, `first_name`, `last_name`, `gender`, `dob`, `phone`, `email`, `guardian_name`, `guardian_phone`, `batch_id`, `class_id`, `section_id`, `department_id`, `course_id`, `semester_id`, `status`) VALUES
(1, 1, 6, 'ADM-2024-001', '24IT001', 'John', 'Doe', 'male', '2004-05-14', '+1 555-0101', 'john@campus360.edu', 'Robert Doe', '+1 555-0100', 2, 1, 1, 2, 2, 5, 'active')
ON DUPLICATE KEY UPDATE `first_name` = VALUES(`first_name`);

-- Seed Grading Scales for Tenant 1 (Section 6.4)
INSERT INTO `grading_scales` (`tenant_id`, `grade`, `min_percentage`, `max_percentage`, `grade_point`) VALUES
(1, 'A+', 90.00, 100.00, 10.00),
(1, 'A',  80.00, 89.99, 9.00),
(1, 'B',  70.00, 79.99, 8.00),
(1, 'C',  60.00, 69.99, 7.00),
(1, 'F',  0.00,  59.99, 0.00);

-- Seed Fee Structures & Installments for Tenant 1 (Section 6.5)
INSERT INTO `fee_structures` (`id`, `tenant_id`, `course_id`, `academic_year`, `fee_type`, `amount`, `due_date`) VALUES
(1, 1, 1, '2026-2027', 'Tuition Fee Term 1', 1250.00, '2026-09-30'),
(2, 1, 1, '2026-2027', 'Lab & Library Fee', 350.00, '2026-09-30')
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);

INSERT INTO `fee_installments` (`id`, `tenant_id`, `student_id`, `fee_structure_id`, `title`, `amount`, `due_date`, `status`) VALUES
(1, 1, 1, 1, 'Tuition Fee Installment 1', 625.00, '2026-09-30', 'unpaid'),
(2, 1, 1, 1, 'Tuition Fee Installment 2', 625.00, '2026-12-31', 'unpaid')
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);

-- Seed Time Tables for Department-Wise & Class-Specific Timetables
INSERT INTO `time_tables` (`id`, `tenant_id`, `academic_year`, `department_id`, `course_id`, `semester_id`, `section_id`, `batch_id`, `day_of_week`, `time_slot`, `start_time`, `end_time`, `subject_id`, `subject_name`, `staff_id`, `classroom_id`, `room_no`) VALUES
(1, 1, '2026-2027', 2, 2, 5, 1, 2, 'Monday', '07:10 AM - 08:00 AM', '07:10:00', '08:00:00', 2, 'Database Management System', 1, 1, 'Block 101'),
(2, 1, '2026-2027', 2, 2, 3, 2, 2, 'Monday', '08:00 AM - 08:50 AM', '08:00:00', '08:50:00', 3, 'Web Technology', 1, 2, 'Block 102'),
(3, 1, '2026-2027', 3, 3, 5, 1, 3, 'Monday', '07:10 AM - 08:00 AM', '07:10:00', '08:00:00', 4, 'Financial Accounting', 2, 2, 'Block 102'),
(4, 1, '2026-2027', 2, 2, 5, 1, 2, 'Tuesday', '08:00 AM - 08:50 AM', '08:00:00', '08:50:00', 2, 'Database Management System', 1, 1, 'Block 101')
ON DUPLICATE KEY UPDATE `subject_name` = VALUES(`subject_name`);

-- Seed Exams for Tenant 1
INSERT INTO `exams` (`id`, `tenant_id`, `name`, `course_id`, `exam_date`, `total_marks`, `passing_marks`) VALUES
(1, 1, 'Term 1 Midterm Examination', 1, '2026-10-15', 100, 40),
(2, 1, 'Term 2 Final Examination', 1, '2026-12-20', 100, 40)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Seed Audit Log
INSERT INTO `security_audit_logs` (`tenant_id`, `user_id`, `action`, `ip_address`, `details`) VALUES
(1, 1, 'TENANT_PROVISIONED', '127.0.0.1', 'Multi-tenant database schema and seed initialized for Campus360.');

-- Seed Configurable Leave Types for Tenant 1
INSERT INTO `leave_types` (`id`, `tenant_id`, `name`, `code`, `days_allowed`, `description`, `status`) VALUES
(1, 1, 'Casual Leave', 'CL', 12, 'Routine personal casual leave allocation per year', 'active'),
(2, 1, 'Sick Leave', 'SL', 10, 'Medical and sickness leave with doctor verification', 'active'),
(3, 1, 'Earned Leave', 'EL', 15, 'Accumulated annual privilege earned leave', 'active'),
(4, 1, 'Emergency Leave', 'EML', 5, 'Immediate family emergency or urgent leave', 'active'),
(5, 1, 'Medical Leave', 'ML', 14, 'Extended medical care leave', 'active'),
(6, 1, 'Maternity Leave', 'MAT', 90, 'Maternity leave for eligible female staff', 'active'),
(7, 1, 'Paternity Leave', 'PAT', 15, 'Paternity leave for eligible male staff', 'active'),
(8, 1, 'Unpaid Leave', 'LWP', 30, 'Leave without pay', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Seed Default Leave Balances for Key Staff Users (Tenant 1)
INSERT INTO `leave_balances` (`tenant_id`, `user_id`, `staff_id`, `leave_type_id`, `allocated_days`, `used_days`, `remaining_days`, `academic_year`) VALUES
(1, 3, 1, 1, 12, 0, 12, '2026-2027'),
(1, 3, 1, 2, 10, 0, 10, '2026-2027'),
(1, 4, 2, 1, 12, 0, 12, '2026-2027'),
(1, 5, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 8, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 9, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 10, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 11, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 12, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 13, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 14, NULL, 1, 12, 0, 12, '2026-2027'),
(1, 15, NULL, 1, 12, 0, 12, '2026-2027')
ON DUPLICATE KEY UPDATE `allocated_days` = VALUES(`allocated_days`);

