<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = adm('Maç Yönetimi & Satışlar · Admin', 'Match management & sales · Admin');
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

            $wasLocked = ($mRow['status'] !== 'UNLOCKABLE');
            if ($wasLocked) $mRow['access_code'] = replash_new_access_code($pdo);
            $upd = $pdo->prepare("UPDATE matches SET status = 'UNLOCKABLE', access_code = ?, playback_url = COALESCE(NULLIF(?, ''), playback_url), sold_at = IF(status = 'UNLOCKABLE', sold_at, NOW()), billable = 1 WHERE id = ?");
            $upd->execute([$mRow['access_code'], $playbackUrl, $matchId]);
            if ($wasLocked) replash_log_sale_event($pdo, $mRow, 'admin_sold', 'admin:' . ($_SESSION['admin_id'] ?? 'admin'));
            $codeHtml = "<strong class='font-mono text-accent text-lg tracking-widest bg-black/50 px-3 py-1 rounded-xl border border-accent/40 select-all'>{$mRow['access_code']}</strong>";
            $where = "{$mRow['facility_name']} - {$mRow['court']} ({$mRow['time_slot']})";
            $success = adm(
                "{$where} satışı onaylandı. Erişim kodu: {$codeHtml}",
                "{$where} sale confirmed. Access code: {$codeHtml}"
            );
        }
    }

    // 2. Quick Lock / Set to Waiting Action
    if (isset($_POST['action']) && $_POST['action'] === 'lock_match') {
        $matchId = $_POST['match_id'] ?? '';
        $stmtRow = $pdo->prepare("SELECT * FROM matches WHERE id = ? LIMIT 1");
        $stmtRow->execute([$matchId]);
        $lockRow = $stmtRow->fetch();
        $upd = $pdo->prepare("UPDATE matches SET status = 'LOCKED', access_code = ?, sold_at = NULL, billable = 0 WHERE id = ?");
        $upd->execute([replash_locked_placeholder(), $matchId]);
        if ($lockRow && $lockRow['status'] === 'UNLOCKABLE') replash_log_sale_event($pdo, $lockRow, 'admin_undone', 'admin:' . ($_SESSION['admin_id'] ?? 'admin'), trim((string)($_POST['reason'] ?? '')) ?: null);
        $success = adm('Maç KİLİTLİ (satış bekliyor) durumuna alındı.', 'Match set to LOCKED (awaiting sale).');
    }

    // 3. Delete Match Action
    if (isset($_POST['action']) && $_POST['action'] === 'delete_match') {
        $matchId = $_POST['match_id'] ?? '';
        $stmtRow = $pdo->prepare("SELECT * FROM matches WHERE id = ? LIMIT 1");
        $stmtRow->execute([$matchId]);
        $delRow = $stmtRow->fetch();
        if ($delRow && $delRow['status'] === 'UNLOCKABLE') replash_log_sale_event($pdo, $delRow, 'admin_deleted', 'admin:' . ($_SESSION['admin_id'] ?? 'admin'));
        $pdo->prepare("DELETE FROM refunds WHERE match_id = ?")->execute([$matchId]);
        $pdo->prepare("DELETE FROM matches WHERE id = ?")->execute([$matchId]);
        $success = adm('Maç kaydı ve bağlı tüm veriler silindi.', 'Match record and all related data deleted.');
    }

    // 4. Edit Match Action
    if (isset($_POST['action']) && $_POST['action'] === 'edit_match') {
        $matchId = $_POST['match_id'] ?? '';
        $court = trim($_POST['court'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $timeSlot = trim($_POST['time_slot'] ?? '');
        $status = $_POST['status'] ?? 'UNLOCKABLE';
        $accessCode = replash_normalize_code((string)($_POST['access_code'] ?? ''));
        $playbackUrl = trim($_POST['playback_url'] ?? '');
        $durationSec = (int)($_POST['duration_sec'] ?? 3600);

        if (!empty($matchId) && !empty($court) && !empty($date) && !empty($timeSlot)) {
            $billable = ($status === 'UNLOCKABLE') ? 1 : 0;
            $soldAt = ($status === 'UNLOCKABLE') ? date('Y-m-d H:i:s') : null;

            $stmtRow = $pdo->prepare("SELECT * FROM matches WHERE id = ? LIMIT 1");
            $stmtRow->execute([$matchId]);
            $before = $stmtRow->fetch();
            // Kilitli maçın kodu olmaz; açık maçın kodu başka açık maçla çakışamaz
            if ($status === 'LOCKED') {
                $accessCode = replash_locked_placeholder();
            } elseif ($status === 'UNLOCKABLE') {
                $clash = null;
                if ($accessCode !== '' && !str_starts_with($accessCode, 'L-')) {
                    $cs = $pdo->prepare("SELECT id FROM matches WHERE access_code = ? AND status = 'UNLOCKABLE' AND id <> ? LIMIT 1");
                    $cs->execute([$accessCode, $matchId]);
                    $clash = $cs->fetchColumn();
                }
                if ($accessCode === '' || str_starts_with($accessCode, 'L-') || $clash) $accessCode = replash_new_access_code($pdo);
            }
            $upd = $pdo->prepare("UPDATE matches SET 
                court = ?, date = ?, time_slot = ?, status = ?, access_code = ?, playback_url = ?, duration_sec = ?,
                billable = CASE WHEN ? = 'UNLOCKABLE' THEN 1 WHEN ? = 'EXPIRED' THEN billable ELSE 0 END,
                sold_at = CASE WHEN ? = 'UNLOCKABLE' THEN COALESCE(sold_at, NOW()) WHEN ? = 'LOCKED' THEN NULL ELSE sold_at END
                WHERE id = ?");
            $upd->execute([$court, $date, $timeSlot, $status, $accessCode, $playbackUrl, $durationSec, $status, $status, $status, $status, $matchId]);
            if ($before && $before['status'] !== $status) {
                if ($status === 'UNLOCKABLE') replash_log_sale_event($pdo, $before, 'admin_sold', 'admin:' . ($_SESSION['admin_id'] ?? 'admin'));
                elseif ($before['status'] === 'UNLOCKABLE' && $status === 'LOCKED') replash_log_sale_event($pdo, $before, 'admin_undone', 'admin:' . ($_SESSION['admin_id'] ?? 'admin'));
            }
            $success = adm('Maç bilgileri güncellendi.', 'Match details updated.');
        } else {
            $error = adm('Lütfen zorunlu alanları eksiksiz doldurun.', 'Please fill in all required fields.');
        }
    }

    // 5. Add Custom Match / Slot
    if (isset($_POST['action']) && $_POST['action'] === 'add_match') {
        $facilityId = trim($_POST['facility_id'] ?? '');
        $court = trim($_POST['court'] ?? '');
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $slot = trim($_POST['time_slot'] ?? '20:00–21:00');
        $status = $_POST['status'] ?? 'UNLOCKABLE';
        $customAccessCode = replash_normalize_code((string)($_POST['access_code'] ?? ''));

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
            if ($status !== 'UNLOCKABLE') {
                $accessCode = replash_locked_placeholder();
            } else {
                $accessCode = $customAccessCode;
                if ($accessCode !== '') {
                    $cs = $pdo->prepare("SELECT 1 FROM matches WHERE access_code = ? AND status = 'UNLOCKABLE' LIMIT 1");
                    $cs->execute([$accessCode]);
                    if ($cs->fetchColumn()) $accessCode = '';
                }
                if ($accessCode === '') $accessCode = replash_new_access_code($pdo);
            }
            $matchId = 'm-' . bin2hex(random_bytes(5));
            $soldAt = ($status === 'UNLOCKABLE') ? date('Y-m-d H:i:s') : null;
            $billable = ($status === 'UNLOCKABLE') ? 1 : 0;

            $ins = $pdo->prepare("INSERT INTO matches 
                (id, code, access_code, camera_id, facility_id, court, date, time_slot, status, playback_url, playback_type, thumbnail_url, duration_sec, sold_at, views, billable, sport, recorded_at, available_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'mp4', 'assets/images/gen_hero_main.webp', 3600, ?, 0, ?, ?, NOW(), NOW())");
            
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

            $success = adm("Yeni maç kaydı ({$court} · {$slot}) oluşturuldu.", "New match record ({$court} · {$slot}) created.");
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
    $sqlMatches = "SELECT m.*, f.name as facility_name, f.city, f.sport as facility_sport, f.unit_price, f.currency, f.sport_prices 
        FROM matches m 
        JOIN facilities f ON m.facility_id = f.id 
        {$whereSql} 
        ORDER BY m.date DESC, m.time_slot DESC 
        LIMIT 100";
    
    $stmtM = $pdo->prepare($sqlMatches);
    $stmtM->execute($queryParams);
    $matchesList = $stmtM->fetchAll();
    // Kilitli maçların kodu yok (yer tutucu); listede boş gösterilir
    foreach ($matchesList as &$ml) {
        $ml['shown_code'] = str_starts_with((string)$ml['access_code'], 'L-') ? '' : $ml['access_code'];
    }
    unset($ml);
}

$adminShell = true;                       // üst çubukta yalnız TR/EN
$internalShell = adm('Yönetim', 'Admin');
require_once __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen pb-10 relative">

  <div class="container-x py-10 relative z-10">

    <?php $adminTab = 'matches'; include __DIR__ . '/../includes/admin_nav.php'; ?>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Toplam Maç Kaydı', 'Total match records')) ?></span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-white"><?= $statsTotal ?></span>
          <span class="text-xs text-neutral-400">slot</span>
        </div>
      </div>

      <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Satışa Açılan (Satıldı)', 'Unlocked (sold)')) ?></span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-emerald-400"><?= $statsSold ?></span>
          <span class="text-xs text-neutral-400"><?= e(adm('maç', 'matches')) ?></span>
        </div>
      </div>

      <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Satış Bekleyen (Kilitli)', 'Awaiting sale (locked)')) ?></span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-amber-400"><?= $statsLocked ?></span>
          <span class="text-xs text-neutral-400">slot</span>
        </div>
      </div>

      <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between">
        <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Toplam Platform Cirosu', 'Total platform revenue')) ?></span>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold font-display text-accent"><?= number_format($totalPlatformRevenue, 0, ',', '.') ?></span>
          <span class="text-xs font-semibold text-neutral-300">EUR</span>
        </div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 mb-8">
      <form action="matches.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
        
        <!-- Facility Filter -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Tesis Filtresi', 'Facility filter')) ?></label>
          <select name="facility_id" class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <option value="all"><?= e(adm('Tüm Tesisler', 'All facilities')) ?></option>
            <?php foreach ($facilities as $fac): ?>
              <option value="<?= e($fac['id']) ?>" <?= $filterFacility === $fac['id'] ? 'selected' : '' ?>>
                <?= e($fac['name']) ?> (<?= e($fac['city']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Satış Durumu', 'Sale status')) ?></label>
          <select name="status" class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <option value="all"><?= e(adm('Tüm Durumlar', 'All statuses')) ?></option>
            <option value="UNLOCKABLE" <?= $filterStatus === 'UNLOCKABLE' ? 'selected' : '' ?>><?= e(adm('Satıldı (İzlemeye Açık)', 'Sold (viewable)')) ?></option>
            <option value="LOCKED" <?= $filterStatus === 'LOCKED' ? 'selected' : '' ?>><?= e(adm('Kilitli (Satış Bekliyor)', 'Locked (awaiting sale)')) ?></option>
            <option value="EXPIRED" <?= $filterStatus === 'EXPIRED' ? 'selected' : '' ?>><?= e(adm('Süresi Dolanlar', 'Expired')) ?></option>
          </select>
        </div>

        <!-- Date Filter -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Tarih', 'Date')) ?></label>
          <input 
            type="date" 
            name="date" 
            value="<?= e($filterDate) ?>" 
            class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none"
          >
        </div>

        <!-- Search Input -->
        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Arama (Kod / Saha)', 'Search (code / court)')) ?></label>
          <input 
            type="text" 
            name="q" 
            placeholder="<?= e(adm('Erişim kodu, saha adı...', 'Access code, court name...')) ?>" 
            value="<?= e($searchQuery) ?>" 
            class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none"
          >
        </div>

        <!-- Submit & Reset Buttons -->
        <div class="flex items-end gap-2">
          <button type="submit" class="flex-1 rounded-xl bg-accent py-2.5 font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition">
            <?= e(adm('Filtrele', 'Filter')) ?>
          </button>
          <a href="matches.php" class="px-3 py-2.5 rounded-xl border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10 transition text-center" title="<?= e(adm('Filtreleri Sıfırla', 'Reset filters')) ?>">
            &times;
          </a>
        </div>
      </form>
    </div>

    <!-- Add Match Modal -->
    <div id="addMatchModal" class="hidden mb-8 p-6 rounded-3xl bg-ink-800 border border-accent/30 shadow-2xl">
      <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/10">
        <div>
          <h3 class="font-display text-lg font-bold text-white"><?= e(adm('Yeni Maç / Slot Kaydı Ekle', 'Add a new match / slot record')) ?></h3>
          <p class="text-xs text-neutral-400"><?= e(adm('İstediğiniz tesis için doğrudan maç kaydı açabilir, satıldı veya kilitli olarak tanımlayabilirsiniz.', 'Create a match record for any facility and set it as sold or locked.')) ?></p>
        </div>
        <button type="button" onclick="document.getElementById('addMatchModal').classList.add('hidden')" class="text-neutral-400 hover:text-white text-lg font-bold">&times;</button>
      </div>

      <form action="matches.php" method="POST" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs items-end">
        <input type="hidden" name="action" value="add_match">

        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Tesis Seçin', 'Select facility')) ?></label>
          <select name="facility_id" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <?php foreach ($facilities as $fac): ?>
              <option value="<?= e($fac['id']) ?>"><?= e($fac['name']) ?> (<?= e($fac['city']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Saha / Kort', 'Court')) ?></label>
          <input type="text" name="court" value="<?= e(adm('Saha 1', 'Court 1')) ?>" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
        </div>

        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Tarih', 'Date')) ?></label>
          <input type="date" name="date" value="<?= date('Y-m-d') ?>" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
        </div>

        <div>
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Saat Slotu', 'Time slot')) ?></label>
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
          <label class="block text-neutral-400 font-semibold mb-1"><?= e(adm('Başlangıç Durumu', 'Initial status')) ?></label>
          <select name="status" class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
            <option value="UNLOCKABLE"><?= e(adm('Satıldı (İzlemeye Açık)', 'Sold (viewable)')) ?></option>
            <option value="LOCKED"><?= e(adm('Kilitli (Satış Bekliyor)', 'Locked (awaiting sale)')) ?></option>
          </select>
        </div>

        <div>
          <button type="submit" class="w-full rounded-xl bg-accent py-2.5 font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition">
            <?= e(adm('Maçı Oluştur →', 'Create match →')) ?>
          </button>
        </div>
      </form>
    </div>

    <!-- Matches Table -->
    <div class="rounded-2xl bg-white/[0.02] overflow-hidden border border-white/10">
      <div class="p-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h2 class="font-display text-xl font-bold text-white"><?= e(adm('Maç Listesi & Satış Kontrolü', 'Match list & sale control')) ?></h2>
          <p class="text-xs text-neutral-400"><?= e(adm('Bulunan', 'Found')) ?> <?= count($matchesList) ?> <?= e(adm('maç kaydı listeleniyor', 'match records listed')) ?></p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
            <tr>
              <th class="px-4 py-3"><?= e(adm('Tesis & Şehir', 'Facility & city')) ?></th>
              <th class="px-4 py-3"><?= e(adm('Saha & Slot', 'Court & slot')) ?></th>
              <th class="px-4 py-3"><?= e(adm('Tarih', 'Date')) ?></th>
              <th class="px-4 py-3"><?= e(adm('Durum', 'Status')) ?></th>
              <th class="px-4 py-3"><?= e(adm('Erişim Kodu', 'Access code')) ?></th>
              <th class="px-4 py-3"><?= e(adm('Satış Tarihi / Fiyat', 'Sale date / price')) ?></th>
              <th class="px-4 py-3 text-center"><?= e(adm('İzlenme', 'Views')) ?></th>
              <th class="px-4 py-3 text-right"><?= e(adm('İşlemler', 'Actions')) ?></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 text-neutral-300">
            <?php if (empty($matchesList)): ?>
              <tr>
                <td colspan="8" class="p-8 text-center text-neutral-500 italic">
                  <?= e(adm('Seçilen filtrelere uygun maç kaydı bulunamadı.', 'No match records fit the selected filters.')) ?>
                </td>
              </tr>
            <?php endif; ?>

            <?php foreach ($matchesList as $m): ?>
              <tr class="border-b border-white/5 hover:bg-white/[0.02] transition">
                <td class="px-4 py-2.5">
                  <div class="font-bold text-white"><?= e($m['facility_name']) ?></div>
                  <div class="text-xs text-neutral-400"><?= e($m['city']) ?> · <?= strtoupper(e($m['facility_sport'])) ?></div>
                </td>
                
                <td class="px-4 py-2.5">
                  <div class="font-semibold text-neutral-200"><?= e($m['court']) ?></div>
                  <div class="text-xs text-neutral-400 font-mono"><?= e($m['time_slot']) ?></div>
                </td>

                <td class="px-4 py-2.5 text-xs font-mono"><?= e($m['date']) ?></td>

                <td class="px-4 py-2.5">
                  <?php if ($m['status'] === 'UNLOCKABLE'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                      <?= e(adm('Satıldı (Açık)', 'Sold (open)')) ?>
                    </span>
                  <?php elseif ($m['status'] === 'LOCKED'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                      <?= e(adm('Kilitli (Bekliyor)', 'Locked (waiting)')) ?>
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-neutral-500/10 text-neutral-400 border border-neutral-500/20">
                      <?= e($m['status']) ?>
                    </span>
                  <?php endif; ?>
                </td>

                <td class="px-4 py-2.5">
                  <?php if ($m['status'] === 'UNLOCKABLE'): ?>
                    <button 
                      type="button" 
                      onclick="navigator.clipboard.writeText('<?= e($m['shown_code']) ?>'); alert('Erişim kodu kopyalandı: <?= e($m['shown_code']) ?>');"
                      title="<?= e(adm('Kopyalamak İçin Tıklayın', 'Click to copy')) ?>"
                      class="inline-flex items-center gap-1.5 font-mono text-sm font-bold text-accent bg-accent/10 hover:bg-accent/20 px-2.5 py-1 rounded-lg border border-accent/30 transition cursor-pointer"
                    >
                      <?= e($m['shown_code']) ?>
                      <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                  <?php else: ?>
                    <span class="text-xs text-neutral-500 italic font-mono"><?= e($m['shown_code']) ?></span>
                  <?php endif; ?>
                </td>

                <td class="px-4 py-2.5 text-xs">
                  <?php if (!empty($m['sold_at'])): ?>
                    <div class="text-neutral-300 font-mono"><?= date('d.m.Y H:i', strtotime($m['sold_at'])) ?></div>
                    <div class="text-emerald-400 font-semibold"><?= e(replash_money(replash_video_price($m['sport'], (float)$m['unit_price'], $m))) ?><?= empty($m['billable']) ? ' · ' . adm('ücretsiz', 'free') : '' ?></div>
                  <?php else: ?>
                    <span class="text-neutral-500 italic"><?= e(adm('Satış Yapılmadı', 'Not sold')) ?></span>
                  <?php endif; ?>
                </td>

                <td class="px-4 py-2.5 text-center">
                  <span class="text-xs font-mono px-2 py-0.5 rounded-md bg-white/5 text-neutral-300">
                    <?= (int)($m['views'] ?? 0) ?>
                  </span>
                </td>

                <td class="px-4 py-2.5 text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Status Toggle Button -->
                    <?php if ($m['status'] === 'LOCKED'): ?>
                      <form action="matches.php" method="POST" class="inline-block">
                        <input type="hidden" name="action" value="unlock_match">
                        <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                        <button type="submit" title="<?= e(adm('Satışa Aç & Kodu Aktifleştir', 'Unlock & activate code')) ?>" class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/30 text-xs font-semibold">
                          <?= e(adm('Satışa Aç', 'Unlock')) ?>
                        </button>
                      </form>
                    <?php else: ?>
                      <form action="matches.php" method="POST" class="inline-block" onsubmit="return confirm('Bu maçı tekrar Kilitli/Bekliyor durumuna almak istediğinize emin misiniz?');">
                        <input type="hidden" name="action" value="lock_match">
                        <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                        <button type="submit" title="<?= e(adm('Kilitle / Satış Bekliyor Yap', 'Lock / set awaiting sale')) ?>" class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/30 text-xs font-semibold">
                          <?= e(adm('Kilitle', 'Lock')) ?>
                        </button>
                      </form>
                    <?php endif; ?>

                    <!-- View Match Link -->
                    <a 
                      href="<?= base_url('find.php?code=' . urlencode($m['shown_code'])) ?>" 
                      target="_blank" 
                      title="<?= e(adm('Videoyu Aç & İzle', 'Open & watch video')) ?>" 
                      class="p-1.5 rounded-lg bg-white/5 text-neutral-300 hover:text-white hover:bg-white/10 border border-white/10"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>

                    <!-- Edit Modal Trigger -->
                    <button 
                      type="button" 
                      data-edit="<?= e(json_encode(['id' => $m['id'], 'title' => $m['facility_name'] . ' · ' . $m['code'], 'court' => $m['court'], 'date' => $m['date'], 'time_slot' => $m['time_slot'], 'access_code' => $m['shown_code'], 'status' => $m['status'], 'playback_url' => $m['playback_url'], 'duration_sec' => (int)($m['duration_sec'] ?? 3600)], JSON_UNESCAPED_UNICODE)) ?>"
                      onclick="openEditMatch(this)"
                      title="<?= e(adm('Maçı Düzenle', 'Edit match')) ?>" 
                      class="p-1.5 rounded-lg bg-accent/10 text-accent hover:bg-accent/20 border border-accent/30"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>

                    <!-- Delete Form -->
                    <form action="matches.php" method="POST" class="inline-block" onsubmit="return confirm('Bu maç kaydını silmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="delete_match">
                      <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                      <button type="submit" title="<?= e(adm('Maçı Sil', 'Delete match')) ?>" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </form>
                  </div>

                                  </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

<!-- Ortak maç düzenleme penceresi (satır başına kopyalar yerine tek pencere) -->
<div id="editMatchModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" role="dialog" aria-modal="true" aria-labelledby="em_heading">
  <div class="w-full max-w-xl rounded-2xl border border-white/10 bg-ink-900 p-6 text-left shadow-2xl">
    <div class="mb-4 flex items-center justify-between border-b border-white/10 pb-3">
      <div>
        <h3 id="em_heading" class="font-display text-lg font-bold"><?= e(adm('Maç kaydını düzenle', 'Edit match record')) ?></h3>
        <span id="em_title" class="text-xs font-semibold text-accent"></span>
      </div>
      <button type="button" onclick="closeEditMatch()" class="text-xl font-bold text-white/50 hover:text-white" aria-label="<?= e(adm('Kapat', 'Close')) ?>">&times;</button>
    </div>
    <form action="matches.php" method="POST" class="space-y-3.5 text-xs">
      <input type="hidden" name="action" value="edit_match">
      <input type="hidden" name="match_id" id="em_id">
      <input type="hidden" name="duration_sec" id="em_duration_sec">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <label class="block"><span class="mb-1 block font-medium text-white/50"><?= e(adm('Saha / Kort', 'Court')) ?></span><input type="text" name="court" id="em_court" required class="w-full rounded-xl border border-white/10 bg-white/[0.04] p-2.5 text-white outline-none focus:border-accent"></label>
        <label class="block"><span class="mb-1 block font-medium text-white/50"><?= e(adm('Tarih', 'Date')) ?></span><input type="date" name="date" id="em_date" required class="w-full rounded-xl border border-white/10 bg-white/[0.04] p-2.5 text-white outline-none focus:border-accent"></label>
      </div>
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <label class="block"><span class="mb-1 block font-medium text-white/50"><?= e(adm('Saat', 'Time')) ?></span><input type="text" name="time_slot" id="em_time_slot" required class="w-full rounded-xl border border-white/10 bg-white/[0.04] p-2.5 text-white outline-none focus:border-accent font-mono"></label>
        <label class="block"><span class="mb-1 block font-medium text-white/50"><?= e(adm('Erişim kodu', 'Access code')) ?></span><input type="text" name="access_code" id="em_access_code" inputmode="numeric" placeholder="<?= e(adm('Boş bırakılırsa otomatik', 'Automatic if left empty')) ?>" class="w-full rounded-xl border border-white/10 bg-white/[0.04] p-2.5 text-white outline-none focus:border-accent font-mono font-bold uppercase text-accent"></label>
        <label class="block"><span class="mb-1 block font-medium text-white/50"><?= e(adm('Durum', 'Status')) ?></span>
          <select name="status" id="em_status" class="w-full rounded-xl border border-white/10 bg-white/[0.04] p-2.5 text-white outline-none focus:border-accent">
            <option value="UNLOCKABLE" class="bg-ink-900"><?= e(adm('Satıldı (açık)', 'Sold (open)')) ?></option>
            <option value="LOCKED" class="bg-ink-900"><?= e(adm('Kilitli (bekliyor)', 'Locked (waiting)')) ?></option>
            <option value="EXPIRED" class="bg-ink-900"><?= e(adm('Süresi doldu', 'Expired')) ?></option>
          </select>
        </label>
      </div>
      <label class="block"><span class="mb-1 block font-medium text-white/50"><?= e(adm('Özel video / yayın adresi (isteğe bağlı)', 'Custom video / stream URL (optional)')) ?></span><input type="text" name="playback_url" id="em_playback_url" class="w-full rounded-xl border border-white/10 bg-white/[0.04] p-2.5 text-white outline-none focus:border-accent font-mono"></label>
      <p class="text-[11px] text-white/40"><?= e(adm('Durum değişiklikleri Satış Denetimi\'ne yönetim işlemi olarak kaydedilir.', 'Status changes are recorded in Sales audit as an admin action.')) ?></p>
      <div class="flex items-center justify-end gap-2 border-t border-white/10 pt-3">
        <button type="button" onclick="closeEditMatch()" class="rounded-xl border border-white/15 px-4 py-2 text-white/70 hover:border-accent hover:text-accent"><?= e(adm('Vazgeç', 'Cancel')) ?></button>
        <button type="submit" class="rounded-xl bg-accent px-5 py-2 font-bold text-ink-900"><?= e(adm('Kaydet', 'Save')) ?></button>
      </div>
    </form>
  </div>
</div>
<script>
  function openEditMatch(btn) {
    var d = JSON.parse(btn.getAttribute('data-edit'));
    ['id','court','date','time_slot','access_code','status','playback_url','duration_sec'].forEach(function (k) {
      var el = document.getElementById('em_' + k); if (el) el.value = d[k] == null ? '' : d[k];
    });
    document.getElementById('em_title').textContent = d.title;
    document.getElementById('editMatchModal').classList.remove('hidden');
  }
  function closeEditMatch() { document.getElementById('editMatchModal').classList.add('hidden'); }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeEditMatch(); });
</script>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
