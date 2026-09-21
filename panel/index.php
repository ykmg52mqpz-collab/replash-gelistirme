<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = __('panel.header.badge');
$error = null;
$success = null;

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['facility_id']);
    header('Location: index.php');
    exit;
}

// Handle PIN Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pin'])) {
    $pin = trim($_POST['pin'] ?? '');
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE pin = ? LIMIT 1");
        $stmt->execute([$pin]);
        $fac = $stmt->fetch();
        if ($fac) {
            $_SESSION['facility_id'] = $fac['id'];
            header('Location: index.php');
            exit;
        } else {
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

            if ($mRow) {
                // Ensure playback URL is current
                $camStmt = $pdo->prepare("SELECT stream_url FROM cameras WHERE id = ? OR (facility_id = ? AND court = ?) LIMIT 1");
                $camStmt->execute([$mRow['camera_id'] ?? 0, $facility['id'], $mRow['court']]);
                $camStream = $camStmt->fetchColumn() ?: '';
                $playbackUrl = buildMatchPlaybackUrl($camStream, $mRow['date'], $mRow['time_slot'], (int)($mRow['duration_sec'] ?? 3600));

                $upd = $pdo->prepare("UPDATE matches SET status = 'UNLOCKABLE', playback_url = ?, sold_at = NOW(), billable = 1 WHERE id = ?");
                $upd->execute([$playbackUrl, $matchId]);
                $success = "{$mRow['court']} ({$mRow['time_slot']}) " . __('panel.table.badge_sold') . "! " . __('find.status.verified_code') . " <strong class='font-mono text-accent text-lg tracking-widest bg-black/50 px-3 py-1 rounded-xl border border-accent/40 select-all'>{$mRow['access_code']}</strong>";
            }
        }

        // Handle Re-locking Action (Locking an Unlocked Slot / Revoking Access)
        if (isset($_POST['action']) && $_POST['action'] === 'mark_locked') {
            $matchId = $_POST['match_id'] ?? '';
            $stmtFind = $pdo->prepare("SELECT * FROM matches WHERE id = ? AND facility_id = ? LIMIT 1");
            $stmtFind->execute([$matchId, $facility['id']]);
            $mRow = $stmtFind->fetch();

            if ($mRow) {
                $upd = $pdo->prepare("UPDATE matches SET status = 'LOCKED', sold_at = NULL, billable = 0 WHERE id = ?");
                $upd->execute([$matchId]);
                $success = "{$mRow['court']} ({$mRow['time_slot']}) " . __('panel.table.badge_locked') . "!";
            }
        }

        // Handle Bulk Day Slots Generation (Pre-generating LOCKED slots for a day)
        if (isset($_POST['action']) && $_POST['action'] === 'generate_day_slots') {
            $court = trim($_POST['court'] ?? '');
            $date = trim($_POST['date'] ?? date('Y-m-d'));
            
            $standardSlots = [
                '16:00–17:00',
                '17:00–18:00',
                '18:00–19:00',
                '19:00–20:00',
                '20:00–21:00',
                '21:00–22:00',
                '22:00–23:00',
                '23:00–24:00'
            ];

            // Courts to generate for
            $courtsToGen = [];
            if ($court === 'all') {
                foreach ($facilityCameras as $fc) {
                    $courtsToGen[] = $fc['court'];
                }
            } elseif (!empty($court)) {
                $courtsToGen[] = $court;
            }

            $addedCount = 0;
            foreach ($courtsToGen as $cName) {
                // Find camera for this court
                $camStmt = $pdo->prepare("SELECT * FROM cameras WHERE facility_id = ? AND court = ? LIMIT 1");
                $camStmt->execute([$facility['id'], $cName]);
                $cam = $camStmt->fetch();
                $camStream = $cam['stream_url'] ?? '';
                $camId = $cam['id'] ?? null;

                foreach ($standardSlots as $slot) {
                    // Check if already exists
                    $checkStmt = $pdo->prepare("SELECT id FROM matches WHERE facility_id = ? AND court = ? AND date = ? AND time_slot = ? LIMIT 1");
                    $checkStmt->execute([$facility['id'], $cName, $date, $slot]);
                    if (!$checkStmt->fetch()) {
                        $slotStart = explode('–', $slot)[0];
                        $slotStartCompact = str_replace(':', '', $slotStart);
                        $dateCompact = str_replace('-', '', $date);
                        $matchCode = "MATCH-{$dateCompact}-{$slotStartCompact}";
                        $accessCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4) . '-' . substr(bin2hex(random_bytes(4)), 0, 4));
                        $matchId = 'm-' . bin2hex(random_bytes(5));
                        $playbackUrl = buildMatchPlaybackUrl($camStream, $date, $slot, 3600);

                        $ins = $pdo->prepare("INSERT INTO matches 
                            (id, code, access_code, camera_id, facility_id, court, date, time_slot, status, playback_url, playback_type, thumbnail_url, duration_sec, sold_at, views, billable, sport, recorded_at, available_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'LOCKED', ?, 'mp4', 'assets/images/gen_hero_main.jpg', 3600, NULL, 0, 0, ?, NOW(), NOW())");
                        $ins->execute([
                            $matchId,
                            $matchCode,
                            $accessCode,
                            $camId,
                            $facility['id'],
                            $cName,
                            $date,
                            $slot,
                            $playbackUrl,
                            $facility['sport']
                        ]);
                        $addedCount++;
                    }
                }
            }

            $success = "{$date}: <strong>{$addedCount}</strong> " . __('panel.table.badge_locked') . "!";
        }

        // Handle Manual Custom Slot Creation
        if (isset($_POST['action']) && $_POST['action'] === 'create_and_sell') {
            $court = trim($_POST['court'] ?? '');
            $date = trim($_POST['date'] ?? date('Y-m-d'));
            $slot = trim($_POST['time_slot'] ?? '20:00–21:00');
            $initialStatus = ($_POST['status'] ?? 'UNLOCKABLE') === 'LOCKED' ? 'LOCKED' : 'UNLOCKABLE';

            if (!empty($court) && !empty($date) && !empty($slot)) {
                // Find camera for this court
                $camStmt = $pdo->prepare("SELECT * FROM cameras WHERE facility_id = ? AND court = ? LIMIT 1");
                $camStmt->execute([$facility['id'], $court]);
                $cam = $camStmt->fetch();

                $camStream = $cam['stream_url'] ?? '';
                $camId = $cam['id'] ?? null;
                $playbackUrl = buildMatchPlaybackUrl($camStream, $date, $slot, 3600);

                // Check if match already exists for this court, date & slot
                $checkStmt = $pdo->prepare("SELECT * FROM matches WHERE facility_id = ? AND court = ? AND date = ? AND time_slot = ? LIMIT 1");
                $checkStmt->execute([$facility['id'], $court, $date, $slot]);
                $existing = $checkStmt->fetch();

                if ($existing) {
                    $soldAt = ($initialStatus === 'UNLOCKABLE') ? 'NOW()' : 'NULL';
                    $billable = ($initialStatus === 'UNLOCKABLE') ? 1 : 0;
                    $upd = $pdo->prepare("UPDATE matches SET status = ?, playback_url = ?, sold_at = " . ($initialStatus === 'UNLOCKABLE' ? 'NOW()' : 'sold_at') . ", billable = ? WHERE id = ?");
                    $upd->execute([$initialStatus, $playbackUrl, $billable, $existing['id']]);
                    if ($initialStatus === 'UNLOCKABLE') {
                        $success = "{$court} ({$date} · {$slot}): " . __('panel.table.badge_sold') . "! " . __('find.status.verified_code') . " <strong class='font-mono text-accent text-lg tracking-widest bg-black/50 px-3 py-1 rounded-xl border border-accent/40 select-all'>{$existing['access_code']}</strong>";
                    } else {
                        $success = "{$court} ({$date} · {$slot}): " . __('panel.table.badge_locked') . "!";
                    }
                } else {
                    $slotStart = explode('–', $slot)[0];
                    $slotStartCompact = str_replace(':', '', $slotStart);
                    $dateCompact = str_replace('-', '', $date);
                    $matchCode = "MATCH-{$dateCompact}-{$slotStartCompact}";
                    $accessCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4) . '-' . substr(bin2hex(random_bytes(4)), 0, 4));
                    $matchId = 'm-' . bin2hex(random_bytes(5));
                    $soldAtVal = ($initialStatus === 'UNLOCKABLE') ? date('Y-m-d H:i:s') : null;
                    $billableVal = ($initialStatus === 'UNLOCKABLE') ? 1 : 0;

                    $insMatch = $pdo->prepare("INSERT INTO matches 
                        (id, code, access_code, camera_id, facility_id, court, date, time_slot, status, playback_url, playback_type, thumbnail_url, duration_sec, sold_at, views, billable, sport, recorded_at, available_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'mp4', 'assets/images/gen_hero_main.jpg', 3600, ?, 0, ?, ?, NOW(), NOW())");
                    
                    $insMatch->execute([
                        $matchId,
                        $matchCode,
                        $accessCode,
                        $camId,
                        $facility['id'],
                        $court,
                        $date,
                        $slot,
                        $initialStatus,
                        $playbackUrl,
                        $soldAtVal,
                        $billableVal,
                        $facility['sport']
                    ]);

                    if ($initialStatus === 'UNLOCKABLE') {
                        $success = "{$court} ({$date} · {$slot}): " . __('panel.table.badge_sold') . "! " . __('find.status.verified_code') . " <strong class='font-mono text-accent text-lg tracking-widest bg-black/50 px-3 py-1 rounded-xl border border-accent/40 select-all'>{$accessCode}</strong>";
                    } else {
                        $success = "{$court} ({$date} · {$slot}): " . __('panel.table.badge_locked') . "!";
                    }
                }
            }
        }

        // Fetch statistics for this facility
        // Billing rules (includes/billing.php): valid sales only, standard price per sport
        $validSql = replash_valid_sale_sql('m');
        $priceSql = replash_sale_price_sql('m', number_format((float)($facility['unit_price'] ?? 0), 2, '.', ''));
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

        // Fetch recent matches
        $stmtM = $pdo->prepare("SELECT * FROM matches WHERE facility_id = ? ORDER BY date DESC, time_slot DESC LIMIT 50");
        $stmtM->execute([$facility['id']]);
        $matches = $stmtM->fetchAll();

        // 5. Weekly Calendar Grid Calculation
        $weekOffset = isset($_GET['week_offset']) ? (int)$_GET['week_offset'] : 0;
        $mondayTimestamp = strtotime("monday this week {$weekOffset} weeks");
        $calendarDays = [];
        $dayNamesShort = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'];
        $langCode = $current_lang ?? 'tr';
        if ($langCode === 'en') {
            $dayNamesShort = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        } elseif ($langCode === 'it') {
            $dayNamesShort = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
        } elseif ($langCode === 'es') {
            $dayNamesShort = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        }

        for ($d = 0; $d < 7; $d++) {
            $dayTs = strtotime("+{$d} days", $mondayTimestamp);
            $calendarDays[] = [
                'date' => date('Y-m-d', $dayTs),
                'day_name' => $dayNamesShort[$d],
                'display_date' => date('d.m', $dayTs),
                'is_today' => (date('Y-m-d', $dayTs) === date('Y-m-d'))
            ];
        }

        $calStartDate = $calendarDays[0]['date'];
        $calEndDate = $calendarDays[6]['date'];

        $stmtCal = $pdo->prepare("SELECT id, court, date, time_slot, status, access_code FROM matches WHERE facility_id = ? AND date BETWEEN ? AND ?");
        $stmtCal->execute([$facility['id'], $calStartDate, $calEndDate]);
        $calMatchesRaw = $stmtCal->fetchAll(PDO::FETCH_ASSOC);

        // Group matches by date and hour prefix (e.g. 19 for 19:00–20:00)
        $calendarMatchMap = [];
        foreach ($calMatchesRaw as $cm) {
            $dateKey = $cm['date'];
            // Normalize time_slot e.g. "19:00–20:00" -> hour 19
            if (preg_match('/^(\d{1,2}):\d{2}/', $cm['time_slot'], $mHour)) {
                $hourInt = (int)$mHour[1];
                $calendarMatchMap[$dateKey][$hourInt][] = $cm;
            }
        }
    }
}

