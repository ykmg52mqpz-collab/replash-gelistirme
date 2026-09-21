<?php
/**
 * Global Initialization File
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/billing.php';

// Ensure DB is initialized
$dbError = null;
try {
    $pdo = DB::getConnection();
} catch (Exception $e) {
    $pdo = null;
    $dbError = $e->getMessage();
    error_log("DB Connection Warning: " . $e->getMessage());
}

// Language Handling
if (isset($_GET['lang']) && in_array(strtolower($_GET['lang']), $SUPPORTED_LANGUAGES)) {
    $_SESSION['lang'] = strtolower($_GET['lang']);
    setcookie('replash_lang', $_SESSION['lang'], time() + (86400 * 365), "/");
} elseif (isset($_COOKIE['replash_lang']) && in_array(strtolower($_COOKIE['replash_lang']), $SUPPORTED_LANGUAGES)) {
    $_SESSION['lang'] = strtolower($_COOKIE['replash_lang']);
}

$current_lang = $_SESSION['lang'] ?? DEFAULT_LANG;
if (!in_array($current_lang, $SUPPORTED_LANGUAGES)) {
    $current_lang = DEFAULT_LANG;
}

// Load Translation Files
$langFile = REPLASH_ROOT . '/languages/' . $current_lang . '.php';
if (!file_exists($langFile)) {
    $langFile = REPLASH_ROOT . '/languages/' . DEFAULT_LANG . '.php';
}
$translations = require $langFile;

/**
 * Translate helper function
 * Usage: __('nav.home') or __('home.hero.title')
 */
function __($key, $fallback = '') {
    global $translations;
    $parts = explode('.', $key);
    $curr = $translations;
    foreach ($parts as $p) {
        if (is_array($curr) && isset($curr[$p])) {
            $curr = $curr[$p];
        } else {
            return !empty($fallback) ? $fallback : $key;
        }
    }
    return is_string($curr) ? $curr : $key;
}

/**
 * Generate a URL switching language while preserving current page & query parameters
 */
function lang_url($lang) {
    $queryParams = $_GET;
    $queryParams['lang'] = $lang;
    $queryString = http_build_query($queryParams);
    $script = basename($_SERVER['PHP_SELF']);
    return $script . '?' . $queryString;
}

/**
 * Base URL helper
 */
function base_url($path = '') {
    $base = rtrim(APP_URL, '/');
    $p = ltrim($path, '/');
    return $p ? $base . '/' . $p : $base;
}

/**
 * Asset URL helper
 */
function asset($path = '') {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Escape HTML output
 */
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}


/**
 * Get setting value from database (JSON decoded if array/object)
 */
function get_setting($key, $default = null) {
    global $pdo;
    if (!$pdo) return $default;
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = ? LIMIT 1");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        if ($val === false || $val === null) {
            return $default;
        }
        $trimmed = trim((string)$val);
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $val;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Set setting value in database (JSON encoded if array/object)
 */
