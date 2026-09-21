<?php
/**
 * 7 günlük saklama süresi dolan maçları siler (komut satırı / zamanlanmış görev).
 * Örnek cron (saatte bir):  0 * * * *  php /path/to/replash/cron/purge.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/init.php';
if (!$pdo) { fwrite(STDERR, "Veritabanı bağlantısı yok\n"); exit(1); }
$n = replash_purge_expired($pdo, true);
echo "Süresi dolan maç: {$n}\n";