$internalShell = __('nav.panel');
require_once __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen pb-10 relative">

  <div class="container-x py-10 relative z-10">

    <?php if (!$facility): ?>
      <!-- PIN giriş kartı (replash.info PanelApp) -->
      <div class="grid min-h-[60vh] place-items-center">
        <form action="index.php" method="POST" class="w-full max-w-sm rounded-3xl border border-white/10 bg-white/[0.03] p-8">
          <input type="hidden" name="login_pin" value="1">
          <h1 class="font-display text-xl font-bold"><?= e(__('panel.shell.login_title')) ?></h1>
          <p class="mb-5 mt-1 text-sm text-white/50"><?= e(__('panel.shell.login_sub')) ?></p>
          <input type="password" name="pin" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="off" required autofocus placeholder="••••••"
            class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 text-center font-mono text-2xl font-bold tracking-[0.5em] text-white outline-none focus:border-accent">
          <?php if ($error): ?>
            <div class="mt-3 rounded-xl border border-red-400/30 bg-red-400/10 p-3 text-sm text-red-200"><?= e($error) ?></div>
          <?php endif; ?>
          <button type="submit" class="mt-4 w-full rounded-xl bg-accent px-6 py-3 text-sm font-bold text-ink-900"><?= e(__('panel.shell.enter')) ?></button>
        </form>
      </div>

    <?php else: ?>
      <!-- Logged in Facility Dashboard -->
      <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
          <div class="text-xs font-bold uppercase tracking-widest text-accent"><?= e($facility['name']) ?></div>
          <h1 class="font-display text-2xl font-bold"><?= e(__('panel.shell.title')) ?></h1>
          <p class="text-xs text-white/40 mt-1"><?= e($facility['city']) ?> · <?= strtoupper(e($facility['sport'])) ?> · <?= e(__('panel.header.price_info')) ?> <?php
            $priceParts = [];
            foreach (get_facility_courts_data($facility) as $pc) { $priceParts[$pc['sport']] = __('panel.billing.sport_' . $pc['sport'], ucfirst($pc['sport'])) . ' ' . replash_money($pc['price'], 0); }
            echo e(implode(' · ', $priceParts));
          ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <button onclick="document.getElementById('manualSaleModal').classList.toggle('hidden')" class="px-3.5 py-2 rounded-xl bg-accent text-ink-900 font-bold text-xs shadow-glow hover:bg-accent-neon transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <?= e(__('panel.header.add_slot_btn')) ?>
          </button>
          <a href="?action=logout" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-white/70 hover:border-accent hover:text-accent">
            <?= e(__('panel.shell.logout')) ?>
          </a>
        </div>
      </div>

      <?php if ($success): ?>
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-5 text-emerald-300 text-sm font-medium mb-8 shadow-lg flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          </div>
          <div><?= $success ?></div>
        </div>
      <?php endif; ?>

      <!-- 2. Manual Custom Slot Form -->
      <div id="manualSaleModal" class="hidden mb-8 p-6 rounded-3xl bg-ink-800 border border-accent/30 shadow-2xl">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/10">
          <div>
            <h3 class="font-display text-lg font-bold text-white"><?= e(__('panel.modals.single_title')) ?></h3>
            <p class="text-xs text-neutral-400"><?= e(__('panel.table.subtitle')) ?></p>
          </div>
          <button type="button" onclick="document.getElementById('manualSaleModal').classList.add('hidden')" class="text-neutral-400 hover:text-white text-lg font-bold">&times;</button>
        </div>

        <form action="index.php" method="POST" class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end text-xs">
          <input type="hidden" name="action" value="create_and_sell">
          
          <div>
            <label class="block text-neutral-400 mb-1 font-semibold"><?= e(__('panel.modals.court_label')) ?></label>
            <select name="court" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-sm text-white focus:border-accent focus:outline-none">
              <?php foreach ($uniqueCourts as $cName): ?>
                <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-neutral-400 mb-1 font-semibold"><?= e(__('panel.modals.date_label')) ?></label>
            <input type="date" name="date" value="<?= date('Y-m-d') ?>" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-sm text-white focus:border-accent focus:outline-none">
          </div>

          <div>
            <label class="block text-neutral-400 mb-1 font-semibold"><?= e(__('panel.modals.slot_label')) ?></label>
            <select name="time_slot" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-sm text-white focus:border-accent focus:outline-none">
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
            <label class="block text-neutral-400 mb-1 font-semibold"><?= e(__('panel.modals.status_label')) ?></label>
            <select name="status" class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-sm text-white focus:border-accent focus:outline-none">
              <option value="LOCKED">🟡 <?= e(__('panel.modals.status_locked_opt')) ?></option>
              <option value="UNLOCKABLE">🟢 <?= e(__('panel.modals.status_unlocked_opt')) ?></option>
            </select>
          </div>

          <div>
            <button type="submit" class="w-full rounded-xl bg-accent py-2.5 text-xs font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition">
              <?= e(__('panel.modals.submit_save')) ?> &rarr;
            </button>
          </div>
        </form>
      </div>

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

      <!-- Matches & Slots Main Section -->
      <div class="space-y-8">
        
        <!-- 1. Weekly Interactive Calendar (Unrestricted Navigation) -->
        <div class="rounded-2xl border-white/10 bg-white/[0.02] p-5 sm:p-6 border border-white/10 bg-ink-800/90 shadow-2xl">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-white/10">
            <div>
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                <h2 class="font-display text-xl font-bold text-white"><?= e(__('panel.modals.calendar_title')) ?></h2>
              </div>
              <p class="text-xs text-neutral-400 mt-1"><?= e(__('panel.modals.calendar_desc')) ?></p>
            </div>
            
            <!-- Week Switcher Controls -->
            <div class="flex items-center gap-2">
              <a href="?week_offset=<?= $weekOffset - 1 ?>" class="px-3 py-1.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-xs font-semibold text-neutral-300 transition flex items-center gap-1.5">
                &larr; <?= e(__('panel.modals.cal_prev_week')) ?>
              </a>
              <?php if ($weekOffset !== 0): ?>
                <a href="index.php" class="px-3 py-1.5 rounded-xl border border-cyan-500/40 bg-cyan-500/15 text-cyan-300 hover:bg-cyan-500/25 text-xs font-bold transition">
                  <?= e(__('panel.modals.cal_today')) ?>
                </a>
              <?php endif; ?>
              <a href="?week_offset=<?= $weekOffset + 1 ?>" class="px-3 py-1.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-xs font-semibold text-neutral-300 transition flex items-center gap-1.5">
                <?= e(__('panel.modals.cal_next_week')) ?> &rarr;
              </a>
            </div>
          </div>

          <!-- 24-Hour Calendar Table -->
          <div class="overflow-x-auto border border-white/10 rounded-2xl bg-ink-950/70">
            <table class="w-full text-left border-collapse min-w-[750px]">
              <thead>
                <tr class="border-b border-white/10 bg-white/[0.03]">
                  <th class="px-4 py-3 text-center text-[11px] font-bold text-neutral-400 uppercase tracking-wider w-16 border-r border-white/10">
                    <?= e(__('panel.modals.cal_time')) ?>
                  </th>
                  <?php foreach ($calendarDays as $cDay): ?>
                    <th class="px-4 py-3 text-center border-r border-white/10 last:border-r-0 <?= $cDay['is_today'] ? 'bg-cyan-500/10 text-cyan-300' : 'text-neutral-300' ?>">
                      <div class="text-[11px] font-semibold uppercase tracking-wider"><?= e($cDay['day_name']) ?></div>
                      <div class="text-xs font-bold <?= $cDay['is_today'] ? 'text-cyan-400 font-mono' : 'text-neutral-400' ?>"><?= e($cDay['display_date']) ?></div>
                    </th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5 text-xs">
                <?php for ($hour = 0; $hour < 24; $hour++): 
                  $startHourStr = sprintf('%02d:00', $hour);
                  $endHourStr = sprintf('%02d:00', ($hour + 1) % 24);
                  $slotFormatted = "{$startHourStr}–{$endHourStr}";
                ?>
                  <tr class="border-b border-white/5 hover:bg-white/[0.02] transition">
                    <td class="p-2 text-center font-mono text-[11px] font-medium text-neutral-400 border-r border-white/10 bg-white/[0.01]">
                      <?= $startHourStr ?>
                    </td>
                    <?php foreach ($calendarDays as $cDay): 
                      $dayDate = $cDay['date'];
                      $cellMatches = $calendarMatchMap[$dayDate][$hour] ?? [];
                      $encodedMatches = htmlspecialchars(json_encode($cellMatches), ENT_QUOTES, 'UTF-8');
                    ?>
                      <td class="p-1 border-r border-white/10 last:border-r-0 align-top relative group">
                        <div class="min-h-[48px] p-1.5 rounded-xl transition cursor-pointer flex flex-col gap-1.5 justify-center items-stretch hover:bg-cyan-500/10 hover:border hover:border-cyan-500/30"
                             onclick='openCalendarSlotPicker("<?= $dayDate ?>", "<?= $slotFormatted ?>", "<?= e($cDay['day_name']) ?>", "<?= e($cDay['display_date']) ?>", <?= $encodedMatches ?>)'>
                          
                          <?php if (!empty($cellMatches)): ?>
                            <?php foreach ($cellMatches as $cm): ?>
                              <?php if ($cm['status'] === 'UNLOCKABLE'): ?>
                                <span class="inline-flex items-center justify-between text-[10px] font-semibold px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm" title="<?= e($cm['court']) ?>: <?= e($cm['access_code']) ?>">
                                  <span class="truncate max-w-[70px]"><?= e($cm['court']) ?></span>
                                  <span class="font-mono text-[9px] ml-1 text-accent font-bold">✓ <?= e($cm['access_code']) ?></span>
                                </span>
                              <?php else: ?>
                                <span class="inline-flex items-center justify-between text-[10px] font-medium px-2 py-1 rounded-lg bg-amber-500/15 text-amber-300 border border-amber-500/30 shadow-sm" title="<?= e($cm['court']) ?> (<?= e(__('panel.table.badge_locked')) ?>)">
                                  <span class="truncate max-w-[70px]"><?= e($cm['court']) ?></span>
                                  <span class="text-[9px] ml-1 text-amber-400 font-bold">🔒 Kilitli</span>
                                </span>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <div class="text-center py-1 text-neutral-600 group-hover:text-cyan-400 transition text-xs font-bold select-none opacity-40 group-hover:opacity-100">
                              +
                            </div>
                          <?php endif; ?>

                        </div>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-neutral-400 px-1">
            <div class="flex items-center gap-4">
              <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> <?= e(__('panel.table.badge_sold')) ?> (Açık & Satıldı)</span>
              <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> <?= e(__('panel.table.badge_locked')) ?> (Kilitli & Ödeme Bekliyor)</span>
              <span class="flex items-center gap-1.5"><span class="text-neutral-500 font-bold">+</span> Boş Slot</span>
            </div>
            <span class="text-neutral-500">Slotlara tıklayarak kilidi açabilir, kodu verebilir veya tekrar kilitleyebilirsiniz.</span>
          </div>
        </div>

        <!-- 2. Recent Matches List Table -->
        <div class="rounded-2xl border-white/10 bg-white/[0.02] overflow-hidden border border-white/10">
          <div class="p-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
              <h2 class="font-display text-xl font-bold text-white"><?= e(__('panel.table.title')) ?></h2>
              <p class="text-xs text-neutral-400"><?= e(__('panel.table.subtitle')) ?></p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
                <tr>
                  <th class="px-4 py-3"><?= e(__('panel.table.th_court')) ?> & Slot</th>
                  <th class="px-4 py-3"><?= e(__('panel.table.th_date')) ?></th>
                  <th class="px-4 py-3"><?= e(__('panel.table.th_status')) ?></th>
                  <th class="px-4 py-3"><?= e(__('panel.table.th_code')) ?></th>
                  <th class="px-4 py-3 text-center"><?= e(__('common.views')) ?></th>
                  <th class="px-4 py-3 text-right"><?= e(__('panel.table.th_video')) ?></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5 text-neutral-300">
                <?php if (empty($matches)): ?>
                  <tr>
                    <td colspan="6" class="p-8 text-center text-xs text-neutral-500">
                      <?= e(__('panel.table.no_records')) ?>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($matches as $m): ?>
                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition">
                      <td class="px-4 py-2.5">
                        <div class="font-semibold text-white"><?= e($m['court']) ?></div>
                        <div class="text-xs text-neutral-400 font-mono"><?= e($m['time_slot']) ?></div>
                      </td>
                      <td class="px-4 py-2.5 text-xs font-mono"><?= formatDateLocalized($m['date'], $current_lang) ?></td>
                      <td class="px-4 py-2.5">
                        <?php if ($m['status'] === 'UNLOCKABLE'): ?>
                          <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2.5 py-0.5 text-[11px] font-bold text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>UNLOCKABLE</span>
                        <?php else: ?>
                          <span class="inline-flex items-center gap-1.5 rounded-full bg-red-400/15 px-2.5 py-0.5 text-[11px] font-bold text-red-300"><span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>LOCKED</span>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-2.5">
                        <?php if ($m['status'] === 'UNLOCKABLE'): ?>
                          <button 
                            type="button" 
                            onclick="navigator.clipboard.writeText('<?= e($m['access_code']) ?>'); alert('<?= e(__('find.status.verified_code')) ?> <?= e($m['access_code']) ?>');"
                            title="<?= e(__('common.copy_link')) ?>"
                            class="inline-flex items-center gap-1.5 font-mono text-xs font-bold tracking-wider text-accent hover:underline cursor-pointer"
                          >
                            <?= e($m['access_code']) ?>
                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                          </button>
                        <?php else: ?>
                          <span class="text-xs text-neutral-500 italic">-</span>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-2.5 text-center">
                        <span class="text-xs font-mono px-2 py-0.5 rounded-md bg-white/5 text-neutral-300">
                          <?= (int)($m['views'] ?? 0) ?>
                        </span>
                      </td>
                      <td class="px-4 py-2.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                          <?php if ($m['status'] === 'LOCKED'): ?>
                            <form action="index.php" method="POST" class="inline-block">
                              <input type="hidden" name="action" value="mark_sold">
                              <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                              <button type="submit" class="rounded-lg bg-accent px-3 py-1.5 text-xs font-bold text-ink-900"><?= e(__('panel.shell.sell')) ?></button>
                            </form>
                          <?php else: ?>
                            <a href="<?= base_url('find.php?code=' . urlencode($m['access_code'])) ?>" target="_blank" class="rounded-lg border border-white/15 bg-white/5 px-2.5 py-1 text-xs text-accent hover:bg-white/10 font-semibold transition">
                              <?= e(__('panel.table.action_watch')) ?> &rarr;
                            </a>
                            <form action="index.php" method="POST" class="inline-block" onsubmit="return confirm(<?= e(json_encode(__('panel.shell.relock_confirm'))) ?>);">
                              <input type="hidden" name="action" value="mark_locked">
                              <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                              <button type="submit" class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-xs text-amber-300 hover:bg-amber-500/20 font-semibold transition">
                                <?= e(__('panel.table.action_relock', '🔒 Tekrar Kilitle')) ?>
                              </button>
                            </form>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Calendar Slot Detail & Actions Popup Modal -->
      <div id="calendarCourtPickerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm hidden p-4">
        <div class="w-full max-w-lg bg-ink-800 border border-cyan-500/40 rounded-3xl p-6 shadow-2xl relative animate-in fade-in zoom-in duration-150">
          <button type="button" onclick="document.getElementById('calendarCourtPickerModal').classList.add('hidden')" class="absolute top-5 right-5 text-neutral-400 hover:text-white text-xl font-bold">&times;</button>
          
          <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
              <h4 class="font-display font-bold text-white text-base"><?= e(__('panel.modals.cal_slot_popup_title')) ?></h4>
              <p id="calModalSlotLabel" class="text-xs text-cyan-300 font-mono mt-0.5"></p>
            </div>
          </div>

          <!-- Existing Matches in this Slot with Instant Unlock / Re-lock -->
          <div id="calExistingMatchesList"></div>

          <!-- New Match Slot Form -->
          <form action="index.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create_and_sell">
            <input type="hidden" name="date" id="calPickerDate" value="">
            <input type="hidden" name="time_slot" id="calPickerSlot" value="">
            <input type="hidden" name="status" value="UNLOCKABLE">

            <div>
              <label class="block text-xs font-semibold text-neutral-300 mb-2">⚡ <?= e(__('panel.modals.court_label')) ?> (Yeni Kayıt / Satış):</label>
              <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                <?php if (!empty($facilityCourts)): ?>
                  <?php foreach ($facilityCourts as $idx => $cItem): 
                    $cName = $cItem['name'];
                    $cPrice = $cItem['price'];
                  ?>
                    <label class="flex items-center justify-between p-3 rounded-xl border border-white/10 bg-ink-900/80 hover:border-cyan-500/50 hover:bg-cyan-500/5 cursor-pointer transition">
                      <div class="flex items-center gap-3">
                        <input type="radio" name="court" value="<?= e($cName) ?>" <?= $idx === 0 ? 'checked' : '' ?> class="text-accent focus:ring-accent">
                        <div>
                          <span class="text-sm font-bold text-white"><?= e($cName) ?></span>
                          <span class="text-[10px] text-neutral-400 block uppercase"><?= e($cItem['sport']) ?></span>
                        </div>
                      </div>
                      <span class="text-xs font-semibold text-accent"><?= e(replash_money((float)$cPrice, 0)) ?></span>
                    </label>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p class="text-xs text-rose-400">Bu tesise tanımlı kort bulunamadı.</p>
                <?php endif; ?>
              </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
              <button type="button" onclick="document.getElementById('calendarCourtPickerModal').classList.add('hidden')" class="w-1/3 py-2.5 rounded-xl border border-white/10 text-xs font-semibold text-neutral-300 hover:bg-white/5 transition">
                <?= e(__('panel.modals.cancel')) ?>
              </button>
              <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-accent text-xs font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?= e(__('panel.modals.cal_confirm_btn')) ?>
              </button>
            </div>
          </form>
        </div>
      </div>

    <?php endif; ?>

  </div>
