<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = adm('Platform Admin Paneli', 'Platform Admin');
$error = null;
$success = null;

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    header('Location: ./');
    exit;
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($pdo && replash_attempt_blocked($pdo, 'admin', REPLASH_LOGIN_MAX_FAILS, REPLASH_LOGIN_WINDOW_MIN)) {
        $error = adm('Çok fazla hatalı giriş denemesi. Lütfen ' . REPLASH_LOGIN_WINDOW_MIN . ' dakika sonra tekrar deneyin.',
                       'Too many failed sign-in attempts. Please try again in ' . REPLASH_LOGIN_WINDOW_MIN . ' minutes.');
    } elseif ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $admin['name'];
                header('Location: ./');
                exit;
            } else {
                // Hangi e-postanın kayıtlı olduğu belli edilmez
                replash_attempt_failed($pdo, 'admin');
                $error = adm('E-posta veya şifre hatalı.', 'Wrong email or password.');
            }
        } catch (PDOException $e) {
            error_log('admin login: ' . $e->getMessage());
            $error = adm('Veritabanı hatası. Lütfen tekrar deneyin.', 'Database error. Please try again.');
        }
    } else {
        // Bağlantı ayrıntısı (sunucu/kullanıcı adı) ekrana yazılmaz; sunucu günlüğünde (includes/init.php)
        $error = adm('Veritabanı bağlantısı kurulamadı. Lütfen MySQL ayarlarını kontrol edin.', 'Could not connect to the database. Please check your MySQL settings.');
    }
}

$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

/**
 * Tesis sporları ve spor başına video fiyatı (facilities.sport_prices).
 */
/** Pilot başlangıç tarihi (YYYY-MM-DD) veya boş = pilot yok. */
function admin_pilot_start_from_post(): ?string {
    $v = trim((string)($_POST['pilot_start'] ?? ''));
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
}

function admin_sport_prices_from_post(): array {
    $out = [];
    $enabled = $_POST['sport_enabled'] ?? [];
    $prices = $_POST['sport_price'] ?? [];
    foreach (REPLASH_SPORTS as $key => $label) {
        if (!empty($enabled[$key])) {
            $p = (isset($prices[$key]) && is_numeric($prices[$key])) ? (float)$prices[$key] : (REPLASH_VIDEO_PRICES[$key] ?? 0);
            $out[$key] = round(max(0, $p), 2);
        }
    }
    return $out;
}

/** Sahaları seçili sporlara bağla; sahada kullanılan ama işaretlenmemiş sporu standart fiyatla ekle. */
function admin_apply_sport_prices(array &$courtList, array &$sportPrices): void {
    if (empty($sportPrices)) $sportPrices = ['football' => REPLASH_VIDEO_PRICES['football']];
    foreach ($courtList as &$ci) {
        $sp = strtolower((string)($ci['sport'] ?? ''));
        if (!isset(REPLASH_SPORTS[$sp])) $sp = array_key_first($sportPrices);
        if (!isset($sportPrices[$sp])) $sportPrices[$sp] = REPLASH_VIDEO_PRICES[$sp] ?? 0;
        $ci['sport'] = $sp;
        $ci['price'] = $sportPrices[$sp];
    }
    unset($ci);
}

function admin_sport_price_fields(array $prices, string $uid): void {
    ?>
    <div class="space-y-2">
      <div class="text-xs font-semibold text-white"><?= e(adm('Sporlar ve video fiyatları', 'Sports and video prices')) ?></div>
      <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
        <?php foreach (REPLASH_SPORTS as $key => $label): $on = isset($prices[$key]); $val = $prices[$key] ?? (REPLASH_VIDEO_PRICES[$key] ?? ''); ?>
          <label class="flex items-center gap-3 rounded-lg border border-white/10 px-3 py-2">
            <input type="checkbox" name="sport_enabled[<?= $key ?>]" value="1" <?= $on ? 'checked' : '' ?> class="h-4 w-4">
            <span class="flex-1 text-xs"><?= e($label) ?></span>
            <span class="text-xs text-white/40">€</span>
            <input type="number" step="0.5" min="0" name="sport_price[<?= $key ?>]" value="<?= e($val) ?>" class="w-20 rounded-md border border-white/10 bg-transparent px-2 py-1 text-right font-mono text-xs" aria-label="<?= e($label) ?> fiyatı">
          </label>
        <?php endforeach; ?>
      </div>
      <p class="text-[11px] text-white/40"><?= e(adm('Her saha aşağıda seçtiğiniz spora göre bu fiyatla satılır. Varsayılanlar Billing Guide\'dan gelir (futbol €15, padel €10, tenis €10).', 'Each court is sold at this price for the sport you pick below. Defaults come from the Billing Guide (football €15, padel €10, tennis €10).')) ?></p>
    </div>
    <?php
}


/** Tesis PIN'i: tam 6 rakam, kolay tahmin edilemez ve başka tesiste kullanılmıyor. Hata yoksa null. */
function admin_pin_check(PDO $pdo, string $pin, string $exceptId = ''): ?string {
    if (!preg_match('/^\d{6}$/', $pin)) {
        return adm('PIN tam 6 rakamdan oluşmalı.', 'The PIN must be exactly 6 digits.');
    }
    $d = array_map('intval', str_split($pin));
    $allSame = count(array_unique($d)) === 1;
    $asc = true; $desc = true;
    for ($i = 1; $i < 6; $i++) { if ($d[$i] !== $d[$i - 1] + 1) $asc = false; if ($d[$i] !== $d[$i - 1] - 1) $desc = false; }
    if ($allSame || $asc || $desc) {
        return adm('Bu PIN çok kolay tahmin edilir (ör. 111111, 123456). Başka bir PIN seçin.', 'This PIN is too easy to guess (e.g. 111111, 123456). Choose another.');
    }
    $st = $pdo->prepare("SELECT COUNT(*) FROM facilities WHERE pin = ? AND id <> ?");
    $st->execute([$pin, $exceptId]);
    if ((int)$st->fetchColumn() > 0) {
        return adm('Bu PIN başka bir tesiste kullanılıyor. Başka bir PIN seçin.', 'This PIN is already used by another facility. Choose a different one.');
    }
    return null;
}
$adminFormError = null;

$facilities = [];
$cameras = [];
$leads = [];
$stats = ['total_matches' => 0, 'sold_matches' => 0, 'total_revenue' => 0, 'active_cameras' => 0];

