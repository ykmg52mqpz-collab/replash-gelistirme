<?php
/**
 * Replash Database Installer — COMMAND LINE ONLY
 * Web access is disabled (returns 404). Run from the server shell:
 *   php install.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

try {
    DB::initDatabase();
    echo "Veritabanı tabloları ve başlangıç verileri yüklendi (" . DB_NAME . ")." . PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR, "Kurulum Hatası: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
