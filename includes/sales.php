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
