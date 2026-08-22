-- ============================================================
--  sample_data.sql — Sample/demo data for WebSys (Alab E-BulSU)
--
--  Every college has at least one program, every program has its
--  own set of courses (year 1-4) plus shared GE courses, every
--  program's courses are mapped into curriculum, and every
--  program/year level has two 30-slot sections (G1 and G2), e.g.
--  'BSIT 1A-G1' and 'BSIT 1A-G2'.
--
--  How to apply (after websys_db.sql, update_schema.sql, and
--  update_schema_v2.sql have already been imported):
--    mysql -u root -p websys_db < sample_data.sql
--
--  Uses INSERT IGNORE, so it's safe to re-run without duplicating rows.
-- ============================================================

USE websys_db;

-- ── Colleges ──────────────────────────────────────────────────
INSERT IGNORE INTO college (college_code, college_name) VALUES
('CICT', 'College of Information and Communications Technology'),
('COE', 'College of Engineering'),
('CBA', 'College of Business Administration'),
('CAS', 'College of Arts and Sciences'),
('COED', 'College of Education');

-- ── Programs ──────────────────────────────────────────────────
-- Every college above has at least one program here.
INSERT IGNORE INTO program (program_code, program_name, college_code) VALUES
('BSIT',  'Bachelor of Science in Information Technology', 'CICT'),
('BSCS',  'Bachelor of Science in Computer Science', 'CICT'),
('BSIS',  'Bachelor of Science in Information Systems', 'CICT'),
('BSCE',  'Bachelor of Science in Civil Engineering', 'COE'),
('BSEE',  'Bachelor of Science in Electrical Engineering', 'COE'),
('BSBA',  'Bachelor of Science in Business Administration', 'CBA'),
('BSA',  'Bachelor of Science in Accountancy', 'CBA'),
('ABPSY',  'Bachelor of Arts in Psychology', 'CAS'),
('ABCOMM',  'Bachelor of Arts in Communication', 'CAS'),
('BSED',  'Bachelor of Secondary Education', 'COED'),
('BEED',  'Bachelor of Elementary Education', 'COED');

-- ── General education courses (shared across all programs) ────
INSERT IGNORE INTO courses (course_code, course_name, description, units, college_code, program_code) VALUES
('GE101', 'Understanding the Self', 'Introduction to the nature of identity.', 3, NULL, NULL),
('GE102', 'Mathematics in the Modern World', 'Math as a tool for analyzing patterns.', 3, NULL, NULL),
('GE103', 'Purposive Communication', 'Communication skills for academic and professional use.', 3, NULL, NULL),
('PE101', 'Physical Education 1', 'Foundations of movement and fitness.', 2, NULL, NULL),
('NSTP101', 'National Service Training Program 1', 'Civic welfare / literacy training service.', 3, NULL, NULL);

