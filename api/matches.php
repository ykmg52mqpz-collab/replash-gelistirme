<?php
/**
 * Matches API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/init.php';

$action = $_GET['action'] ?? 'list';

try {
    if (!$pdo) {
        echo json_encode(['ok' => false, 'error' => 'Database connection unavailable']);
        exit;
    }

    // Tesis/tarih/saat ile arama kaldırıldı (maça yalnızca kodla ulaşılır): herkese açık slot listesi artık verilmez.
    if ($action === 'get_slots') {
        http_response_code(410);
        echo json_encode(['ok' => false, 'error' => 'Gone']);
        exit;
    }
    if (false && $action === 'get_slots') {
        $facilityId = $_GET['facility_id'] ?? '';
        $date = $_GET['date'] ?? '';

        if (empty($facilityId) || empty($date)) {
            echo json_encode(['ok' => false, 'error' => 'Missing facility_id or date']);
            exit;
        }

        // Public endpoint: never expose access codes or locked/expired matches (Stealth Mode)
        $slots = [];
        if (!is_match_expired($date)) {
            $stmt = $pdo->prepare("SELECT id, court, time_slot, status FROM matches WHERE facility_id = ? AND date = ? AND status = 'UNLOCKABLE' ORDER BY time_slot ASC");
            $stmt->execute([$facilityId, $date]);
            $slots = $stmt->fetchAll();
        }

        echo json_encode(['ok' => true, 'slots' => $slots]);
        exit;
    }

    if ($action === 'verify_code') {
        $code = replash_normalize_code((string)($_GET['code'] ?? ''));
        if ($code === '') {
            echo json_encode(['ok' => false, 'error' => 'Code required']);
            exit;
        }
        if (replash_code_blocked($pdo)) {
            http_response_code(429);
            echo json_encode(['ok' => false, 'error' => 'Too many attempts']);
            exit;
        }

        // Only unlocked, non-expired matches; never return playback URLs or codes
        $row = replash_match_by_code($pdo, $code, true);
        $match = $row ? array_intersect_key($row, array_flip(['id', 'court', 'date', 'time_slot', 'sport', 'status', 'facility_name'])) : null;

        if (!$match || $match['status'] !== 'UNLOCKABLE' || is_match_expired($match['date'])) {
            replash_code_failed($pdo);
            echo json_encode(['ok' => false, 'error' => 'Geçersiz veya süresi dolmuş kod']);
            exit;
        }

        echo json_encode(['ok' => true, 'match' => $match]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    error_log('api/matches.php: ' . $e->getMessage());   // ayrıntı sunucu günlüğüne, istemciye genel mesaj
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
