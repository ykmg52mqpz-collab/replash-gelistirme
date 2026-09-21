<?php
/**
 * Video Access & Streaming Controller
 * GET /api/video.php?id=m-xxxx&t=token OR ?code=ACCESS_CODE
 * Secure access control, validation & 302 CDN redirect or direct stream
 */

require_once __DIR__ . '/../includes/init.php';

$matchId = $_GET['id'] ?? '';
$accessCode = trim($_GET['code'] ?? '');

try {
    if (!$pdo) {
        die("Veritabanı bağlantısı yok.");
    }

    $match = null;
    $accessCode = replash_normalize_code($accessCode);
    if ($accessCode !== '' && replash_code_blocked($pdo)) {
        http_response_code(429);
        die("Çok fazla hatalı deneme. Lütfen daha sonra tekrar deneyin.");
    }
    if (!empty($accessCode)) {
        $match = replash_match_by_code($pdo, $accessCode);
        // id verildiyse kod o maça ait olmalı
        if ($match && !empty($matchId) && $match['id'] !== $matchId) $match = null;
        if (!$match || !was_match_sold($match)) replash_code_failed($pdo);
    } elseif (!empty($matchId)) {
        http_response_code(403);
        die("Bu videoya erişmek için erişim kodu gereklidir.");
    }

    // Stealth mode: unsold matches look exactly like non-existent ones
    if (!$match || !was_match_sold($match)) {
        http_response_code(404);
        die("Maç kaydı bulunamadı.");
    }

    // Retention Check: 7-day expiry rule (only reached for sold matches)
    if (is_match_expired($match['date']) || $match['status'] === 'EXPIRED') {
        http_response_code(410);
        die("Bu maçın 7 günlük saklama süresi dolduğu için video kaydı silinmiştir.");
    }

    // Increment Views
    replash_record_match_open($pdo, $match['id']);

    // Build dynamic playback URL with exact time & duration
    $rawPlayback = !empty($match['playback_url']) ? $match['playback_url'] : '';
    $requestedCamId = $_GET['camera_id'] ?? '';
    
    if (!empty($requestedCamId) && $pdo) {
        $camStmt = $pdo->prepare("SELECT stream_url FROM cameras WHERE id = ? AND facility_id = ? LIMIT 1");
        $camStmt->execute([$requestedCamId, $match['facility_id']]);
        $camStream = $camStmt->fetchColumn();
        if ($camStream) {
            $rawPlayback = $camStream;
        }
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
    $filename = !empty($_GET['filename']) ? $_GET['filename'] : 'replash.mp4';
    $startOffset = max(0, (int)($_GET['start_offset'] ?? 0));
    $duration = (int)($_GET['duration'] ?? 0);
    if ($duration <= 0) {
        $duration = (int)($match['duration_sec'] ?? 3600);
    }

    $finalPlaybackUrl = buildMatchPlaybackUrl($rawPlayback, $match['date'], $match['time_slot'], $duration, $isDownload, $filename, $startOffset);
    if ($finalPlaybackUrl === '') {
        http_response_code(404);
        die("Maç kaydı bulunamadı.");
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
                'verify_peer' => false,
                'verify_peer_name' => false
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
    die("Sunucu Hatası: " . $e->getMessage());
}