if ($isAdmin && $pdo) {
    // PIN doğrulaması: geçersizse ekleme/güncelleme hiç çalışmaz, üstte hata gösterilir
    if (isset($_POST['action']) && in_array($_POST['action'], ['add_facility', 'update_facility'], true)) {
        $pinExcept = ($_POST['action'] === 'update_facility') ? trim((string)($_POST['facility_id'] ?? '')) : '';
        $adminFormError = admin_pin_check($pdo, trim((string)($_POST['pin'] ?? '')), $pinExcept);
        if ($adminFormError !== null) { unset($_POST['action']); }
    }
    // Add Facility
    if (isset($_POST['action']) && $_POST['action'] === 'add_facility') {
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $sport = $_POST['sport'] ?? 'padel';
        $pin = trim($_POST['pin'] ?? '');
        $unitPrice = (float)($_POST['unit_price'] ?? 6.00);
        $currency = $_POST['currency'] ?? 'EUR';
        
        // Courts and their multiple camera stream URLs
        $courtList = [];
        $courtsInput = $_POST['courts'] ?? [];

        if (!empty($courtsInput) && is_array($courtsInput)) {
            foreach ($courtsInput as $cData) {
                $cName = trim($cData['name'] ?? '');
                $cSport = trim($cData['sport'] ?? $sport);
                $cPrice = (isset($cData['price']) && is_numeric($cData['price'])) ? (float)$cData['price'] : $unitPrice;
                if (!empty($cName)) {
                    $streams = [];
                    if (!empty($cData['cameras']) && is_array($cData['cameras'])) {
                        foreach ($cData['cameras'] as $sUrl) {
                            $sTrimmed = trim($sUrl);
                            if (!empty($sTrimmed)) {
                                $streams[] = $sTrimmed;
                            }
                        }
                    }
                    if (empty($streams)) {
                        $streams = [''];
                    }
                    $courtList[] = [
                        'name' => $cName,
                        'sport' => $cSport,
                        'price' => $cPrice,
                        'cameras' => $streams
                    ];
                }
            }
        }

        // Fallback for flat court_names[] / court_streams[]
        if (empty($courtList) && !empty($_POST['court_names']) && is_array($_POST['court_names'])) {
            foreach ($_POST['court_names'] as $idx => $cName) {
                $trimmed = trim($cName);
                if (!empty($trimmed)) {
                    $courtList[] = [
                        'name' => $trimmed,
                        'sport' => $sport,
                        'price' => $unitPrice,
                        'cameras' => [trim($_POST['court_streams'][$idx] ?? '')]
                    ];
                }
            }
        }

        // Fallback for simple comma separated text if used
        if (empty($courtList) && !empty($_POST['courts']) && is_string($_POST['courts'])) {
            $rawCourts = array_map('trim', explode(',', $_POST['courts']));
            foreach ($rawCourts as $rc) {
                if (!empty($rc)) {
                    $courtList[] = ['name' => $rc, 'sport' => $sport, 'price' => $unitPrice, 'cameras' => ['']];
                }
            }
        }

        if (empty($courtList)) {
            $courtList = [['name' => 'Saha 1', 'sport' => $sport, 'price' => $unitPrice, 'cameras' => ['']]];
        }
        $sportPrices = admin_sport_prices_from_post();
        admin_apply_sport_prices($courtList, $sportPrices);
        $unitPrice = (float)reset($sportPrices);
        $currency = REPLASH_BILLING_CURRENCY;

        $facId = 'fac-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));

        $courtObjects = [];
        $distinctSports = [];
        foreach ($courtList as $cItem) {
            $courtObjects[] = [
                'name' => $cItem['name'],
                'sport' => $cItem['sport'] ?? $sport,
                'price' => (float)($cItem['price'] ?? $unitPrice)
            ];
            if (!empty($cItem['sport']) && !in_array($cItem['sport'], $distinctSports)) {
                $distinctSports[] = $cItem['sport'];
            }
        }
        $primarySport = !empty($distinctSports) ? implode(', ', $distinctSports) : $sport;

        $pilotStart = admin_pilot_start_from_post();
        $ins = $pdo->prepare("INSERT INTO facilities (id, name, slug, city, sport, courts, pin, unit_price, currency, sport_prices, pilot_start) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$facId, $name, $slug, $city, $primarySport, json_encode($courtObjects, JSON_UNESCAPED_UNICODE), $pin, $unitPrice, $currency, json_encode($sportPrices), $pilotStart]);

        // Add cameras for each court
        $insCam = $pdo->prepare("INSERT INTO cameras (id, facility_id, court, name, token, channel_id, stream_url, status, last_seen_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'online', NOW())");
        foreach ($courtList as $cItem) {
            $c = $cItem['name'];
            $cSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $c));
            $camNum = 1;
            $totalCams = count($cItem['cameras']);
            foreach ($cItem['cameras'] as $cStream) {
                $camId = ($camNum === 1) ? "cam-{$slug}-{$cSlug}" : "cam-{$slug}-{$cSlug}-{$camNum}";
                $channelId = ($camNum === 1) ? "{$slug}-{$cSlug}" : "{$slug}-{$cSlug}-{$camNum}";
                $token = 'camk_' . bin2hex(random_bytes(8));
                $camName = ($totalCams > 1) ? "{$name} · {$c} · {$camNum}. Kamera" : "{$name} · {$c}";
                $insCam->execute([$camId, $facId, $c, $camName, $token, $channelId, $cStream]);
                $camNum++;
            }
        }

        $success = adm('Yeni tesis ve sahalara özel canlı yayın linkleri kaydedildi.', 'New facility and its court stream links saved.');
    }

    // Update Camera Stream URL
    if (isset($_POST['action']) && $_POST['action'] === 'update_camera_stream') {
        $camId = trim($_POST['camera_id'] ?? '');
        $newStream = trim($_POST['stream_url'] ?? '');
        if (!empty($camId)) {
            $updCam = $pdo->prepare("UPDATE cameras SET stream_url = ? WHERE id = ?");
            $updCam->execute([$newStream, $camId]);
            $success = adm('Kamera canlı yayın adresi güncellendi.', 'Camera stream URL updated.');
        }
    }

    // Update Facility
    if (isset($_POST['action']) && $_POST['action'] === 'update_facility') {
        $facId = trim($_POST['facility_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $sport = $_POST['sport'] ?? 'padel';
        $pin = trim($_POST['pin'] ?? '');
        $unitPrice = (float)($_POST['unit_price'] ?? 6.00);
        $currency = $_POST['currency'] ?? 'EUR';
        
        $courtList = [];
        $courtsInput = $_POST['courts'] ?? [];

        if (!empty($courtsInput) && is_array($courtsInput)) {
            foreach ($courtsInput as $cData) {
                $cName = trim($cData['name'] ?? '');
                $cSport = trim($cData['sport'] ?? $sport);
                $cPrice = (isset($cData['price']) && is_numeric($cData['price'])) ? (float)$cData['price'] : $unitPrice;
                if (!empty($cName)) {
                    $streams = [];
                    if (!empty($cData['cameras']) && is_array($cData['cameras'])) {
                        foreach ($cData['cameras'] as $sUrl) {
                            $sTrimmed = trim($sUrl);
                            if (!empty($sTrimmed)) {
                                $streams[] = $sTrimmed;
                            }
                        }
                    }
                    if (empty($streams)) {
                        $streams = [''];
                    }
                    $courtList[] = [
                        'name' => $cName,
                        'sport' => $cSport,
                        'price' => $cPrice,
                        'cameras' => $streams
                    ];
                }
            }
        }

        // Fallback for flat court_names[] / court_streams[]
        if (empty($courtList) && !empty($_POST['court_names']) && is_array($_POST['court_names'])) {
            foreach ($_POST['court_names'] as $idx => $cName) {
                $trimmed = trim($cName);
                if (!empty($trimmed)) {
                    $courtList[] = [
                        'name' => $trimmed,
                        'sport' => $sport,
                        'price' => $unitPrice,
                        'cameras' => [trim($_POST['court_streams'][$idx] ?? '')]
                    ];
                }
            }
        }

        if (empty($courtList) && !empty($_POST['courts']) && is_string($_POST['courts'])) {
            $rawCourts = array_map('trim', explode(',', $_POST['courts']));
            foreach ($rawCourts as $rc) {
                if (!empty($rc)) {
                    $courtList[] = ['name' => $rc, 'sport' => $sport, 'price' => $unitPrice, 'cameras' => ['']];
                }
            }
        }

        if (empty($courtList)) {
            $courtList = [['name' => 'Saha 1', 'sport' => $sport, 'price' => $unitPrice, 'cameras' => ['']]];
        }
        $sportPrices = admin_sport_prices_from_post();
        admin_apply_sport_prices($courtList, $sportPrices);
        $unitPrice = (float)reset($sportPrices);
        $currency = REPLASH_BILLING_CURRENCY;

        if (!empty($facId) && !empty($name) && !empty($pin)) {
            // Kamera kimlikleri tesis adı değişse de sabit kalsın (kayıt sistemi channel_id ile bağlanıyor)
            $slugStmt = $pdo->prepare("SELECT slug FROM facilities WHERE id = ?");
            $slugStmt->execute([$facId]);
            $slug = $slugStmt->fetchColumn() ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            // Mevcut kameralar saha + sıra ile eşleşir; kimlik, kanal ve anahtar korunur
            $oldCams = [];
            $tokStmt = $pdo->prepare("SELECT id, court, channel_id, token FROM cameras WHERE facility_id = ? ORDER BY id");
            $tokStmt->execute([$facId]);
            foreach ($tokStmt->fetchAll() as $ot) { $oldCams[$ot['court']][] = $ot; }

            $courtObjects = [];
            $distinctSports = [];
            foreach ($courtList as $cItem) {
                $courtObjects[] = [
                    'name' => $cItem['name'],
                    'sport' => $cItem['sport'] ?? $sport,
                    'price' => (float)($cItem['price'] ?? $unitPrice)
                ];
                if (!empty($cItem['sport']) && !in_array($cItem['sport'], $distinctSports)) {
                    $distinctSports[] = $cItem['sport'];
                }
            }
            $primarySport = !empty($distinctSports) ? implode(', ', $distinctSports) : $sport;

            $pilotStart = admin_pilot_start_from_post();
            $upd = $pdo->prepare("UPDATE facilities SET name = ?, city = ?, sport = ?, pin = ?, unit_price = ?, currency = ?, courts = ?, sport_prices = ?, pilot_start = ? WHERE id = ?");
            $upd->execute([$name, $city, $primarySport, $pin, $unitPrice, $currency, json_encode($courtObjects, JSON_UNESCAPED_UNICODE), json_encode($sportPrices), $pilotStart, $facId]);

            // Sync cameras
            $pdo->prepare("DELETE FROM cameras WHERE facility_id = ?")->execute([$facId]);

            $insCam = $pdo->prepare("INSERT INTO cameras (id, facility_id, court, name, token, channel_id, stream_url, status, last_seen_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'online', NOW())");
            foreach ($courtList as $cItem) {
                $c = $cItem['name'];
                $cSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $c));
                $camNum = 1;
                $totalCams = count($cItem['cameras']);
                foreach ($cItem['cameras'] as $cStream) {
                    $prev = $oldCams[$c][$camNum - 1] ?? null;
                    $camId = $prev['id'] ?? (($camNum === 1) ? "cam-{$slug}-{$cSlug}" : "cam-{$slug}-{$cSlug}-{$camNum}");
                    $channelId = $prev['channel_id'] ?? (($camNum === 1) ? "{$slug}-{$cSlug}" : "{$slug}-{$cSlug}-{$camNum}");
                    $token = $prev['token'] ?? ('camk_' . bin2hex(random_bytes(8)));
                    $camName = ($totalCams > 1) ? "{$name} · {$c} · {$camNum}. Kamera" : "{$name} · {$c}";
                    $insCam->execute([$camId, $facId, $c, $camName, $token, $channelId, $cStream]);
                    $camNum++;
                }
            }

            $success = adm('Tesis ve saha kamera adresleri güncellendi.', 'Facility and court camera URLs updated.');
        }
    }

    // Delete Facility
    if (isset($_POST['action']) && $_POST['action'] === 'delete_facility') {
        $facId = trim($_POST['facility_id'] ?? '');
        if (!empty($facId)) {
            $pdo->prepare("DELETE FROM matches WHERE facility_id = ?")->execute([$facId]);
            $pdo->prepare("DELETE FROM cameras WHERE facility_id = ?")->execute([$facId]);
            $pdo->prepare("DELETE FROM refunds WHERE facility_id = ?")->execute([$facId]);
            $pdo->prepare("DELETE FROM facilities WHERE id = ?")->execute([$facId]);
            $success = adm('Tesis, bağlı kameraları ve tüm kayıtları silindi.', 'Facility, its cameras and all its records were deleted.');
        }
    }

    // Update Search Form Display Settings
    if (isset($_POST['action']) && $_POST['action'] === 'update_search_form_settings') {
        $searchFacility = isset($_POST['search_by_facility_enabled']) ? '1' : '0';
        $searchCode = isset($_POST['search_by_code_enabled']) ? '1' : '0';

        // Graceful validation: ensure at least one search method is active
        if ($searchFacility === '0' && $searchCode === '0') {
            $searchCode = '1';
        }

        set_setting('search_by_facility_enabled', $searchFacility);
        set_setting('search_by_code_enabled', $searchCode);
        $success = adm('Arama formu görünüm ayarları güncellendi.', 'Search form display settings updated.');
    }

    $searchFacilityEnabled = (string)get_setting('search_by_facility_enabled', '1') === '1';
    $searchCodeEnabled = (string)get_setting('search_by_code_enabled', '1') === '1';

    // Stats
    $stats['total_matches'] = (int)$pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
    $stats['sold_matches'] = (int)$pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'UNLOCKABLE'")->fetchColumn();
    $stats['active_cameras'] = (int)$pdo->query("SELECT COUNT(*) FROM cameras WHERE status = 'online'")->fetchColumn();
    
    // Lists
    $facilities = $pdo->query("SELECT * FROM facilities ORDER BY name ASC")->fetchAll();
    $cameras = $pdo->query("SELECT c.*, f.name as facility_name FROM cameras c JOIN facilities f ON c.facility_id = f.id ORDER BY f.name, c.court")->fetchAll();
    $leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 20")->fetchAll();

    $camerasByFacility = [];
    foreach ($cameras as $c) {
        $camerasByFacility[$c['facility_id']][] = $c;
    }

    // Selected Facility Filter for Analytics
    $selectedFacilityId = $_GET['facility_id'] ?? 'all';
    $selectedFacilityName = adm('Tüm Tesisler (Platform Geneli)', 'All facilities (platform-wide)');
    $selectedFacilityCurrency = REPLASH_BILLING_CURRENCY;

    if ($selectedFacilityId !== 'all') {
        foreach ($facilities as $fac) {
            if ($fac['id'] === $selectedFacilityId) {
                $selectedFacilityName = $fac['name'] . ' (' . $fac['city'] . ')';
                break;
            }
        }
    }

    // Billing rules (includes/billing.php): valid sales only, standard price per sport
    $validSql = replash_valid_sale_sql('m');
    $priceSql = replash_sale_price_sql('m', 'f.unit_price');

    // Facility-by-Facility Breakdown
    $facilityBreakdownStmt = $pdo->query("
        SELECT 
            f.id, f.name, f.city, f.sport, f.unit_price, f.currency, f.courts,
            COUNT(m.id) as total_matches,
            SUM(CASE WHEN {$validSql} THEN 1 ELSE 0 END) as sold_matches,
            SUM(CASE WHEN {$validSql} AND DATE(COALESCE(m.sold_at, m.date)) = CURDATE() THEN 1 ELSE 0 END) as today_sold,
            SUM(CASE WHEN {$validSql} AND DATE_FORMAT(COALESCE(m.sold_at, m.date), '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN 1 ELSE 0 END) as month_sold,
            COALESCE(SUM(CASE WHEN {$validSql} THEN {$priceSql} ELSE 0 END), 0) as total_revenue,
            COALESCE(SUM(CASE WHEN {$validSql} AND DATE(COALESCE(m.sold_at, m.date)) = CURDATE() THEN {$priceSql} ELSE 0 END), 0) as today_revenue,
            COALESCE(SUM(CASE WHEN {$validSql} AND DATE_FORMAT(COALESCE(m.sold_at, m.date), '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN {$priceSql} ELSE 0 END), 0) as month_revenue
        FROM facilities f
        LEFT JOIN matches m ON f.id = m.facility_id
        GROUP BY f.id
        ORDER BY total_revenue DESC, f.name ASC
    ");
    $facilityBreakdown = $facilityBreakdownStmt->fetchAll();

    // Chart Time Series Calculation (for selected facility or all)
    // 1. Daily (14 Days)
    $dailyLabels = [];
    $dailyRevenue = [];
    $dailyCount = [];
    $dailyMapRev = [];
    $dailyMapCnt = [];

    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $dailyLabels[] = date('d/m', strtotime($d));
        $dailyMapRev[$d] = 0;
        $dailyMapCnt[$d] = 0;
    }

    $sqlDaily = "SELECT DATE(COALESCE(m.sold_at, m.date)) as s_date, COUNT(*) as cnt, SUM({$priceSql}) as rev 
        FROM matches m 
        JOIN facilities f ON m.facility_id = f.id
        WHERE {$validSql} AND DATE(COALESCE(m.sold_at, m.date)) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
    $paramsDaily = [];
    if ($selectedFacilityId !== 'all') {
        $sqlDaily .= " AND m.facility_id = ?";
        $paramsDaily[] = $selectedFacilityId;
    }
    $sqlDaily .= " GROUP BY s_date";
    $stmtDaily = $pdo->prepare($sqlDaily);
    $stmtDaily->execute($paramsDaily);
    while ($row = $stmtDaily->fetch(PDO::FETCH_ASSOC)) {
        if (isset($dailyMapRev[$row['s_date']])) {
            $dailyMapRev[$row['s_date']] = round((float)$row['rev'], 2);
            $dailyMapCnt[$row['s_date']] = (int)$row['cnt'];
        }
    }
    foreach ($dailyMapRev as $d => $rev) {
        $dailyRevenue[] = $rev;
        $dailyCount[] = $dailyMapCnt[$d];
    }

    // 2. Weekly (8 Weeks)
    $weeklyLabels = [];
    $weeklyCount = [];
    $weeklyRevenue = [];

    for ($w = 7; $w >= 0; $w--) {
        $startOfWeek = date('Y-m-d', strtotime("-{$w} weeks monday this week"));
        $endOfWeek = date('Y-m-d', strtotime("-{$w} weeks sunday this week"));
        $weeklyLabels[] = date('d/m', strtotime($startOfWeek)) . '-' . date('d/m', strtotime($endOfWeek));

        $sqlW = "SELECT COUNT(*) as cnt, COALESCE(SUM({$priceSql}), 0) as rev 
            FROM matches m 
            JOIN facilities f ON m.facility_id = f.id
            WHERE {$validSql} AND DATE(COALESCE(m.sold_at, m.date)) BETWEEN ? AND ?";
        $paramsW = [$startOfWeek, $endOfWeek];
        if ($selectedFacilityId !== 'all') {
            $sqlW .= " AND m.facility_id = ?";
            $paramsW[] = $selectedFacilityId;
        }
        $stmtW = $pdo->prepare($sqlW);
        $stmtW->execute($paramsW);
        $rowW = $stmtW->fetch(PDO::FETCH_ASSOC);
        $weeklyCount[] = (int)($rowW['cnt'] ?? 0);
        $weeklyRevenue[] = round((float)($rowW['rev'] ?? 0), 2);
    }

    // 3. Monthly (6 Months)
    $monthlyLabels = [];
    $monthlyCount = [];
    $monthlyRevenue = [];
    $monthNamesTr = admin_lang() === 'tr'
        ? ['01'=>'Ocak','02'=>'Şubat','03'=>'Mart','04'=>'Nisan','05'=>'Mayıs','06'=>'Haziran','07'=>'Temmuz','08'=>'Ağustos','09'=>'Eylül','10'=>'Ekim','11'=>'Kasım','12'=>'Aralık']
        : ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];

    for ($m = 5; $m >= 0; $m--) {
        $yearMonth = date('Y-m', strtotime("-{$m} months"));
        $mNum = date('m', strtotime($yearMonth . '-01'));
        $monthlyLabels[] = ($monthNamesTr[$mNum] ?? $mNum) . ' ' . date('y', strtotime($yearMonth . '-01'));

        $sqlM = "SELECT COUNT(*) as cnt, COALESCE(SUM({$priceSql}), 0) as rev 
            FROM matches m 
            JOIN facilities f ON m.facility_id = f.id
            WHERE {$validSql} AND DATE_FORMAT(COALESCE(m.sold_at, m.date), '%Y-%m') = ?";
        $paramsM = [$yearMonth];
        if ($selectedFacilityId !== 'all') {
            $sqlM .= " AND m.facility_id = ?";
            $paramsM[] = $selectedFacilityId;
        }
        $stmtM = $pdo->prepare($sqlM);
        $stmtM->execute($paramsM);
        $rowM = $stmtM->fetch(PDO::FETCH_ASSOC);
        $monthlyCount[] = (int)($rowM['cnt'] ?? 0);
        $monthlyRevenue[] = round((float)($rowM['rev'] ?? 0), 2);
    }

    // 4. Totals for selected scope
    $sqlTotals = "SELECT 
        COUNT(*) as total_matches,
        SUM(CASE WHEN {$validSql} THEN 1 ELSE 0 END) as sold_matches,
        SUM(CASE WHEN {$validSql} AND DATE(COALESCE(m.sold_at, m.date)) = CURDATE() THEN 1 ELSE 0 END) as today_sold_count,
        SUM(CASE WHEN {$validSql} AND YEARWEEK(COALESCE(m.sold_at, m.date), 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) as this_week_sold_count,
        SUM(CASE WHEN {$validSql} AND DATE_FORMAT(COALESCE(m.sold_at, m.date), '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN 1 ELSE 0 END) as this_month_sold_count,
        COALESCE(SUM(CASE WHEN {$validSql} THEN {$priceSql} ELSE 0 END), 0) as total_revenue,
        COALESCE(SUM(CASE WHEN {$validSql} AND DATE(COALESCE(m.sold_at, m.date)) = CURDATE() THEN {$priceSql} ELSE 0 END), 0) as today_revenue,
        COALESCE(SUM(CASE WHEN {$validSql} AND YEARWEEK(COALESCE(m.sold_at, m.date), 1) = YEARWEEK(CURDATE(), 1) THEN {$priceSql} ELSE 0 END), 0) as this_week_revenue,
        COALESCE(SUM(CASE WHEN {$validSql} AND DATE_FORMAT(COALESCE(m.sold_at, m.date), '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN {$priceSql} ELSE 0 END), 0) as this_month_revenue
    FROM matches m
    JOIN facilities f ON m.facility_id = f.id";
    $paramsTotals = [];
    if ($selectedFacilityId !== 'all') {
        $sqlTotals .= " WHERE m.facility_id = ?";
        $paramsTotals[] = $selectedFacilityId;
    }
    $stmtTotals = $pdo->prepare($sqlTotals);
    $stmtTotals->execute($paramsTotals);
    $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC) ?: [];
}

// Sekme + satış denetimi verisi (includes/sales.php)
$adminTab = in_array($_GET['tab'] ?? '', ['overview', 'facilities', 'audit', 'leads', 'settings'], true) ? $_GET['tab'] : 'overview';
$auditEvents = [];
$undoByFacility = [];
$acceptances = [];
if ($isAdmin && $pdo) {
    try {
        $auditFacility = $_GET['audit_facility'] ?? '';
        $sqlAudit = "SELECT se.*, f.name AS facility_name, m.court, m.date, m.time_slot, m.access_code, m.first_opened_at
            FROM sale_events se
            LEFT JOIN facilities f ON f.id = se.facility_id
            LEFT JOIN matches m ON m.id = se.match_id";
        $auditParams = [];
        if ($auditFacility !== '') { $sqlAudit .= " WHERE se.facility_id = ?"; $auditParams[] = $auditFacility; }
        $sqlAudit .= " ORDER BY se.id DESC LIMIT 200";
        $stA = $pdo->prepare($sqlAudit); $stA->execute($auditParams);
        $auditEvents = $stA->fetchAll();
        $stU = $pdo->query("SELECT facility_id, SUM(action = 'undone') AS undone, SUM(action = 'sold') AS sold
            FROM sale_events WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') GROUP BY facility_id");
        foreach ($stU->fetchAll() as $u) { $undoByFacility[$u['facility_id']] = $u; }
    } catch (Exception $e) {
        error_log('admin audit: ' . $e->getMessage());
    }
    // Oyuncu onayları (find.php): kod ile izlemeden önce ad + beyan
    try {
        $sqlAcc = "SELECT va.*, f.name AS facility_name, m.court, m.date, m.time_slot
            FROM viewer_acceptances va
            LEFT JOIN facilities f ON f.id = va.facility_id
            LEFT JOIN matches m ON m.id = va.match_id";
        $accParams = [];
        if (($auditFacility ?? '') !== '') { $sqlAcc .= " WHERE va.facility_id = ?"; $accParams[] = $auditFacility; }
        $sqlAcc .= " ORDER BY va.id DESC LIMIT 200";
        $stAcc = $pdo->prepare($sqlAcc); $stAcc->execute($accParams);
        $acceptances = $stAcc->fetchAll();
    } catch (Exception $e) {
        error_log('admin acceptances: ' . $e->getMessage());
    }
}

$shellLangs = ['tr', 'en'];               // yönetim: Türkçe / İngilizce
$internalShell = adm('Yönetim', 'Admin');
require_once __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen pb-10 relative">

  <div class="container-x py-10 relative z-10">

    <?php if ($isAdmin && $adminFormError): ?>
      <div role="alert" class="mb-6 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800"><?= e($adminFormError) ?></div>
    <?php endif; ?>

    <?php if (!$isAdmin): ?>
      <!-- Yönetim girişi (replash.info AdminApp) -->
      <div class="grid min-h-[60vh] place-items-center">
        <form action="./" method="POST" class="w-full max-w-sm rounded-3xl border border-white/10 bg-white/[0.03] p-8">
          <input type="hidden" name="admin_login" value="1">
          <h1 class="font-display text-xl font-bold"><?= e(adm('Platform Admin', 'Platform Admin')) ?></h1>
          <p class="mb-5 mt-1 text-sm text-white/50"><?= e(adm('Replash sistem yöneticisi hesabınızla giriş yapın.', 'Sign in with your Replash system administrator account.')) ?></p>
          <div class="flex flex-col gap-3">
            <input type="email" name="email" autocomplete="username" required placeholder="<?= e(adm('E-posta', 'Email')) ?>" class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm text-white outline-none focus:border-accent">
            <input type="password" name="password" autocomplete="current-password" required placeholder="<?= e(adm('Şifre', 'Password')) ?>" class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm text-white outline-none focus:border-accent">
          </div>
          <?php if (!$pdo): ?>
            <div class="mt-3 rounded-xl border border-red-400/30 bg-red-400/10 p-3 text-sm text-red-200"><?= e(adm('Veritabanına bağlanılamıyor. Sunucu ayarlarını kontrol edin.', 'Cannot reach the database. Check the server settings.')) ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="mt-3 rounded-xl border border-red-400/30 bg-red-400/10 p-3 text-sm text-red-200"><?= e($error) ?></div>
          <?php endif; ?>
          <button type="submit" class="mt-4 w-full rounded-xl bg-accent px-6 py-3 text-sm font-bold text-ink-900"><?= e(adm('Giriş', 'Sign in')) ?></button>
        </form>
      </div>

    <?php else: ?>
      <?php include __DIR__ . '/../includes/admin_nav.php'; ?>

      <?php if ($success): ?>
        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-emerald-300 text-sm font-medium mb-6">
          <?= e($success) ?>
        </div>
      <?php endif; ?>

      <?php if ($adminTab === 'overview'): ?>
      <!-- Facility Filter Toolbar for Analytics -->
      <div class="rounded-2xl bg-white/[0.02] p-4 sm:p-5 border border-white/10 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-semibold uppercase tracking-wider text-accent"><?= e(adm('Finansal Raporlama & Gelir Analizi', 'Financial reporting & revenue analysis')) ?></span>
          <h2 class="font-display text-xl font-bold text-white flex items-center gap-2 mt-0.5">
            <span><?= e($selectedFacilityName) ?></span>
            <?php if ($selectedFacilityId !== 'all'): ?>
              <a href="./" class="text-xs font-normal text-rose-400 hover:underline bg-rose-500/10 px-2 py-0.5 rounded-md border border-rose-500/20"><?= e(adm('Filtreyi Kaldır ×', 'Clear filter ×')) ?></a>
            <?php endif; ?>
          </h2>
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs text-neutral-400 font-semibold whitespace-nowrap"><?= e(adm('Tesis Seçin:', 'Facility:')) ?></label>
          <select 
            onchange="window.location.href = './?facility_id=' + encodeURIComponent(this.value);" 
            class="rounded-xl border border-white/10 bg-ink-900 px-3.5 py-2 text-xs font-medium text-white focus:border-accent focus:outline-none"
          >
            <option value="all" <?= $selectedFacilityId === 'all' ? 'selected' : '' ?>><?= e(adm('Tüm Tesisler (Platform Geneli)', 'All facilities (platform-wide)')) ?></option>
            <?php foreach ($facilities as $fac): ?>
              <option value="<?= e($fac['id']) ?>" <?= $selectedFacilityId === $fac['id'] ? 'selected' : '' ?>>
                <?= e($fac['name']) ?> (<?= e($fac['city']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Financial Metric Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Bugünkü Hasılat', 'Revenue today')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-emerald-400"><?= number_format((float)($totals['today_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(adm('Bugün Satılan:', 'Sold today:')) ?></span>
            <span class="font-bold text-white"><?= (int)($totals['today_sold_count'] ?? 0) ?> <?= e(adm('Maç', 'matches')) ?></span>
          </div>
        </div>

        <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Bu Haftaki Hasılat', 'Revenue this week')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-sky-400"><?= number_format((float)($totals['this_week_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(adm('Bu Hafta Satılan:', 'Sold this week:')) ?></span>
            <span class="font-bold text-white"><?= (int)($totals['this_week_sold_count'] ?? 0) ?> <?= e(adm('Maç', 'matches')) ?></span>
          </div>
        </div>

        <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Bu Ayki Hasılat', 'Revenue this month')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-accent"><?= number_format((float)($totals['this_month_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(adm('Bu Ay Satılan:', 'Sold this month:')) ?></span>
            <span class="font-bold text-white"><?= (int)($totals['this_month_sold_count'] ?? 0) ?> <?= e(adm('Maç', 'matches')) ?></span>
          </div>
        </div>

        <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-xs font-semibold text-neutral-400"><?= e(adm('Toplam Ciro / Ödeme', 'Total revenue')) ?></span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-white"><?= number_format((float)($totals['total_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-accent"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span><?= e(adm('Toplam Satılan:', 'Total sold:')) ?></span>
            <span class="font-bold text-white"><?= (int)($totals['sold_matches'] ?? 0) ?> <?= e(adm('Maç', 'matches')) ?></span>
          </div>
        </div>
      </div>

      <!-- Interactive Graphical Revenue Chart -->
      <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10 mb-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-white/10">
          <div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-accent animate-pulse"></div>
              <h2 class="font-display text-xl font-bold text-white"><?= e(adm('Ödeme & Satış Dağılım Grafiği', 'Revenue & sales chart')) ?></h2>
            </div>
            <p class="text-xs text-neutral-400 mt-0.5"><?= e(adm('Kapsam:', 'Scope:')) ?> <span class="text-accent font-semibold"><?= e($selectedFacilityName) ?></span></p>
          </div>

          <!-- Chart Period Tabs -->
          <div class="inline-flex rounded-xl bg-ink-900/90 p-1 border border-white/10 text-xs font-semibold">
            <button 
              type="button" 
              onclick="switchAdminRevenueChart('daily', this)" 
              class="admin-revenue-tab-btn active px-3.5 py-1.5 rounded-lg bg-accent text-ink-900 font-bold shadow-sm transition"
            >
              <?= e(adm('Günlük (14 Gün)', 'Daily (14 days)')) ?>
            </button>
            <button 
              type="button" 
              onclick="switchAdminRevenueChart('weekly', this)" 
              class="admin-revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition"
            >
              <?= e(adm('Haftalık (8 Hafta)', 'Weekly (8 weeks)')) ?>
            </button>
            <button 
              type="button" 
              onclick="switchAdminRevenueChart('monthly', this)" 
              class="admin-revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition"
            >
              <?= e(adm('Aylık (6 Ay)', 'Monthly (6 months)')) ?>
            </button>
          </div>
        </div>

        <!-- Chart Canvas Container -->
        <div class="relative w-full h-72 sm:h-80">
          <canvas id="adminRevenueAnalyticsChart"></canvas>
        </div>
      </div>

      <!-- Facility Breakdown Performance Table -->
      <div class="rounded-2xl bg-white/[0.02] overflow-hidden border border-white/10 mb-10">
        <div class="p-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <h2 class="font-display text-xl font-bold text-white"><?= e(adm('Tesis Bazlı Gelir & Performans Tablosu', 'Revenue & performance by facility')) ?></h2>
            <p class="text-xs text-neutral-400"><?= e(adm('Geçerli video satışları · aylık kademe tesis toplamına göre (0–49 %20 · 50–99 %25 · 100+ %30) · KDV düşülmeden brüt', 'Valid video sales · monthly tier from the facility total (0–49 20% · 50–99 25% · 100+ 30%) · gross, before VAT')) ?></p>
          </div>
          <span class="text-xs font-semibold px-3 py-1 rounded-full bg-accent/10 text-accent"><?= count($facilityBreakdown) ?> <?= e(adm('Tesis Listeleniyor', 'facilities listed')) ?></span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
              <tr>
                <th class="px-4 py-3"><?= e(adm('Tesis Adı & Şehir', 'Facility & city')) ?></th>
                <th class="px-4 py-3"><?= e(adm('Video Fiyatı', 'Video price')) ?></th>
                <th class="px-4 py-3 text-center"><?= e(adm('Bugünkü Satış', 'Sold today')) ?></th>
                <th class="px-4 py-3 text-center"><?= e(adm('Bu Ay Satış & Kademe', 'This month: sales & tier')) ?></th>
                <th class="px-4 py-3 text-right"><?= e(adm('Bu Ay Tesis Payı', 'Facility share this month')) ?></th>
                <th class="px-4 py-3 text-right"><?= e(adm('Bu Ay Replash Faturası', 'Replash invoice this month')) ?></th>
                <th class="px-4 py-3 text-center"><?= e(adm('Toplam Satış', 'Total sales')) ?></th>
                <th class="px-4 py-3 text-right"><?= e(adm('Toplam Ciro', 'Total revenue')) ?></th>
                <th class="px-4 py-3 text-right"><?= e(adm('İşlem', 'Action')) ?></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-neutral-300">
              <?php foreach ($facilityBreakdown as $fb):
                // Monthly split with the facility-wide tier (includes/billing.php)
                $fbSplit = replash_split_gross((float)$fb['month_revenue'], (int)$fb['month_sold']);
                $fbPrices = [];
                foreach (get_facility_courts_data($fb) as $pc) { $fbPrices[$pc['sport']] = ucfirst($pc['sport']) . ' ' . replash_money($pc['price'], 0); }
              ?>
                <tr class="border-b border-white/5 hover:bg-white/[0.02] transition <?= $selectedFacilityId === $fb['id'] ? 'bg-accent/5' : '' ?>">
                  <td class="px-4 py-2.5">
                    <div class="font-bold text-white flex items-center gap-2">
                      <?= e($fb['name']) ?>
                      <?php if ($selectedFacilityId === $fb['id']): ?>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-accent text-ink-900"><?= e(adm('Aktif Filtre', 'Active filter')) ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="text-xs text-neutral-400"><?= e($fb['city']) ?> · <?= strtoupper(e($fb['sport'])) ?></div>
                  </td>
                  <td class="px-4 py-2.5 text-xs font-mono font-semibold text-neutral-200">
                    <?= e(implode(' · ', $fbPrices)) ?>
                  </td>
                  <td class="px-4 py-2.5 text-center">
                    <span class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-md">
                      <?= (int)$fb['today_sold'] ?> ad (<?= e(replash_money((float)$fb['today_revenue'], 0)) ?>)
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-center">
                    <span class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-sky-400 bg-sky-500/10 px-2.5 py-0.5 rounded-md">
                      <?= (int)$fb['month_sold'] ?> <?= e(adm('ad', 'pcs')) ?> · <?= e(adm_pct((int)$fbSplit['facility_pct'])) ?>
                    </span>
                    <div class="text-[10px] text-neutral-500 mt-1"><?= e(adm('brüt', 'gross')) ?> <?= e(replash_money($fbSplit['gross'], 0)) ?></div>
                  </td>
                  <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold text-emerald-400"><?= e(replash_money($fbSplit['facility_share'])) ?></td>
                  <td class="px-4 py-2.5 text-right font-mono text-xs font-bold text-white"><?= e(replash_money($fbSplit['replash_share'])) ?></td>
                  <td class="px-4 py-2.5 text-center font-mono text-xs text-neutral-300">
                    <?= (int)$fb['sold_matches'] ?> / <?= (int)$fb['total_matches'] ?>
                  </td>
                  <td class="px-4 py-2.5 text-right font-mono text-sm font-bold text-accent">
                    <?= e(replash_money((float)$fb['total_revenue'], 0)) ?>
                  </td>
                  <td class="px-4 py-2.5 text-right">
                    <?php if ($selectedFacilityId === $fb['id']): ?>
                      <a href="./" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-300 text-xs font-semibold hover:bg-rose-500/20 transition">
                        <?= e(adm('Filtreyi Kaldır', 'Clear filter')) ?>
                      </a>
                    <?php else: ?>
                      <a href="./?facility_id=<?= urlencode($fb['id']) ?>" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-accent text-ink-900 text-xs font-semibold shadow-glow hover:bg-accent-neon transition">
                        <?= e(adm('Grafiği İncele →', 'View chart →')) ?>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Operational Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10">
          <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400"><?= e(adm('Toplam Kaydedilen Maç', 'Recorded matches')) ?></div>
          <div class="font-display text-3xl font-bold text-white mt-1"><?= $stats['total_matches'] ?></div>
        </div>
        <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10">
          <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400"><?= e(adm('Satılan (Açık) Videolar', 'Sold (unlocked) videos')) ?></div>
          <div class="font-display text-3xl font-bold text-accent mt-1"><?= $stats['sold_matches'] ?></div>
        </div>
        <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10">
          <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400"><?= e(adm('Aktif Kameralar', 'Active cameras')) ?></div>
          <div class="font-display text-3xl font-bold text-emerald-400 mt-1"><?= $stats['active_cameras'] ?> / <?= count($cameras) ?></div>
        </div>
      </div>

      <?php endif; ?>

      <?php if ($adminTab === 'facilities'): ?>
      <!-- Facilities & Cameras Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        
        <!-- Facilities List & Add Modal -->
        <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="font-display text-xl font-bold text-white"><?= e(adm('Kayıtlı Tesisler', 'Registered facilities')) ?></h2>
                <span class="text-xs text-neutral-400"><?= count($facilities) ?> <?= e(adm('Tesis Aktif', 'facilities active')) ?></span>
              </div>
              <button onclick="document.getElementById('addFacilityModal').classList.toggle('hidden')" class="px-3 py-1.5 rounded-xl bg-accent text-ink-900 font-semibold text-xs shadow-glow hover:bg-accent-neon transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <?= e(adm('Yeni Tesis Ekle', 'Add facility')) ?>
              </button>
            </div>

            <!-- Add Facility Form (Hidden by default or toggled) -->
            <div id="addFacilityModal" class="hidden mb-6 p-5 rounded-2xl bg-ink-900 border border-accent/30 shadow-xl">
              <div class="flex items-center justify-between mb-3 pb-2 border-b border-white/10">
                <h3 class="text-sm font-bold text-accent"><?= e(adm('Yeni Tesis & Canlı Yayın Kameraları Tanımla', 'Add a facility & its live cameras')) ?></h3>
                <span class="text-[11px] text-neutral-400"><?= e(adm('Her sahaya özel canlı RTSP/HLS linki bağlayın', 'Attach an RTSP/HLS link to each court')) ?></span>
              </div>

              <form action="./?tab=facilities" method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="add_facility">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-neutral-400 mb-1 font-medium"><?= e(adm('Tesis Adı', 'Facility name')) ?></label>
                    <input type="text" name="name" placeholder="<?= e(adm('Örn: Kadıköy Halısaha & Padel', 'e.g. Milano Padel Club')) ?>" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                  </div>
                  <div>
                    <label class="block text-neutral-400 mb-1 font-medium"><?= e(adm('Şehir', 'City')) ?></label>
                    <input type="text" name="city" placeholder="<?= e(adm('Örn: İstanbul', 'e.g. Milano')) ?>" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                  </div>
                </div>

                <div>
                  <label class="block text-neutral-400 mb-1 font-medium"><?= e(adm('6 haneli PIN', '6-digit PIN')) ?></label>
                  <input type="text" name="pin" maxlength="6" inputmode="numeric" required class="w-40 rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white font-mono focus:border-accent focus:outline-none">
                </div>
                <div>
                  <label class="block text-neutral-400 mb-1 font-medium" for="pilot-start-new"><?= e(adm('Pilot başlangıç tarihi (Replash payı %0 — ' . REPLASH_PILOT_DAYS . ' gün)', 'Pilot start date (Replash share 0% — ' . REPLASH_PILOT_DAYS . ' days)')) ?></label>
                  <input type="date" id="pilot-start-new" name="pilot_start" class="w-48 rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                </div>
                <?php admin_sport_price_fields(['football' => REPLASH_VIDEO_PRICES['football']], 'new'); ?>

                <!-- Dynamic Courts & Multiple Camera Stream URLs -->
                <div class="pt-2 border-t border-white/10 space-y-3">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="font-bold text-white text-xs"><?= e(adm('Sahalar & Canlı Kamera Yayın Linkleri', 'Courts & live camera links')) ?></label>
                      <p class="text-[11px] text-neutral-400"><?= e(adm('Her sahaya 1 veya birden fazla kamera açısı ekleyebilirsiniz', 'You can add one or more camera angles per court')) ?></p>
                    </div>
                    <button type="button" onclick="addCourtBlock('courtsContainer')" class="px-2.5 py-1 rounded-lg bg-accent/15 border border-accent/30 text-xs text-accent hover:bg-accent hover:text-ink-900 font-bold transition flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      <?= e(adm('+ Saha Ekle', '+ Add court')) ?>
                    </button>
                  </div>

                  <div id="courtsContainer" class="space-y-3">
                    <div class="court-block bg-ink-800/90 p-3.5 rounded-xl border border-white/10 space-y-2.5" data-court-index="0">
                      <div class="flex items-center justify-between pb-1.5 border-b border-white/5">
                        <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-accent"></span>
                          <span class="font-bold text-white text-xs court-title"><?= e(adm('1. Saha / Kort', 'Court 1')) ?></span>
                        </div>
                        <button type="button" onclick="removeCourtBlock(this)" class="px-2 py-0.5 rounded text-[11px] bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 font-semibold transition"><?= e(adm('× Sahayı Sil', '× Remove court')) ?></button>
                      </div>

                      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                          <label class="block text-neutral-400 mb-1 text-[11px] font-medium"><?= e(adm('Saha Adı', 'Court name')) ?></label>
                          <input type="text" name="courts[0][name]" value="Saha 1" placeholder="<?= e(adm('Örn: Açık Saha, 1. Kort', 'e.g. Outdoor pitch, Court 1')) ?>" required class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                        <div>
                          <label class="block text-neutral-400 mb-1 text-[11px] font-medium"><?= e(adm('Spor Türü', 'Sport')) ?></label>
                          <select name="courts[0][sport]" class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                            <option value="football"><?= e(adm('Futbol (Halı Saha)', 'Football (5-a-side)')) ?></option>
                            <option value="padel">Padel</option>
                            <option value="tennis"><?= e(adm('Tenis', 'Tennis')) ?></option>
                            <option value="basketball"><?= e(adm('Basketbol', 'Basketball')) ?></option>
                          </select>
                        </div>
                      </div>

                      <div class="pt-1.5 space-y-1.5">
                        <div class="flex items-center justify-between">
                          <span class="text-[11px] font-semibold text-neutral-300"><?= e(adm('Kamera Yayın URL\'leri:', 'Camera stream URLs:')) ?></span>
                          <button type="button" onclick="addCameraToCourt(this)" class="text-[11px] text-accent hover:underline flex items-center gap-1 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <?= e(adm('+ Kamera Ekle', '+ Add camera')) ?>
                          </button>
                        </div>
                        <div class="cameras-container space-y-1.5">
                          <div class="camera-row flex items-center gap-1.5">
                            <input type="text" name="courts[0][cameras][]" placeholder="<?= e(adm('1. Kamera Canlı Yayın URL (rtsp://... veya https://.../live.m3u8)', 'Camera 1 stream URL (rtsp://... or https://.../live.m3u8)')) ?>" class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
                            <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="<?= e(adm('Kamerayı Kaldır', 'Remove camera')) ?>">&times;</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/10">
                  <button type="button" onclick="document.getElementById('addFacilityModal').classList.add('hidden')" class="px-3.5 py-2 rounded-xl border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10"><?= e(adm('İptal', 'Cancel')) ?></button>
                  <button type="submit" class="px-5 py-2 rounded-xl bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow"><?= e(adm('Tesis & Kameraları Kaydet', 'Save facility & cameras')) ?></button>
                </div>
              </form>
            </div>

            <!-- Facilities Items -->
            <div class="space-y-3 max-h-96 overflow-y-auto">
              <?php foreach ($facilities as $f): 
                $courtsData = get_facility_courts_data($f);
                $courtDisplayParts = [];
                foreach ($courtsData as $c) {
                    $cSportLabel = strtoupper($c['sport'] ?? $f['sport'] ?? 'football');
                    $cPriceLabel = isset($c['price']) ? ' - ' . $c['price'] . ' ' . $f['currency'] : '';
                    $courtDisplayParts[] = e($c['name']) . ' (' . $cSportLabel . $cPriceLabel . ')';
                }
                $courtsStr = implode(', ', $courtDisplayParts);
              ?>
                <div class="p-4 rounded-xl bg-ink-900 border border-white/5 text-sm space-y-2.5">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <div class="font-semibold text-white text-base"><?= e($f['name']) ?></div>
                      <div class="text-xs text-neutral-400 mt-0.5">
                        <?= e($f['city']) ?> · PIN: <code class="text-accent font-bold"><?= e($f['pin']) ?></code> · <?= e($f['unit_price']) ?> <?= e($f['currency']) ?>
                      </div>
                      <div class="text-[11px] text-neutral-500 mt-1"><?= e(adm('Sahalar:', 'Courts:')) ?> <?= e($courtsStr) ?></div>
                    </div>
                    
                    <div class="flex items-center gap-2 flex-shrink-0">
                      <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-accent/10 text-accent uppercase"><?= e($f['sport']) ?></span>
                      
                      <!-- Edit Toggle Button -->
                      <button type="button" onclick="document.getElementById('editFacility_<?= e($f['id']) ?>').classList.toggle('hidden')" class="p-1.5 rounded-lg border border-white/10 bg-white/5 text-neutral-300 hover:text-white hover:bg-white/10" title="<?= e(adm('Tesisi Düzenle', 'Edit facility')) ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                      </button>

                      <!-- Delete Facility Form -->
                      <form action="./?tab=facilities" method="POST" onsubmit="return confirm('<?= e($f['name']) ?> tesisini, bağlı tüm kameraları ve maç kayıtlarını tamamen silmek istediğinize emin misiniz?');" class="inline">
                        <input type="hidden" name="action" value="delete_facility">
                        <input type="hidden" name="facility_id" value="<?= e($f['id']) ?>">
                        <button type="submit" class="p-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20" title="<?= e(adm('Tesisi Sil', 'Delete facility')) ?>">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      </form>
                    </div>
                  </div>

                  <!-- Edit Facility Form (Collapsible) -->
                  <div id="editFacility_<?= e($f['id']) ?>" class="hidden pt-3 border-t border-white/10 bg-ink-800/90 p-4 rounded-xl text-xs space-y-3.5">
                    <div class="flex items-center justify-between pb-1 border-b border-white/10">
                      <h4 class="font-bold text-accent"><?= e(adm('Tesis & Kamera Yayınlarını Düzenle', 'Edit facility & camera streams')) ?></h4>
                      <span class="text-[11px] text-neutral-400"><?= e(adm('Her sahaya birden fazla kamera URL\'si atayabilirsiniz', 'You can assign several camera URLs to each court')) ?></span>
                    </div>

                    <form action="./?tab=facilities" method="POST" class="space-y-3">
                      <input type="hidden" name="action" value="update_facility">
                      <input type="hidden" name="facility_id" value="<?= e($f['id']) ?>">
                      
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                          <label class="block text-neutral-400 mb-1 font-medium"><?= e(adm('Tesis Adı', 'Facility name')) ?></label>
                          <input type="text" name="name" value="<?= e($f['name']) ?>" required class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                        <div>
                          <label class="block text-neutral-400 mb-1 font-medium"><?= e(adm('Şehir', 'City')) ?></label>
                          <input type="text" name="city" value="<?= e($f['city']) ?>" required class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                      </div>

                      <div>
                        <label class="block text-neutral-400 mb-1 font-medium"><?= e(adm('6 haneli PIN', '6-digit PIN')) ?></label>
                        <input type="text" name="pin" maxlength="6" inputmode="numeric" value="<?= e($f['pin']) ?>" required class="w-40 rounded-lg border border-white/10 bg-ink-900 p-2 text-white font-mono text-xs focus:border-accent focus:outline-none">
                      </div>
                      <div>
                        <label class="block text-neutral-400 mb-1 font-medium" for="pilot-start-<?= e($f['id']) ?>"><?= e(adm('Pilot başlangıç tarihi (Replash payı %0 — ' . REPLASH_PILOT_DAYS . ' gün)', 'Pilot start date (Replash share 0% — ' . REPLASH_PILOT_DAYS . ' days)')) ?></label>
                        <input type="date" id="pilot-start-<?= e($f['id']) ?>" name="pilot_start" value="<?= e($f['pilot_start'] ?? '') ?>" class="w-48 rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                      </div>
                      <?php
                        $fPrices = replash_facility_prices($f);
                        if (!$fPrices) { foreach (get_facility_courts_data($f) as $cd) { $fPrices[$cd['sport']] = $cd['price']; } }
                        admin_sport_price_fields($fPrices, $f['id']);
                      ?>

                      <!-- Courts & Multiple Stream URLs for this facility -->
                      <div class="pt-2 border-t border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                          <div>
                            <label class="font-bold text-white text-xs"><?= e(adm('Sahalar & Kamera Canlı Yayın URL\'leri', 'Courts & camera stream URLs')) ?></label>
                            <p class="text-[11px] text-neutral-400"><?= e(adm('Her sahaya özel spor türü, birim fiyat ve kamera açıları tanımlayın', 'Set the sport, price and camera angles for each court')) ?></p>
                          </div>
                          <button type="button" onclick="addCourtBlock('editCourtsContainer_<?= e($f['id']) ?>')" class="px-2.5 py-1 rounded-lg bg-accent/15 border border-accent/30 text-xs text-accent hover:bg-accent hover:text-ink-900 font-bold transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <?= e(adm('+ Saha Ekle', '+ Add court')) ?>
                          </button>
                        </div>

                        <div id="editCourtsContainer_<?= e($f['id']) ?>" class="space-y-3">
                          <?php 
                          $thisCourtsData = get_facility_courts_data($f);
                          $camsByCourt = [];
                          $thisFacCams = $camerasByFacility[$f['id']] ?? [];
                          foreach ($thisFacCams as $camRow) {
                              $camsByCourt[$camRow['court']][] = $camRow['stream_url'];
                          }
                          if (empty($thisCourtsData)) {
                              $thisCourtsData = [['name' => 'Saha 1', 'sport' => $f['sport'] ?? 'football', 'price' => $f['unit_price'] ?? 250]];
                          }
                          $cIdx = 0;
                          foreach ($thisCourtsData as $cItem): 
                              $cName = $cItem['name'];
                              $cSport = $cItem['sport'] ?? $f['sport'] ?? 'football';
                              $cPrice = $cItem['price'] ?? $f['unit_price'] ?? 250;
                              $streams = $camsByCourt[$cName] ?? [''];
                              if (empty($streams)) $streams = [''];
                          ?>
                            <div class="court-block bg-ink-900/90 p-3.5 rounded-xl border border-white/10 space-y-2.5" data-court-index="<?= $cIdx ?>">
                              <div class="flex items-center justify-between pb-1.5 border-b border-white/5">
                                <div class="flex items-center gap-2">
                                  <span class="w-2 h-2 rounded-full bg-accent"></span>
                                  <span class="font-bold text-white text-xs court-title"><?= ($cIdx + 1) ?>. Saha / Kort</span>
                                </div>
                                <button type="button" onclick="removeCourtBlock(this)" class="px-2 py-0.5 rounded text-[11px] bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 font-semibold transition"><?= e(adm('× Sahayı Sil', '× Remove court')) ?></button>
                              </div>

                              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div>
                                  <label class="block text-neutral-400 mb-1 text-[11px] font-medium"><?= e(adm('Saha Adı', 'Court name')) ?></label>
                                  <input type="text" name="courts[<?= $cIdx ?>][name]" value="<?= e($cName) ?>" placeholder="<?= e(adm('Saha Adı', 'Court name')) ?>" required class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white text-xs focus:border-accent focus:outline-none">
                                </div>
                                <div>
                                  <label class="block text-neutral-400 mb-1 text-[11px] font-medium"><?= e(adm('Spor Türü', 'Sport')) ?></label>
                                  <select name="courts[<?= $cIdx ?>][sport]" class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white text-xs focus:border-accent focus:outline-none">
                                    <option value="football" <?= $cSport === 'football' ? 'selected' : '' ?>><?= e(adm('Futbol (Halı Saha)', 'Football (5-a-side)')) ?></option>
                                    <option value="padel" <?= $cSport === 'padel' ? 'selected' : '' ?>>Padel</option>
                                    <option value="tennis" <?= $cSport === 'tennis' ? 'selected' : '' ?>><?= e(adm('Tenis', 'Tennis')) ?></option>
                                    <option value="basketball" <?= $cSport === 'basketball' ? 'selected' : '' ?>><?= e(adm('Basketbol', 'Basketball')) ?></option>
                                  </select>
                                </div>
                              </div>

                              <div class="pt-1.5 space-y-1.5">
                                <div class="flex items-center justify-between">
                                  <span class="text-[11px] font-semibold text-neutral-300"><?= e(adm('Kamera Yayın URL\'leri:', 'Camera stream URLs:')) ?></span>
                                  <button type="button" onclick="addCameraToCourt(this)" class="text-[11px] text-accent hover:underline flex items-center gap-1 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <?= e(adm('+ Kamera Ekle', '+ Add camera')) ?>
                                  </button>
                                </div>
                                <div class="cameras-container space-y-1.5">
                                  <?php foreach ($streams as $sIdx => $sUrl): ?>
                                    <div class="camera-row flex items-center gap-1.5">
                                      <input type="text" name="courts[<?= $cIdx ?>][cameras][]" value="<?= e($sUrl) ?>" placeholder="<?= ($sIdx + 1) ?>. Kamera URL (rtsp://... veya https://...)" class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
                                      <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="<?= e(adm('Kamerayı Kaldır', 'Remove camera')) ?>">&times;</button>
                                    </div>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            </div>
                          <?php 
                            $cIdx++;
                          endforeach; 
                          ?>
                        </div>
                      </div>

                      <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/10">
                        <button type="button" onclick="document.getElementById('editFacility_<?= e($f['id']) ?>').classList.add('hidden')" class="px-3.5 py-1.5 rounded-lg border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10"><?= e(adm('Vazgeç', 'Cancel')) ?></button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow"><?= e(adm('Değişiklikleri Kaydet', 'Save changes')) ?></button>
                      </div>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Cameras List & Stream Management -->
        <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="font-display text-xl font-bold text-white"><?= e(adm('Kamera & Canlı Yayınlar', 'Cameras & live streams')) ?></h2>
              <p class="text-xs text-neutral-400"><?= e(adm('Her sahaya atanmış olan canlı kamera bağlantıları', 'Live camera links assigned to each court')) ?></p>
            </div>
            <span class="text-xs text-neutral-400"><?= count($cameras) ?> <?= e(adm('Kamera', 'Camera')) ?></span>
          </div>
          <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php foreach ($cameras as $c): ?>
              <div class="p-3.5 rounded-xl bg-ink-900 border border-white/5 text-sm space-y-2">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-semibold text-white"><?= e($c['name']) ?></div>
                    <div class="text-xs text-neutral-400 font-mono">channelId: <span class="text-neutral-300"><?= e($c['channel_id']) ?></span></div>
                  </div>
                  <div>
                    <?php if ($c['status'] === 'online'): ?>
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Online
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Offline
                      </span>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Stream URL display and inline edit -->
                <div class="bg-ink-800/80 p-2.5 rounded-lg border border-white/5">
                  <div class="flex items-center justify-between gap-2 mb-1">
                    <span class="text-[11px] font-semibold text-accent uppercase tracking-wider"><?= e(adm('Kamera Canlı Yayın URL:', 'Camera stream URL:')) ?></span>
                    <button type="button" onclick="document.getElementById('edit-stream-<?= e($c['id']) ?>').classList.toggle('hidden')" class="text-[11px] text-neutral-400 hover:text-white underline">
                      <?= e(adm('URL Değiştir', 'Change URL')) ?>
                    </button>
                  </div>
                  <div class="text-xs font-mono text-neutral-300 truncate">
                    <?= !empty($c['stream_url']) ? e($c['stream_url']) : '<span class="text-neutral-500 italic">' . e(adm('Yayın URL tanımlanmadı', 'No stream URL set')) . '</span>' ?>
                  </div>

                  <!-- Edit Stream URL Form -->
                  <form id="edit-stream-<?= e($c['id']) ?>" action="./" method="POST" class="hidden mt-2 pt-2 border-t border-white/10 flex gap-2">
                    <input type="hidden" name="action" value="update_camera_stream">
                    <input type="hidden" name="camera_id" value="<?= e($c['id']) ?>">
                    <input type="text" name="stream_url" value="<?= e($c['stream_url'] ?? '') ?>" placeholder="<?= e(adm('rtsp://... veya https://...', 'rtsp://... or https://...')) ?>" class="flex-1 rounded-lg border border-white/10 bg-ink-900 px-2 py-1 text-xs text-white font-mono focus:border-accent focus:outline-none">
                    <button type="submit" class="rounded-lg bg-accent px-3 py-1 text-xs font-bold text-ink-900 hover:bg-accent-neon"><?= e(adm('Kaydet', 'Save')) ?></button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <?php endif; ?>

      <?php if ($adminTab === 'settings'): ?>
      <a href="<?= base_url('admin/calculator') ?>" class="mb-6 flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] p-5 transition hover:border-accent/40">
        <div>
          <div class="font-semibold text-white"><?= e(adm('Gelir Hesaplayıcı', 'Revenue calculator')) ?></div>
          <div class="text-xs text-white/50"><?= e(adm('Sahalar İçin sayfasındaki hesaplayıcının varsayılanları ve metinleri', 'Defaults and texts of the calculator on the For Facilities page')) ?></div>
        </div>
        <span class="text-accent">→</span>
      </a>
      <!-- Search Form Display Settings -->
      <div class="rounded-2xl bg-white/[0.02] p-6 sm:p-8 border border-white/10 mb-8 bg-ink-800/80">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-white/10">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-accent"><?= e(adm('Site Görünüm Ayarları', 'Site display settings')) ?></span>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mt-1"><?= e(adm('Arama & Maç Bulma Formu Yönetimi', 'Search & find-match form')) ?></h2>
          </div>
          <span class="text-xs text-neutral-400 bg-white/5 border border-white/10 px-3 py-1.5 rounded-full">
            <?= e(adm('index.php & find.php Form Kontrolü', 'index.php & find.php form control')) ?>
          </span>
        </div>

        <form action="./?tab=settings" method="POST" class="space-y-6">
          <input type="hidden" name="action" value="update_search_form_settings">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Option 1: Facility Search -->
            <label class="relative flex items-start gap-4 p-5 rounded-2xl border transition-all cursor-pointer <?= $searchFacilityEnabled ? 'border-accent/40 bg-accent/[0.04]' : 'border-white/10 bg-white/[0.02]' ?> hover:border-accent/30">
              <input type="checkbox" name="search_by_facility_enabled" value="1" <?= $searchFacilityEnabled ? 'checked' : '' ?> class="mt-1 h-5 w-5 rounded border-white/20 bg-ink-900 text-accent focus:ring-accent focus:ring-offset-ink-900">
              <div class="space-y-1">
                <span class="font-bold text-white text-base block"><?= e(adm('Tesis, Tarih ve Saat ile Arama', 'Search by facility, date and time')) ?></span>
                <p class="text-xs text-neutral-400 leading-relaxed">
                  <?= e(adm('Ziyaretçilerin tesis adı, tarih ve saat dilimi seçerek maçlarını listelemesine ve bulmasına olanak tanır.', 'Lets visitors list and find their match by picking facility, date and time slot.')) ?>
                </p>
              </div>
            </label>

            <!-- Option 2: Code Search -->
            <label class="relative flex items-start gap-4 p-5 rounded-2xl border transition-all cursor-pointer <?= $searchCodeEnabled ? 'border-accent/40 bg-accent/[0.04]' : 'border-white/10 bg-white/[0.02]' ?> hover:border-accent/30">
              <input type="checkbox" name="search_by_code_enabled" value="1" <?= $searchCodeEnabled ? 'checked' : '' ?> class="mt-1 h-5 w-5 rounded border-white/20 bg-ink-900 text-accent focus:ring-accent focus:ring-offset-ink-900">
              <div class="space-y-1">
                <span class="font-bold text-white text-base block"><?= e(adm('Sadece Kod (PIN / Erişim Kodu) ile Arama', 'Code only (access code)')) ?></span>
                <p class="text-xs text-neutral-400 leading-relaxed">
                  <?= e(adm('Oyuncuların doğrudan 8 haneli maç erişim kodunu girerek maçlarına ulaşmasını sağlar.', 'Players reach their match by entering the access code directly.')) ?>
                </p>
              </div>
            </label>
          </div>

          <div class="rounded-xl border border-white/5 bg-ink-900/60 p-4 text-xs text-neutral-400 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
              <svg class="w-5 h-5 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span><strong><?= e(adm('Tasarım Davranışı:', 'Layout behaviour:')) ?></strong> <?= e(adm('Sadece kod ile arama seçildiğinde ana sayfada (index.php) yatay şık bir arama çubuğu, find.php sayfasında ise merkezi büyük PIN giriş kartı aktif olur.', 'With code-only search, the home page shows a slim horizontal bar and find.php shows a large centred code card.')) ?></span>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow whitespace-nowrap text-xs transition">
              <?= e(adm('Ayarları Kaydet →', 'Save settings →')) ?>
            </button>
          </div>
        </form>
      </div>

      <?php endif; ?>

      <?php if ($adminTab === 'leads'): ?>
      <!-- Leads / Contact Requests -->
      <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10 mb-10">
        <h2 class="font-display text-xl font-bold text-white mb-4"><?= e(adm('Gelen Pilot Başvuruları & Talepler', 'Incoming pilot requests & leads')) ?></h2>
        <?php if (empty($leads)): ?>
          <p class="text-xs text-neutral-500"><?= e(adm('Henüz yeni bir başvuru bulunmuyor.', 'No leads yet.')) ?></p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
                <tr>
                  <th class="px-4 py-3"><?= e(adm('Ad / Tesis', 'Name / facility')) ?></th>
                  <th class="px-4 py-3"><?= e(adm('İletişim', 'Contact')) ?></th>
                  <th class="px-4 py-3"><?= e(adm('Şehir / Spor', 'City / sport')) ?></th>
                  <th class="px-4 py-3"><?= e(adm('Mesaj', 'Message')) ?></th>
                  <th class="px-4 py-3"><?= e(adm('Tarih', 'Date')) ?></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5 text-neutral-300">
                <?php foreach ($leads as $l): ?>
                  <tr>
                    <td class="p-3">
                      <div class="font-semibold text-white"><?= e($l['name']) ?></div>
                      <div class="text-xs text-accent"><?= e($l['facility_name']) ?> (<?= e($l['court_count']) ?> Saha)</div>
                    </td>
                    <td class="p-3 text-xs">
                      <div><?= e($l['email']) ?></div>
                      <div class="text-neutral-400"><?= e($l['phone']) ?></div>
                    </td>
                    <td class="p-3 text-xs"><?= e($l['city']) ?> · <?= strtoupper(e($l['sport'])) ?></td>
                    <td class="p-3 text-xs text-neutral-400 max-w-xs truncate"><?= e($l['message']) ?></td>
                    <td class="p-3 text-xs font-mono"><?= e($l['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ($adminTab === 'audit'): ?>
      <!-- SATIŞ DENETİMİ: satış / geri alma kayıtları (silinmez) -->
      <p class="mb-5 inline-block rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 text-xs text-white/50">
        ⓘ <?= e(adm(
              'Tesisler satışı yalnızca ilk ' . REPLASH_UNDO_WINDOW_MIN . ' dakikada, video açılmadıysa ve neden yazarak geri alabilir. Daha sonraki düzeltmeler yalnızca buradan (Maçlar sekmesi) yapılır.',
              'Facilities can undo a sale only within the first ' . REPLASH_UNDO_WINDOW_MIN . ' minutes, only if the video was never opened, and only with a reason. Later corrections are made here (Matches tab).'
           )) ?>
      </p>

      <div class="mb-6 overflow-x-auto rounded-2xl border border-white/10">
        <table class="w-full min-w-[640px] text-sm">
          <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
            <tr><th class="px-4 py-3"><?= e(adm('Tesis', 'Facility')) ?></th><th class="px-4 py-3 text-right"><?= e(adm('Bu ay satış', 'Sales this month')) ?></th><th class="px-4 py-3 text-right"><?= e(adm('Bu ay geri alma', 'Undone this month')) ?></th><th class="px-4 py-3 text-right"><?= e(adm('Oran', 'Rate')) ?></th></tr>
          </thead>
          <tbody>
            <?php foreach ($facilities as $fac): $u = $undoByFacility[$fac['id']] ?? ['sold' => 0, 'undone' => 0]; $rate = ((int)$u['sold'] > 0) ? round(100 * (int)$u['undone'] / (int)$u['sold']) : 0; ?>
              <tr class="border-b border-white/5 hover:bg-white/[0.02]">
                <td class="px-4 py-2.5"><a class="hover:text-accent" href="?tab=audit&audit_facility=<?= urlencode($fac['id']) ?>"><?= e($fac['name']) ?></a></td>
                <td class="px-4 py-2.5 text-right font-mono"><?= (int)$u['sold'] ?></td>
                <td class="px-4 py-2.5 text-right font-mono <?= (int)$u['undone'] > 0 ? 'text-red-300' : 'text-white/40' ?>"><?= (int)$u['undone'] ?></td>
                <td class="px-4 py-2.5 text-right font-mono <?= $rate >= 10 ? 'text-red-300 font-bold' : 'text-white/60' ?>"><?= e(adm_pct($rate)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-white/10">
        <table class="w-full min-w-[860px] text-sm">
          <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
            <tr><th class="px-4 py-3"><?= e(adm('Zaman', 'Time')) ?></th><th class="px-4 py-3"><?= e(adm('Tesis', 'Facility')) ?></th><th class="px-4 py-3"><?= e(adm('Maç', 'matches')) ?></th><th class="px-4 py-3"><?= e(adm('İşlem', 'Action')) ?></th><th class="px-4 py-3"><?= e(adm('Kim', 'Who')) ?></th><th class="px-4 py-3"><?= e(adm('Neden', 'Reason')) ?></th></tr>
          </thead>
          <tbody>
            <?php if (empty($auditEvents)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-white/40"><?= e(adm('Kayıt yok.', 'No records.')) ?></td></tr>
            <?php endif; ?>
            <?php
              $actionLabels = [
                'sold' => [adm('Satıldı', 'Sold'), 'bg-emerald-400/15 text-emerald-300'],
                'undone' => [adm('Tesis geri aldı', 'Facility undid'), 'bg-red-400/15 text-red-300'],
                'admin_sold' => [adm('Yönetim açtı', 'Admin unlocked'), 'bg-emerald-400/15 text-emerald-300'],
                'admin_undone' => [adm('Yönetim geri aldı', 'Admin undid'), 'bg-white/10 text-white/70'],
                'admin_deleted' => [adm('Yönetim sildi', 'Admin deleted'), 'bg-white/10 text-white/70'],
              ];
            ?>
            <?php foreach ($auditEvents as $ev): [$al, $ac] = $actionLabels[$ev['action']] ?? [$ev['action'], 'bg-white/10 text-white/70']; ?>
              <tr class="border-b border-white/5 hover:bg-white/[0.02]">
                <td class="px-4 py-2.5 font-mono text-xs text-white/60"><?= e(date('d.m.Y H:i', strtotime($ev['created_at']))) ?></td>
                <td class="px-4 py-2.5"><?= e($ev['facility_name'] ?? $ev['facility_id']) ?></td>
                <td class="px-4 py-2.5 text-xs"><?= e(($ev['court'] ?? '—') . ' · ' . ($ev['date'] ?? '') . ' · ' . ($ev['time_slot'] ?? '')) ?></td>
                <td class="px-4 py-2.5"><span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold <?= $ac ?>"><?= e($al) ?></span></td>
                <td class="px-4 py-2.5 font-mono text-xs text-white/50"><?= e($ev['actor']) ?></td>
                <td class="px-4 py-2.5 text-xs text-white/70"><?= e($ev['reason'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <h2 class="mb-2 mt-10 text-lg font-semibold"><?= e(adm('Oyuncu onayları', 'Player confirmations')) ?></h2>
      <p class="mb-4 text-xs text-white/50"><?= e(adm(
            'Kodla izlemeden önce oyuncu beyan eder: bu koda ait maçta oynadığını ve kaydedildiğini bildiğini, grubun videoyu satın almayı kabul ettiğini, gizlilik bildirimini okuduğunu. Ad tutulmaz; cihaz, IP\'nin şifreli özetinden türetilir. Kayıtlar ' . REPLASH_ACCEPT_RETENTION_MONTHS . ' ay sonra otomatik silinir.',
            'Before watching with a code, the player confirms having played in the match for that code and knowing it was recorded, that the group agreed to buy the video and having read the privacy notice. No name is stored; the device is derived from an encrypted IP hash. Records are deleted automatically after ' . REPLASH_ACCEPT_RETENTION_MONTHS . ' months.'
         )) ?></p>
      <div class="overflow-x-auto rounded-2xl border border-white/10">
        <table class="w-full min-w-[760px] text-sm">
          <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
            <tr><th class="px-4 py-3"><?= e(adm('Zaman', 'Time')) ?></th><th class="px-4 py-3"><?= e(adm('Tesis', 'Facility')) ?></th><th class="px-4 py-3"><?= e(adm('Maç', 'Match')) ?></th><th class="px-4 py-3"><?= e(adm('Cihaz', 'Device')) ?></th><th class="px-4 py-3"><?= e(adm('Metin sürümü', 'Text version')) ?></th></tr>
          </thead>
          <tbody>
            <?php if (empty($acceptances)): ?>
              <tr><td colspan="5" class="px-4 py-8 text-center text-white/40"><?= e(adm('Kayıt yok.', 'No records.')) ?></td></tr>
            <?php endif; ?>
            <?php foreach ($acceptances as $ac): ?>
              <tr class="border-b border-white/5 hover:bg-white/[0.02]">
                <td class="px-4 py-2.5 font-mono text-xs text-white/60"><?= e(date('d.m.Y H:i', strtotime($ac['accepted_at']))) ?></td>
                <td class="px-4 py-2.5"><?= e($ac['facility_name'] ?? $ac['facility_id']) ?></td>
                <td class="px-4 py-2.5 text-xs"><?= e(($ac['court'] ?? '—') . ' · ' . ($ac['date'] ?? '') . ' · ' . ($ac['time_slot'] ?? '')) ?></td>
                <td class="px-4 py-2.5 font-mono text-xs text-white/60">#<?= e(substr((string)$ac['client_key'], 0, 8)) ?></td>
                <td class="px-4 py-2.5 font-mono text-xs text-white/50"><?= e($ac['text_version']) ?> · <?= e(strtoupper((string)$ac['lang'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    <?php endif; ?>

  </div>
</main>

<script>
function addCourtBlock(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const courtBlocks = container.querySelectorAll('.court-block');
  const nextIdx = courtBlocks.length;
  const displayNum = nextIdx + 1;
  const isEdit = containerId.startsWith('editCourtsContainer');
  const bgClass = isEdit ? 'bg-ink-900/90' : 'bg-ink-800/90';
  const inputBgClass = isEdit ? 'bg-ink-800' : 'bg-ink-900';

  const div = document.createElement('div');
  div.className = `court-block ${bgClass} p-3.5 rounded-xl border border-white/10 space-y-2.5`;
  div.setAttribute('data-court-index', nextIdx);
  div.innerHTML = `
    <div class="flex items-center justify-between pb-1.5 border-b border-white/5">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-accent"></span>
        <span class="font-bold text-white text-xs court-title"><?= e(adm('Saha', 'Court')) ?> ${displayNum}</span>
      </div>
      <button type="button" onclick="removeCourtBlock(this)" class="px-2 py-0.5 rounded text-[11px] bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 font-semibold transition">&times; <?= e(adm('Sahayı Sil', 'Remove court')) ?></button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
      <div>
        <label class="block text-neutral-400 mb-1 text-[11px] font-medium"><?= e(adm('Saha Adı', 'Court name')) ?></label>
        <input type="text" name="courts[${nextIdx}][name]" value="<?= e(adm('Saha', 'Court')) ?> ${displayNum}" placeholder="<?= e(adm('Saha Adı', 'Court name')) ?>" required class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs focus:border-accent focus:outline-none">
      </div>
      <div>
        <label class="block text-neutral-400 mb-1 text-[11px] font-medium"><?= e(adm('Spor Türü', 'Sport')) ?></label>
        <select name="courts[${nextIdx}][sport]" class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs focus:border-accent focus:outline-none">
          <option value="football"><?= e(adm('Futbol (Halı Saha)', 'Football (5-a-side)')) ?></option>
          <option value="padel">Padel</option>
          <option value="tennis"><?= e(adm('Tenis', 'Tennis')) ?></option>
          <option value="basketball"><?= e(adm('Basketbol', 'Basketball')) ?></option>
        </select>
      </div>
    </div>
    <div class="pt-1.5 space-y-1.5">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-semibold text-neutral-300"><?= e(adm('Kamera Yayın adresleri:', 'Camera stream URLs:')) ?></span>
        <button type="button" onclick="addCameraToCourt(this)" class="text-[11px] text-accent hover:underline flex items-center gap-1 font-medium">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          + <?= e(adm('Kamera Ekle', 'Add camera')) ?>
        </button>
      </div>
      <div class="cameras-container space-y-1.5">
        <div class="camera-row flex items-center gap-1.5">
          <input type="text" name="courts[${nextIdx}][cameras][]" placeholder="1. <?= e(adm('Kamera yayın adresi', 'camera stream URL')) ?> (rtsp://... / https://...)" class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
          <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="<?= e(adm('Kamerayı Kaldır', 'Remove camera')) ?>">&times;</button>
        </div>
      </div>
    </div>
  `;
  container.appendChild(div);
}

function addCameraToCourt(btn) {
  const courtBlock = btn.closest('.court-block');
  if (!courtBlock) return;
  const courtIdx = courtBlock.getAttribute('data-court-index') || '0';
  const camerasContainer = courtBlock.querySelector('.cameras-container');
  if (!camerasContainer) return;
  const count = camerasContainer.querySelectorAll('.camera-row').length + 1;
  const isEdit = courtBlock.classList.contains('bg-ink-900/90');
  const inputBgClass = isEdit ? 'bg-ink-800' : 'bg-ink-900';

  const div = document.createElement('div');
  div.className = 'camera-row flex items-center gap-1.5';
  div.innerHTML = `
    <input type="text" name="courts[${courtIdx}][cameras][]" placeholder="${count}. <?= e(adm('Kamera yayın adresi', 'camera stream URL')) ?> (rtsp://... / https://...)" class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
    <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="<?= e(adm('Kamerayı Kaldır', 'Remove camera')) ?>">&times;</button>
  `;
  camerasContainer.appendChild(div);
}

function removeCourtBlock(btn) {
  const courtBlock = btn.closest('.court-block');
  if (!courtBlock) return;
  const container = courtBlock.parentElement;
  if (container.querySelectorAll('.court-block').length <= 1) {
    alert('En az 1 saha tanımlı olmalıdır.');
    return;
  }
  courtBlock.remove();
}

function removeCameraRow(btn) {
  const row = btn.closest('.camera-row');
  if (!row) return;
  const container = row.parentElement;
  if (container.querySelectorAll('.camera-row').length <= 1) {
    const input = row.querySelector('input');
    if (input) input.value = '';
    return;
  }
  row.remove();
}
</script>

<?php if ($isAdmin): ?>
<!-- Chart.js Library for Graphical Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var ADM_LOCALE = <?= json_encode(admin_lang() === 'tr' ? 'tr-TR' : 'en-GB') ?>;
  var currency = <?= json_encode($selectedFacilityCurrency) ?>;
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

  var canvas = document.getElementById('adminRevenueAnalyticsChart');
  if (!canvas) return;

  var adminRevenueChart = new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels: chartData.daily.labels,
      datasets: [
        {
          label: '<?= e(adm('Hasılat', 'Revenue')) ?> (' + currency + ')',
          data: chartData.daily.revenue,
          backgroundColor: 'rgba(250, 204, 21, 0.75)',
          hoverBackgroundColor: 'rgba(250, 204, 21, 1)',
          borderRadius: 8,
          borderSkipped: false,
          yAxisID: 'y'
        },
        {
          label: '<?= e(adm('Satılan Maç Adedi', 'Matches sold')) ?>',
          data: chartData.daily.count,
          type: 'line',
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.10)',
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
                return ' ' + context.dataset.label + ': ' + Number(context.raw).toLocaleString(ADM_LOCALE) + ' ' + currency;
              }
              return ' ' + context.dataset.label + ': ' + context.raw + ' <?= e(adm('adet', 'matches')) ?>';
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
            callback: function(val) { return Number(val).toLocaleString(ADM_LOCALE) + ' ' + currency; }
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
            callback: function(val) { return val + ' <?= e(adm('ad', 'pcs')) ?>'; }
          }
        }
      }
    }
  });

  window.switchAdminRevenueChart = function(period, btn) {
    document.querySelectorAll('.admin-revenue-tab-btn').forEach(function(b) {
      b.className = 'admin-revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition';
    });
    btn.className = 'admin-revenue-tab-btn active px-3.5 py-1.5 rounded-lg bg-accent text-ink-900 font-bold shadow-sm transition';

    var targetData = chartData[period];
    if (targetData && adminRevenueChart) {
      adminRevenueChart.data.labels = targetData.labels;
      adminRevenueChart.data.datasets[0].data = targetData.revenue;
      adminRevenueChart.data.datasets[1].data = targetData.count;
      adminRevenueChart.update();
    }
  };
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