-- ── Program-specific courses (every program has its own set) ──
INSERT IGNORE INTO courses (course_code, course_name, description, units, college_code, program_code) VALUES
('IT101', 'Introduction to Computing', 'Basic computing concepts and problem solving.', 3, 'CICT', 'BSIT'),
('IT102', 'Computer Programming 1', 'Fundamentals of programming using a high-level language.', 3, 'CICT', 'BSIT'),
('IT201', 'Data Structures and Algorithms', 'Core data structures and algorithm analysis.', 3, 'CICT', 'BSIT'),
('IT202', 'Web Systems and Technologies', 'Front-end and back-end web development.', 3, 'CICT', 'BSIT'),
('IT301', 'Systems Analysis and Design', 'Methods for analyzing and designing information systems.', 3, 'CICT', 'BSIT'),
('IT302', 'Software Testing and Quality Assurance', 'Principles of testing and QA processes.', 3, 'CICT', 'BSIT'),
('IT306', 'Mobile Application Development', 'Building applications for mobile platforms.', 3, 'CICT', 'BSIT'),
('IT401', 'System Integration and Architecture', 'Integrating systems and enterprise architecture.', 3, 'CICT', 'BSIT'),
('IT402', 'Capstone Project 1', 'Proposal and initial development of the capstone system.', 3, 'CICT', 'BSIT'),
('CS101', 'Discrete Structures', 'Mathematical foundations for computer science.', 3, 'CICT', 'BSCS'),
('CS201', 'Object-Oriented Programming', 'OOP principles and design.', 3, 'CICT', 'BSCS'),
('CS301', 'Automata Theory and Formal Languages', 'Theoretical foundations of computation.', 3, 'CICT', 'BSCS'),
('CS401', 'Capstone Project 1', 'Proposal and initial development of the capstone system.', 3, 'CICT', 'BSCS'),
('IS101', 'Fundamentals of Information Systems', 'Core concepts of information systems in organizations.', 3, 'CICT', 'BSIS'),
('IS201', 'Database Management Systems', 'Design and management of relational databases.', 3, 'CICT', 'BSIS'),
('IS301', 'IT Project Management', 'Planning and managing IT projects.', 3, 'CICT', 'BSIS'),
('IS401', 'Systems Integration', 'Integrating enterprise information systems.', 3, 'CICT', 'BSIS'),
('CE101', 'Engineering Drawing', 'Technical drawing fundamentals for engineers.', 2, 'COE', 'BSCE'),
('CE201', 'Statics of Rigid Bodies', 'Analysis of forces on rigid bodies.', 3, 'COE', 'BSCE'),
('CE301', 'Structural Analysis', 'Analysis of structural systems and loads.', 3, 'COE', 'BSCE'),
('CE401', 'Reinforced Concrete Design', 'Design principles for reinforced concrete structures.', 3, 'COE', 'BSCE'),
('EE101', 'Circuit Analysis 1', 'Fundamentals of electrical circuit analysis.', 3, 'COE', 'BSEE'),
('EE201', 'Electronics 1', 'Basic electronic devices and circuits.', 3, 'COE', 'BSEE'),
('EE301', 'Electrical Machines', 'Principles of electrical machines and transformers.', 3, 'COE', 'BSEE'),
('EE401', 'Power Systems', 'Analysis and design of electrical power systems.', 3, 'COE', 'BSEE'),
('BA101', 'Principles of Management', 'Fundamentals of management theory and practice.', 3, 'CBA', 'BSBA'),
('BA201', 'Financial Management', 'Principles of financial decision-making.', 3, 'CBA', 'BSBA'),
('BA301', 'Marketing Management', 'Concepts and strategies in marketing.', 3, 'CBA', 'BSBA'),
('BA401', 'Strategic Management', 'Formulating and implementing organizational strategy.', 3, 'CBA', 'BSBA'),
('AC101', 'Financial Accounting 1', 'Fundamentals of financial accounting.', 3, 'CBA', 'BSA'),
('AC201', 'Cost Accounting', 'Cost concepts and cost accounting systems.', 3, 'CBA', 'BSA'),
('AC301', 'Taxation', 'Principles of Philippine taxation.', 3, 'CBA', 'BSA'),
('AC401', 'Auditing Theory', 'Concepts and standards in auditing.', 3, 'CBA', 'BSA'),
('PSY101', 'General Psychology', 'Introduction to the scientific study of behavior and mind.', 3, 'CAS', 'ABPSY'),
('PSY201', 'Abnormal Psychology', 'Study of atypical behavior patterns.', 3, 'CAS', 'ABPSY'),
('PSY301', 'Industrial Psychology', 'Application of psychology in the workplace.', 3, 'CAS', 'ABPSY'),
('PSY401', 'Psychological Assessment', 'Principles of psychological testing and assessment.', 3, 'CAS', 'ABPSY'),
('COM101', 'Introduction to Mass Communication', 'Overview of mass communication theories and media.', 3, 'CAS', 'ABCOMM'),
('COM201', 'Broadcast Media', 'Principles of radio and television broadcasting.', 3, 'CAS', 'ABCOMM'),
('COM301', 'Public Relations', 'Principles and practice of public relations.', 3, 'CAS', 'ABCOMM'),
('COM401', 'Media Production', 'Hands-on production for print, broadcast, and digital media.', 3, 'CAS', 'ABCOMM'),
('ED101', 'The Teaching Profession', 'Foundations of teaching as a profession.', 3, 'COED', 'BSED'),
('ED201', 'Facilitating Learning', 'Principles of learning and learner-centered teaching.', 3, 'COED', 'BSED'),
('ED301', 'Assessment in Learning', 'Principles and tools of learning assessment.', 3, 'COED', 'BSED'),
('ED401', 'Practice Teaching', 'Supervised field teaching experience.', 6, 'COED', 'BSED'),
('EED101', 'Child and Adolescent Development', 'Development stages relevant to elementary learners.', 3, 'COED', 'BEED'),
('EED201', 'Principles of Teaching', 'Core principles and strategies of teaching.', 3, 'COED', 'BEED'),
('EED301', 'Teaching Strategies for Elementary Grades', 'Instructional strategies for elementary education.', 3, 'COED', 'BEED'),
('EED401', 'Student Teaching', 'Supervised field teaching experience.', 6, 'COED', 'BEED');

