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

    if ($action === 'get_slots') {
        $facilityId = $_GET['facility_id'] ?? '';
        $date = $_GET['date'] ?? '';

        if (empty($facilityId) || empty($date)) {
            echo json_encode(['ok' => false, 'error' => 'Missing facility_id or date']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, court, time_slot, status, access_code FROM matches WHERE facility_id = ? AND date = ? ORDER BY time_slot ASC");
        $stmt->execute([$facilityId, $date]);
        $slots = $stmt->fetchAll();

        echo json_encode(['ok' => true, 'slots' => $slots]);
        exit;
    }

    if ($action === 'verify_code') {
        $code = trim($_GET['code'] ?? '');
        if (empty($code)) {
            echo json_encode(['ok' => false, 'error' => 'Code required']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT m.*, f.name as facility_name FROM matches m JOIN facilities f ON m.facility_id = f.id WHERE m.access_code = ? LIMIT 1");
        $stmt->execute([$code]);
        $match = $stmt->fetch();

        if (!$match) {
            echo json_encode(['ok' => false, 'error' => 'Geçersiz veya süresi dolmuş kod']);
            exit;
        }

        echo json_encode(['ok' => true, 'match' => $match]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
