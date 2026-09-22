<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = __('panel.header.badge');
$error = null;
$success = null;
$soldNotice = null; // satıştan sonra büyük kod ekranı

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['facility_id']);
    header('Location: index.php');
    exit;
}

// Handle PIN Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pin'])) {
    $pin = trim($_POST['pin'] ?? '');
    if ($pdo && replash_attempt_blocked($pdo, 'pin', REPLASH_LOGIN_MAX_FAILS, REPLASH_LOGIN_WINDOW_MIN)) {
        $error = str_replace('{m}', (string)REPLASH_LOGIN_WINDOW_MIN, __('panel.login.too_many'));
    } elseif ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE pin = ? LIMIT 1");
        $stmt->execute([replash_pin_hash($pin)]);
        $fac = $stmt->fetch();
        if ($fac) {
            session_regenerate_id(true);
            $_SESSION['facility_id'] = $fac['id'];
            header('Location: index.php');
            exit;
        } else {
            replash_attempt_failed($pdo, 'pin');
            $error = __('panel.login.invalid_pin');
        }
    } else {
        $error = "Veritabanı bağlantısı kurulamadı! / Database connection failed.";
    }
}

$facility = null;
$matches = [];
$facilityCameras = [];

if (isset($_SESSION['facility_id']) && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['facility_id']]);
    $facility = $stmt->fetch();

    if ($facility) {
        // Fetch facility cameras
        $stmtC = $pdo->prepare("SELECT * FROM cameras WHERE facility_id = ? ORDER BY court ASC");
        $stmtC->execute([$facility['id']]);
        $facilityCameras = $stmtC->fetchAll();

        // Unique list of court data for this facility
        $facilityCourts = get_facility_courts_data($facility);
        $uniqueCourts = array_column($facilityCourts, 'name');
        if (empty($uniqueCourts)) {
            $uniqueCourts = [];
            foreach ($facilityCameras as $fc) {
                if (!in_array($fc['court'], $uniqueCourts)) {
                    $uniqueCourts[] = $fc['court'];
                }
            }
        }
        if (empty($uniqueCourts)) {
            $uniqueCourts = ['Saha 1'];
        }

        // Handle Video Sold Action (Unlocking an Existing Locked Slot)
        if (isset($_POST['action']) && $_POST['action'] === 'mark_sold') {
            $matchId = $_POST['match_id'] ?? '';
            $stmtFind = $pdo->prepare("SELECT * FROM matches WHERE id = ? AND facility_id = ? LIMIT 1");
            $stmtFind->execute([$matchId, $facility['id']]);
            $mRow = $stmtFind->fetch();

            if ($mRow && (replash_match_expired($mRow))) {
                $error = __('panel.shell.expired_sell');
            } elseif ($mRow && $mRow['status'] === 'LOCKED') {
                // Ensure playback URL is current
                $camStmt = $pdo->prepare("SELECT stream_url FROM cameras WHERE id = ? OR (facility_id = ? AND court = ?) LIMIT 1");
                $camStmt->execute([$mRow['camera_id'] ?? 0, $facility['id'], $mRow['court']]);
                $camStream = $camStmt->fetchColumn() ?: '';
                $playbackUrl = buildMatchPlaybackUrl($camStream, $mRow['date'], $mRow['time_slot'], (int)($mRow['duration_sec'] ?? 3600));

                $newCode = replash_new_access_code($pdo);
                $upd = $pdo->prepare("UPDATE matches SET status = 'UNLOCKABLE', access_code = ?, playback_url = COALESCE(NULLIF(?, ''), playback_url), sold_at = NOW(), billable = 1 WHERE id = ? AND status = 'LOCKED'");
                $upd->execute([$newCode, $playbackUrl, $matchId]);
                replash_record_sale($pdo, $mRow, 'sold', 'panel');
                $soldNotice = ['code' => $newCode, 'court' => $mRow['court'], 'slot' => $mRow['time_slot']];
            }
        }

        // Maçtan önce satış: kayıt henüz gelmeden saha + saat için satış yapılır, kod hemen verilir.
        // Kamera kaydı geldiğinde aynı saha/tarih/saat kaydına bağlanır (api/hooks/recording.php).
        if (isset($_POST['action']) && $_POST['action'] === 'presell') {
            $psCourt = trim((string)($_POST['court'] ?? ''));
            $psDate = (string)($_POST['date'] ?? date('Y-m-d'));
            $psStart = (string)($_POST['start'] ?? '');
            $psDur = (int)($_POST['duration'] ?? 60);
            $courtData = null;
            foreach (get_facility_courts_data($facility) as $cd) { if ($cd['name'] === $psCourt) { $courtData = $cd; break; } }
            $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $psDate) && $psDate >= date('Y-m-d') && $psDate <= date('Y-m-d', strtotime('+7 days'));
            if (!$courtData || !$validDate || !preg_match('/^([01]\d|2[0-3]):(00|30)$/', $psStart) || !in_array($psDur, [60, 90, 120], true)) {
                $error = __('panel.presale.invalid');
            } else {
                $psStartTs = strtotime($psDate . ' ' . $psStart);
                $psSlot = $psStart . '–' . date('H:i', $psStartTs + $psDur * 60);
                $psHour = (int)substr($psStart, 0, 2);
                $ex = $pdo->prepare("SELECT * FROM matches WHERE facility_id = ? AND court = ? AND date = ? AND time_slot = ? LIMIT 1");
                $ex->execute([$facility['id'], $psCourt, $psDate, $psSlot]);
                $row = $ex->fetch();
                if ($row && $row['status'] === 'UNLOCKABLE') {
                    // Zaten satılmış: aynı kodu tekrar göster
                    $soldNotice = ['code' => $row['access_code'], 'court' => $row['court'], 'slot' => $row['time_slot']];
                } elseif ($row && (replash_match_expired($row))) {
                    $error = __('panel.shell.expired_sell');
                } else {
                    $newCode = replash_new_access_code($pdo);
                    if ($row) {
                        $pdo->prepare("UPDATE matches SET status = 'UNLOCKABLE', access_code = ?, sold_at = NOW(), billable = 1 WHERE id = ? AND status = 'LOCKED'")
                            ->execute([$newCode, $row['id']]);
                        $logRow = $row;
                    } else {
                        $camStmt = $pdo->prepare("SELECT id FROM cameras WHERE facility_id = ? AND court = ? ORDER BY id ASC LIMIT 1");
                        $camStmt->execute([$facility['id'], $psCourt]);
                        $logRow = ['id' => 'm-' . bin2hex(random_bytes(5)), 'facility_id' => $facility['id']];
                        $pdo->prepare("INSERT INTO matches
                            (id, code, access_code, camera_id, facility_id, court, date, time_slot, status, playback_url, duration_sec, sold_at, views, billable, sport, source)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'UNLOCKABLE', NULL, " . ($psDur * 60) . ", NOW(), 0, 1, ?, 'presale')")
                            ->execute([$logRow['id'], 'MATCH-' . str_replace('-', '', $psDate) . '-' . str_replace(':', '', $psStart), $newCode,
                                       $camStmt->fetchColumn() ?: null, $facility['id'], $psCourt, $psDate, $psSlot, $courtData['sport']]);
                    }
                    replash_record_sale($pdo, $logRow, 'sold', 'panel', 'presale');
                    $soldNotice = ['code' => $newCode, 'court' => $psCourt, 'slot' => $psSlot];
                    $presaleDay = $psDate; // tablo satılan günü göstersin
                }
            }
        }

        // Handle Re-locking Action (Locking an Unlocked Slot / Revoking Access)
        if (isset($_POST['action']) && $_POST['action'] === 'mark_locked') {
            $matchId = $_POST['match_id'] ?? '';
            $stmtFind = $pdo->prepare("SELECT * FROM matches WHERE id = ? AND facility_id = ? LIMIT 1");
            $stmtFind->execute([$matchId, $facility['id']]);
            $mRow = $stmtFind->fetch();

            if ($mRow) {
                $undo = replash_staff_undo_status($mRow);
                $reason = trim(mb_substr((string)($_POST['reason'] ?? ''), 0, 300));
                if (!$undo['allowed']) {
                    $error = str_replace('{m}', (string)REPLASH_UNDO_WINDOW_MIN, __('panel.shell.undo_' . $undo['why']));
                } elseif ($reason === '') {
                    $error = __('panel.shell.undo_reason_required');
                } else {
                    // Kod iptal: oyuncunun kodu ve eski linki artık çalışmaz
                    $upd = $pdo->prepare("UPDATE matches SET status = 'LOCKED', access_code = ?, sold_at = NULL, billable = 0, sale_price = NULL, sale_currency = NULL WHERE id = ? AND first_opened_at IS NULL");
                    $upd->execute([replash_locked_placeholder(), $matchId]);
                    replash_log_sale_event($pdo, $mRow, 'undone', 'panel', $reason, isset($mRow['sale_price']) ? (float)$mRow['sale_price'] : null, $mRow['sale_currency'] ?? null);
                    $success = e($mRow['court'] . ' · ' . $mRow['time_slot'] . ' — ' . __('panel.shell.undone_ok'));
                }
            }
        }

        // Fetch statistics for this facility
        // Billing rules (includes/billing.php): valid sales only, standard price per sport
        $validSql = replash_valid_sale_sql('m');
        $priceSql = replash_sale_price_sql('m', number_format((float)($facility['unit_price'] ?? 0), 2, '.', ''), replash_facility_prices($facility), null);
        $saleDateSql = replash_sale_date_sql('m');

        $stmtStats = $pdo->prepare("SELECT 
            COUNT(*) as total_records,
            SUM(CASE WHEN {$validSql} THEN 1 ELSE 0 END) as sold_count,
            SUM(CASE WHEN {$validSql} AND {$saleDateSql} = CURDATE() THEN 1 ELSE 0 END) as today_sold_count,
            COALESCE(SUM(CASE WHEN {$validSql} THEN {$priceSql} ELSE 0 END), 0) as total_revenue,
            COALESCE(SUM(CASE WHEN {$validSql} AND {$saleDateSql} = CURDATE() THEN {$priceSql} ELSE 0 END), 0) as today_revenue,
            SUM(CASE WHEN m.status = 'LOCKED' THEN 1 ELSE 0 END) as locked_count,
            SUM(m.views) as total_views
        FROM matches m WHERE m.facility_id = ?");
        $stmtStats->execute([$facility['id']]);
        $stats = $stmtStats->fetch() ?: [
            'total_records' => 0,
            'sold_count' => 0,
            'today_sold_count' => 0,
            'total_revenue' => 0,
            'today_revenue' => 0,
            'locked_count' => 0,
            'total_views' => 0
        ];

        $currency = REPLASH_BILLING_CURRENCY;

        // 1. Daily Data (Last 14 Days)
        $dailyLabels = [];
        $dailyRevenue = [];
        $dailyCount = [];
        $dailyMap = [];

        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $dailyLabels[] = date('d/m', strtotime($d));
            $dailyMap[$d] = 0;
        }

        $dailyRevMap = $dailyMap;
        $stmtDaily = $pdo->prepare("SELECT {$saleDateSql} as s_date, COUNT(*) as cnt, COALESCE(SUM({$priceSql}), 0) as rev
            FROM matches m
            WHERE m.facility_id = ? AND {$validSql} AND {$saleDateSql} >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            GROUP BY s_date");
        $stmtDaily->execute([$facility['id']]);
        while ($row = $stmtDaily->fetch(PDO::FETCH_ASSOC)) {
            if (isset($dailyMap[$row['s_date']])) {
                $dailyMap[$row['s_date']] = (int)$row['cnt'];
                $dailyRevMap[$row['s_date']] = (float)$row['rev'];
            }
        }
        foreach ($dailyMap as $d => $cnt) {
            $dailyCount[] = $cnt;
            $dailyRevenue[] = round($dailyRevMap[$d], 2);
        }

        // 2. Weekly Data (Last 8 Weeks)
        $weeklyLabels = [];
        $weeklyCount = [];
        $weeklyRevenue = [];

        for ($w = 7; $w >= 0; $w--) {
            $startOfWeek = date('Y-m-d', strtotime("-{$w} weeks monday this week"));
            $endOfWeek = date('Y-m-d', strtotime("-{$w} weeks sunday this week"));
            $weeklyLabels[] = date('d/m', strtotime($startOfWeek)) . '-' . date('d/m', strtotime($endOfWeek));

            $stmtW = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM({$priceSql}), 0) as rev FROM matches m
                WHERE m.facility_id = ? AND {$validSql}
                AND {$saleDateSql} BETWEEN ? AND ?");
            $stmtW->execute([$facility['id'], $startOfWeek, $endOfWeek]);
            $wRow = $stmtW->fetch(PDO::FETCH_ASSOC);
            $weeklyCount[] = (int)$wRow['cnt'];
            $weeklyRevenue[] = round((float)$wRow['rev'], 2);
        }

        // 3. Monthly Data (Last 6 Months)
        $monthlyLabels = [];
        $monthlyCount = [];
        $monthlyRevenue = [];
        
        $monthDict = [
            'tr' => ['01'=>'Oca','02'=>'Şub','03'=>'Mar','04'=>'Nis','05'=>'May','06'=>'Haz','07'=>'Tem','08'=>'Ağu','09'=>'Eyl','10'=>'Eki','11'=>'Kas','12'=>'Ara'],
            'en' => ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'],
            'es' => ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'],
            'it' => ['01'=>'Gen','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mag','06'=>'Giu','07'=>'Lug','08'=>'Ago','09'=>'Set','10'=>'Ott','11'=>'Nov','12'=>'Dic']
        ];
        $activeMonthNames = $monthDict[$current_lang] ?? $monthDict['en'];

        for ($m = 5; $m >= 0; $m--) {
            $yearMonth = date('Y-m', strtotime("-{$m} months"));
            $mNum = date('m', strtotime($yearMonth . '-01'));
            $monthlyLabels[] = ($activeMonthNames[$mNum] ?? $mNum) . ' ' . date('y', strtotime($yearMonth . '-01'));

            $stmtMth = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM({$priceSql}), 0) as rev FROM matches m
                WHERE m.facility_id = ? AND {$validSql}
                AND DATE_FORMAT({$saleDateSql}, '%Y-%m') = ?");
            $stmtMth->execute([$facility['id'], $yearMonth]);
            $mRow = $stmtMth->fetch(PDO::FETCH_ASSOC);
            $monthlyCount[] = (int)$mRow['cnt'];
            $monthlyRevenue[] = round((float)$mRow['rev'], 2);
        }

        // 4. Period Totals
        $todayRevenue = (float)$stats['today_revenue'];

        $stmtThisWeek = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM({$priceSql}), 0) as rev FROM matches m
            WHERE m.facility_id = ? AND {$validSql}
            AND YEARWEEK({$saleDateSql}, 1) = YEARWEEK(CURDATE(), 1)");
        $stmtThisWeek->execute([$facility['id']]);
        $weekRow = $stmtThisWeek->fetch(PDO::FETCH_ASSOC);
        $thisWeekCount = (int)$weekRow['cnt'];
        $thisWeekRevenue = (float)$weekRow['rev'];

        // Current month statement (tier, shares, Replash invoice) — billing guide
        $monthStatement = replash_monthly_statement($pdo, $facility['id'], date('Y-m'));
        $thisMonthCount = $monthStatement['valid_sales'];
        $thisMonthRevenue = $monthStatement['gross'];

        $totalRevenue = (float)$stats['total_revenue'];
    }
}


