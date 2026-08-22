# WebSys — Student Enrollment Management System

IT 211 group project (Alab E-BulSU).

## Setup

1. Import the schema:
   ```bash
   mysql -u root -p websys_db < websys_db.sql
   mysql -u root -p websys_db < update_schema.sql
   mysql -u root -p websys_db < update_schema_v2.sql
   mysql -u root -p websys_db < update_schema_v3.sql
   ```
   `update_schema_v2.sql` adds the `audit_log` table (used by the Audit
   Log admin page) and the password-reset tokens table — skipping it
   will make Audit Log hang on "Loading..." and break password reset.
   `update_schema_v3.sql` fixes the `enrollment` → `sections` foreign
   key so deleting a section can no longer cascade-delete student
   enrollment records — safe (and recommended) to run even on a fresh
   `websys_db.sql` import, since the base schema also carries this fix.
2. (Optional) Load sample reference data — a few colleges, programs,
   courses, curriculum entries, and sections — so the admin pages
   aren't empty on first run:
   ```bash
   mysql -u root -p websys_db < sample_data.sql
   ```
3. Copy `.env.example` to `.env` and fill in your local DB credentials:
   ```bash
   cp .env.example .env
   ```
4. Visit `setup.php` in your browser once to create the admin account.
   It will generate and display a random admin password **one time only**
   and then lock itself (`storage/setup.lock`). Save that password —
   it will not be shown again. If you need to run setup again during
   local development, delete `storage/setup.lock` first.
5. Log in at `admin/login.php` with the generated credentials.

## Notes

- `.env` is git-ignored — never commit real database credentials.
- `storage/` holds runtime files (setup lock, login rate-limit state)
  and is git-ignored.
- See `docs/IMPLEMENTATION_SUMMARY.md` for notes on the registration/profile
  field alignment update.
