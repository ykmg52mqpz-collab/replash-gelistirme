<?php
/**
 * Replash One-Click Database Installer & Reset Tool
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$message = null;
$error = null;

if (isset($_POST['install'])) {
    try {
        DB::initDatabase();
        $message = "Veritabanı tabloları ve başlangıç verileri başarıyla yüklendi!";
    } catch (Exception $e) {
        $error = "Kurulum Hatası: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Replash Kurulum Aracı</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-950 text-neutral-100 flex items-center justify-center min-h-screen p-4">
  <div class="max-w-md w-full bg-neutral-900 border border-neutral-800 rounded-3xl p-8 text-center shadow-2xl">
    <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/30 text-amber-300 flex items-center justify-center mx-auto mb-4 font-bold text-xl">
      R
    </div>
    <h1 class="text-2xl font-bold text-white mb-2">Replash DB Kurulumu</h1>
    <p class="text-xs text-neutral-400 mb-6">
      MySQL veritabanını (`<?= DB_NAME ?>`) ve örnek tesis, kamera ve maç kayıtlarını yüklemek için aşağıdaki butona tıklayın.
    </p>

    <?php if ($message): ?>
      <div class="p-3 mb-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-medium">
        <?= htmlspecialchars($message) ?>
      </div>
      <a href="index.php" class="inline-block w-full py-2.5 rounded-xl bg-amber-300 text-neutral-950 font-semibold text-sm mb-2 hover:bg-amber-200">
        Ana Sayfaya Git &rarr;
      </a>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="p-3 mb-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-medium">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="install" value="1">
      <button type="submit" class="w-full py-3 rounded-xl bg-amber-300 text-neutral-950 font-semibold text-sm hover:bg-amber-200 transition">
        Veritabanını Kur & Örnek Verileri Yükle
      </button>
    </form>
  </div>
</body>
</html>
