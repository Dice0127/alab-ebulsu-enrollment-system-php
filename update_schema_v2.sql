-- Update v2: adds audit logging and student password-reset support.
-- Run this after update_schema.sql:
--   mysql -u root websys_db < update_schema_v2.sql

-- Tracks admin-initiated create/update/delete/auth actions for accountability.
CREATE TABLE IF NOT EXISTS audit_log (
    log_id INT NOT NULL AUTO_INCREMENT,
    admin_id INT NULL,
    admin_username VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_audit_created_at (created_at),
    INDEX idx_audit_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password reset tokens for students. Only the SHA-256 hash of the token is
-- stored (same principle as never storing plaintext passwords) — the raw
-- token only ever exists in the URL sent to the student.
CREATE TABLE IF NOT EXISTS password_resets (
    reset_id INT NOT NULL AUTO_INCREMENT,
    account_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (reset_id),
    CONSTRAINT fk_reset_account FOREIGN KEY (account_id) REFERENCES student_account(account_id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_reset_token (token_hash),
    INDEX idx_reset_account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