</main>

<?php if ($facility): ?>
<!-- Chart.js Library for Graphical Analytics -->
<script>var RELOCK_CONFIRM = <?= json_encode(__('panel.shell.relock_confirm')) ?>;</script>
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
            backgroundColor: 'rgba(16, 185, 129, 0.12)',
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
            grid: { color: 'rgba(255, 255, 255, 0.04)', drawBorder: false },
            ticks: { color: '#9CA3AF', font: { size: 10 } }
          },
          y: {
            type: 'linear',
            position: 'left',
            grid: { color: 'rgba(255, 255, 255, 0.06)', drawBorder: false },
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

  window.openCalendarSlotPicker = function(dateStr, slotStr, dayName, displayDate, existingMatches) {
    document.getElementById('calPickerDate').value = dateStr;
    document.getElementById('calPickerSlot').value = slotStr;
    document.getElementById('calModalSlotLabel').textContent = dayName + ' (' + displayDate + ') • ' + slotStr;

    var container = document.getElementById('calExistingMatchesList');
    if (container) {
      container.innerHTML = '';
      if (existingMatches && existingMatches.length > 0) {
        var html = '<div class="mb-4 pb-3 border-b border-white/10 space-y-2">';
        html += '<div class="text-[11px] uppercase tracking-wider font-semibold text-neutral-400 mb-1.5">Bu Saatteki Maçlar / Kortlar:</div>';
        existingMatches.forEach(function(m) {
          var isUnlocked = m.status === 'UNLOCKABLE';
          html += '<div class="p-3 rounded-2xl bg-ink-900 border ' + (isUnlocked ? 'border-emerald-500/40 bg-emerald-500/5' : 'border-amber-500/30 bg-amber-500/5') + ' flex items-center justify-between gap-3">';
          html += '<div>';
          html += '<div class="font-bold text-white text-sm">' + escapeHtml(m.court) + '</div>';
          if (isUnlocked) {
            html += '<div class="text-xs text-emerald-400 flex items-center gap-1.5 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Kod: <strong class="font-mono text-accent select-all bg-black/40 px-2 py-0.5 rounded border border-accent/30">' + escapeHtml(m.access_code) + '</strong></div>';
          } else {
            html += '<div class="text-xs text-amber-400 flex items-center gap-1 mt-0.5">🔒 Kilitli (Ödeme Bekleniyor)</div>';
          }
          html += '</div>';
          
          html += '<div class="flex items-center gap-2">';
          if (isUnlocked) {
            html += '<form action="index.php" method="POST" onsubmit="return confirm(RELOCK_CONFIRM);"><input type="hidden" name="action" value="mark_locked"><input type="hidden" name="match_id" value="' + escapeHtml(m.id) + '"><button type="submit" class="px-3 py-1.5 rounded-xl border border-amber-500/40 bg-amber-500/15 text-amber-300 text-xs font-semibold hover:bg-amber-500/25 transition">🔒 Kilitle</button></form>';
          } else {
            html += '<form action="index.php" method="POST"><input type="hidden" name="action" value="mark_sold"><input type="hidden" name="match_id" value="' + escapeHtml(m.id) + '"><button type="submit" class="px-3 py-1.5 rounded-xl bg-accent text-ink-900 text-xs font-bold shadow-glow hover:bg-accent-neon transition">⚡ Kilidi Aç & Sat</button></form>';
          }
          html += '</div>';
          html += '</div>';
        });
        html += '</div>';
        container.innerHTML = html;
      }
    }

    document.getElementById('calendarCourtPickerModal').classList.remove('hidden');
  };
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
