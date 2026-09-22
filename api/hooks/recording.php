<?php
/**
 * POST /api/hooks/recording
 * Webhook endpoint called by external video provider when a match recording is ready.
 * Implements HMAC-SHA256 signature verification as defined in INTEGRATION.md.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');

// 1. Signature Verification
$signatureHeader = $_SERVER['HTTP_X_REPLASH_SIGNATURE'] ?? '';
$expectedSignature = hash_hmac('sha256', $rawBody, REPLASH_WEBHOOK_SECRET);

$providedSig = str_replace('sha256=', '', $signatureHeader);

// INTEGRATION.md: every request must be signed. Unsigned requests are rejected unless the
// admin setting webhook_allow_unsigned = 1 (temporary, only if the provider cannot sign yet).
$allowUnsigned = (string)get_setting('webhook_allow_unsigned', '0') === '1';
if (($signatureHeader === '' && !$allowUnsigned) || ($signatureHeader !== '' && !hash_equals($expectedSignature, $providedSig))) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Invalid signature']);
    exit;
}

$data = json_decode($rawBody, true);
if (!$data || !isset($data['channelId']) || !isset($data['playbackUrl'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing channelId or playbackUrl']);
    exit;
}

$channelId = $data['channelId'];
$playbackUrl = $data['playbackUrl'];
$playbackType = $data['playbackType'] ?? (strpos($playbackUrl, '.m3u8') !== false ? 'hls' : 'mp4');
$thumbnailUrl = $data['thumbnailUrl'] ?? null;
$durationSec = (int)($data['durationSec'] ?? 3600);
$sizeBytes = isset($data['sizeBytes']) ? (int)$data['sizeBytes'] : null;
$recordingId = $data['recordingId'] ?? null;

// Determine slot and date
$date = $data['date'] ?? null;
$slot = $data['slot'] ?? null;

if (!$slot && isset($data['startedAt'])) {
    $time = strtotime($data['startedAt']);
    $hour = date('H', $time);
    $nextHour = str_pad((int)$hour + 1, 2, '0', STR_PAD_LEFT);
    $slot = "{$hour}:00–{$nextHour}:00";
    if (!$date) $date = date('Y-m-d', $time);
}

if (!$date) $date = date('Y-m-d');
if (!$slot) $slot = '18:00–19:00';

try {
    // 2. Find Camera by channelId or camera id
    $stmt = $pdo->prepare("SELECT c.*, f.id as fac_id, f.sport AS facility_sport, f.courts, f.unit_price, f.sport_prices FROM cameras c JOIN facilities f ON c.facility_id = f.id WHERE c.channel_id = ? OR c.id = ? LIMIT 1");
    $stmt->execute([$channelId, $channelId]);
    $cam = $stmt->fetch();
    if ($cam) {
        // Maçın sporu: kameranın bağlı olduğu sahanın sporu (tesis birden çok spor sunabilir)
        $cam['sport'] = trim(explode(',', (string)$cam['facility_sport'])[0]) ?: 'football';
        foreach (get_facility_courts_data(['courts' => $cam['courts'], 'sport' => $cam['sport'], 'unit_price' => $cam['unit_price'], 'sport_prices' => $cam['sport_prices']]) as $cd) {
            if ($cd['name'] === $cam['court']) { $cam['sport'] = $cd['sport']; break; }
        }
    }

    if (!$cam) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'unknown_channel']);
        exit;
    }

    // Mark camera online
    $updCam = $pdo->prepare("UPDATE cameras SET status = 'online', last_seen_at = NOW() WHERE id = ?");
    $updCam->execute([$cam['id']]);

    // 3. Upsert Match (Idempotent per facility + court + date + slot)
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE facility_id = ? AND court = ? AND date = ? AND time_slot = ? LIMIT 1");
    $stmt->execute([$cam['facility_id'], $cam['court'], $date, $slot]);
    $existing = $stmt->fetch();

    $slotStart = explode('–', $slot)[0];
    $dateCompact = str_replace('-', '', $date);
    $slotCompact = str_replace(':', '', $slotStart);
    $matchCode = "MATCH-{$dateCompact}-{$slotCompact}";

    if ($existing) {
        $updMatch = $pdo->prepare("UPDATE matches SET 
            playback_url = ?, 
            playback_type = ?, 
            thumbnail_url = ?, 
            duration_sec = ?, 
            size_bytes = ?, 
            recording_id = ?,
            available_at = NOW()
            WHERE id = ?");
        $updMatch->execute([$playbackUrl, $playbackType, $thumbnailUrl, $durationSec, $sizeBytes, $recordingId, $existing['id']]);

        echo json_encode([
            'ok' => true,
            'matchId' => $existing['id'],
            'code' => $existing['code'],
            'status' => $existing['status'],
            'upserted' => true
        ]);
    } else {
        $matchId = 'm-' . bin2hex(random_bytes(5));
        $accessCode = replash_locked_placeholder(); // 4 haneli kod kilit açılınca verilir

        $insMatch = $pdo->prepare("INSERT INTO matches 
            (id, code, access_code, camera_id, facility_id, court, date, time_slot, status, playback_url, playback_type, thumbnail_url, duration_sec, size_bytes, recording_id, sport, recorded_at, available_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'LOCKED', ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $insMatch->execute([
            $matchId,
            $matchCode,
            $accessCode,
            $cam['id'],
            $cam['facility_id'],
            $cam['court'],
            $date,
            $slot,
            $playbackUrl,
            $playbackType,
            $thumbnailUrl,
            $durationSec,
            $sizeBytes,
            $recordingId,
            $cam['sport']
        ]);

        echo json_encode([
            'ok' => true,
            'matchId' => $matchId,
            'code' => $matchCode,
            'status' => 'LOCKED',
            'upserted' => false
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
