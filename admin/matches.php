<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = 'Maç Yönetimi & Satışlar · Admin';
$error = null;
$success = null;

// Auth check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

if ($pdo) {
    // 1. Quick Unlock / Mark Sold Action
    if (isset($_POST['action']) && $_POST['action'] === 'unlock_match') {
        $matchId = $_POST['match_id'] ?? '';
        $stmtFind = $pdo->prepare("SELECT m.*, f.name as facility_name FROM matches m JOIN facilities f ON m.facility_id = f.id WHERE m.id = ? LIMIT 1");
        $stmtFind->execute([$matchId]);
        $mRow = $stmtFind->fetch();

        if ($mRow) {
            $camStmt = $pdo->prepare("SELECT stream_url FROM cameras WHERE id = ? OR (facility_id = ? AND court = ?) LIMIT 1");
            $camStmt->execute([$mRow['camera_id'] ?? 0, $mRow['facility_id'], $mRow['court']]);
            $camStream = $camStmt->fetchColumn() ?: '';
            $playbackUrl = buildMatchPlaybackUrl($camStream, $mRow['date'], $mRow['time_slot'], (int)($mRow['duration_sec'] ?? 3600));

            $upd = $pdo->prepare("UPDATE matches SET status = 'UNLOCKABLE', playback_url = ?, sold_at = NOW(), billable = 1 WHERE id = ?");
            $upd->execute([$playbackUrl, $matchId]);
            $success = "{$mRow['facility_name']} - {$mRow['court']} ({$mRow['time_slot']}) satışı onaylandı! Erişim Kodu: <strong class='font-mono text-accent text-lg tracking-widest bg-black/50 px-3 py-1 rounded-xl border border-accent/40 select-all'>{$mRow['access_code']}</strong>";
        }
    }

    // 2. Quick Lock / Set to Waiting Action
    if (isset($_POST['action']) && $_POST['action'] === 'lock_match') {
        $matchId = $_POST['match_id'] ?? '';
        $upd = $pdo->prepare("UPDATE matches SET status = 'LOCKED', sold_at = NULL, billable = 0 WHERE id = ?");
        $upd->execute([$matchId]);
        $success = "Maç başarıyla KİLİTLİ (Satış Bekliyor) durumuna alındı.";
    }

    // 3. Delete Match Action
    if (isset($_POST['action']) && $_POST['action'] === 'delete_match') {
        $matchId = $_POST['match_id'] ?? '';
        $pdo->prepare("DELETE FROM clips WHERE match_id = ?")->execute([$matchId]);
        $pdo->prepare("DELETE FROM refunds WHERE match_id = ?")->execute([$matchId]);
        $pdo->prepare("DELETE FROM matches WHERE id = ?")->execute([$matchId]);
        $success = "Maç kaydı ve bağlı tüm veriler başarıyla silindi.";
    }

    // 4. Edit Match Action
    if (isset($_POST['action']) && $_POST['action'] === 'edit_match') {
        $matchId = $_POST['match_id'] ?? '';
        $court = trim($_POST['court'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $timeSlot = trim($_POST['time_slot'] ?? '');
        $status = $_POST['status'] ?? 'UNLOCKABLE';
        $accessCode = strtoupper(trim($_POST['access_code'] ?? ''));
        $playbackUrl = trim($_POST['playback_url'] ?? '');
        $durationSec = (int)($_POST['duration_sec'] ?? 3600);

        if (!empty($matchId) && !empty($court) && !empty($date) && !empty($timeSlot)) {
            $billable = ($status === 'UNLOCKABLE') ? 1 : 0;
            $soldAt = ($status === 'UNLOCKABLE') ? date('Y-m-d H:i:s') : null;

            $upd = $pdo->prepare("UPDATE matches SET 
                court = ?, date = ?, time_slot = ?, status = ?, access_code = ?, playback_url = ?, duration_sec = ?, billable = ? 
                WHERE id = ?");
            $upd->execute([$court, $date, $timeSlot, $status, $accessCode, $playbackUrl, $durationSec, $billable, $matchId]);
            $success = "Maç bilgileri başarıyla güncellendi.";
        } else {
            $error = "Lütfen zorunlu alanları eksiksiz doldurun.";
        }
    }

    // 5. Add Custom Match / Slot
    if (isset($_POST['action']) && $_POST['action'] === 'add_match') {
        $facilityId = trim($_POST['facility_id'] ?? '');
        $court = trim($_POST['court'] ?? '');
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $slot = trim($_POST['time_slot'] ?? '20:00–21:00');
        $status = $_POST['status'] ?? 'UNLOCKABLE';
        $customAccessCode = strtoupper(trim($_POST['access_code'] ?? ''));

        if (!empty($facilityId) && !empty($court) && !empty($date) && !empty($slot)) {
            $facStmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ? LIMIT 1");
            $facStmt->execute([$facilityId]);
            $fac = $facStmt->fetch();

            $camStmt = $pdo->prepare("SELECT * FROM cameras WHERE facility_id = ? AND court = ? LIMIT 1");
            $camStmt->execute([$facilityId, $court]);
            $cam = $camStmt->fetch();

            $camStream = $cam['stream_url'] ?? '';
            $camId = $cam['id'] ?? null;
            $playbackUrl = buildMatchPlaybackUrl($camStream, $date, $slot, 3600);

            $slotStart = explode('–', $slot)[0];
            $slotStartCompact = str_replace(':', '', $slotStart);
            $dateCompact = str_replace('-', '', $date);
            $matchCode = "MATCH-{$dateCompact}-{$slotStartCompact}";
            $accessCode = !empty($customAccessCode) ? $customAccessCode : strtoupper(substr(bin2hex(random_bytes(4)), 0, 4) . '-' . substr(bin2hex(random_bytes(4)), 0, 4));
            $matchId = 'm-' . bin2hex(random_bytes(5));
            $soldAt = ($status === 'UNLOCKABLE') ? date('Y-m-d H:i:s') : null;
            $billable = ($status === 'UNLOCKABLE') ? 1 : 0;

            $ins = $pdo->prepare("INSERT INTO matches 
                (id, code, access_code, camera_id, facility_id, court, date, time_slot, status, playback_url, playback_type, thumbnail_url, duration_sec, sold_at, views, billable, sport, recorded_at, available_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'mp4', 'assets/images/gen_hero_main.jpg', 3600, ?, 0, ?, ?, NOW(), NOW())");
            
            $ins->execute([
                $matchId,
                $matchCode,
                $accessCode,
                $camId,
                $facilityId,
                $court,
                $date,
                $slot,
                $status,
                $playbackUrl,
                $soldAt,
                $billable,
                $fac['sport'] ?? 'padel'
            ]);

            $success = "Yeni maç kaydı ({$court} · {$slot}) başarıyla oluşturuldu!";
        }
    }

    // Fetch Facilities for Filters & Modals
    $facilities = $pdo->query("SELECT * FROM facilities ORDER BY name ASC")->fetchAll();

    // Filters Query
    $filterFacility = $_GET['facility_id'] ?? 'all';
    $filterStatus = $_GET['status'] ?? 'all';
    $filterDate = $_GET['date'] ?? '';
    $searchQuery = trim($_GET['q'] ?? '');

    $whereClauses = [];
    $queryParams = [];

    if ($filterFacility !== 'all' && !empty($filterFacility)) {
        $whereClauses[] = "m.facility_id = ?";
        $queryParams[] = $filterFacility;
    }

    if ($filterStatus !== 'all' && !empty($filterStatus)) {
        $whereClauses[] = "m.status = ?";
        $queryParams[] = $filterStatus;
    }

    if (!empty($filterDate)) {
        $whereClauses[] = "m.date = ?";
        $queryParams[] = $filterDate;
    }

    if (!empty($searchQuery)) {
        $whereClauses[] = "(m.access_code LIKE ? OR m.code LIKE ? OR m.court LIKE ? OR f.name LIKE ?)";
        $queryParams[] = "%{$searchQuery}%";
        $queryParams[] = "%{$searchQuery}%";
        $queryParams[] = "%{$searchQuery}%";
        $queryParams[] = "%{$searchQuery}%";
    }

    $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

    // Stats for Matches View
    $statsTotal = (int)$pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
    $statsSold = (int)$pdo->query("SELECT COUNT(*) FROM matches m WHERE " . replash_valid_sale_sql('m'))->fetchColumn();
    $statsLocked = (int)$pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'LOCKED'")->fetchColumn();
    
    // Billing rules (includes/billing.php): valid sales, standard price per sport
    $revenueStmt = $pdo->query("SELECT COALESCE(SUM(" . replash_sale_price_sql('m', 'f.unit_price') . "), 0) FROM matches m JOIN facilities f ON m.facility_id = f.id WHERE " . replash_valid_sale_sql('m'));
    $totalPlatformRevenue = (float)$revenueStmt->fetchColumn();

    // Fetch Filtered Matches List
    $sqlMatches = "SELECT m.*, f.name as facility_name, f.city, f.sport as facility_sport, f.unit_price, f.currency 
        FROM matches m 
        JOIN facilities f ON m.facility_id = f.id 
        {$whereSql} 
        ORDER BY m.date DESC, m.time_slot DESC 
        LIMIT 100";
    
    $stmtM = $pdo->prepare($sqlMatches);
    $stmtM->execute($queryParams);
    $matchesList = $stmtM->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen pt-28 pb-20 relative">
  <div class="hero-glow"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-white/10">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <a href="<?= base_url('admin/index.php') ?>" class="text-xs font-semibold text-accent hover:underline flex items-center gap-1">
            &larr; Admin Dashboard
          </a>
          <span class="text-neutral-500">/</span>
          <span class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Satış & Maç Yönetimi</span>
        </div>
        <h1 class="font-display text-3xl font-bold text-white">Tüm Maçlar & Satış Paneli</h1>
        <p class="text-xs text-neutral-400 mt-1">Platformdaki tüm sahaların satışa açılan, bekleyen ve kilitli maç kayıtları</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <a href="<?= base_url('admin/calculator.php') ?>" class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-semibold text-neutral-200 hover:text-white hover:bg-white/10 transition flex items-center gap-1.5">
          <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          Gelir Hesaplayıcı
        </a>
        <button 
          onclick="document.getElementById('addMatchModal').classList.toggle('hidden')"
          class="px-4 py-2 rounded-xl bg-accent text-ink-900 font-bold text-xs shadow-glow hover:bg-accent-neon transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          + Yeni Maç / Slot Ekle
        </button>
        <a href="<?= base_url('admin/index.php') ?>" class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-medium text-neutral-300 hover:bg-white/10 transition">
          Dashboard'a Dön
        </a>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-300 text-sm font-medium mb-8 shadow-lg flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div><?= $success ?></div>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-rose-300 text-sm font-medium mb-8 shadow-lg flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0">&times;</div>
        <div><?= e($error) ?></div>
      </div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="card-elevated rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400">Toplam Maç Kaydı</span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-white"><?= $statsTotal ?></span>
          <span class="text-xs text-neutral-400">slot</span>
        </div>
      </div>

      <div class="card-elevated rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400">Satışa Açılan (Satıldı)</span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-emerald-400"><?= $statsSold ?></span>
          <span class="text-xs text-neutral-400">maç</span>
        </div>
      </div>

      <div class="card-elevated rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400">Satış Bekleyen (Kilitli)</span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-amber-400"><?= $statsLocked ?></span>
          <span class="text-xs text-neutral-400">slot</span>
        </div>
      </div>

      <div class="card-elevated rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400">Toplam Platform Cirosu</span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-accent"><?= number_format($totalPlatformRevenue, 0, ',', '.') ?></span>
          <span class="text-xs font-semibold text-neutral-300">EUR</span>
        </div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card-elevated rounded-2xl p-5 border border-white/10 mb-8">
      <form action="matches.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
        
        <!-- Facility Filter -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Tesis Filtresi</label>
          <select name="facility_id" class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <option value="all">⚡ Tüm Tesisler</option>
            <?php foreach ($facilities as $fac): ?>
              <option value="<?= e($fac['id']) ?>" <?= $filterFacility === $fac['id'] ? 'selected' : '' ?>>
                <?= e($fac['name']) ?> (<?= e($fac['city']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Satış Durumu</label>
          <select name="status" class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <option value="all">Tüm Durumlar</option>
            <option value="UNLOCKABLE" <?= $filterStatus === 'UNLOCKABLE' ? 'selected' : '' ?>>🟢 Satıldı (İzlemeye Açık)</option>
            <option value="LOCKED" <?= $filterStatus === 'LOCKED' ? 'selected' : '' ?>>🟡 Kilitli (Satış Bekliyor)</option>
            <option value="EXPIRED" <?= $filterStatus === 'EXPIRED' ? 'selected' : '' ?>>⚪ Süresi Dolanlar</option>
          </select>
        </div>

        <!-- Date Filter -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Tarih</label>
          <input 
            type="date" 
            name="date" 
            value="<?= e($filterDate) ?>" 
            class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none"
          >
        </div>

        <!-- Search Input -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Arama (Kod / Saha)</label>
          <input 
            type="text" 
            name="q" 
            placeholder="Erişim kodu, saha adı..." 
            value="<?= e($searchQuery) ?>" 
            class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none"
          >
        </div>

        <!-- Submit & Reset Buttons -->
        <div class="flex items-end gap-2">
          <button type="submit" class="flex-1 rounded-xl bg-accent py-2.5 font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition">
            Filtrele
          </button>
          <a href="matches.php" class="px-3 py-2.5 rounded-xl border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10 transition text-center" title="Filtreleri Sıfırla">
            &times;
          </a>
        </div>
      </form>
    </div>

    <!-- Add Match Modal -->
    <div id="addMatchModal" class="hidden mb-8 p-6 rounded-3xl bg-ink-800 border border-accent/30 shadow-2xl">
      <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/10">
        <div>
          <h3 class="font-display text-lg font-bold text-white">Yeni Maç / Slot Kaydı Ekle</h3>
          <p class="text-xs text-neutral-400">İstediğiniz tesis için doğrudan maç kaydı açabilir, satıldı veya kilitli olarak tanımlayabilirsiniz.</p>
        </div>
        <button type="button" onclick="document.getElementById('addMatchModal').classList.add('hidden')" class="text-neutral-400 hover:text-white text-lg font-bold">&times;</button>
      </div>

      <form action="matches.php" method="POST" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs items-end">
        <input type="hidden" name="action" value="add_match">

        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Tesis Seçin</label>
          <select name="facility_id" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <?php foreach ($facilities as $fac): ?>
              <option value="<?= e($fac['id']) ?>"><?= e($fac['name']) ?> (<?= e($fac['city']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Saha / Kort</label>
          <input type="text" name="court" value="Saha 1" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
        </div>

        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Tarih</label>
          <input type="date" name="date" value="<?= date('Y-m-d') ?>" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
        </div>

        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Saat Slotu</label>
          <select name="time_slot" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <option value="16:00–17:00">16:00 – 17:00</option>
            <option value="17:00–18:00">17:00 – 18:00</option>
            <option value="18:00–19:00">18:00 – 19:00</option>
            <option value="19:00–20:00" selected>19:00 – 20:00</option>
            <option value="20:00–21:00">20:00 – 21:00</option>
            <option value="21:00–22:00">21:00 – 22:00</option>
            <option value="22:00–23:00">22:00 – 23:00</option>
            <option value="23:00–24:00">23:00 – 24:00</option>
          </select>
        </div>

        <div>
          <label class="block text-neutral-400 font-semibold mb-1">Başlangıç Durumu</label>
          <select name="status" class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <option value="UNLOCKABLE">🟢 Satıldı (İzlemeye Açık)</option>
            <option value="LOCKED">🟡 Kilitli (Satış Bekliyor)</option>
          </select>
        </div>

        <div>
          <button type="submit" class="w-full rounded-xl bg-accent py-2.5 font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition">
            Maçı Oluştur &rarr;
          </button>
        </div>
      </form>
    </div>

    <!-- Matches Table -->
    <div class="card-elevated rounded-3xl overflow-hidden border border-white/10">
      <div class="p-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h2 class="font-display text-xl font-bold text-white">Maç Listesi & Satış Kontrolü</h2>
          <p class="text-xs text-neutral-400">Bulunan <?= count($matchesList) ?> maç kaydı listeleniyor</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-ink-900/60 text-xs font-semibold uppercase tracking-wider text-neutral-400 border-b border-white/5">
            <tr>
              <th class="p-4">Tesis & Şehir</th>
              <th class="p-4">Saha & Slot</th>
              <th class="p-4">Tarih</th>
              <th class="p-4">Durum</th>
              <th class="p-4">Erişim Kodu</th>
              <th class="p-4">Satış Tarihi / Fiyat</th>
              <th class="p-4 text-center">İzlenme</th>
              <th class="p-4 text-right">İşlemler</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 text-neutral-300">
            <?php if (empty($matchesList)): ?>
              <tr>
                <td colspan="8" class="p-8 text-center text-neutral-500 italic">
                  Seçilen filtrelere uygun maç kaydı bulunamadı.
                </td>
              </tr>
            <?php endif; ?>

            <?php foreach ($matchesList as $m): ?>
              <tr class="hover:bg-white/[0.02] transition">
                <td class="p-4">
                  <div class="font-bold text-white"><?= e($m['facility_name']) ?></div>
                  <div class="text-xs text-neutral-400"><?= e($m['city']) ?> · <?= strtoupper(e($m['facility_sport'])) ?></div>
                </td>
                
                <td class="p-4">
                  <div class="font-semibold text-neutral-200"><?= e($m['court']) ?></div>
                  <div class="text-xs text-neutral-400 font-mono"><?= e($m['time_slot']) ?></div>
                </td>

                <td class="p-4 text-xs font-mono"><?= e($m['date']) ?></td>

                <td class="p-4">
                  <?php if ($m['status'] === 'UNLOCKABLE'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                      Satıldı (Açık)
                    </span>
                  <?php elseif ($m['status'] === 'LOCKED'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                      Kilitli (Bekliyor)
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-neutral-500/10 text-neutral-400 border border-neutral-500/20">
                      <?= e($m['status']) ?>
                    </span>
                  <?php endif; ?>
                </td>

                <td class="p-4">
                  <?php if ($m['status'] === 'UNLOCKABLE'): ?>
                    <button 
                      type="button" 
                      onclick="navigator.clipboard.writeText('<?= e($m['access_code']) ?>'); alert('Erişim kodu kopyalandı: <?= e($m['access_code']) ?>');"
                      title="Kopyalamak İçin Tıklayın"
                      class="inline-flex items-center gap-1.5 font-mono text-sm font-bold text-accent bg-accent/10 hover:bg-accent/20 px-2.5 py-1 rounded-lg border border-accent/30 transition cursor-pointer"
                    >
                      <?= e($m['access_code']) ?>
                      <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                  <?php else: ?>
                    <span class="text-xs text-neutral-500 italic font-mono"><?= e($m['access_code']) ?></span>
                  <?php endif; ?>
                </td>

                <td class="p-4 text-xs">
                  <?php if (!empty($m['sold_at'])): ?>
                    <div class="text-neutral-300 font-mono"><?= date('d.m.Y H:i', strtotime($m['sold_at'])) ?></div>
                    <div class="text-emerald-400 font-semibold"><?= e(replash_money(replash_video_price($m['sport'], (float)$m['unit_price']))) ?><?= empty($m['billable']) ? ' · ücretsiz' : '' ?></div>
                  <?php else: ?>
                    <span class="text-neutral-500 italic">Satış Yapılmadı</span>
                  <?php endif; ?>
                </td>

                <td class="p-4 text-center">
                  <span class="text-xs font-mono px-2 py-0.5 rounded-md bg-white/5 text-neutral-300">
                    <?= (int)($m['views'] ?? 0) ?>
                  </span>
                </td>

                <td class="p-4 text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Status Toggle Button -->
                    <?php if ($m['status'] === 'LOCKED'): ?>
                      <form action="matches.php" method="POST" class="inline-block">
                        <input type="hidden" name="action" value="unlock_match">
                        <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                        <button type="submit" title="Satışa Aç & Kodu Aktifleştir" class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/30 text-xs font-semibold">
                          ⚡ Satışa Aç
                        </button>
                      </form>
                    <?php else: ?>
                      <form action="matches.php" method="POST" class="inline-block" onsubmit="return confirm('Bu maçı tekrar Kilitli/Bekliyor durumuna almak istediğinize emin misiniz?');">
                        <input type="hidden" name="action" value="lock_match">
                        <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                        <button type="submit" title="Kilitle / Satış Bekliyor Yap" class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/30 text-xs font-semibold">
                          🔒 Kilitle
                        </button>
                      </form>
                    <?php endif; ?>

                    <!-- View Match Link -->
                    <a 
                      href="<?= base_url('find.php?code=' . urlencode($m['access_code'])) ?>" 
                      target="_blank" 
                      title="Videoyu Aç & İzle" 
                      class="p-1.5 rounded-lg bg-white/5 text-neutral-300 hover:text-white hover:bg-white/10 border border-white/10"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>

                    <!-- Edit Modal Trigger -->
                    <button 
                      type="button" 
                      onclick="document.getElementById('editMatch_<?= e($m['id']) ?>').classList.toggle('hidden')"
                      title="Maçı Düzenle" 
                      class="p-1.5 rounded-lg bg-accent/10 text-accent hover:bg-accent/20 border border-accent/30"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>

                    <!-- Delete Form -->
                    <form action="matches.php" method="POST" class="inline-block" onsubmit="return confirm('Bu maç kaydını silmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="delete_match">
                      <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                      <button type="submit" title="Maçı Sil" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </form>
                  </div>

                  <!-- Inline Edit Modal -->
                  <div id="editMatch_<?= e($m['id']) ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
                    <div class="card-elevated rounded-3xl p-6 border border-accent/40 bg-ink-900 w-full max-w-xl text-left shadow-2xl space-y-4">
                      <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <div>
                          <h3 class="font-display text-lg font-bold text-white">Maç Kaydını Düzenle</h3>
                          <span class="text-xs text-accent font-semibold"><?= e($m['facility_name']) ?> · <?= e($m['code']) ?></span>
                        </div>
                        <button type="button" onclick="document.getElementById('editMatch_<?= e($m['id']) ?>').classList.add('hidden')" class="text-neutral-400 hover:text-white text-xl font-bold">&times;</button>
                      </div>

                      <form action="matches.php" method="POST" class="space-y-3.5 text-xs">
                        <input type="hidden" name="action" value="edit_match">
                        <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                          <div>
                            <label class="block text-neutral-400 mb-1 font-medium">Saha / Kort Adı</label>
                            <input type="text" name="court" value="<?= e($m['court']) ?>" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                          </div>
                          <div>
                            <label class="block text-neutral-400 mb-1 font-medium">Tarih</label>
                            <input type="date" name="date" value="<?= e($m['date']) ?>" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                          </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                          <div>
                            <label class="block text-neutral-400 mb-1 font-medium">Saat Slotu</label>
                            <input type="text" name="time_slot" value="<?= e($m['time_slot']) ?>" placeholder="Örn: 20:00–21:00" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white font-mono focus:border-accent focus:outline-none">
                          </div>
                          <div>
                            <label class="block text-neutral-400 mb-1 font-medium">Erişim Kodu</label>
                            <input type="text" name="access_code" value="<?= e($m['access_code']) ?>" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-accent font-mono font-bold uppercase focus:border-accent focus:outline-none">
                          </div>
                          <div>
                            <label class="block text-neutral-400 mb-1 font-medium">Satış Durumu</label>
                            <select name="status" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                              <option value="UNLOCKABLE" <?= $m['status'] === 'UNLOCKABLE' ? 'selected' : '' ?>>🟢 Satıldı (Açık)</option>
                              <option value="LOCKED" <?= $m['status'] === 'LOCKED' ? 'selected' : '' ?>>🟡 Kilitli (Bekliyor)</option>
                              <option value="EXPIRED" <?= $m['status'] === 'EXPIRED' ? 'selected' : '' ?>>⚪ Süresi Doldu</option>
                            </select>
                          </div>
                        </div>

                        <div>
                          <label class="block text-neutral-400 mb-1 font-medium">Özel Video / Stream URL'si (İsteğe Bağlı)</label>
                          <input type="text" name="playback_url" value="<?= e($m['playback_url']) ?>" placeholder="Boş bırakılırsa kameranın canlı yayın URL'si kullanılır" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white font-mono focus:border-accent focus:outline-none">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-white/10">
                          <button type="button" onclick="document.getElementById('editMatch_<?= e($m['id']) ?>').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10">Vazgeç</button>
                          <button type="submit" class="px-5 py-2 rounded-xl bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow">Değişiklikleri Kaydet</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
