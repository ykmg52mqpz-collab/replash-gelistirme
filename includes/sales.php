<?php
/**
 * Replash Sales Control — "Video Sold" audit trail and the staff undo rule.
 *
 * Why: staff may press "Video sold" on the wrong match, so a narrow undo exists.
 * It must not become a way to hide paid sales from monthly billing, so:
 *  - Staff can undo only within REPLASH_UNDO_WINDOW_MIN minutes of the sale,
 *  - and only if the video was never opened (watched or downloaded) by anyone,
 *  - and must give a reason.
 *  - Every sale and undo is written to `sale_events` and never deleted,
 *    so Replash sees undo counts per facility.
 *  - Anything outside these limits can only be reversed by Replash admin.
 */

const REPLASH_UNDO_WINDOW_MIN = 10;

// Oyuncunun kesebileceği klip süresi (saniye). Hazır seçenekler find.php'de.
const REPLASH_CLIP_MIN_SEC = 15;
const REPLASH_CLIP_MAX_SEC = 45;

/**
 * Mark a match as opened (first watch or download). The first open makes the sale final.
 * Session-deduplicated so page reloads do not inflate views.
 */
function replash_record_match_open(PDO $pdo, string $matchId): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $seen = $_SESSION['opened_matches'] ?? [];
        if (!empty($seen[$matchId])) return;
        $seen[$matchId] = 1;
        $_SESSION['opened_matches'] = $seen;
    }
    try {
        $pdo->prepare("UPDATE matches SET views = views + 1, first_opened_at = COALESCE(first_opened_at, NOW()) WHERE id = ?")
            ->execute([$matchId]);
    } catch (Exception $e) {
        error_log('replash_record_match_open: ' . $e->getMessage());
    }
}

/**
 * Append an entry to the sale audit trail.
 * $action: sold | undone | admin_sold | admin_undone
 */
function replash_log_sale_event(PDO $pdo, array $match, string $action, string $actor, ?string $reason = null): void {
    try {
        $pdo->prepare("INSERT INTO sale_events (match_id, facility_id, action, actor, reason) VALUES (?, ?, ?, ?, ?)")
            ->execute([$match['id'], $match['facility_id'], $action, $actor, $reason]);
    } catch (Exception $e) {
        error_log('replash_log_sale_event: ' . $e->getMessage());
    }
}

/**
 * Can facility staff undo this sale right now?
 * Returns ['allowed' => bool, 'why' => 'ok'|'not_sold'|'opened'|'expired', 'seconds_left' => int]
 */
function replash_staff_undo_status(array $match): array {
    if (($match['status'] ?? '') !== 'UNLOCKABLE' || empty($match['sold_at'])) {
        return ['allowed' => false, 'why' => 'not_sold', 'seconds_left' => 0];
    }
    if (!empty($match['first_opened_at'])) {
        return ['allowed' => false, 'why' => 'opened', 'seconds_left' => 0];
    }
    $left = strtotime($match['sold_at']) + REPLASH_UNDO_WINDOW_MIN * 60 - time();
    if ($left <= 0) {
        return ['allowed' => false, 'why' => 'expired', 'seconds_left' => 0];
    }
    return ['allowed' => true, 'why' => 'ok', 'seconds_left' => $left];
}

/**
 * Number of staff undos for a facility in a month ('YYYY-MM'), for reports.
 */