-- ── Curriculum: shared GE courses, Year 1, for every program ───
INSERT IGNORE INTO curriculum (program_code, year_level, course_id, semester, is_required)
SELECT 'BSIT', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BSCS', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BSIS', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BSCE', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BSEE', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BSBA', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BSA', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'ABPSY', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'ABCOMM', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BSED', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101')
UNION ALL
SELECT 'BEED', '1st Year', course_id, 1, 1 FROM courses WHERE course_code IN ('GE101','GE103','NSTP101');

INSERT IGNORE INTO curriculum (program_code, year_level, course_id, semester, is_required)
SELECT 'BSIT', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BSCS', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BSIS', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BSCE', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BSEE', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BSBA', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BSA', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'ABPSY', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'ABCOMM', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BSED', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101')
UNION ALL
SELECT 'BEED', '1st Year', course_id, 2, 1 FROM courses WHERE course_code IN ('GE102','PE101');

-- ── Curriculum: program-specific courses, mapped to their year level ──
INSERT IGNORE INTO curriculum (program_code, year_level, course_id, semester, is_required)
SELECT 'BSIT', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT101'
UNION ALL
SELECT 'BSIT', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT102'
UNION ALL
SELECT 'BSIT', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT201'
UNION ALL
SELECT 'BSIT', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT202'
UNION ALL
SELECT 'BSIT', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT301'
UNION ALL
SELECT 'BSIT', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT302'
UNION ALL
SELECT 'BSIT', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT306'
UNION ALL
SELECT 'BSIT', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT401'
UNION ALL
SELECT 'BSIT', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'IT402'
UNION ALL
SELECT 'BSCS', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'CS101'
UNION ALL
SELECT 'BSCS', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'CS201'
UNION ALL
SELECT 'BSCS', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'CS301'
UNION ALL
SELECT 'BSCS', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'CS401'
UNION ALL
SELECT 'BSIS', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'IS101'
UNION ALL
SELECT 'BSIS', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'IS201'
UNION ALL
SELECT 'BSIS', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'IS301'
UNION ALL
SELECT 'BSIS', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'IS401'
UNION ALL
SELECT 'BSCE', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'CE101'
UNION ALL
SELECT 'BSCE', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'CE201'
UNION ALL
SELECT 'BSCE', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'CE301'
UNION ALL
SELECT 'BSCE', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'CE401'
UNION ALL
SELECT 'BSEE', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'EE101'
UNION ALL
SELECT 'BSEE', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'EE201'
UNION ALL
SELECT 'BSEE', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'EE301'
UNION ALL
SELECT 'BSEE', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'EE401'
UNION ALL
SELECT 'BSBA', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'BA101'
UNION ALL
SELECT 'BSBA', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'BA201'
UNION ALL
SELECT 'BSBA', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'BA301'
UNION ALL
SELECT 'BSBA', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'BA401'
UNION ALL
SELECT 'BSA', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'AC101'
UNION ALL
SELECT 'BSA', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'AC201'
UNION ALL
SELECT 'BSA', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'AC301'
UNION ALL
SELECT 'BSA', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'AC401'
UNION ALL
SELECT 'ABPSY', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'PSY101'
UNION ALL
SELECT 'ABPSY', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'PSY201'
UNION ALL
SELECT 'ABPSY', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'PSY301'
UNION ALL
SELECT 'ABPSY', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'PSY401'
UNION ALL
SELECT 'ABCOMM', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'COM101'
UNION ALL
SELECT 'ABCOMM', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'COM201'
UNION ALL
SELECT 'ABCOMM', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'COM301'
UNION ALL
SELECT 'ABCOMM', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'COM401'
UNION ALL
SELECT 'BSED', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'ED101'
UNION ALL
SELECT 'BSED', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'ED201'
UNION ALL
SELECT 'BSED', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'ED301'
UNION ALL
SELECT 'BSED', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'ED401'
UNION ALL
SELECT 'BEED', '1st Year', course_id, 1, 1 FROM courses WHERE course_code = 'EED101'
UNION ALL
SELECT 'BEED', '2nd Year', course_id, 1, 1 FROM courses WHERE course_code = 'EED201'
UNION ALL
SELECT 'BEED', '3rd Year', course_id, 1, 1 FROM courses WHERE course_code = 'EED301'
UNION ALL
SELECT 'BEED', '4th Year', course_id, 1, 1 FROM courses WHERE course_code = 'EED401';

