<?php
/**
 * POST /api/hooks/camera-status
 * Webhook endpoint to update camera status (online/offline).
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');

// Signature Verification
$signatureHeader = $_SERVER['HTTP_X_REPLASH_SIGNATURE'] ?? '';
$expectedSignature = hash_hmac('sha256', $rawBody, REPLASH_WEBHOOK_SECRET);
$providedSig = str_replace('sha256=', '', $signatureHeader);

if (!empty($signatureHeader) && !hash_equals($expectedSignature, $providedSig)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Invalid signature']);
    exit;
}

$data = json_decode($rawBody, true);
if (!$data || !isset($data['channelId']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing channelId or status']);
    exit;
}

$channelId = $data['channelId'];
$status = in_array(strtolower($data['status']), ['online', 'offline']) ? strtolower($data['status']) : 'offline';

try {
    $stmt = $pdo->prepare("UPDATE cameras SET status = ?, last_seen_at = NOW() WHERE channel_id = ? OR id = ?");
    $stmt->execute([$status, $channelId, $channelId]);

    echo json_encode(['ok' => true, 'channelId' => $channelId, 'status' => $status]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
