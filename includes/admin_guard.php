<?php
// ============================================================
//  admin_guard.php — require an authenticated admin session
//  before an AJAX endpoint runs.
//
//  Include this at the top of every ajax/*.php file that performs
//  an admin-only action (reading/writing enrollment, student,
//  program, college, section, curriculum, or subject data).
//  CSRF tokens alone do NOT prove the caller is logged in — a
//  token is issued to any session, including anonymous visitors
//  on the public login page. This check is what actually confirms
//  the caller is an authenticated admin.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin authentication required.']);
    exit;
}
