-- Student Enrollment Management System database schema
-- Use this file to create the required tables and helper views for the WebFinalProject.

CREATE DATABASE IF NOT EXISTS websys_db;
USE websys_db;

CREATE TABLE IF NOT EXISTS admin (
    admin_id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS student_account (
    account_id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS college (
    college_code VARCHAR(10) NOT NULL,
    college_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (college_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS program (
    program_code VARCHAR(15) NOT NULL,
    program_name VARCHAR(100) NOT NULL,
    college_code VARCHAR(10) NULL,
    PRIMARY KEY (program_code),
    CONSTRAINT fk_program_college FOREIGN KEY (college_code) REFERENCES college(college_code) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_program_college_code (college_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS year_levels (
    year_id INT NOT NULL AUTO_INCREMENT,
    year_level VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS courses (
    course_id INT NOT NULL AUTO_INCREMENT,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    units INT NOT NULL DEFAULT 3,
    college_code VARCHAR(10) NULL,
    program_code VARCHAR(15) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (course_id),
    CONSTRAINT fk_courses_college FOREIGN KEY (college_code) REFERENCES college(college_code) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_courses_program FOREIGN KEY (program_code) REFERENCES program(program_code) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_courses_code_name (course_code, course_name),
    INDEX idx_courses_college (college_code),
    INDEX idx_courses_program (program_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS curriculum (
    curriculum_id INT NOT NULL AUTO_INCREMENT,
    program_code VARCHAR(15) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    course_id INT NOT NULL,
    semester INT NOT NULL DEFAULT 1,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (curriculum_id),
    CONSTRAINT fk_curriculum_program FOREIGN KEY (program_code) REFERENCES program(program_code) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_curriculum_course FOREIGN KEY (course_id) REFERENCES courses(course_id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY unique_curriculum (program_code, year_level, semester, course_id),
    INDEX idx_curriculum_program_year_semester (program_code, year_level, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sections (
    section_id INT NOT NULL AUTO_INCREMENT,
    section_code VARCHAR(20) NOT NULL UNIQUE,
    section_name VARCHAR(100) NOT NULL,
    college_code VARCHAR(10) NULL,
    program_code VARCHAR(15) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    max_capacity INT NOT NULL DEFAULT 30,
    current_enrolled INT NOT NULL DEFAULT 0,
    status ENUM('Open','Full','Closed') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (section_id),
    CONSTRAINT fk_sections_college FOREIGN KEY (college_code) REFERENCES college(college_code) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_sections_program FOREIGN KEY (program_code) REFERENCES program(program_code) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_sections_college (college_code),
    INDEX idx_sections_program_year (program_code, year_level),
    INDEX idx_sections_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS enrollment (
    enrollment_id INT NOT NULL AUTO_INCREMENT,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    account_id INT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NULL,
    last_name VARCHAR(50) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    birthday DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    program_code VARCHAR(15) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    section_id INT NULL,
    semester VARCHAR(20) NOT NULL,
    school_year VARCHAR(9) NOT NULL,
    student_type VARCHAR(20) NOT NULL DEFAULT 'Regular',
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (enrollment_id),
    CONSTRAINT fk_enrollment_account FOREIGN KEY (account_id) REFERENCES student_account(account_id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_enrollment_program FOREIGN KEY (program_code) REFERENCES program(program_code) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_enrollment_section FOREIGN KEY (section_id) REFERENCES sections(section_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_enrollment_account_id (account_id),
    INDEX idx_enrollment_program_code (program_code),
    INDEX idx_enrollment_year_level (year_level),
    INDEX idx_enrollment_section_id (section_id),
    INDEX idx_enrollment_status (status),
    INDEX idx_enrollment_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS enrollment_courses (
    enrollment_course_id INT NOT NULL AUTO_INCREMENT,
    enrollment_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (enrollment_course_id),
    CONSTRAINT fk_enrollment_courses_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollment(enrollment_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_enrollment_courses_course FOREIGN KEY (course_id) REFERENCES courses(course_id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY uq_enrollment_course (enrollment_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE OR REPLACE VIEW v_section_slots AS
SELECT s.section_id, s.section_code, s.section_name, s.program_code, s.year_level, s.max_capacity,
       s.current_enrolled, (s.max_capacity - s.current_enrolled) AS available_slots,
       CASE WHEN s.status = 'Closed' THEN 'Closed' WHEN s.current_enrolled >= s.max_capacity THEN 'Full' ELSE 'Open' END AS computed_status
FROM sections s;