function set_setting($key, $value) {
    global $pdo;
    if (!$pdo) return false;
    try {
        $valStr = (is_array($value) || is_object($value)) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
        return $stmt->execute([$key, $valStr]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Default configuration for ROI Calculator
 */
function get_default_roi_calculator_config() {
    return [
        'default_sport' => 'padel',
        'disclaimer_note' => '* Tahmin spor başına ortalama video satış fiyatına dayanmaktadır. Hesaplama tamamen örnektir.',
        'hook_title' => 'Bu tahmini tesisinin aylık gelirine çevir.',
        'hook_desc' => 'Kısa bir görüşme ayarla. Bu rakamları senin tesisine göre çıkaralım; kurulum, süre ve şartları birlikte konuşalım.',
        'hook_cta_text' => 'Görüşme ayarla',
        'hook_cta_url' => 'contact.php',
        'sports' => [
            'padel' => [
                'id' => 'padel',
                'name' => 'Padel',
                'price' => 8.00,
                'share_pct' => 15.0,
                'currency' => 'EUR',
                'currency_symbol' => '€',
                'courts_default' => 4,
                'courts_min' => 1,
                'courts_max' => 12,
                'videos_default' => 4,
                'videos_min' => 1,
                'videos_max' => 15,
                'days_default' => 26,
                'days_min' => 20,
                'days_max' => 30,
                'active' => 1
            ],
            'football' => [
                'id' => 'football',
                'name' => 'Futbol',
                'price' => 15.00,
                'share_pct' => 15.0,
                'currency' => 'EUR',
                'currency_symbol' => '€',
                'courts_default' => 4,
                'courts_min' => 1,
                'courts_max' => 12,
                'videos_default' => 4,
                'videos_min' => 1,
                'videos_max' => 15,
                'days_default' => 26,
                'days_min' => 20,
                'days_max' => 30,
                'active' => 1
            ]
        ]
    ];
}

/**
 * Helper to fetch effective ROI calculator config
 */
function get_roi_calculator_config() {
    $defaults = get_default_roi_calculator_config();
    $saved = get_setting('roi_calculator_config', null);
    $config = (!is_array($saved) || empty($saved['sports'])) ? $defaults : array_replace_recursive($defaults, $saved);

    // Billing Guide: fixed video prices (EUR) and tiered facility share (includes/billing.php)
    foreach ($config['sports'] as $key => $sp) {
        $config['sports'][$key]['price'] = replash_video_price($sp['id'] ?? $key, (float)($sp['price'] ?? 0));
        if (isset(REPLASH_VIDEO_PRICES[strtolower($sp['id'] ?? $key)])) {
            $config['sports'][$key]['currency'] = REPLASH_BILLING_CURRENCY;
            $config['sports'][$key]['currency_symbol'] = '€';
        }
    }
    $config['share_tiers'] = replash_share_tiers();
    return $config;
}

/**
 * Format Date helper with localized day/month
 */
function formatDateLocalized($dateStr, $lang = null) {
    global $current_lang;
    if ($lang === null) {
        $lang = $current_lang ?? DEFAULT_LANG;
    }
    $time = strtotime($dateStr);
    if (!$time) return $dateStr;
    
    $trMonths = ['01' => 'Ocak', '02' => 'Şubat', '03' => 'Mart', '04' => 'Nisan', '05' => 'Mayıs', '06' => 'Haziran', '07' => 'Temmuz', '08' => 'Ağustos', '09' => 'Eylül', '10' => 'Ekim', '11' => 'Kasım', '12' => 'Aralık'];
    $esMonths = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
    $itMonths = ['01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo', '04' => 'Aprile', '05' => 'Maggio', '06' => 'Giugno', '07' => 'Luglio', '08' => 'Agosto', '09' => 'Settembre', '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre'];
    
    $m = date('m', $time);
    $d = date('d', $time);
    $y = date('Y', $time);
    
    if ($lang === 'tr') {
        return "{$d} " . ($trMonths[$m] ?? $m) . " {$y}";
    } elseif ($lang === 'es') {
        return "{$d} de " . ($esMonths[$m] ?? $m) . " de {$y}";
    } elseif ($lang === 'it') {
        return "{$d} " . ($itMonths[$m] ?? $m) . " {$y}";
    }
    return date('M d, Y', $time);
}

/**
 * Builds a dynamic timestamped cloud camera playback URL with date, time, and duration parameters
 * Example output:
 * https://cloud.kamerabulutkayit.com/cameras/59/streaming/main.mp4?authorization=Basic%20YWRtaW46&time=2026-09-11T22:00:00&duration=01:00:00
 */
function buildMatchPlaybackUrl($streamUrl, $date, $timeSlot, $durationSec = 3600, $download = false, $filename = 'replash.mp4', $startOffsetSec = 0) {
    if (empty($streamUrl)) {
        return 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
    }

    // Extract slot start and end time
    $slotParts = preg_split('/[–\-]/u', (string)$timeSlot);
    $startRaw = trim($slotParts[0] ?? '20:00');
    $endRaw = trim($slotParts[1] ?? '');

    // Format start hour (HH:MM:SS)
    if (preg_match('/^\d{1,2}:\d{2}$/', $startRaw)) {
        $startHour = str_pad($startRaw, 5, '0', STR_PAD_LEFT) . ':00';
    } elseif (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $startRaw)) {
        $startHour = $startRaw;
    } else {
        $startHour = '20:00:00';
    }

    $slotStartTime = strtotime("{$date} {$startHour}");

    // If start offset is specified (clipping a segment), shift start time
    if ($startOffsetSec > 0 && $slotStartTime) {
        $slotStartTime += (int)$startOffsetSec;
        $startHour = date('H:i:s', $slotStartTime);
        $date = date('Y-m-d', $slotStartTime);
    }

    // Calculate duration in seconds if end time is given and not a custom clip
    if ($startOffsetSec == 0 && !empty($endRaw) && preg_match('/^\d{1,2}:\d{2}/', $endRaw)) {
        $endTime = strtotime("{$date} {$endRaw}:00");
        if ($endTime && $slotStartTime && $endTime > $slotStartTime) {
            $durationSec = $endTime - $slotStartTime;
        }
    }

    // Format duration HH:MM:SS
    $durHours = str_pad((int)floor($durationSec / 3600), 2, '0', STR_PAD_LEFT);
    $durMins = str_pad((int)floor(($durationSec % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $durSecs = str_pad((int)($durationSec % 60), 2, '0', STR_PAD_LEFT);
    $durationStr = "{$durHours}:{$durMins}:{$durSecs}";

    $isoTime = "{$date}T{$startHour}";

    // If stream URL is an RTSP stream, return as is
    if (strpos($streamUrl, 'rtsp://') === 0) {
        return $streamUrl;
    }

    // Parse URL and update time & duration parameters
    $parsed = parse_url($streamUrl);
    if (!$parsed || !isset($parsed['host'])) {
        return $streamUrl;
    }

    $query = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $query);
    }

    $query['time'] = $isoTime;
    $query['duration'] = $durationStr;

    $scheme   = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : 'https://';
    $host     = $parsed['host'] ?? '';
    $port     = isset($parsed['port']) ? ':' . $parsed['port'] : '';
    $user     = $parsed['user'] ?? '';
    $pass     = isset($parsed['pass']) ? ':' . $parsed['pass'] : '';
    $userPass = ($user || $pass) ? "{$user}{$pass}@" : '';
    $path     = $parsed['path'] ?? '';

    if ($download) {
        $query['download'] = '1';
        $query['filename'] = !empty($filename) ? $filename : 'replash.mp4';
        // When downloading, convert HLS playlist (.m3u8) to direct MP4 file (.mp4)
        if (preg_match('/\.m3u8$/i', $path)) {
            $path = preg_replace('/\.m3u8$/i', '.mp4', $path);
        }
    }

    // Build query with preserved colons in timestamp and %20 in authorization
    $queryString = str_replace(['%3A', '+'], [':', '%20'], http_build_query($query));
    $newQuery = '?' . $queryString;

    return "{$scheme}{$userPass}{$host}{$port}{$path}{$newQuery}";
}

/**
 * Check if a match date has exceeded the 7-day retention period
 */
function is_match_expired($dateStr) {
    if (empty($dateStr)) return false;
    $matchTimestamp = strtotime($dateStr . ' 23:59:59');
    $expireThreshold = strtotime('-7 days');
    return $matchTimestamp < $expireThreshold;
}

/**
 * Was this match's video ever sold (unlocked by facility staff)?
 * Unsold matches must stay invisible to players (Stealth Mode), even after expiry.
 */
function was_match_sold($match) {
    if (empty($match)) return false;
    if (($match['status'] ?? '') === 'UNLOCKABLE') return true;
    return ($match['status'] ?? '') === 'EXPIRED' && !empty($match['sold_at']);
}

/**
 * Helper to parse facility courts into structured array
 * Supports both new format: [{"name": "Açık Saha", "sport": "football", "price": 15.00}, ...]
 * and legacy format: ["Saha 1", "Saha 2"]
 */
function get_facility_courts_data($facility) {
    if (empty($facility)) return [];
    $raw = json_decode($facility['courts'] ?? '[]', true) ?: [];
    $result = [];
    foreach ($raw as $c) {
        if (is_array($c)) {
            $sport = $c['sport'] ?? ($facility['sport'] ?? 'padel');
            $courtPrice = isset($c['price']) ? (float)$c['price'] : (float)($facility['unit_price'] ?? 6.00);
            $result[] = [
                'name' => $c['name'] ?? 'Saha',
                'sport' => $sport,
                'price' => replash_video_price($sport, $courtPrice) // standard price for football/padel (billing.php)
            ];
        } else {
            $sport = $facility['sport'] ?? 'padel';
            $result[] = [
                'name' => (string)$c,
                'sport' => $sport,
                'price' => replash_video_price($sport, (float)($facility['unit_price'] ?? 6.00))
            ];
        }
    }
    return $result;
}

/**
 * Helper to get court names list for a facility
 */
function get_facility_court_names($facility) {
    $courtsData = get_facility_courts_data($facility);
    return array_column($courtsData, 'name');
}