-- ── Sections: every program × year level has a G1 and G2 section, ──
-- ── each with a 30-student capacity (e.g. 'BSIT 1A-G1'). ───────
INSERT IGNORE INTO sections (section_code, section_name, college_code, program_code, year_level, max_capacity, current_enrolled, status) VALUES
('BSIT-1A-G1', 'BSIT 1A-G1', 'CICT', 'BSIT', '1st Year', 30, 0, 'Open'),
('BSIT-1A-G2', 'BSIT 1A-G2', 'CICT', 'BSIT', '1st Year', 30, 0, 'Open'),
('BSIT-2A-G1', 'BSIT 2A-G1', 'CICT', 'BSIT', '2nd Year', 30, 0, 'Open'),
('BSIT-2A-G2', 'BSIT 2A-G2', 'CICT', 'BSIT', '2nd Year', 30, 0, 'Open'),
('BSIT-3A-G1', 'BSIT 3A-G1', 'CICT', 'BSIT', '3rd Year', 30, 0, 'Open'),
('BSIT-3A-G2', 'BSIT 3A-G2', 'CICT', 'BSIT', '3rd Year', 30, 0, 'Open'),
('BSIT-4A-G1', 'BSIT 4A-G1', 'CICT', 'BSIT', '4th Year', 30, 0, 'Open'),
('BSIT-4A-G2', 'BSIT 4A-G2', 'CICT', 'BSIT', '4th Year', 30, 0, 'Open'),
('BSCS-1A-G1', 'BSCS 1A-G1', 'CICT', 'BSCS', '1st Year', 30, 0, 'Open'),
('BSCS-1A-G2', 'BSCS 1A-G2', 'CICT', 'BSCS', '1st Year', 30, 0, 'Open'),
('BSCS-2A-G1', 'BSCS 2A-G1', 'CICT', 'BSCS', '2nd Year', 30, 0, 'Open'),
('BSCS-2A-G2', 'BSCS 2A-G2', 'CICT', 'BSCS', '2nd Year', 30, 0, 'Open'),
('BSCS-3A-G1', 'BSCS 3A-G1', 'CICT', 'BSCS', '3rd Year', 30, 0, 'Open'),
('BSCS-3A-G2', 'BSCS 3A-G2', 'CICT', 'BSCS', '3rd Year', 30, 0, 'Open'),
('BSCS-4A-G1', 'BSCS 4A-G1', 'CICT', 'BSCS', '4th Year', 30, 0, 'Open'),
('BSCS-4A-G2', 'BSCS 4A-G2', 'CICT', 'BSCS', '4th Year', 30, 0, 'Open'),
('BSIS-1A-G1', 'BSIS 1A-G1', 'CICT', 'BSIS', '1st Year', 30, 0, 'Open'),
('BSIS-1A-G2', 'BSIS 1A-G2', 'CICT', 'BSIS', '1st Year', 30, 0, 'Open'),
('BSIS-2A-G1', 'BSIS 2A-G1', 'CICT', 'BSIS', '2nd Year', 30, 0, 'Open'),
('BSIS-2A-G2', 'BSIS 2A-G2', 'CICT', 'BSIS', '2nd Year', 30, 0, 'Open'),
('BSIS-3A-G1', 'BSIS 3A-G1', 'CICT', 'BSIS', '3rd Year', 30, 0, 'Open'),
('BSIS-3A-G2', 'BSIS 3A-G2', 'CICT', 'BSIS', '3rd Year', 30, 0, 'Open'),
('BSIS-4A-G1', 'BSIS 4A-G1', 'CICT', 'BSIS', '4th Year', 30, 0, 'Open'),
('BSIS-4A-G2', 'BSIS 4A-G2', 'CICT', 'BSIS', '4th Year', 30, 0, 'Open'),
('BSCE-1A-G1', 'BSCE 1A-G1', 'COE', 'BSCE', '1st Year', 30, 0, 'Open'),
('BSCE-1A-G2', 'BSCE 1A-G2', 'COE', 'BSCE', '1st Year', 30, 0, 'Open'),
('BSCE-2A-G1', 'BSCE 2A-G1', 'COE', 'BSCE', '2nd Year', 30, 0, 'Open'),
('BSCE-2A-G2', 'BSCE 2A-G2', 'COE', 'BSCE', '2nd Year', 30, 0, 'Open'),
('BSCE-3A-G1', 'BSCE 3A-G1', 'COE', 'BSCE', '3rd Year', 30, 0, 'Open'),
('BSCE-3A-G2', 'BSCE 3A-G2', 'COE', 'BSCE', '3rd Year', 30, 0, 'Open'),
('BSCE-4A-G1', 'BSCE 4A-G1', 'COE', 'BSCE', '4th Year', 30, 0, 'Open'),
('BSCE-4A-G2', 'BSCE 4A-G2', 'COE', 'BSCE', '4th Year', 30, 0, 'Open'),
('BSEE-1A-G1', 'BSEE 1A-G1', 'COE', 'BSEE', '1st Year', 30, 0, 'Open'),
('BSEE-1A-G2', 'BSEE 1A-G2', 'COE', 'BSEE', '1st Year', 30, 0, 'Open'),
('BSEE-2A-G1', 'BSEE 2A-G1', 'COE', 'BSEE', '2nd Year', 30, 0, 'Open'),
('BSEE-2A-G2', 'BSEE 2A-G2', 'COE', 'BSEE', '2nd Year', 30, 0, 'Open'),
('BSEE-3A-G1', 'BSEE 3A-G1', 'COE', 'BSEE', '3rd Year', 30, 0, 'Open'),
('BSEE-3A-G2', 'BSEE 3A-G2', 'COE', 'BSEE', '3rd Year', 30, 0, 'Open'),
('BSEE-4A-G1', 'BSEE 4A-G1', 'COE', 'BSEE', '4th Year', 30, 0, 'Open'),
('BSEE-4A-G2', 'BSEE 4A-G2', 'COE', 'BSEE', '4th Year', 30, 0, 'Open'),
('BSBA-1A-G1', 'BSBA 1A-G1', 'CBA', 'BSBA', '1st Year', 30, 0, 'Open'),
('BSBA-1A-G2', 'BSBA 1A-G2', 'CBA', 'BSBA', '1st Year', 30, 0, 'Open'),
('BSBA-2A-G1', 'BSBA 2A-G1', 'CBA', 'BSBA', '2nd Year', 30, 0, 'Open'),
('BSBA-2A-G2', 'BSBA 2A-G2', 'CBA', 'BSBA', '2nd Year', 30, 0, 'Open'),
('BSBA-3A-G1', 'BSBA 3A-G1', 'CBA', 'BSBA', '3rd Year', 30, 0, 'Open'),
('BSBA-3A-G2', 'BSBA 3A-G2', 'CBA', 'BSBA', '3rd Year', 30, 0, 'Open'),
('BSBA-4A-G1', 'BSBA 4A-G1', 'CBA', 'BSBA', '4th Year', 30, 0, 'Open'),
('BSBA-4A-G2', 'BSBA 4A-G2', 'CBA', 'BSBA', '4th Year', 30, 0, 'Open'),
('BSA-1A-G1', 'BSA 1A-G1', 'CBA', 'BSA', '1st Year', 30, 0, 'Open'),
('BSA-1A-G2', 'BSA 1A-G2', 'CBA', 'BSA', '1st Year', 30, 0, 'Open'),
('BSA-2A-G1', 'BSA 2A-G1', 'CBA', 'BSA', '2nd Year', 30, 0, 'Open'),
('BSA-2A-G2', 'BSA 2A-G2', 'CBA', 'BSA', '2nd Year', 30, 0, 'Open'),
('BSA-3A-G1', 'BSA 3A-G1', 'CBA', 'BSA', '3rd Year', 30, 0, 'Open'),
('BSA-3A-G2', 'BSA 3A-G2', 'CBA', 'BSA', '3rd Year', 30, 0, 'Open'),
('BSA-4A-G1', 'BSA 4A-G1', 'CBA', 'BSA', '4th Year', 30, 0, 'Open'),
('BSA-4A-G2', 'BSA 4A-G2', 'CBA', 'BSA', '4th Year', 30, 0, 'Open'),
('ABPSY-1A-G1', 'ABPSY 1A-G1', 'CAS', 'ABPSY', '1st Year', 30, 0, 'Open'),
('ABPSY-1A-G2', 'ABPSY 1A-G2', 'CAS', 'ABPSY', '1st Year', 30, 0, 'Open'),
('ABPSY-2A-G1', 'ABPSY 2A-G1', 'CAS', 'ABPSY', '2nd Year', 30, 0, 'Open'),
('ABPSY-2A-G2', 'ABPSY 2A-G2', 'CAS', 'ABPSY', '2nd Year', 30, 0, 'Open'),
('ABPSY-3A-G1', 'ABPSY 3A-G1', 'CAS', 'ABPSY', '3rd Year', 30, 0, 'Open'),
('ABPSY-3A-G2', 'ABPSY 3A-G2', 'CAS', 'ABPSY', '3rd Year', 30, 0, 'Open'),
('ABPSY-4A-G1', 'ABPSY 4A-G1', 'CAS', 'ABPSY', '4th Year', 30, 0, 'Open'),
('ABPSY-4A-G2', 'ABPSY 4A-G2', 'CAS', 'ABPSY', '4th Year', 30, 0, 'Open'),
('ABCOMM-1A-G1', 'ABCOMM 1A-G1', 'CAS', 'ABCOMM', '1st Year', 30, 0, 'Open'),
('ABCOMM-1A-G2', 'ABCOMM 1A-G2', 'CAS', 'ABCOMM', '1st Year', 30, 0, 'Open'),
('ABCOMM-2A-G1', 'ABCOMM 2A-G1', 'CAS', 'ABCOMM', '2nd Year', 30, 0, 'Open'),
('ABCOMM-2A-G2', 'ABCOMM 2A-G2', 'CAS', 'ABCOMM', '2nd Year', 30, 0, 'Open'),
('ABCOMM-3A-G1', 'ABCOMM 3A-G1', 'CAS', 'ABCOMM', '3rd Year', 30, 0, 'Open'),
('ABCOMM-3A-G2', 'ABCOMM 3A-G2', 'CAS', 'ABCOMM', '3rd Year', 30, 0, 'Open'),
('ABCOMM-4A-G1', 'ABCOMM 4A-G1', 'CAS', 'ABCOMM', '4th Year', 30, 0, 'Open'),
('ABCOMM-4A-G2', 'ABCOMM 4A-G2', 'CAS', 'ABCOMM', '4th Year', 30, 0, 'Open'),
('BSED-1A-G1', 'BSED 1A-G1', 'COED', 'BSED', '1st Year', 30, 0, 'Open'),
('BSED-1A-G2', 'BSED 1A-G2', 'COED', 'BSED', '1st Year', 30, 0, 'Open'),
('BSED-2A-G1', 'BSED 2A-G1', 'COED', 'BSED', '2nd Year', 30, 0, 'Open'),
('BSED-2A-G2', 'BSED 2A-G2', 'COED', 'BSED', '2nd Year', 30, 0, 'Open'),
('BSED-3A-G1', 'BSED 3A-G1', 'COED', 'BSED', '3rd Year', 30, 0, 'Open'),
('BSED-3A-G2', 'BSED 3A-G2', 'COED', 'BSED', '3rd Year', 30, 0, 'Open'),
('BSED-4A-G1', 'BSED 4A-G1', 'COED', 'BSED', '4th Year', 30, 0, 'Open'),
('BSED-4A-G2', 'BSED 4A-G2', 'COED', 'BSED', '4th Year', 30, 0, 'Open'),
('BEED-1A-G1', 'BEED 1A-G1', 'COED', 'BEED', '1st Year', 30, 0, 'Open'),
('BEED-1A-G2', 'BEED 1A-G2', 'COED', 'BEED', '1st Year', 30, 0, 'Open'),
('BEED-2A-G1', 'BEED 2A-G1', 'COED', 'BEED', '2nd Year', 30, 0, 'Open'),
('BEED-2A-G2', 'BEED 2A-G2', 'COED', 'BEED', '2nd Year', 30, 0, 'Open'),
('BEED-3A-G1', 'BEED 3A-G1', 'COED', 'BEED', '3rd Year', 30, 0, 'Open'),
('BEED-3A-G2', 'BEED 3A-G2', 'COED', 'BEED', '3rd Year', 30, 0, 'Open'),
('BEED-4A-G1', 'BEED 4A-G1', 'COED', 'BEED', '4th Year', 30, 0, 'Open'),
('BEED-4A-G2', 'BEED 4A-G2', 'COED', 'BEED', '4th Year', 30, 0, 'Open');

