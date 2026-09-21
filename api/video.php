<?php
/**
 * Video Access & Streaming Controller
 * GET /api/video.php?id=m-xxxx&t=token OR ?code=ACCESS_CODE
 * Secure access control, validation & 302 CDN redirect or direct stream
 */

require_once __DIR__ . '/../includes/init.php';

// Oyuncuya düz metin hata (4 dil); iç hata ayrıntısı gösterilmez
function replash_video_error(int $status, string $key): void {
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    exit(__('api.video.' . $key));
}

$matchId = $_GET['id'] ?? '';
$accessCode = trim($_GET['code'] ?? '');

try {
    if (!$pdo) {
        replash_video_error(503, 'server');
    }

    $match = null;
    $accessCode = replash_normalize_code($accessCode);
    if ($accessCode !== '' && replash_code_blocked($pdo)) {
        http_response_code(429);
        replash_video_error(429, 'too_many');
    }
    if (!empty($accessCode)) {
        $match = replash_match_by_code($pdo, $accessCode);
        // id verildiyse kod o maça ait olmalı
        if ($match && !empty($matchId) && $match['id'] !== $matchId) $match = null;
        if (!$match || !was_match_sold($match)) replash_code_failed($pdo);
    } elseif (!empty($matchId)) {
        http_response_code(403);
        replash_video_error(403, 'code_required');
    }

    // Stealth mode: unsold matches look exactly like non-existent ones
    if (!$match || !was_match_sold($match)) {
        http_response_code(404);
        replash_video_error(404, 'not_found');
    }

    // Retention Check: 7-day expiry rule (only reached for sold matches)
    if (is_match_expired($match['date']) || $match['status'] === 'EXPIRED') {
        http_response_code(410);
        replash_video_error(410, 'expired');
    }

    // Increment Views
    replash_record_match_open($pdo, $match['id']);

    // Build dynamic playback URL with exact time & duration
    $rawPlayback = !empty($match['playback_url']) ? $match['playback_url'] : '';
    $requestedCamId = $_GET['camera_id'] ?? '';
    
    if (!empty($requestedCamId) && $pdo) {
        // Kamera maçın kendi sahasına ait olmalı (aynı tesisteki başka saha açılamaz)
        $camStmt = $pdo->prepare("SELECT stream_url FROM cameras WHERE id = ? AND facility_id = ? AND court = ? LIMIT 1");
        $camStmt->execute([$requestedCamId, $match['facility_id'], $match['court']]);
        $camStream = $camStmt->fetchColumn();
        if (!$camStream) {
            replash_video_error(404, 'not_found');
        }
        $rawPlayback = $camStream;
    } elseif (empty($rawPlayback) && !empty($match['camera_id']) && $pdo) {
        $camStmt = $pdo->prepare("SELECT stream_url FROM cameras WHERE id = ? LIMIT 1");
        $camStmt->execute([$match['camera_id']]);
        $camStream = $camStmt->fetchColumn();
        if ($camStream) {
            $rawPlayback = $camStream;
        }
    }

    // Check if download or clipping was requested
    $isDownload = (isset($_GET['download']) && $_GET['download'] == '1');
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string)($_GET['filename'] ?? ''))) ?: 'replash.mp4';
    // Zaman aralığı maçın kendi süresiyle sınırlı; klip en fazla REPLASH_CLIP_MAX saniye
    [$slotStart, $slotEnd] = replash_slot_bounds($match);
    $matchLen = max(60, $slotEnd - $slotStart);
    $isClip = isset($_GET['duration']) || isset($_GET['start_offset']);
    $startOffset = min(max(0, (int)($_GET['start_offset'] ?? 0)), $matchLen - 1);
    if ($isClip) {
        $duration = (int)($_GET['duration'] ?? REPLASH_CLIP_MAX);
        $duration = min(max(1, $duration), REPLASH_CLIP_MAX, $matchLen - $startOffset);
    } else {
        $duration = $matchLen;
    }

    $finalPlaybackUrl = buildMatchPlaybackUrl($rawPlayback, $match['date'], $match['time_slot'], $duration, $isDownload, $filename, $startOffset, $isClip);
    if (!preg_match('#^https?://#i', $finalPlaybackUrl)) {
        replash_video_error(404, 'not_found');
    }
    if ($finalPlaybackUrl === '') {
        http_response_code(404);
        replash_video_error(404, 'not_found');
    }

    if ($isDownload) {
        // Enforce attachment header to make the browser save the file rather than play it
        header('Content-Description: File Transfer');
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        // Stream remote video in chunks
        $context = stream_context_create([
            'http' => [
                'timeout' => 600,
                'follow_location' => 1,
                'header' => "User-Agent: Replash-Video-Downloader/1.0\r\n"
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true
            ]
        ]);

        $handle = @fopen($finalPlaybackUrl, 'rb', false, $context);
        if ($handle) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            fpassthru($handle);
            fclose($handle);
            exit;
        }

        // Fallback to 302 redirect if fopen fails
        header("Location: " . $finalPlaybackUrl, true, 302);
        exit;
    }

    header("Location: " . $finalPlaybackUrl, true, 302);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    error_log('api/video.php: ' . $e->getMessage());
    replash_video_error(500, 'server');
}
