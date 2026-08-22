-- ============================================================
--  update_schema_v3.sql — fixes a data-loss risk in the original
--  schema.
--
--  enrollment.section_id was originally FOREIGN KEY ... ON DELETE
--  CASCADE, meaning deleting a single section from the Sections
--  admin page would silently delete every student enrollment record
--  linked to it — no warning, no confirmation beyond the normal
--  "Delete this section?" prompt.
--
--  This changes it to ON DELETE RESTRICT: MySQL will now refuse to
--  delete a section that still has enrollment records pointing to
--  it, and the app (see ajax/manage_sections.php) returns a clear
--  "still has enrolled students" message instead of losing data.
--
--  How to apply:
--    mysql -u root -p websys_db < update_schema_v3.sql
--  (or on Windows PowerShell:)
--    Get-Content update_schema_v3.sql | mysql -u root -p websys_db
-- ============================================================

USE websys_db;

ALTER TABLE enrollment DROP FOREIGN KEY fk_enrollment_section;

ALTER TABLE enrollment
    ADD CONSTRAINT fk_enrollment_section
    FOREIGN KEY (section_id) REFERENCES sections(section_id)
    ON UPDATE CASCADE ON DELETE RESTRICT;

-- ------------------------------------------------------------
--  Second gap closed in this same file: the section-level fix
--  above only stops a SECTION delete from wiping enrollments.
--  It did nothing to stop a PROGRAM or COLLEGE delete from doing
--  the same thing, because:
--
--    fk_enrollment_program  was ON DELETE CASCADE -> deleting a
--      program deleted every enrollment row for it directly,
--      bypassing the section-level RESTRICT entirely.
--    fk_sections_program    was ON DELETE CASCADE -> deleting a
--      program also deleted its sections outright.
--    fk_curriculum_program  was ON DELETE CASCADE -> deleting a
--      program silently deleted its curriculum too.
--
--  Since fk_program_college is ON DELETE CASCADE, deleting a
--  COLLEGE cascades into deleting its programs, which cascaded
--  into all of the above -- so a college delete could still wipe
--  enrollment data even after the section-level fix.
--
--  All three are changed to RESTRICT below. sections.program_code
--  and enrollment.program_code are both NOT NULL columns, so
--  SET NULL is not an option for them; curriculum.program_code is
--  NOT NULL too, so RESTRICT is used there as well for consistency
--  -- curriculum removal should be a deliberate admin action, not
--  a side effect of deleting a program. This also means a college
--  delete now correctly fails (instead of silently cascading) if
--  any of its programs still have curriculum, sections, or
--  enrollment records.
-- ------------------------------------------------------------

ALTER TABLE curriculum DROP FOREIGN KEY fk_curriculum_program;
ALTER TABLE curriculum
    ADD CONSTRAINT fk_curriculum_program
    FOREIGN KEY (program_code) REFERENCES program(program_code)
    ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE sections DROP FOREIGN KEY fk_sections_program;
ALTER TABLE sections
    ADD CONSTRAINT fk_sections_program
    FOREIGN KEY (program_code) REFERENCES program(program_code)
    ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE enrollment DROP FOREIGN KEY fk_enrollment_program;
ALTER TABLE enrollment
    ADD CONSTRAINT fk_enrollment_program
    FOREIGN KEY (program_code) REFERENCES program(program_code)
    ON UPDATE CASCADE ON DELETE RESTRICT;
