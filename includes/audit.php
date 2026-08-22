<?php
// ============================================================
//  audit.php — Admin action audit logging
//
//  Records who did what, when, and from where. Call log_action()
//  right after any admin-initiated create/update/delete succeeds,
//  and log_action_as() for auth events (login success/failure)
//  where there may not be an established session yet.
//
//  Failures to write an audit row never block the calling action —
//  auditing is best-effort and must not become a new point of
//  failure for the feature it's observing.
// ============================================================

function log_action(mysqli $conn, string $action, ?string $details = null): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $adminId   = $_SESSION['admin_id']   ?? null;
    $adminUser = $_SESSION['admin_user'] ?? 'unknown';
    log_action_as($conn, $adminId, $adminUser, $action, $details);
}

function log_action_as(mysqli $conn, ?int $adminId, string $adminUser, string $action, ?string $details = null): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    try {
        $stmt = $conn->prepare(
            "INSERT INTO audit_log (admin_id, admin_username, action, details, ip_address)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('issss', $adminId, $adminUser, $action, $details, $ip);
        $stmt->execute();
    } catch (Throwable $e) {
        // Don't let a logging failure break the admin action it's recording.
        error_log('audit.php log_action_as error: ' . $e->getMessage());
    }
}
?>
