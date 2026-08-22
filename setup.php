<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alab E-BulSU Setup</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .box { background:#fff; padding:36px 40px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.1); max-width:480px; width:100%; }
        h2  { color:#1f2a3a; margin-bottom:6px; }
        p   { color:#718096; font-size:14px; margin-bottom:20px; }
        .ok   { background:#d1e7dd; color:#0f5132; padding:12px 16px; border-radius:6px; font-size:13px; margin-bottom:10px; }
        .err  { background:#f8d7da; color:#842029; padding:12px 16px; border-radius:6px; font-size:13px; margin-bottom:10px; }
        .info { background:#cff4fc; color:#055160; padding:12px 16px; border-radius:6px; font-size:13px; margin-bottom:10px; }
        a.btn { display:inline-block; margin-top:18px; padding:10px 22px; background:#2981B8; color:#fff; border-radius:6px; text-decoration:none; font-size:14px; font-weight:bold; }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>


<div class="box">
    <h2>Alab E-BulSU Setup</h2>
    <p>This script sets up the admin account for your server. It can only be run once.</p>

    <?php
    // ── Setup lock — prevents this script from ever being used again
    // (e.g. by an attacker who finds it on a live server) once an admin
    // account has been created. Delete /storage/setup.lock manually if
    // you genuinely need to re-run setup during development.
    $lockDir  = __DIR__ . '/storage';
    $lockFile = $lockDir . '/setup.lock';

    $messages = [];
    $hasError = false;

    if (file_exists($lockFile)) {
        $messages[] = ['err', 'Setup has already been run on this server. For security this script is now locked. Delete storage/setup.lock manually if you need to re-run it during local development.'];
        $hasError = true;
    } else {
        require_once 'includes/conn.php';

        // ── Step 1: Check tables exist ──────────────────────────
        $baseTables = ['college', 'program', 'enrollment', 'admin', 'student_account'];
        $v2Tables   = ['audit_log', 'password_resets'];

        foreach ($baseTables as $t) {
            $r = $conn->query("SHOW TABLES LIKE '$t'");
            if ($r->num_rows === 0) {
                $messages[] = ['err', "Table '$t' not found. Please import websys_db.sql first."];
                $hasError = true;
            } else {
                $messages[] = ['ok', "Table '$t' exists. "];
            }
        }

        // These come from update_schema_v2.sql. Missing them doesn't block
        // setup (core enrollment still works), but flag it clearly so the
        // Audit Log page and password reset don't silently break later.
        foreach ($v2Tables as $t) {
            $r = $conn->query("SHOW TABLES LIKE '$t'");
            if ($r->num_rows === 0) {
                $messages[] = ['err', "Table '$t' not found. Run update_schema_v2.sql, or the Audit Log page and password reset will not work."];
            } else {
                $messages[] = ['ok', "Table '$t' exists. "];
            }
        }

        if (!$hasError) {
            // ── Step 2: Create/reset the admin account with a random,
            // one-time-shown password (never hardcoded) ─────────────
            $generatedPassword = bin2hex(random_bytes(8)); // 16-char random password
            $hash = password_hash($generatedPassword, PASSWORD_DEFAULT);

            $check = $conn->prepare("SELECT admin_id FROM admin WHERE username = ?");
            $adminUsername = 'admin';
            $check->bind_param('s', $adminUsername);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;

            if ($exists) {
                $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
                $stmt->bind_param('ss', $hash, $adminUsername);
                $ok = $stmt->execute();
                $okMsg = 'Admin password set successfully.';
            } else {
                $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
                $stmt->bind_param('ss', $adminUsername, $hash);
                $ok = $stmt->execute();
                $okMsg = 'Admin account created successfully.';
            }

            if ($ok) {
                $messages[] = ['ok', $okMsg];
                $messages[] = ['info', '<strong>Setup complete!</strong> Save this password now — it will not be shown again:<br>Username: <strong>admin</strong> &nbsp;|&nbsp; Password: <strong>' . htmlspecialchars($generatedPassword) . '</strong>'];

                // Write the lock file so this script refuses to run again.
                if (!is_dir($lockDir)) {
                    mkdir($lockDir, 0770, true);
                }
                file_put_contents($lockFile, 'Setup completed on ' . date('c') . "\n");
            } else {
                $messages[] = ['err', 'Failed to set up admin account. Please check your database connection and try again.'];
                error_log('setup.php error: ' . $stmt->error);
                $hasError = true;
            }
        }
    }

    foreach ($messages as $m) {
        echo '<div class="' . htmlspecialchars($m[0]) . '">' . $m[1] . '</div>';
    }
    ?>

    <?php if (!$hasError): ?>
        <a href="admin/login.php" class="btn">Go to Admin Login &rarr;</a>
    <?php endif; ?>
</div>
</body>
</html>
