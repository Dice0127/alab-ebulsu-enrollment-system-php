<?php
// ============================================================
//  csrf.php — CSRF token generation & validation helper
// ============================================================

function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the CSRF token sent with a request.
 * On failure, sends a 403 JSON response and exits — call this at the
 * top of any AJAX endpoint that changes data (POST save/update/delete).
 */
function csrf_verify(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        header('Content-Type: application/json');
        // Note: intentionally NOT sending a 403 status here. The site's existing
        // JS reads response.success/message on a normal 200 response — a non-2xx
        // status would instead trigger jQuery's generic .fail() handler and show
        // a generic "Server error" message instead of this one.
        echo json_encode(['success' => false, 'message' => 'Invalid or expired request. Please refresh the page and try again.']);
        exit;
    }
}

/** Outputs a hidden input field carrying the token, for use inside <form> tags. */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}
?>
