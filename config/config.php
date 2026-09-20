<?php
/**
 * Replash Main Configuration
 * Compatible with XAMPP (Windows) and Standard Linux (LEMP/LAMP)
 */

if (!defined('REPLASH_ROOT')) {
    define('REPLASH_ROOT', dirname(__DIR__));
}

// Session security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Load .env file if exists
$envFile = REPLASH_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Database Credentials (Can be edited directly here or set via .env / environment variables)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'replash_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', (getenv('DB_PASS') !== false && getenv('DB_PASS') !== '') ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'Replash');
define('APP_TAGLINE', 'Play. Record. Relive.');

if (!defined('APP_URL')) {
    if (getenv('APP_URL')) {
        define('APP_URL', getenv('APP_URL'));
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1'))
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $protocol = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $appRoot = str_replace('\\', '/', realpath(REPLASH_ROOT) ?: REPLASH_ROOT);
        
        $subPath = '';
        if (!empty($docRoot) && stripos($appRoot, $docRoot) === 0) {
            $subPath = substr($appRoot, strlen($docRoot));
        } elseif (isset($_SERVER['SCRIPT_NAME'])) {
            $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            $dir = preg_replace('#/(admin|panel|api(/hooks)?|config|includes)$#i', '', $dir);
            $subPath = $dir;
        }
        
        define('APP_URL', $protocol . '://' . $host . rtrim($subPath, '/'));
    } else {
        define('APP_URL', 'http://localhost/replash');
    }
}

// Webhook HMAC Secret (Matches INTEGRATION.md)
define('REPLASH_WEBHOOK_SECRET', getenv('REPLASH_WEBHOOK_SECRET') ?: 'whsec_replash_2026_secret_key');
define('REPLASH_TOKEN_SECRET', getenv('REPLASH_TOKEN_SECRET') ?: 'replash_token_signing_secret_9988');

// Default Retention & Processing
define('RETENTION_DAYS', 7);
define('PROCESSING_MINUTES', 8);
define('DEFAULT_CURRENCY', 'EUR');

// Supported Languages
$SUPPORTED_LANGUAGES = ['it', 'tr', 'en', 'es'];
define('DEFAULT_LANG', 'it');