// ===== Maçlar ve Slotlar: tarih kısıtı olmayan ay takvimi + seçili günün saha × saat tablosu =====
$tab = (($_GET['tab'] ?? '') === 'account') ? 'account' : 'matches';
$viewDay = $presaleDay ?? ((isset($_GET['day']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['day'])) ? $_GET['day'] : date('Y-m-d'));
$viewMonth = (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month'])) ? $_GET['month'] : substr($viewDay, 0, 7);
$dayMatches = [];
$monthCounts = [];
$gridCourts = [];
$gridSlots = [];
$grid = [];
$undoCountMonth = 0;
if (!empty($facility) && $pdo) {
    $stmtDay = $pdo->prepare("SELECT * FROM matches WHERE facility_id = ? AND date = ? ORDER BY time_slot ASC, court ASC");
    $stmtDay->execute([$facility['id'], $viewDay]);
    $dayMatches = $stmtDay->fetchAll();

    $stmtMonth = $pdo->prepare("SELECT date, COUNT(*) AS total, SUM(status = 'UNLOCKABLE') AS sold, SUM(status = 'LOCKED') AS locked
        FROM matches WHERE facility_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? GROUP BY date");
    $stmtMonth->execute([$facility['id'], $viewMonth]);
    foreach ($stmtMonth->fetchAll() as $mc) { $monthCounts[$mc['date']] = $mc; }

    // Sütunlar: tesisin sahaları (+ o gün kayıtta görünen diğer sahalar)
    foreach (get_facility_courts_data($facility) as $cd) { $gridCourts[$cd['name']] = true; }
    foreach ($dayMatches as $dm) {
        $gridCourts[$dm['court']] = true;
        $gridSlots[$dm['time_slot']] = true;
        $grid[$dm['time_slot']][$dm['court']][] = $dm;
    }
    $gridCourts = array_keys($gridCourts);
    $gridSlots = array_keys($gridSlots);
    sort($gridSlots);
    $undoCountMonth = replash_undo_count($pdo, $facility['id'], date('Y-m'));
}
$panelUrl = function (array $params = []) use ($tab, $viewDay, $viewMonth) {
    return 'index.php?' . http_build_query(array_merge(['tab' => $tab, 'day' => $viewDay, 'month' => $viewMonth], $params));
};

$internalShell = __('nav.panel');
require_once __DIR__ . '/../includes/header.php';

$monthNamesFull = [
    'tr' => ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
    'en' => ['January','February','March','April','May','June','July','August','September','October','November','December'],
    'it' => ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
    'es' => ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
];
$weekdayShort = [
    'tr' => ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'],
    'en' => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
    'it' => ['Lun','Mar','Mer','Gio','Ven','Sab','Dom'],
    'es' => ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'],
];
$L = in_array($current_lang, ['tr','en','it','es'], true) ? $current_lang : 'en';
?>

<main class="min-h-screen pb-10">
  <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">

    <?php if (!$facility): ?>
      <!-- Giriş -->
      <div class="grid min-h-[60vh] place-items-center">
        <form action="index.php" method="POST" class="w-full max-w-sm rounded-lg border border-gray-200 bg-white p-8 shadow-sm"><?= csrf_field() ?>
          <input type="hidden" name="login_pin" value="1">
          <h1 class="text-lg font-semibold text-gray-900"><?= e(__('panel.shell.login_title')) ?></h1>
          <p class="mb-5 mt-1 text-sm text-gray-500"><?= e(__('panel.shell.login_sub')) ?></p>
          <input type="password" name="pin" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="off" required autofocus placeholder="••••••"
            class="w-full rounded-md border border-gray-300 px-4 py-3 text-center font-mono text-2xl tracking-[0.5em] text-gray-900 outline-none focus:border-gray-900">
          <?php if ($error): ?>
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?= e($error) ?></div>
          <?php endif; ?>
          <button type="submit" class="mt-4 w-full rounded-md bg-gray-900 px-6 py-3 text-sm font-semibold text-white hover:bg-gray-800"><?= e(__('panel.shell.enter')) ?></button>
        </form>
      </div>

    <?php else: ?>
      <!-- Başlık ve sekmeler -->
      <div class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-gray-200">
        <div class="pb-3">
          <div class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= e($facility['name']) ?></div>
          <h1 class="mt-1 text-2xl font-semibold text-gray-900"><?= e($tab === 'account' ? __('panel.shell.tab_account') : __('panel.shell.tab_matches')) ?></h1>
        </div>
        <div class="flex items-end gap-6">
          <nav class="flex gap-6 text-sm" aria-label="Panel">
            <?php foreach (['matches' => __('panel.shell.tab_matches'), 'account' => __('panel.shell.tab_account')] as $tk => $tl): ?>
              <a href="index.php?tab=<?= $tk ?>" class="-mb-px border-b-2 pb-3 <?= $tab === $tk ? 'border-gray-900 font-semibold text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900' ?>" <?= $tab === $tk ? 'aria-current="page"' : '' ?>><?= e($tl) ?></a>
            <?php endforeach; ?>
          </nav>
          <a href="?action=logout" class="mb-2 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"><?= e(__('panel.shell.logout')) ?></a>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= e($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="mb-5 rounded-md border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700"><?= $success ?></div>
      <?php endif; ?>

      <?php if ($soldNotice): ?>
        <!-- Kilit açıldı: kodu oyuncuya ver -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-lg border border-gray-900 bg-white px-6 py-5">
          <div>
            <div class="text-sm text-gray-500"><?= e(__('panel.shell.sold_title')) ?> · <?= e($soldNotice['court']) ?> · <?= e($soldNotice['slot']) ?></div>
            <div class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500"><?= e(__('panel.shell.give_code')) ?></div>
          </div>
          <div class="flex items-center gap-4">
            <span class="select-all font-mono text-3xl font-bold tracking-[0.15em] text-gray-900 sm:text-4xl"><?= e($soldNotice['code']) ?></span>
            <button type="button" data-copy="<?= e($soldNotice['code']) ?>" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"><?= e(__('panel.shell.copy')) ?></button>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($tab === 'matches'): ?>
        <?php
          $monthTs = strtotime($viewMonth . '-01');
          $prevMonth = date('Y-m', strtotime('-1 month', $monthTs));
          $nextMonth = date('Y-m', strtotime('+1 month', $monthTs));
          $firstDow = (int)date('N', $monthTs); // 1 = Pazartesi
          $daysInMonth = (int)date('t', $monthTs);
          $today = date('Y-m-d');
          $dayTotals = ['t' => count($dayMatches), 's' => 0, 'l' => 0];
          foreach ($dayMatches as $dm) { if ($dm['status'] === 'UNLOCKABLE') $dayTotals['s']++; elseif ($dm['status'] === 'LOCKED') $dayTotals['l']++; }
        ?>
        <?php $courtPrices = []; foreach (get_facility_courts_data($facility) as $cd) { $courtPrices[$cd['name']] = (float)$cd['price']; } ?>
        <?php if (!empty($facilityCameras)): ?>
          <!-- Kamera durumu: kapalı kamera fark edilmeden maç kaydı kaçmasın -->
          <div class="mb-4 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm">
            <span class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= e(__('panel.cameras.title')) ?></span>
            <?php foreach ($facilityCameras as $fc):
              $seen = !empty($fc['last_seen_at']) ? strtotime($fc['last_seen_at']) : 0;
              if (!$seen) { $dot = 'bg-gray-400'; $txt = __('panel.cameras.never'); $cls = 'text-gray-500'; }
              elseif (($fc['status'] ?? '') === 'offline') { $dot = 'bg-red-600'; $txt = __('panel.cameras.offline'); $cls = 'text-red-700 font-medium'; }
              elseif ($seen && time() - $seen <= 7200) { $dot = 'bg-emerald-600'; $txt = __('panel.cameras.online'); $cls = 'text-gray-700'; }
              else { $dot = 'bg-gray-400'; $txt = $seen ? str_replace('{t}', date(date('Y-m-d', $seen) === date('Y-m-d') ? 'H:i' : 'd.m H:i', $seen), __('panel.cameras.last_seen')) : __('panel.cameras.never'); $cls = 'text-gray-500'; }
            ?>
              <span class="inline-flex items-center gap-2" title="<?= e($fc['court']) ?>">
                <span class="h-2 w-2 rounded-full <?= $dot ?>"></span>
                <span class="text-gray-900"><?= e($fc['name'] ?: $fc['court']) ?></span>
                <span class="<?= $cls ?>"><?= e($txt) ?></span>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="grid gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">

          <!-- Ay takvimi (tarih kısıtı yok) -->
          <aside class="self-start rounded-lg border border-gray-200 bg-white p-4">
            <div class="mb-3 flex items-center justify-between">
              <a href="index.php?tab=matches&month=<?= $prevMonth ?>&day=<?= e($viewDay) ?>" class="rounded-md px-2 py-1 text-gray-600 hover:bg-gray-100" aria-label="‹">‹</a>
              <div class="text-sm font-semibold text-gray-900"><?= e($monthNamesFull[$L][(int)date('n', $monthTs) - 1] . ' ' . date('Y', $monthTs)) ?></div>
              <a href="index.php?tab=matches&month=<?= $nextMonth ?>&day=<?= e($viewDay) ?>" class="rounded-md px-2 py-1 text-gray-600 hover:bg-gray-100" aria-label="›">›</a>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-gray-400">
              <?php foreach ($weekdayShort[$L] as $wd): ?><div class="py-1"><?= e($wd) ?></div><?php endforeach; ?>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center">
              <?php for ($i = 1; $i < $firstDow; $i++): ?><div></div><?php endfor; ?>
              <?php for ($d = 1; $d <= $daysInMonth; $d++):
                $ds = sprintf('%s-%02d', $viewMonth, $d);
                $mc = $monthCounts[$ds] ?? null;
                $isSel = ($ds === $viewDay);
                $isToday = ($ds === $today);
              ?>
                <a href="index.php?tab=matches&month=<?= $viewMonth ?>&day=<?= $ds ?>"
                   class="flex h-11 flex-col items-center justify-center rounded-md text-sm <?= $isSel ? 'bg-gray-900 text-white' : ($isToday ? 'border border-gray-900 text-gray-900' : 'text-gray-700 hover:bg-gray-100') ?>">
                  <span class="leading-none"><?= $d ?></span>
                  <?php if ($mc): ?>
                    <span class="mt-1 text-[10px] leading-none <?= $isSel ? 'text-gray-300' : ((int)$mc['locked'] > 0 ? 'text-gray-900 font-semibold' : 'text-gray-400') ?>"><?= (int)$mc['total'] ?></span>
                  <?php endif; ?>
                </a>
              <?php endfor; ?>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs text-gray-500">
              <a href="index.php?tab=matches" class="rounded-md border border-gray-300 px-2.5 py-1 text-gray-700 hover:bg-gray-50"><?= e(__('panel.shell.go_today')) ?></a>
              <span><?= e(__('panel.shell.cal_legend')) ?></span>
            </div>
          </aside>

          <!-- Seçili gün: saha × saat -->
          <section class="min-w-0 rounded-lg border border-gray-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-5 py-4">
              <div class="text-base font-semibold text-gray-900"><?= e(formatDateLocalized($viewDay, $current_lang)) ?></div>
              <div class="text-sm text-gray-500"><?= e(str_replace(['{t}', '{s}', '{l}'], [$dayTotals['t'], $dayTotals['s'], $dayTotals['l']], __('panel.shell.day_summary'))) ?></div>
            </div>

            <?php if ($viewDay >= date('Y-m-d') && $viewDay <= date('Y-m-d', strtotime('+7 days'))):
              $psDefault = ($viewDay === date('Y-m-d')) ? sprintf('%02d:%s', (int)date('G'), (int)date('i') >= 30 ? '30' : '00') : '18:00'; ?>
              <!-- Maçtan önce satış: ödeme alınınca kod hemen verilir, video maç bitince açılır -->
              <form action="<?= e($panelUrl()) ?>" method="POST" class="flex flex-wrap items-end gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3"><?= csrf_field() ?>
                <input type="hidden" name="action" value="presell">
                <input type="hidden" name="date" value="<?= e($viewDay) ?>">
                <div class="mr-auto min-w-[180px]">
                  <div class="text-sm font-medium text-gray-900"><?= e(__('panel.presale.title')) ?></div>
                  <div class="text-xs text-gray-500"><?= e(__('panel.presale.hint')) ?></div>
                </div>
                <label class="text-xs text-gray-600"><?= e(__('panel.presale.court')) ?>
                  <select name="court" id="psCourt" class="mt-1 block rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-sm text-gray-900">
                    <?php foreach (get_facility_courts_data($facility) as $cd): ?><option value="<?= e($cd['name']) ?>" data-sport="<?= e(strtolower($cd['sport'])) ?>"><?= e($cd['name']) ?></option><?php endforeach; ?>
                  </select>
                </label>
                <label class="text-xs text-gray-600"><?= e(__('panel.presale.start')) ?>
                  <select name="start" class="mt-1 block rounded-md border border-gray-300 bg-white px-2.5 py-1.5 font-mono text-sm text-gray-900">
                    <?php for ($m = 6 * 60; $m <= 23 * 60 + 30; $m += 30): $hv = sprintf('%02d:%02d', intdiv($m, 60), $m % 60); ?><option value="<?= $hv ?>" <?= $hv === $psDefault ? 'selected' : '' ?>><?= $hv ?></option><?php endfor; ?>
                  </select>
                </label>
                <label class="text-xs text-gray-600"><?= e(__('panel.presale.duration')) ?>
                  <select name="duration" id="psDuration" class="mt-1 block rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-sm text-gray-900">
                    <?php foreach ([60, 90, 120] as $dm): ?><option value="<?= $dm ?>"><?= $dm ?> <?= e(__('panel.presale.minutes')) ?></option><?php endforeach; ?>
                  </select>
                </label>
                <script>
                  // Padel genelde 90 dk, futbol 60 dk: saha seçilince varsayılan süre
                  (function () {
                    var c = document.getElementById('psCourt'), d = document.getElementById('psDuration');
                    var set = function () { var o = c.options[c.selectedIndex]; d.value = (o && o.getAttribute('data-sport') === 'padel') ? '90' : '60'; };
                    c.addEventListener('change', set); set();
                  })();
                </script>
                <button type="submit" class="rounded-md bg-gray-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-gray-800"><?= e(__('panel.presale.button')) ?></button>
              </form>
            <?php endif; ?>

            <?php if (empty($dayMatches)): ?>
              <div class="px-5 py-12 text-center text-sm text-gray-500"><?= e(__('panel.shell.no_matches')) ?></div>
            <?php else: ?>
              <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] border-collapse text-sm">
                  <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                      <th class="w-28 px-4 py-2.5"><?= e(__('panel.shell.time_col')) ?></th>
                      <?php foreach ($gridCourts as $gc): ?><th class="px-4 py-2.5"><?= e($gc) ?></th><?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($gridSlots as $gs): ?>
                      <tr class="border-b border-gray-100 align-top">
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-sm text-gray-700"><?= e($gs) ?></td>
                        <?php foreach ($gridCourts as $gc): ?>
                          <td class="px-4 py-3">
                            <?php if (empty($grid[$gs][$gc])): ?>
                              <span class="text-gray-300">—</span>
                            <?php else: foreach ($grid[$gs][$gc] as $cm):
                              $expired = replash_match_expired($cm);
                              $undoState = replash_staff_undo_status($cm);
                            ?>
                              <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <?php if ($expired): ?>
                                  <span class="text-sm text-gray-400"><?= e(__('panel.shell.deleted_state')) ?></span>
                                <?php elseif ($cm['status'] === 'UNLOCKABLE'): ?>
                                  <div class="min-w-[110px]">
                                    <div class="text-[11px] font-medium uppercase tracking-wide text-emerald-700"><?= e(__('panel.shell.sold_state')) ?><?php if ($cm['sale_price'] !== null): ?> · <?= e(replash_money((float)$cm['sale_price'])) ?><?php endif; ?></div>
                                    <button type="button" data-copy="<?= e($cm['access_code']) ?>" title="<?= e(__('panel.shell.copy')) ?>" class="select-all font-mono text-base font-semibold tracking-wider text-gray-900 hover:underline"><?= e($cm['access_code']) ?></button>
                                    <?php $vs = replash_video_state($pdo, $cm); if ($vs['state'] !== 'ready'): ?>
                                      <div class="mt-0.5 text-[11px] <?= $vs['state'] === 'delayed' ? 'font-medium text-red-700' : 'text-gray-500' ?>"><?= e(str_replace('{t}', date('H:i', $vs['eta']), __('panel.presale.state_' . $vs['state']))) ?></div>
                                    <?php endif; ?>
                                  </div>
                                  <?php if ($undoState['allowed']): ?>
                                    <button type="button" data-relock="<?= e($cm['id']) ?>" data-label="<?= e($gc . ' · ' . $gs) ?>" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                      title="<?= e(str_replace('{m}', (string)max(1, (int)ceil($undoState['seconds_left'] / 60)), __('panel.shell.undo_left'))) ?>"><?= e(__('panel.shell.relock')) ?></button>
                                  <?php else: ?>
                                    <button type="button" disabled title="<?= e(__('panel.shell.final_hint')) ?>" class="cursor-not-allowed rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-400"><?= e(__('panel.shell.final_state')) ?></button>
                                  <?php endif; ?>
                                <?php else: ?>
                                  <div class="min-w-[110px] text-[11px] font-medium uppercase tracking-wide text-gray-500"><?= e(__('panel.shell.locked_state')) ?><?php if (isset($courtPrices[$cm['court']])): ?> · <?= e(replash_money($courtPrices[$cm['court']])) ?><?php endif; ?></div>
                                  <form action="<?= e($panelUrl()) ?>" method="POST"><?= csrf_field() ?>
                                    <input type="hidden" name="action" value="mark_sold">
                                    <input type="hidden" name="match_id" value="<?= e($cm['id']) ?>">
                                    <button type="submit" class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800"><?= e(__('panel.shell.unlock')) ?></button>
                                  </form>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; endif; ?>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>

            <div class="border-t border-gray-200 px-5 py-3 text-xs text-gray-500"><?= e(__('panel.shell.auto_note')) ?></div>
          </section>
        </div>

        <?php $ms = $monthStatement; ?>
        <p class="mt-4 text-xs text-gray-500">
          <?= e(str_replace(['{n}', '{pct}', '{share}'], [$ms['valid_sales'], $ms['facility_pct'], replash_money($ms['facility_share'])], __('panel.shell.month_line'))) ?>
          · <a href="index.php?tab=account" class="text-gray-700 underline"><?= e(__('panel.shell.tab_account')) ?></a>
        </p>

        <!-- Tekrar kilitleme (aynı düğme; kurallar: includes/sales.php) -->
        <dialog id="relockDialog" class="w-full max-w-md rounded-lg border border-gray-200 p-0 shadow-xl backdrop:bg-black/30">
          <form action="<?= e($panelUrl()) ?>" method="POST" class="p-6"><?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_locked">
            <input type="hidden" name="match_id" id="relockMatchId">
            <h2 class="text-base font-semibold text-gray-900"><?= e(__('panel.shell.relock_title')) ?></h2>
            <p id="relockLabel" class="mt-1 text-sm text-gray-500"></p>
            <p class="mt-3 text-sm text-gray-600"><?= e(__('panel.shell.relock_desc')) ?></p>
            <label for="relockReason" class="mt-4 block text-sm font-medium text-gray-700"><?= e(__('panel.shell.undo_reason_label')) ?></label>
            <input id="relockReason" name="reason" required maxlength="300" placeholder="<?= e(__('panel.shell.undo_reason_placeholder')) ?>" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-900">
            <div class="mt-5 flex justify-end gap-2">
              <button type="button" onclick="document.getElementById('relockDialog').close()" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><?= e(__('panel.shell.cancel')) ?></button>
              <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"><?= e(__('panel.shell.relock')) ?></button>
            </div>
          </form>
        </dialog>
      <?php endif; ?>

      <?php if ($tab === 'account'): ?>
        <p class="mb-4 text-xs text-gray-500"><?= e(str_replace('{n}', (string)$undoCountMonth, __('panel.shell.undo_count'))) ?> · <?= e(__('panel.shell.final_note')) ?></p>
      <!-- Revenue & Payment Overview Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-4 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-[11px] uppercase tracking-wider text-white/40"><?= e(__('panel.metrics.today_sold')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-emerald-400"><?= number_format($todayRevenue, 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($currency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(__('panel.metrics.today_sold')) ?>:</span>
            <span class="font-bold text-white"><?= (int)$stats['today_sold_count'] ?> <?= e(__('panel.metrics.matches_unit')) ?></span>
          </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-4 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-[11px] uppercase tracking-wider text-white/40"><?= e(__('panel.metrics.this_week_sold')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-sky-400"><?= number_format($thisWeekRevenue, 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($currency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(__('panel.metrics.this_week_sold')) ?>:</span>
            <span class="font-bold text-white"><?= (int)$thisWeekCount ?> <?= e(__('panel.metrics.matches_unit')) ?></span>
          </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-4 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-[11px] uppercase tracking-wider text-white/40"><?= e(__('panel.metrics.this_month_sold')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-accent"><?= number_format($thisMonthRevenue, 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($currency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(__('panel.metrics.this_month_sold')) ?>:</span>
            <span class="font-bold text-white"><?= (int)$thisMonthCount ?> <?= e(__('panel.metrics.matches_unit')) ?></span>
          </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-4 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-[11px] uppercase tracking-wider text-white/40"><?= e(__('panel.metrics.total_revenue')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-white"><?= number_format($totalRevenue, 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-accent"><?= e($currency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(__('common.sold')) ?>:</span>
            <span class="font-bold text-white"><?= (int)$stats['sold_count'] ?> <?= e(__('panel.metrics.matches_unit')) ?></span>
          </div>
        </div>
      </div>

      <!-- Monthly Billing Statement (includes/billing.php — Billing Guide rules) -->
      <?php
        $ms = $monthStatement;
        $nextTier = null;
        foreach (replash_share_tiers() as $t) { if ($t['min'] > $ms['valid_sales']) { $nextTier = $t; break; } }
      ?>
      <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-6 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 mb-5 pb-4 border-b border-white/10">
          <div>
            <h2 class="font-display text-xl font-bold text-white"><?= e(__('panel.billing.title')) ?> · <?= e(($activeMonthNames[substr($ms['month'], 5, 2)] ?? '') . ' ' . substr($ms['month'], 0, 4)) ?></h2>
            <p class="text-xs text-neutral-400 mt-0.5"><?= e(__('panel.billing.subtitle')) ?></p>
          </div>
          <span class="text-[11px] text-neutral-400">
            <?php if ($nextTier): ?>
              <?= e(str_replace(['{n}', '{pct}'], [$nextTier['min'] - $ms['valid_sales'], $nextTier['facility_pct']], __('panel.billing.next_tier'))) ?>
            <?php else: ?>
              <?= e(__('panel.billing.top_tier')) ?>
            <?php endif; ?>
          </span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(__('panel.billing.valid_sales')) ?></span>
            <div class="mt-1 text-2xl font-bold font-display text-white"><?= (int)$ms['valid_sales'] ?></div>
            <div class="text-[11px] text-neutral-500 mt-0.5">
              <?php foreach ($ms['by_sport'] as $sp => $row): ?>
                <span class="mr-2"><?= e(__('panel.billing.sport_' . $sp, ucfirst($sp))) ?> <?= (int)$row['sales'] ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(__('panel.billing.gross')) ?></span>
            <div class="mt-1 text-2xl font-bold font-display text-white"><?= e(replash_money($ms['gross'])) ?></div>
            <div class="text-[11px] text-neutral-500 mt-0.5">
              <?php foreach ($ms['by_sport'] as $sp => $row): ?>
                <span class="mr-2"><?= e(__('panel.billing.sport_' . $sp, ucfirst($sp))) ?> <?= e(replash_money($row['gross'], 0)) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(__('panel.billing.tier')) ?></span>
            <div class="mt-1 text-2xl font-bold font-display text-accent">%<?= (int)$ms['facility_pct'] ?> / %<?= (int)$ms['replash_pct'] ?></div>
            <div class="text-[11px] text-neutral-500 mt-0.5"><?= e(__('panel.billing.tier_hint')) ?></div>
          </div>
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(__('panel.billing.facility_share')) ?></span>
            <div class="mt-1 text-2xl font-bold font-display text-emerald-400"><?= e(replash_money($ms['facility_share'])) ?></div>
          </div>
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(__('panel.billing.replash_share')) ?></span>
            <div class="mt-1 text-2xl font-bold font-display text-white"><?= e(replash_money($ms['replash_share'])) ?></div>
          </div>
        </div>

        <div class="mt-5 pt-4 border-t border-white/10 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="flex flex-wrap gap-2 text-[11px]">
            <span class="text-neutral-400 self-center mr-1"><?= e(__('panel.billing.tiers_title')) ?>:</span>
            <?php foreach (replash_share_tiers() as $t): $active = $t['facility_pct'] === $ms['facility_pct']; ?>
              <span class="px-2.5 py-1 rounded-lg border <?= $active ? 'border-accent/50 bg-accent/15 text-accent font-bold' : 'border-white/10 text-neutral-400' ?>">
                <?= (int)$t['min'] ?><?= $t['max'] !== null ? '–' . (int)$t['max'] : '+' ?> → %<?= (int)$t['facility_pct'] ?>
              </span>
            <?php endforeach; ?>
          </div>
          <p class="text-[11px] text-neutral-500 lg:max-w-md"><?= e(__('panel.billing.note')) ?></p>
        </div>
      </div>

      <!-- Interactive Revenue & Payment Analytics Chart Card -->
      <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-6 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-white/10">
          <div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-accent animate-pulse"></div>
              <h2 class="font-display text-xl font-bold text-white"><?= e(__('panel.chart.title')) ?></h2>
            </div>
            <p class="text-xs text-neutral-400 mt-0.5"><?= e(__('panel.table.subtitle')) ?></p>
          </div>

          <!-- Chart Period Tabs -->
          <div class="inline-flex rounded-xl bg-ink-900/90 p-1 border border-white/10 text-xs font-semibold">
            <button 
              type="button" 
              onclick="switchRevenueChart('daily', this)" 
              class="revenue-tab-btn active px-3.5 py-1.5 rounded-lg bg-accent text-ink-900 font-bold shadow-sm transition"
            >
              <?= e(__('panel.chart.daily')) ?>
            </button>
            <button 
              type="button" 
              onclick="switchRevenueChart('weekly', this)" 
              class="revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition"
            >
              <?= e(__('panel.chart.weekly')) ?>
            </button>
            <button 
              type="button" 
              onclick="switchRevenueChart('monthly', this)" 
              class="revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition"
            >
              <?= e(__('panel.chart.monthly')) ?>
            </button>
          </div>
        </div>

        <!-- Chart Canvas Container -->
        <div class="relative w-full h-72 sm:h-80">
          <canvas id="revenueAnalyticsChart"></canvas>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<script>
  // Kodu kopyala
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (btn) {
      var label = btn.textContent;
      var show = function (text) { btn.textContent = text; setTimeout(function () { btn.textContent = label; }, 1800); };
      var ok = function () { show(<?= json_encode(__('panel.shell.copied')) ?>); };
      var fail = function () { show(<?= json_encode(__('panel.shell.copy_failed')) ?>); };
      // "Kopyalandı" yalnız gerçekten kopyalanınca gösterilir
      if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(ok, fail); else fail();
      return;
    }
    // Aynı düğme: satılmış maçı tekrar kilitle (neden sorulur)
    var rl = e.target.closest('[data-relock]');
    if (rl) {
      document.getElementById('relockMatchId').value = rl.getAttribute('data-relock');
      document.getElementById('relockLabel').textContent = rl.getAttribute('data-label');
      document.getElementById('relockReason').value = '';
      document.getElementById('relockDialog').showModal();
    }
  });
</script>

<?php if ($facility && $tab === 'account'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function escapeHtml(text) {
  if (!text) return '';
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function() {
  var currency = <?= json_encode($currency) ?>;
  var labelRevenue = <?= json_encode(__('panel.chart.revenue')) ?>;
  var labelCount = <?= json_encode(__('panel.chart.sold_matches')) ?>;

  var chartData = {
    daily: {
      labels: <?= json_encode($dailyLabels) ?>,
      revenue: <?= json_encode($dailyRevenue) ?>,
      count: <?= json_encode($dailyCount) ?>
    },
    weekly: {
      labels: <?= json_encode($weeklyLabels) ?>,
      revenue: <?= json_encode($weeklyRevenue) ?>,
      count: <?= json_encode($weeklyCount) ?>
    },
    monthly: {
      labels: <?= json_encode($monthlyLabels) ?>,
      revenue: <?= json_encode($monthlyRevenue) ?>,
      count: <?= json_encode($monthlyCount) ?>
    }
  };

  var canvas = document.getElementById('revenueAnalyticsChart');
  if (canvas) {
    var revenueChart = new Chart(canvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: chartData.daily.labels,
        datasets: [
          {
            label: labelRevenue + ' (' + currency + ')',
            data: chartData.daily.revenue,
            backgroundColor: 'rgba(250, 204, 21, 0.75)',
            hoverBackgroundColor: 'rgba(250, 204, 21, 1)',
            borderRadius: 8,
            borderSkipped: false,
            yAxisID: 'y'
          },
          {
            label: labelCount,
            data: chartData.daily.count,
            type: 'line',
            borderColor: '#10B981',
            backgroundColor: ''#111827'',
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#10B981',
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              color: '#A3A3A3',
              font: { size: 11, family: 'Inter, sans-serif', weight: '600' },
              boxWidth: 12,
              usePointStyle: true,
              pointStyle: 'circle'
            }
          },
          tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.95)',
            titleColor: '#FFFFFF',
            bodyColor: '#E5E7EB',
            borderColor: 'rgba(255, 255, 255, 0.15)',
            borderWidth: 1,
            padding: 12,
            boxPadding: 6,
            usePointStyle: true,
            callbacks: {
              label: function(context) {
                if (context.dataset.yAxisID === 'y') {
                  return ' ' + context.dataset.label + ': ' + Number(context.raw).toLocaleString() + ' ' + currency;
                }
                return ' ' + context.dataset.label + ': ' + context.raw;
              }
            }
          }
        },
        scales: {
          x: {
            grid: { color: '#eef0f3', drawBorder: false },
            ticks: { color: '#9CA3AF', font: { size: 10 } }
          },
          y: {
            type: 'linear',
            position: 'left',
            grid: { color: '#eef0f3', drawBorder: false },
            ticks: {
              color: '#FACC15',
              font: { size: 10 },
              callback: function(val) { return val + ' ' + currency; }
            }
          },
          y1: {
            type: 'linear',
            position: 'right',
            grid: { drawOnChartArea: false },
            ticks: {
              color: '#10B981',
              font: { size: 10 },
              stepSize: 1,
              precision: 0,
              callback: function(val) { return val; }
            }
          }
        }
      }
    });

    window.switchRevenueChart = function(period, btn) {
      document.querySelectorAll('.revenue-tab-btn').forEach(function(b) {
        b.className = 'revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition';
      });
      btn.className = 'revenue-tab-btn active px-3.5 py-1.5 rounded-lg bg-accent text-ink-900 font-bold shadow-sm transition';

      var targetData = chartData[period];
      if (targetData && revenueChart) {
        revenueChart.data.labels = targetData.labels;
        revenueChart.data.datasets[0].data = targetData.revenue;
        revenueChart.data.datasets[1].data = targetData.count;
        revenueChart.update();
      }
    };
  }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
