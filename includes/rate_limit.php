<?php
// ============================================================
//  rate_limit.php — Simple file-based login throttling
//  No schema changes required. Tracks failed attempts per
//  "identifier" (e.g. IP + username/email) in a JSON file with
//  file locking to stay safe under concurrent requests.
// ============================================================

define('RL_STORE_PATH', __DIR__ . '/../storage/rate_limit.json');
define('RL_MAX_ATTEMPTS', 5);      // attempts allowed per window
define('RL_WINDOW_SECONDS', 300);  // 5 minutes
define('RL_LOCKOUT_SECONDS', 300); // how long a key stays locked after hitting the max

/**
 * Builds a rate-limit key from the client IP and an identifier
 * (username/email) so one bad actor can't lock out other users
 * sharing the endpoint, and so brute-forcing a single account is
 * still throttled even from rotating IPs isn't fully solved by this
 * (that needs per-account tracking too, so we key on both).
 */
function rl_key(string $scope, string $identifier): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return $scope . ':' . strtolower(trim($identifier)) . ':' . $ip;
}

function rl_load(): array {
    if (!file_exists(RL_STORE_PATH)) {
        return [];
    }
    $fp = fopen(RL_STORE_PATH, 'r');
    if (!$fp) return [];
    flock($fp, LOCK_SH);
    $contents = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $data = json_decode($contents ?: '[]', true);
    return is_array($data) ? $data : [];
}

function rl_save(array $data): void {
    $dir = dirname(RL_STORE_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }
    $fp = fopen(RL_STORE_PATH, 'c+');
    if (!$fp) return;
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * Returns true if the given key is currently allowed to attempt login.
 * Also opportunistically prunes expired entries.
 */
function rl_is_allowed(string $key): bool {
    $data = rl_load();
    $now = time();
    $entry = $data[$key] ?? null;

    if (!$entry) {
        return true;
    }

    // Locked out?
    if (!empty($entry['locked_until']) && $entry['locked_until'] > $now) {
        return false;
    }

    // Window expired — attempts no longer count.
    if (($now - ($entry['first_attempt'] ?? 0)) > RL_WINDOW_SECONDS) {
        return true;
    }

    return ($entry['count'] ?? 0) < RL_MAX_ATTEMPTS;
}

/** Returns seconds remaining until the key is unlocked, or 0 if not locked. */
function rl_seconds_remaining(string $key): int {
    $data = rl_load();
    $entry = $data[$key] ?? null;
    if (!$entry || empty($entry['locked_until'])) {
        return 0;
    }
    $remaining = $entry['locked_until'] - time();
    return $remaining > 0 ? $remaining : 0;
}

/** Records a failed login attempt for the key. */
function rl_register_failure(string $key): void {
    $data = rl_load();
    $now = time();
    $entry = $data[$key] ?? null;

    if (!$entry || ($now - ($entry['first_attempt'] ?? 0)) > RL_WINDOW_SECONDS) {
        $entry = ['first_attempt' => $now, 'count' => 0, 'locked_until' => 0];
    }

    $entry['count'] = ($entry['count'] ?? 0) + 1;

    if ($entry['count'] >= RL_MAX_ATTEMPTS) {
        $entry['locked_until'] = $now + RL_LOCKOUT_SECONDS;
    }

    $data[$key] = $entry;
    rl_save($data);
}

/** Clears throttle state for a key — call on successful login. */
function rl_reset(string $key): void {
    $data = rl_load();
    if (isset($data[$key])) {
        unset($data[$key]);
        rl_save($data);
    }
}
