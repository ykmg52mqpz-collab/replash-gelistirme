<?php
/**
 * Yönetici şifresi belirleme — SADECE KOMUT SATIRI (web'den 404).
 *   php cron/admin-password.php admin@ornek.com            -> rastgele güçlü şifre üretir ve yazdırır
 *   php cron/admin-password.php admin@ornek.com 'Sifre123' -> verilen şifreyi ayarlar
 * E-posta kayıtlı değilse yeni yönetici oluşturur.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../includes/init.php';

$email = trim($argv[1] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Kullanım: php cron/admin-password.php <e-posta> [şifre]" . PHP_EOL);
    exit(1);
}
$password = $argv[2] ?? rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'Kx'), '=');
if (strlen($password) < 10) {
    fwrite(STDERR, "Şifre en az 10 karakter olmalı." . PHP_EOL);
    exit(1);
}
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
if ($id = $stmt->fetchColumn()) {
    $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);
    echo "Şifre güncellendi: {$email}" . PHP_EOL;
} else {
    $pdo->prepare("INSERT INTO admins (id, email, name, password_hash, role) VALUES (?, ?, ?, ?, 'superadmin')")
        ->execute(['admin-' . bin2hex(random_bytes(4)), $email, 'Replash Admin', $hash]);
    echo "Yeni yönetici oluşturuldu: {$email}" . PHP_EOL;
}
if (!isset($argv[2])) echo "Şifre: {$password}" . PHP_EOL;
