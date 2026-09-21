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
function replash_log_sale_event(PDO $pdo, array $match, string $action, string $actor, ?string $reason = null, ?float $amount = null, ?string $currency = null): void {
    try {
        $pdo->prepare("INSERT INTO sale_events (match_id, facility_id, action, actor, reason, amount, currency) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$match['id'], $match['facility_id'], $action, $actor, $reason, $amount, $currency]);
    } catch (Exception $e) {
        error_log('replash_log_sale_event: ' . $e->getMessage());
    }
}

/**
 * Satış anındaki tutarı maça yazar (T07): saha sporunun tesis fiyatı, yoksa standart fiyat.
 * Sonradan fiyat değişse de o satışın raporu değişmez. Döner: [tutar, para birimi].
 */
function replash_snapshot_price(PDO $pdo, string $matchId): array {
    $st = $pdo->prepare("SELECT m.id AS m_id, m.court AS m_court, m.sport AS m_sport, m.sale_price AS m_sale_price,
            m.sale_currency AS m_sale_currency, f.*
        FROM matches m JOIN facilities f ON f.id = m.facility_id WHERE m.id = ? LIMIT 1");
    $st->execute([$matchId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return [null, null];
    if ($row['m_sale_price'] !== null) return [(float)$row['m_sale_price'], $row['m_sale_currency'] ?: REPLASH_BILLING_CURRENCY];
    $fallback = (float)($row['unit_price'] ?? 0);
    $sport = $row['m_sport'] ?: 'football';
    foreach (get_facility_courts_data($row) as $cd) {
        if ($cd['name'] === $row['m_court']) { $fallback = (float)$cd['price']; break; }
    }
    $price = replash_video_price($sport, $fallback, $row);
    $cur = strtoupper(substr((string)($row['currency'] ?? ''), 0, 3)) ?: REPLASH_BILLING_CURRENCY;
    $pdo->prepare("UPDATE matches SET sale_price = ?, sale_currency = ? WHERE id = ? AND sale_price IS NULL")->execute([$price, $cur, $row['m_id']]);
    return [$price, $cur];
}

/** Tutarı olmayan satılmış maçlara güncel kuralla tutar yazar (tek seferlik geçiş; sonra no-op). */
function replash_backfill_sale_prices(PDO $pdo): int {
    try {
        $expr = replash_sale_price_sql_live('m', 'f.unit_price', null, 'f');
        return (int)$pdo->exec("UPDATE matches m JOIN facilities f ON f.id = m.facility_id
            SET m.sale_price = {$expr}, m.sale_currency = COALESCE(NULLIF(f.currency, ''), '" . REPLASH_BILLING_CURRENCY . "')
            WHERE m.sold_at IS NOT NULL AND m.sale_price IS NULL");
    } catch (Exception $e) {
        error_log('replash_backfill_sale_prices: ' . $e->getMessage());
        return 0;
    }
}

/** Satış olayı: tutarı dondurur ve olayı tutarıyla yazar. */
function replash_record_sale(PDO $pdo, array $match, string $action, string $actor, ?string $reason = null): void {
    [$amount, $cur] = replash_snapshot_price($pdo, $match['id']);
    replash_log_sale_event($pdo, $match, $action, $actor, $reason, $amount, $cur);
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

/*
 * Saklama: süre maçın gerçek bitişinden sayılır. Satılmayan kayıt REPLASH_RETENTION_UNSOLD_DAYS (3),
 * satılan kayıt RETENTION_DAYS (7) gün sonra erişime kapanır: durum EXPIRED olur ve kayıtlı video
 * adresi silinir, kodlar ve eski bağlantılar çalışmaz. Satış kaydı (sold_at, billable, sale_events)
 * faturalama için korunur. Kamera bulutundaki fiziksel dosyanın silinmesi sağlayıcının saklama
 * ayarına bağlıdır, buradan doğrulanamaz.
 * Sayfa açıldıkça saatte en fazla bir kez çalışır; cron/purge.php ile zamanlanabilir.
 */
const REPLASH_RETENTION_UNSOLD_DAYS = 3;

/** Maçın erişim süresinin bittiği an (timestamp). */
function replash_expires_at(array $match): int {
    [, $end] = replash_slot_bounds($match);
    $sold = !empty($match['sold_at']) || ($match['status'] ?? '') === 'UNLOCKABLE';
    return $end + ($sold ? RETENTION_DAYS : REPLASH_RETENTION_UNSOLD_DAYS) * 86400;
}

function replash_match_expired(array $match): bool {
    return ($match['status'] ?? '') === 'EXPIRED' || time() >= replash_expires_at($match);
}

function replash_purge_expired(PDO $pdo, bool $force = false): int {
    try {
        if (!$force) {
            $last = (int)get_setting('last_retention_purge', 0);
            if (time() - $last < 3600) return 0;
        }
        // Aday: en az 2 gün önceki maçlar (satılmayan en erken bitiş + 3 günde kapanır)
        $stmt = $pdo->prepare("SELECT id, date, time_slot, status, sold_at, duration_sec FROM matches
            WHERE status <> 'EXPIRED' AND `date` <= DATE_SUB(CURDATE(), INTERVAL 2 DAY)");
        $stmt->execute();
        $ids = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $m) {
            if (replash_match_expired($m)) $ids[] = $m['id'];
        }
        foreach (array_chunk($ids, 200) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $pdo->prepare("UPDATE matches SET status = 'EXPIRED', playback_url = NULL WHERE id IN ($in)")->execute($chunk);
        }
        $pdo->exec("DELETE vs FROM viewer_sessions vs JOIN matches m ON m.id = vs.match_id WHERE m.status = 'EXPIRED'");
        set_setting('last_retention_purge', (string)time());
        return count($ids);
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
const REPLASH_DELAY_GRACE_MIN = 30;

/** Klip süresi sınırları (saniye) — oyuncu sayfası ve video API'si aynı değeri kullanır. */
const REPLASH_CLIP_MIN = 15;
const REPLASH_CLIP_MAX = 40; // after the estimate, show "taking longer than expected"

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
