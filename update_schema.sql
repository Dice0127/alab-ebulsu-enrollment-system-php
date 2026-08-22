-- Update to extend student_account table with additional personal fields
-- Run this to add missing columns for student personal information

ALTER TABLE student_account ADD COLUMN middle_name VARCHAR(50) NULL AFTER first_name;
ALTER TABLE student_account ADD COLUMN gender VARCHAR(20) NULL AFTER middle_name;
ALTER TABLE student_account ADD COLUMN birthday DATE NULL AFTER gender;
ALTER TABLE student_account ADD COLUMN contact_number VARCHAR(20) NULL AFTER birthday;
ALTER TABLE student_account ADD COLUMN address TEXT NULL AFTER contact_number;

-- These columns should only be added if they don't already exist
-- Run this SQL safely with: mysql -u root websys_db < update_schema.sql