function replash_undo_count(PDO $pdo, ?string $facilityId, string $yearMonth): int {
    $sql = "SELECT COUNT(*) FROM sale_events WHERE action = 'undone' AND DATE_FORMAT(created_at, '%Y-%m') = ?";
    $params = [$yearMonth];
    if ($facilityId !== null) {
        $sql .= " AND facility_id = ?";
        $params[] = $facilityId;
    }
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * 7-day retention (Platform Brief): from the match date, every recording — sold or not —
 * is removed after RETENTION_DAYS. Status becomes EXPIRED and the stored playback URL is
 * cleared, so access codes and old links stop working. Sold matches keep sold_at/billable
 * for monthly billing.
 * Runs at most once per hour on normal page loads; cron/purge.php can run it on a schedule.
 */
function replash_purge_expired(PDO $pdo, bool $force = false): int {
    try {
        if (!$force) {
            $last = (int)get_setting('last_retention_purge', 0);
            if (time() - $last < 3600) return 0;
        }
        $stmt = $pdo->prepare("UPDATE matches SET status = 'EXPIRED', playback_url = NULL
            WHERE status <> 'EXPIRED' AND DATE_ADD(`date`, INTERVAL ? DAY) < CURDATE()");
        $stmt->execute([RETENTION_DAYS]);
        $n = $stmt->rowCount();
        $pdo->exec("DELETE vs FROM viewer_sessions vs JOIN matches m ON m.id = vs.match_id WHERE m.status = 'EXPIRED'");
        set_setting('last_retention_purge', (string)time());
        return $n;
    } catch (Exception $e) {
        error_log('replash_purge_expired: ' . $e->getMessage());
        return 0;
    }
}

/*
 * Access codes (Platform Brief v3.4): 4-digit numeric, given only when a match is unlocked.
 * Locked matches carry a random placeholder that can never be typed as a code.
 * Re-locking replaces the code with a placeholder, so the old code stops working.
 * A 4-digit space is guessable, so failed attempts are rate-limited per client.
 */
const REPLASH_CODE_DIGITS = 4;
const REPLASH_CODE_MAX_FAILS = 10;      // failed attempts allowed ...
const REPLASH_CODE_WINDOW_MIN = 15;     // ... per this many minutes, per client

function replash_locked_placeholder(): string {
    return 'L-' . bin2hex(random_bytes(8));
}

/** New 4-digit code, unique among currently watchable (unlocked) matches. */
function replash_new_access_code(PDO $pdo): string {
    $max = 10 ** REPLASH_CODE_DIGITS - 1;
    $check = $pdo->prepare("SELECT 1 FROM matches WHERE access_code = ? AND status = 'UNLOCKABLE' LIMIT 1");
    for ($i = 0; $i < 200; $i++) {
        $code = str_pad((string)random_int(0, $max), REPLASH_CODE_DIGITS, '0', STR_PAD_LEFT);
        $check->execute([$code]);
        if (!$check->fetchColumn()) return $code;
    }
    throw new RuntimeException('No free access code');
}

/** "48 21", "4821" → "4821"; older alphanumeric codes are kept (upper-case, no spaces). */
function replash_normalize_code(string $raw): string {
    $c = strtoupper(preg_replace('/\s+/', '', $raw));
    if (preg_match('/^[\d\-]+$/', $c)) $c = str_replace('-', '', $c);
    return $c;
}

/**
 * Match for an access code. A 4-digit code can be reused after its match is locked or
 * expired, so the watchable match wins, then the most recently sold one.
 */
function replash_match_by_code(PDO $pdo, string $code, bool $withFacility = false): ?array {
    if ($code === '' || str_starts_with($code, 'L-')) return null;
    $sql = $withFacility
        ? "SELECT m.*, f.name AS facility_name, f.city, f.sport AS facility_sport FROM matches m JOIN facilities f ON m.facility_id = f.id"
        : "SELECT m.* FROM matches m";
    $stmt = $pdo->prepare($sql . " WHERE UPPER(m.access_code) = ? ORDER BY (m.status = 'UNLOCKABLE') DESC, m.sold_at DESC LIMIT 1");
    $stmt->execute([$code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/** Hashed client id for rate limiting; $scope keeps code, PIN and admin attempts apart. */
function replash_client_key(string $scope = 'code'): string {
    // Behind Cloudflare the visitor IP is in CF-Connecting-IP
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
    return hash('sha256', $scope . '|' . $ip . '|' . (defined('REPLASH_TOKEN_SECRET') ? REPLASH_TOKEN_SECRET : 'replash'));
}

/** Too many failed attempts of this kind from this client within the window? */
function replash_attempt_blocked(PDO $pdo, string $scope, int $maxFails, int $windowMin): bool {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM code_attempts WHERE client_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $stmt->execute([replash_client_key($scope), $windowMin]);
        return (int)$stmt->fetchColumn() >= $maxFails;
    } catch (Exception $e) {
        return false;
    }
}

function replash_attempt_failed(PDO $pdo, string $scope): void {
    try {
        $pdo->prepare("INSERT INTO code_attempts (client_key) VALUES (?)")->execute([replash_client_key($scope)]);
        if (random_int(1, 50) === 1) $pdo->exec("DELETE FROM code_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    } catch (Exception $e) {
        error_log('replash_attempt_failed: ' . $e->getMessage());
    }
}

/** Access codes: 10 wrong codes per 15 minutes. */
function replash_code_blocked(PDO $pdo): bool {
    return replash_attempt_blocked($pdo, 'code', REPLASH_CODE_MAX_FAILS, REPLASH_CODE_WINDOW_MIN);
}

function replash_code_failed(PDO $pdo): void {
    replash_attempt_failed($pdo, 'code');
}

/** Facility PIN and admin login: 5 wrong tries per 15 minutes. */
const REPLASH_LOGIN_MAX_FAILS = 5;
const REPLASH_LOGIN_WINDOW_MIN = 15;

/*
 * Video readiness for a sold match (shown on the player page).
 * A match can be sold before its recording exists (pre-sale at the desk). The player's code
 * works right away; the page shows the stage until the video can be played.
 * Ready = recording reported by the camera (available_at), or, for time-window cloud playback,
 * the slot ended PROCESSING_MINUTES ago and the court has a camera.
 */
const REPLASH_DELAY_GRACE_MIN = 30; // after the estimate, show "taking longer than expected"

/** [start, end] timestamps of a match slot like "21:00–22:00" (end may be 24:00). */
function replash_slot_bounds(array $match): array {
    $parts = preg_split('/[–\-]/u', (string)$match['time_slot']);
    $start = strtotime($match['date'] . ' ' . trim($parts[0] ?? '00:00'));
    $endRaw = trim($parts[1] ?? '');
    if ($endRaw === '24:00') $endRaw = '23:59:59';
    $end = $endRaw !== '' ? strtotime($match['date'] . ' ' . $endRaw) : $start + (int)($match['duration_sec'] ?? 3600);
    if ($end <= $start) $end += 86400;
    if ($endRaw === '23:59:59') $end += 1;
    return [$start, $end];
}

/**
 * Returns ['state' => ready|upcoming|playing|processing|delayed, 'start', 'end', 'eta'] (timestamps).
 */
function replash_video_state(PDO $pdo, array $match, ?int $now = null): array {
    $now = $now ?? time();
    [$start, $end] = replash_slot_bounds($match);
    $eta = $end + (defined('PROCESSING_MINUTES') ? PROCESSING_MINUTES : 8) * 60;
    $out = ['start' => $start, 'end' => $end, 'eta' => $eta];

    if (!empty($match['available_at']) && strtotime($match['available_at']) <= $now) return ['state' => 'ready'] + $out;
    if ($now < $start) return ['state' => 'upcoming'] + $out;
    if ($now < $end) return ['state' => 'playing'] + $out;
    if ($now >= $eta) {
        $cam = $pdo->prepare("SELECT 1 FROM cameras WHERE facility_id = ? AND court = ? AND stream_url <> '' LIMIT 1");
        $cam->execute([$match['facility_id'], $match['court']]);
        if ($cam->fetchColumn()) return ['state' => 'ready'] + $out;
    }
    return ['state' => ($now < $eta + REPLASH_DELAY_GRACE_MIN * 60) ? 'processing' : 'delayed'] + $out;
}
