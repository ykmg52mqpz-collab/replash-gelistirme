<?php
/**
 * 7 günlük saklama süresi dolan maçları kapatır ve kilidi hiç açılmamış maçları (maç tarihinden 3 gün sonra) siler (komut satırı / zamanlanmış görev).
 * Örnek cron (saatte bir):  0 * * * *  php /path/to/replash/cron/purge.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/init.php';
if (!$pdo) { fwrite(STDERR, "Veritabanı bağlantısı yok\n"); exit(1); }
$n = replash_purge_expired($pdo, true, $unsold);
echo "Süresi dolan maç: {$n}\n";
echo "Kilidi açılmadığı için silinen maç (" . REPLASH_UNSOLD_RETENTION_DAYS . " gün): {$unsold}\n";
