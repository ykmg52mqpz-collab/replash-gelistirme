<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = 'Platform Admin Paneli';
$error = null;
$success = null;

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    header('Location: index.php');
    exit;
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($pdo && replash_attempt_blocked($pdo, 'admin', REPLASH_LOGIN_MAX_FAILS, REPLASH_LOGIN_WINDOW_MIN)) {
        $error = "Çok fazla hatalı giriş denemesi. Lütfen " . REPLASH_LOGIN_WINDOW_MIN . " dakika sonra tekrar deneyin.";
    } elseif ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $admin['name'];
                header('Location: index.php');
                exit;
            } else {
                // Hangi e-postanın kayıtlı olduğu belli edilmez
                replash_attempt_failed($pdo, 'admin');
                $error = "E-posta veya şifre hatalı.";
            }
        } catch (PDOException $e) {
            $error = "Veritabanı Hatası: " . $e->getMessage();
        }
    } else {
        $error = "Veritabanı bağlantısı kurulamadı! " . ($dbError ? "(" . $dbError . ")" : "Lütfen MySQL ayarlarınızı kontrol ediniz.");
    }
}

$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Aylık mutabakat CSV (T20): geçerli satışlar satış anındaki tutarla, kademe ve paylar, iadeler
if ($isAdmin && $pdo && ($_GET['export'] ?? '') === 'statement') {
    $fid = (string)($_GET['facility_id'] ?? '');
    $ym = preg_match('/^\d{4}-\d{2}$/', (string)($_GET['month'] ?? '')) ? $_GET['month'] : date('Y-m');
    $fst = $pdo->prepare("SELECT * FROM facilities WHERE id = ? LIMIT 1");
    $fst->execute([$fid]);
    $fac = $fst->fetch();
    if (!$fac) { http_response_code(404); exit('Tesis bulunamadı'); }
    $st = replash_monthly_statement($pdo, $fid, $ym);
    $price = replash_sale_price_sql('m', 'f.unit_price');
    $rows = $pdo->prepare("SELECT m.date, m.time_slot, m.court, m.sport, m.sold_at, m.status, {$price} AS amount, COALESCE(m.sale_currency, f.currency, 'EUR') AS cur
        FROM matches m JOIN facilities f ON f.id = m.facility_id
        WHERE m.facility_id = ? AND " . replash_valid_sale_sql('m') . " AND DATE_FORMAT(" . replash_sale_date_sql('m') . ", '%Y-%m') = ?
        ORDER BY m.sold_at, m.date, m.time_slot");
    $rows->execute([$fid, $ym]);
    $ref = $pdo->prepare("SELECT se.created_at, se.reason, se.amount, se.currency, m.date, m.time_slot, m.court FROM sale_events se
        LEFT JOIN matches m ON m.id = se.match_id WHERE se.facility_id = ? AND se.action = 'refunded' AND DATE_FORMAT(se.created_at, '%Y-%m') = ? ORDER BY se.created_at");
    $ref->execute([$fid, $ym]);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($fac['name']));
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="replash-mutabakat-' . $slug . '-' . $ym . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // Excel için UTF-8 işareti
    fputcsv($out, ['Tesis', $fac['name'], 'Dönem', $ym, 'Para birimi', $st['currency']], ';');
    fputcsv($out, [], ';');
    fputcsv($out, ['Maç tarihi', 'Saat', 'Saha', 'Spor', 'Satış zamanı', 'Durum', 'Tutar', 'Para birimi'], ';');
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $r) {
        fputcsv($out, [$r['date'], $r['time_slot'], $r['court'], $r['sport'], $r['sold_at'], $r['status'], number_format((float)$r['amount'], 2, ',', ''), $r['cur']], ';');
    }
    fputcsv($out, [], ';');
    fputcsv($out, ['Geçerli satış', $st['valid_sales']], ';');
    fputcsv($out, ['Brüt satış', number_format($st['gross'], 2, ',', '')], ';');
    fputcsv($out, ['Tesis payı %', $st['facility_pct']], ';');
    fputcsv($out, ['Tesis payı', number_format($st['facility_share'], 2, ',', '')], ';');
    fputcsv($out, ['Replash payı', number_format($st['replash_share'], 2, ',', '')], ';');
    $refRows = $ref->fetchAll(PDO::FETCH_ASSOC);
    if ($refRows) {
        fputcsv($out, [], ';');
        fputcsv($out, ['İADELER (bu dönemde, satıştan düşülmüştür)'], ';');
        fputcsv($out, ['İade zamanı', 'Maç tarihi', 'Saat', 'Saha', 'Tutar', 'Para birimi', 'Sebep'], ';');
        foreach ($refRows as $r) {
            fputcsv($out, [$r['created_at'], $r['date'], $r['time_slot'], $r['court'], $r['amount'] !== null ? number_format((float)$r['amount'], 2, ',', '') : '', $r['currency'], $r['reason']], ';');
        }
    }
    fclose($out);
    exit;
}

/**
 * PIN kuralı: 6 hane, başka tesiste kullanılmıyor (giriş PIN özeti üzerinden yapılır).
 * $exceptFacility: düzenlenen tesisin kendisi. Sorun yoksa null döner.
 */
function admin_pin_problem(PDO $pdo, string $pin, ?string $exceptFacility): ?string {
    if (!preg_match('/^\d{6}$/', $pin)) return 'PIN 6 haneli bir sayı olmalı.';
    $st = $pdo->prepare("SELECT id FROM facilities WHERE pin = ? AND id <> ? LIMIT 1");
    $st->execute([replash_pin_hash($pin), (string)$exceptFacility]);
    return $st->fetchColumn() ? 'Bu PIN başka bir tesiste kullanılıyor, farklı bir PIN seçin.' : null;
}

/**
 * Tesis sporları ve spor başına video fiyatı (facilities.sport_prices).
 */
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
      <div class="text-xs font-semibold text-white">Sporlar ve video fiyatları</div>
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
      <p class="text-[11px] text-white/40">Her saha aşağıda seçtiğiniz spora göre bu fiyatla satılır. Varsayılanlar Billing Guide'dan gelir (futbol €15, padel €10).</p>
    </div>
    <?php
}


$facilities = [];
$cameras = [];
$leads = [];
$stats = ['total_matches' => 0, 'sold_matches' => 0, 'total_revenue' => 0, 'active_cameras' => 0];

if ($isAdmin && $pdo) {
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

        $pinErr = admin_pin_problem($pdo, $pin, null);
        if ($pinErr) {
            $error = $pinErr;
        } else {
        $ins = $pdo->prepare("INSERT INTO facilities (id, name, slug, city, sport, courts, pin, unit_price, currency, sport_prices) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$facId, $name, $slug, $city, $primarySport, json_encode($courtObjects, JSON_UNESCAPED_UNICODE), replash_pin_hash($pin), $unitPrice, $currency, json_encode($sportPrices)]);

        // Add cameras for each court
        $insCam = $pdo->prepare("INSERT INTO cameras (id, facility_id, court, name, token, channel_id, stream_url, status, last_seen_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'offline', NULL)");
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

        $success = "Yeni tesis ve sahalara özel canlı yayın linkleri başarıyla kaydedildi.";
        }
    }

    // Update Camera Stream URL
    if (isset($_POST['action']) && $_POST['action'] === 'update_camera_stream') {
        $camId = trim($_POST['camera_id'] ?? '');
        $newStream = trim($_POST['stream_url'] ?? '');
        if (!empty($camId)) {
            $updCam = $pdo->prepare("UPDATE cameras SET stream_url = ? WHERE id = ?");
            $updCam->execute([$newStream, $camId]);
            $success = "Kamera canlı yayın URL'si başarıyla güncellendi.";
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

        $pinErr = ($pin !== '') ? admin_pin_problem($pdo, $pin, $facId) : null;
        if ($pinErr) {
            $error = $pinErr;
        } elseif (!empty($facId) && !empty($name)) {
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

            // PIN alanı boş bırakıldıysa mevcut PIN korunur
            $upd = $pdo->prepare("UPDATE facilities SET name = ?, city = ?, sport = ?, pin = COALESCE(?, pin), unit_price = ?, currency = ?, courts = ?, sport_prices = ? WHERE id = ?");
            $upd->execute([$name, $city, $primarySport, $pin !== '' ? replash_pin_hash($pin) : null, $unitPrice, $currency, json_encode($courtObjects, JSON_UNESCAPED_UNICODE), json_encode($sportPrices), $facId]);

            // Sync cameras
            $pdo->prepare("DELETE FROM cameras WHERE facility_id = ?")->execute([$facId]);

            $insCam = $pdo->prepare("INSERT INTO cameras (id, facility_id, court, name, token, channel_id, stream_url, status, last_seen_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
                    // Gerçek durum korunur: yeni kamera 'henüz sinyal yok' olarak başlar
                    $insCam->execute([$camId, $facId, $c, $camName, $token, $channelId, $cStream, $prev['status'] ?? 'offline', $prev['last_seen_at'] ?? null]);
                    $camNum++;
                }
            }

            $success = "Tesis ve saha kamera canlı yayın URL'leri başarıyla güncellendi.";
        }
    }

    // Delete Facility
    if (isset($_POST['action']) && $_POST['action'] === 'delete_facility') {
        $facId = trim($_POST['facility_id'] ?? '');
        $soldCnt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE facility_id = ? AND sold_at IS NOT NULL");
        $soldCnt->execute([$facId]);
        if ((int)$soldCnt->fetchColumn() > 0) {
            $error = "Bu tesisin satış geçmişi var; muhasebe kaydı kaybolmasın diye silinemez.";
        } elseif (!empty($facId)) {
            $pdo->prepare("DELETE FROM matches WHERE facility_id = ?")->execute([$facId]);
            $pdo->prepare("DELETE FROM cameras WHERE facility_id = ?")->execute([$facId]);
            $pdo->prepare("DELETE FROM refunds WHERE facility_id = ?")->execute([$facId]);
            $pdo->prepare("DELETE FROM facilities WHERE id = ?")->execute([$facId]);
            $success = "Tesis, bağlı kameraları ve tüm kayıtları başarıyla silindi.";
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
        $success = "Arama ve maç bulma formu görünüm ayarları başarıyla güncellendi.";
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
    $selectedFacilityName = 'Tüm Tesisler (Platform Geneli)';
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
    $monthNamesTr = ['01'=>'Ocak','02'=>'Şubat','03'=>'Mart','04'=>'Nisan','05'=>'Mayıs','06'=>'Haziran','07'=>'Temmuz','08'=>'Ağustos','09'=>'Eylül','10'=>'Ekim','11'=>'Kasım','12'=>'Aralık'];

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
}

$internalShell = __('nav.admin');
require_once __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen pb-10 relative">

  <div class="container-x py-10 relative z-10">

    <?php if (!$isAdmin): ?>
      <!-- Yönetim girişi (replash.info AdminApp) -->
      <div class="grid min-h-[60vh] place-items-center">
        <form action="index.php" method="POST" class="w-full max-w-sm rounded-3xl border border-white/10 bg-white/[0.03] p-8"><?= csrf_field() ?>
          <input type="hidden" name="admin_login" value="1">
          <h1 class="font-display text-xl font-bold">Platform Admin</h1>
          <p class="mb-5 mt-1 text-sm text-white/50">Replash sistem yöneticisi hesabınızla giriş yapın.</p>
          <div class="flex flex-col gap-3">
            <input type="email" name="email" autocomplete="username" required placeholder="E-posta" class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm text-white outline-none focus:border-accent">
            <input type="password" name="password" autocomplete="current-password" required placeholder="Şifre" class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm text-white outline-none focus:border-accent">
          </div>
          <?php if (!$pdo): ?>
            <div class="mt-3 rounded-xl border border-red-400/30 bg-red-400/10 p-3 text-sm text-red-200">Veritabanına bağlanılamıyor. Sunucu ayarlarını kontrol edin.</div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="mt-3 rounded-xl border border-red-400/30 bg-red-400/10 p-3 text-sm text-red-200"><?= e($error) ?></div>
          <?php endif; ?>
          <button type="submit" class="mt-4 w-full rounded-xl bg-accent px-6 py-3 text-sm font-bold text-ink-900">Giriş</button>
        </form>
      </div>

    <?php else: ?>
      <?php include __DIR__ . '/../includes/admin_nav.php'; ?>

      <?php if (!empty($error)): ?>
        <div class="mb-6 rounded-2xl border border-red-400/30 bg-red-400/10 p-4 text-sm font-medium text-red-200" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-emerald-300 text-sm font-medium mb-6">
          <?= e($success) ?>
        </div>
      <?php endif; ?>

      <?php if ($adminTab === 'overview'): ?>
      <!-- Facility Filter Toolbar for Analytics -->
      <div class="rounded-2xl bg-white/[0.02] p-4 sm:p-5 border border-white/10 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-semibold uppercase tracking-wider text-accent">Finansal Raporlama & Gelir Analizi</span>
          <h2 class="font-display text-xl font-bold text-white flex items-center gap-2 mt-0.5">
            <span><?= e($selectedFacilityName) ?></span>
            <?php if ($selectedFacilityId !== 'all'): ?>
              <a href="index.php" class="text-xs font-normal text-rose-400 hover:underline bg-rose-500/10 px-2 py-0.5 rounded-md border border-rose-500/20">Filtreyi Kaldır &times;</a>
            <?php endif; ?>
          </h2>
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs text-neutral-400 font-semibold whitespace-nowrap">Tesis Seçin:</label>
          <select 
            onchange="window.location.href = 'index.php?facility_id=' + encodeURIComponent(this.value);" 
            class="rounded-xl border border-white/10 bg-ink-900 px-3.5 py-2 text-xs font-medium text-white focus:border-accent focus:outline-none"
          >
            <option value="all" <?= $selectedFacilityId === 'all' ? 'selected' : '' ?>>Tüm Tesisler (Platform Geneli)</option>
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
            <span class="text-xs font-semibold text-neutral-400">Bugünkü Hasılat</span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-emerald-400"><?= number_format((float)($totals['today_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span>Bugün Satılan:</span>
            <span class="font-bold text-white"><?= (int)($totals['today_sold_count'] ?? 0) ?> Maç</span>
          </div>
        </div>

        <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-xs font-semibold text-neutral-400">Bu Haftaki Hasılat</span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-sky-400"><?= number_format((float)($totals['this_week_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span>Bu Hafta Satılan:</span>
            <span class="font-bold text-white"><?= (int)($totals['this_week_sold_count'] ?? 0) ?> Maç</span>
          </div>
        </div>

        <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-xs font-semibold text-neutral-400">Bu Ayki Hasılat</span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-accent"><?= number_format((float)($totals['this_month_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-neutral-300"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span>Bu Ay Satılan:</span>
            <span class="font-bold text-white"><?= (int)($totals['this_month_sold_count'] ?? 0) ?> Maç</span>
          </div>
        </div>

        <div class="rounded-2xl bg-white/[0.02] p-5 border border-white/10 flex flex-col justify-between relative overflow-hidden">
          <div>
            <span class="text-xs font-semibold text-neutral-400">Toplam Ciro / Ödeme</span>
            <div class="mt-2 flex items-baseline gap-1.5">
              <span class="text-2xl sm:text-3xl font-bold font-display text-white"><?= number_format((float)($totals['total_revenue'] ?? 0), 0, ',', '.') ?></span>
              <span class="text-xs font-semibold text-accent"><?= e($selectedFacilityCurrency) ?></span>
            </div>
          </div>
          <div class="mt-3 text-[11px] text-neutral-400 flex items-center justify-between border-t border-white/5 pt-2">
            <span>Toplam Satılan:</span>
            <span class="font-bold text-white"><?= (int)($totals['sold_matches'] ?? 0) ?> Maç</span>
          </div>
        </div>
      </div>

      <!-- Interactive Graphical Revenue Chart -->
      <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10 mb-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-white/10">
          <div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-accent animate-pulse"></div>
              <h2 class="font-display text-xl font-bold text-white">Ödeme & Satış Dağılım Grafiği</h2>
            </div>
            <p class="text-xs text-neutral-400 mt-0.5">Kapsam: <span class="text-accent font-semibold"><?= e($selectedFacilityName) ?></span></p>
          </div>

          <!-- Chart Period Tabs -->
          <div class="inline-flex rounded-xl bg-ink-900/90 p-1 border border-white/10 text-xs font-semibold">
            <button 
              type="button" 
              onclick="switchAdminRevenueChart('daily', this)" 
              class="admin-revenue-tab-btn active px-3.5 py-1.5 rounded-lg bg-accent text-ink-900 font-bold shadow-sm transition"
            >
              Günlük (14 Gün)
            </button>
            <button 
              type="button" 
              onclick="switchAdminRevenueChart('weekly', this)" 
              class="admin-revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition"
            >
              Haftalık (8 Hafta)
            </button>
            <button 
              type="button" 
              onclick="switchAdminRevenueChart('monthly', this)" 
              class="admin-revenue-tab-btn px-3.5 py-1.5 rounded-lg text-neutral-400 hover:text-white transition"
            >
              Aylık (6 Ay)
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
            <h2 class="font-display text-xl font-bold text-white">Tesis Bazlı Gelir & Performans Tablosu</h2>
            <p class="text-xs text-neutral-400">Geçerli video satışları · aylık kademe tesis toplamına göre (0–49 %20 · 50–99 %25 · 100+ %30) · KDV düşülmeden brüt</p>
          </div>
          <span class="text-xs font-semibold px-3 py-1 rounded-full bg-accent/10 text-accent"><?= count($facilityBreakdown) ?> Tesis Listeleniyor</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
              <tr>
                <th class="px-4 py-3">Tesis Adı & Şehir</th>
                <th class="px-4 py-3">Video Fiyatı</th>
                <th class="px-4 py-3 text-center">Bugünkü Satış</th>
                <th class="px-4 py-3 text-center">Bu Ay Satış & Kademe</th>
                <th class="px-4 py-3 text-right">Bu Ay Tesis Payı</th>
                <th class="px-4 py-3 text-right">Bu Ay Replash Faturası</th>
                <th class="px-4 py-3 text-center">Toplam Satış</th>
                <th class="px-4 py-3 text-right">Toplam Ciro</th>
                <th class="px-4 py-3 text-right">İşlem</th>
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
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-accent text-ink-900">Aktif Filtre</span>
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
                      <?= (int)$fb['month_sold'] ?> ad · %<?= (int)$fbSplit['facility_pct'] ?>
                    </span>
                    <div class="text-[10px] text-neutral-500 mt-1">brüt <?= e(replash_money($fbSplit['gross'], 0)) ?></div>
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
                      <a href="index.php" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-300 text-xs font-semibold hover:bg-rose-500/20 transition">
                        Filtreyi Kaldır
                      </a>
                    <?php else: ?>
                      <a href="index.php?facility_id=<?= urlencode($fb['id']) ?>" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-accent text-ink-900 text-xs font-semibold shadow-glow hover:bg-accent-neon transition">
                        Grafiği İncele &rarr;
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
          <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Toplam Kaydedilen Maç</div>
          <div class="font-display text-3xl font-bold text-white mt-1"><?= $stats['total_matches'] ?></div>
        </div>
        <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10">
          <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Satılan (Açık) Videolar</div>
          <div class="font-display text-3xl font-bold text-accent mt-1"><?= $stats['sold_matches'] ?></div>
        </div>
        <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10">
          <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Aktif Kameralar</div>
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
                <h2 class="font-display text-xl font-bold text-white">Kayıtlı Tesisler</h2>
                <span class="text-xs text-neutral-400"><?= count($facilities) ?> Tesis Aktif</span>
              </div>
              <button onclick="document.getElementById('addFacilityModal').classList.toggle('hidden')" class="px-3 py-1.5 rounded-xl bg-accent text-ink-900 font-semibold text-xs shadow-glow hover:bg-accent-neon transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Yeni Tesis Ekle
              </button>
            </div>

            <!-- Add Facility Form (Hidden by default or toggled) -->
            <div id="addFacilityModal" class="hidden mb-6 p-5 rounded-2xl bg-ink-900 border border-accent/30 shadow-xl">
              <div class="flex items-center justify-between mb-3 pb-2 border-b border-white/10">
                <h3 class="text-sm font-bold text-accent">Yeni Tesis & Canlı Yayın Kameraları Tanımla</h3>
                <span class="text-[11px] text-neutral-400">Her sahaya özel canlı RTSP/HLS linki bağlayın</span>
              </div>

              <form action="index.php?tab=facilities" method="POST" class="space-y-4 text-xs"><?= csrf_field() ?>
                <input type="hidden" name="action" value="add_facility">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-neutral-400 mb-1 font-medium">Tesis Adı</label>
                    <input type="text" name="name" placeholder="Örn: Kadıköy Halısaha & Padel" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                  </div>
                  <div>
                    <label class="block text-neutral-400 mb-1 font-medium">Şehir</label>
                    <input type="text" name="city" placeholder="Örn: İstanbul" required class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                  </div>
                </div>

                <div>
                  <label class="block text-neutral-400 mb-1 font-medium">6 haneli PIN</label>
                  <input type="text" name="pin" maxlength="6" inputmode="numeric" required class="w-40 rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white font-mono focus:border-accent focus:outline-none">
                </div>
                <?php admin_sport_price_fields(['football' => REPLASH_VIDEO_PRICES['football']], 'new'); ?>

                <!-- Dynamic Courts & Multiple Camera Stream URLs -->
                <div class="pt-2 border-t border-white/10 space-y-3">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="font-bold text-white text-xs">Sahalar & Canlı Kamera Yayın Linkleri</label>
                      <p class="text-[11px] text-neutral-400">Her sahaya 1 veya birden fazla kamera açısı ekleyebilirsiniz</p>
                    </div>
                    <button type="button" onclick="addCourtBlock('courtsContainer')" class="px-2.5 py-1 rounded-lg bg-accent/15 border border-accent/30 text-xs text-accent hover:bg-accent hover:text-ink-900 font-bold transition flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      + Saha Ekle
                    </button>
                  </div>

                  <div id="courtsContainer" class="space-y-3">
                    <div class="court-block bg-ink-800/90 p-3.5 rounded-xl border border-white/10 space-y-2.5" data-court-index="0">
                      <div class="flex items-center justify-between pb-1.5 border-b border-white/5">
                        <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-accent"></span>
                          <span class="font-bold text-white text-xs court-title">1. Saha / Kort</span>
                        </div>
                        <button type="button" onclick="removeCourtBlock(this)" class="px-2 py-0.5 rounded text-[11px] bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 font-semibold transition">&times; Sahayı Sil</button>
                      </div>

                      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                          <label class="block text-neutral-400 mb-1 text-[11px] font-medium">Saha Adı</label>
                          <input type="text" name="courts[0][name]" value="Saha 1" placeholder="Örn: Açık Saha, 1. Kort" required class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                        <div>
                          <label class="block text-neutral-400 mb-1 text-[11px] font-medium">Spor Türü</label>
                          <select name="courts[0][sport]" class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                            <option value="football">Futbol (Halı Saha)</option>
                            <option value="padel">Padel</option>
                            <option value="tennis">Tenis</option>
                            <option value="basketball">Basketbol</option>
                          </select>
                        </div>
                      </div>

                      <div class="pt-1.5 space-y-1.5">
                        <div class="flex items-center justify-between">
                          <span class="text-[11px] font-semibold text-neutral-300">Kamera Yayın URL'leri:</span>
                          <button type="button" onclick="addCameraToCourt(this)" class="text-[11px] text-accent hover:underline flex items-center gap-1 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            + Kamera Ekle
                          </button>
                        </div>
                        <div class="cameras-container space-y-1.5">
                          <div class="camera-row flex items-center gap-1.5">
                            <input type="text" name="courts[0][cameras][]" placeholder="1. Kamera Canlı Yayın URL (rtsp://... veya https://.../live.m3u8)" class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
                            <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="Kamerayı Kaldır">&times;</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/10">
                  <button type="button" onclick="document.getElementById('addFacilityModal').classList.add('hidden')" class="px-3.5 py-2 rounded-xl border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10">İptal</button>
                  <button type="submit" class="px-5 py-2 rounded-xl bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow">Tesis & Kameraları Kaydet</button>
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
                        <?= e($f['city']) ?> · PIN: <code class="text-accent font-bold">••••••</code> · <?= e($f['unit_price']) ?> <?= e($f['currency']) ?>
                      </div>
                      <div class="text-[11px] text-neutral-500 mt-1">Sahalar: <?= e($courtsStr) ?></div>
                    </div>
                    
                    <div class="flex items-center gap-2 flex-shrink-0">
                      <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-accent/10 text-accent uppercase"><?= e($f['sport']) ?></span>
                      
                      <!-- Edit Toggle Button -->
                      <button type="button" onclick="document.getElementById('editFacility_<?= e($f['id']) ?>').classList.toggle('hidden')" class="p-1.5 rounded-lg border border-white/10 bg-white/5 text-neutral-300 hover:text-white hover:bg-white/10" title="Tesisi Düzenle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                      </button>

                      <!-- Delete Facility Form -->
                      <form action="index.php?tab=facilities" method="POST" onsubmit="return confirm('<?= e($f['name']) ?> tesisini, bağlı tüm kameraları ve maç kayıtlarını tamamen silmek istediğinize emin misiniz?');" class="inline"><?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_facility">
                        <input type="hidden" name="facility_id" value="<?= e($f['id']) ?>">
                        <button type="submit" class="p-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20" title="Tesisi Sil">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      </form>
                    </div>
                  </div>

                  <!-- Edit Facility Form (Collapsible) -->
                  <div id="editFacility_<?= e($f['id']) ?>" class="hidden pt-3 border-t border-white/10 bg-ink-800/90 p-4 rounded-xl text-xs space-y-3.5">
                    <div class="flex items-center justify-between pb-1 border-b border-white/10">
                      <h4 class="font-bold text-accent">Tesis & Kamera Yayınlarını Düzenle</h4>
                      <span class="text-[11px] text-neutral-400">Her sahaya birden fazla kamera URL'si atayabilirsiniz</span>
                    </div>

                    <form action="index.php?tab=facilities" method="POST" class="space-y-3"><?= csrf_field() ?>
                      <input type="hidden" name="action" value="update_facility">
                      <input type="hidden" name="facility_id" value="<?= e($f['id']) ?>">
                      
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                          <label class="block text-neutral-400 mb-1 font-medium">Tesis Adı</label>
                          <input type="text" name="name" value="<?= e($f['name']) ?>" required class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                        <div>
                          <label class="block text-neutral-400 mb-1 font-medium">Şehir</label>
                          <input type="text" name="city" value="<?= e($f['city']) ?>" required class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                      </div>

                      <div>
                        <label class="block text-neutral-400 mb-1 font-medium">6 haneli PIN</label>
                        <input type="text" name="pin" maxlength="6" inputmode="numeric" value="" placeholder="Değiştirmek için yeni PIN" autocomplete="off" class="w-40 rounded-lg border border-white/10 bg-ink-900 p-2 text-white font-mono text-xs focus:border-accent focus:outline-none">
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
                            <label class="font-bold text-white text-xs">Sahalar & Kamera Canlı Yayın URL'leri</label>
                            <p class="text-[11px] text-neutral-400">Her sahaya özel spor türü, birim fiyat ve kamera açıları tanımlayın</p>
                          </div>
                          <button type="button" onclick="addCourtBlock('editCourtsContainer_<?= e($f['id']) ?>')" class="px-2.5 py-1 rounded-lg bg-accent/15 border border-accent/30 text-xs text-accent hover:bg-accent hover:text-ink-900 font-bold transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            + Saha Ekle
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
                                <button type="button" onclick="removeCourtBlock(this)" class="px-2 py-0.5 rounded text-[11px] bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 font-semibold transition">&times; Sahayı Sil</button>
                              </div>

                              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div>
                                  <label class="block text-neutral-400 mb-1 text-[11px] font-medium">Saha Adı</label>
                                  <input type="text" name="courts[<?= $cIdx ?>][name]" value="<?= e($cName) ?>" placeholder="Saha Adı" required class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white text-xs focus:border-accent focus:outline-none">
                                </div>
                                <div>
                                  <label class="block text-neutral-400 mb-1 text-[11px] font-medium">Spor Türü</label>
                                  <select name="courts[<?= $cIdx ?>][sport]" class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white text-xs focus:border-accent focus:outline-none">
                                    <option value="football" <?= $cSport === 'football' ? 'selected' : '' ?>>Futbol (Halı Saha)</option>
                                    <option value="padel" <?= $cSport === 'padel' ? 'selected' : '' ?>>Padel</option>
                                    <option value="tennis" <?= $cSport === 'tennis' ? 'selected' : '' ?>>Tenis</option>
                                    <option value="basketball" <?= $cSport === 'basketball' ? 'selected' : '' ?>>Basketbol</option>
                                  </select>
                                </div>
                              </div>

                              <div class="pt-1.5 space-y-1.5">
                                <div class="flex items-center justify-between">
                                  <span class="text-[11px] font-semibold text-neutral-300">Kamera Yayın URL'leri:</span>
                                  <button type="button" onclick="addCameraToCourt(this)" class="text-[11px] text-accent hover:underline flex items-center gap-1 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    + Kamera Ekle
                                  </button>
                                </div>
                                <div class="cameras-container space-y-1.5">
                                  <?php foreach ($streams as $sIdx => $sUrl): ?>
                                    <div class="camera-row flex items-center gap-1.5">
                                      <input type="text" name="courts[<?= $cIdx ?>][cameras][]" value="<?= e($sUrl) ?>" placeholder="<?= ($sIdx + 1) ?>. Kamera URL (rtsp://... veya https://...)" class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
                                      <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="Kamerayı Kaldır">&times;</button>
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
                        <button type="button" onclick="document.getElementById('editFacility_<?= e($f['id']) ?>').classList.add('hidden')" class="px-3.5 py-1.5 rounded-lg border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10">Vazgeç</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow">Değişiklikleri Kaydet</button>
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
              <h2 class="font-display text-xl font-bold text-white">Kamera & Canlı Yayınlar</h2>
              <p class="text-xs text-neutral-400">Her sahaya atanmış olan canlı kamera bağlantıları</p>
            </div>
            <span class="text-xs text-neutral-400"><?= count($cameras) ?> Kamera</span>
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
                    <span class="text-[11px] font-semibold text-accent uppercase tracking-wider">Kamera Canlı Yayın URL:</span>
                    <button type="button" onclick="document.getElementById('edit-stream-<?= e($c['id']) ?>').classList.toggle('hidden')" class="text-[11px] text-neutral-400 hover:text-white underline">
                      URL Değiştir
                    </button>
                  </div>
                  <div class="text-xs font-mono text-neutral-300 truncate">
                    <?= !empty($c['stream_url']) ? e($c['stream_url']) : '<span class="text-neutral-500 italic">Yayın URL tanımlanmadı</span>' ?>
                  </div>

                  <!-- Edit Stream URL Form -->
                  <form id="edit-stream-<?= e($c['id']) ?>" action="index.php" method="POST" class="hidden mt-2 pt-2 border-t border-white/10 flex gap-2"><?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_camera_stream">
                    <input type="hidden" name="camera_id" value="<?= e($c['id']) ?>">
                    <input type="text" name="stream_url" value="<?= e($c['stream_url'] ?? '') ?>" placeholder="rtsp://... veya https://..." class="flex-1 rounded-lg border border-white/10 bg-ink-900 px-2 py-1 text-xs text-white font-mono focus:border-accent focus:outline-none">
                    <button type="submit" class="rounded-lg bg-accent px-3 py-1 text-xs font-bold text-ink-900 hover:bg-accent-neon">Kaydet</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <?php endif; ?>

      <?php if ($adminTab === 'settings'): ?>
      <a href="<?= base_url('admin/calculator.php') ?>" class="mb-6 flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] p-5 transition hover:border-accent/40">
        <div>
          <div class="font-semibold text-white">Gelir Hesaplayıcı</div>
          <div class="text-xs text-white/50">Sahalar İçin sayfasındaki hesaplayıcının varsayılanları ve metinleri</div>
        </div>
        <span class="text-accent">→</span>
      </a>
      <!-- Search Form Display Settings -->
      <div class="rounded-2xl bg-white/[0.02] p-6 sm:p-8 border border-white/10 mb-8 bg-ink-800/80">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-white/10">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-accent">Site Görünüm Ayarları</span>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mt-1">Arama & Maç Bulma Formu Yönetimi</h2>
          </div>
          <span class="text-xs text-neutral-400 bg-white/5 border border-white/10 px-3 py-1.5 rounded-full">
            index.php & find.php Form Kontrolü
          </span>
        </div>

        <form action="index.php?tab=settings" method="POST" class="space-y-6"><?= csrf_field() ?>
          <input type="hidden" name="action" value="update_search_form_settings">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Option 1: Facility Search -->
            <label class="relative flex items-start gap-4 p-5 rounded-2xl border transition-all cursor-pointer <?= $searchFacilityEnabled ? 'border-accent/40 bg-accent/[0.04]' : 'border-white/10 bg-white/[0.02]' ?> hover:border-accent/30">
              <input type="checkbox" name="search_by_facility_enabled" value="1" <?= $searchFacilityEnabled ? 'checked' : '' ?> class="mt-1 h-5 w-5 rounded border-white/20 bg-ink-900 text-accent focus:ring-accent focus:ring-offset-ink-900">
              <div class="space-y-1">
                <span class="font-bold text-white text-base block">Tesis, Tarih ve Saat ile Arama</span>
                <p class="text-xs text-neutral-400 leading-relaxed">
                  Ziyaretçilerin tesis adı, tarih ve saat dilimi seçerek maçlarını listelemesine ve bulmasına olanak tanır.
                </p>
              </div>
            </label>

            <!-- Option 2: Code Search -->
            <label class="relative flex items-start gap-4 p-5 rounded-2xl border transition-all cursor-pointer <?= $searchCodeEnabled ? 'border-accent/40 bg-accent/[0.04]' : 'border-white/10 bg-white/[0.02]' ?> hover:border-accent/30">
              <input type="checkbox" name="search_by_code_enabled" value="1" <?= $searchCodeEnabled ? 'checked' : '' ?> class="mt-1 h-5 w-5 rounded border-white/20 bg-ink-900 text-accent focus:ring-accent focus:ring-offset-ink-900">
              <div class="space-y-1">
                <span class="font-bold text-white text-base block">Sadece Kod (PIN / Erişim Kodu) ile Arama</span>
                <p class="text-xs text-neutral-400 leading-relaxed">
                  Oyuncuların doğrudan 8 haneli maç erişim kodunu girerek maçlarına ulaşmasını sağlar.
                </p>
              </div>
            </label>
          </div>

          <div class="rounded-xl border border-white/5 bg-ink-900/60 p-4 text-xs text-neutral-400 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
              <svg class="w-5 h-5 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span><strong>Tasarım Davranışı:</strong> Sadece kod ile arama seçildiğinde ana sayfada (index.php) yatay şık bir arama çubuğu, find.php sayfasında ise merkezi büyük PIN giriş kartı aktif olur.</span>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow whitespace-nowrap text-xs transition">
              Ayarları Kaydet &rarr;
            </button>
          </div>
        </form>
      </div>

      <?php endif; ?>

      <?php if ($adminTab === 'leads'): ?>
      <!-- Leads / Contact Requests -->
      <div class="rounded-2xl bg-white/[0.02] p-6 border border-white/10 mb-10">
        <h2 class="font-display text-xl font-bold text-white mb-4">Gelen Pilot Başvuruları & Talepler</h2>
        <?php if (empty($leads)): ?>
          <p class="text-xs text-neutral-500">Henüz yeni bir başvuru bulunmuyor.</p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
                <tr>
                  <th class="px-4 py-3">Ad / Tesis</th>
                  <th class="px-4 py-3">İletişim</th>
                  <th class="px-4 py-3">Şehir / Spor</th>
                  <th class="px-4 py-3">Mesaj</th>
                  <th class="px-4 py-3">Tarih</th>
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
      <!-- Aylık mutabakat dışa aktarımı -->
      <form method="GET" action="index.php" class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-white/10 bg-white/[0.02] p-4">
        <input type="hidden" name="export" value="statement">
        <div class="mr-auto">
          <div class="text-sm font-semibold text-white">Aylık mutabakat (CSV)</div>
          <div class="text-xs text-white/50">Geçerli satışlar satış anındaki tutarla, kademe, tesis ve Replash payı, iadeler.</div>
        </div>
        <label class="text-xs text-white/60">Tesis
          <select name="facility_id" class="mt-1 block rounded-lg border border-white/10 bg-ink-900 px-2.5 py-1.5 text-sm text-white">
            <?php foreach ($facilities as $fOpt): ?><option value="<?= e($fOpt['id']) ?>"><?= e($fOpt['name']) ?></option><?php endforeach; ?>
          </select>
        </label>
        <label class="text-xs text-white/60">Ay
          <input type="month" name="month" value="<?= date('Y-m') ?>" class="mt-1 block rounded-lg border border-white/10 bg-ink-900 px-2.5 py-1.5 text-sm text-white">
        </label>
        <button type="submit" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-ink-900">CSV indir</button>
      </form>

      <!-- SATIŞ DENETİMİ: satış / geri alma kayıtları (silinmez) -->
      <p class="mb-5 inline-block rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 text-xs text-white/50">
        ⓘ Tesisler satışı yalnızca ilk <?= REPLASH_UNDO_WINDOW_MIN ?> dakikada, video açılmadıysa ve neden yazarak geri alabilir. Daha sonraki düzeltmeler yalnızca buradan (Maçlar sekmesi) yapılır.
      </p>

      <div class="mb-6 overflow-x-auto rounded-2xl border border-white/10">
        <table class="w-full min-w-[640px] text-sm">
          <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
            <tr><th class="px-4 py-3">Tesis</th><th class="px-4 py-3 text-right">Bu ay satış</th><th class="px-4 py-3 text-right">Bu ay geri alma</th><th class="px-4 py-3 text-right">Oran</th></tr>
          </thead>
          <tbody>
            <?php foreach ($facilities as $fac): $u = $undoByFacility[$fac['id']] ?? ['sold' => 0, 'undone' => 0]; $rate = ((int)$u['sold'] > 0) ? round(100 * (int)$u['undone'] / (int)$u['sold']) : 0; ?>
              <tr class="border-b border-white/5 hover:bg-white/[0.02]">
                <td class="px-4 py-2.5"><a class="hover:text-accent" href="?tab=audit&audit_facility=<?= urlencode($fac['id']) ?>"><?= e($fac['name']) ?></a></td>
                <td class="px-4 py-2.5 text-right font-mono"><?= (int)$u['sold'] ?></td>
                <td class="px-4 py-2.5 text-right font-mono <?= (int)$u['undone'] > 0 ? 'text-red-300' : 'text-white/40' ?>"><?= (int)$u['undone'] ?></td>
                <td class="px-4 py-2.5 text-right font-mono <?= $rate >= 10 ? 'text-red-300 font-bold' : 'text-white/60' ?>">%<?= $rate ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-white/10">
        <table class="w-full min-w-[860px] text-sm">
          <thead class="border-b border-white/10 text-left text-[11px] uppercase tracking-wider text-white/40">
            <tr><th class="px-4 py-3">Zaman</th><th class="px-4 py-3">Tesis</th><th class="px-4 py-3">Maç</th><th class="px-4 py-3">İşlem</th><th class="px-4 py-3 text-right">Tutar</th><th class="px-4 py-3">Kim</th><th class="px-4 py-3">Neden</th></tr>
          </thead>
          <tbody>
            <?php if (empty($auditEvents)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-white/40">Kayıt yok.</td></tr>
            <?php endif; ?>
            <?php
              $actionLabels = [
                'sold' => ['Satıldı', 'bg-emerald-400/15 text-emerald-300'],
                'undone' => ['Tesis geri aldı', 'bg-red-400/15 text-red-300'],
                'admin_sold' => ['Yönetim açtı', 'bg-emerald-400/15 text-emerald-300'],
                'admin_undone' => ['Yönetim geri aldı', 'bg-white/10 text-white/70'],
                'admin_deleted' => ['Yönetim sildi', 'bg-white/10 text-white/70'],
                'refunded' => ['İade edildi', 'bg-amber-400/15 text-amber-300'],
              ];
            ?>
            <?php foreach ($auditEvents as $ev): [$al, $ac] = $actionLabels[$ev['action']] ?? [$ev['action'], 'bg-white/10 text-white/70']; ?>
              <tr class="border-b border-white/5 hover:bg-white/[0.02]">
                <td class="px-4 py-2.5 font-mono text-xs text-white/60"><?= e(date('d.m.Y H:i', strtotime($ev['created_at']))) ?></td>
                <td class="px-4 py-2.5"><?= e($ev['facility_name'] ?? $ev['facility_id']) ?></td>
                <td class="px-4 py-2.5 text-xs"><?= e(($ev['court'] ?? '—') . ' · ' . ($ev['date'] ?? '') . ' · ' . ($ev['time_slot'] ?? '')) ?></td>
                <td class="px-4 py-2.5"><span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold <?= $ac ?>"><?= e($al) ?></span></td>
                <td class="px-4 py-2.5 text-right font-mono text-xs text-white/70"><?= isset($ev['amount']) && $ev['amount'] !== null ? e(replash_money((float)$ev['amount'])) : '—' ?></td>
                <td class="px-4 py-2.5 font-mono text-xs text-white/50"><?= e($ev['actor']) ?></td>
                <td class="px-4 py-2.5 text-xs text-white/70"><?= e($ev['reason'] ?? '') ?></td>
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
        <span class="font-bold text-white text-xs court-title">${displayNum}. Saha / Kort</span>
      </div>
      <button type="button" onclick="removeCourtBlock(this)" class="px-2 py-0.5 rounded text-[11px] bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 font-semibold transition">&times; Sahayı Sil</button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
      <div>
        <label class="block text-neutral-400 mb-1 text-[11px] font-medium">Saha Adı</label>
        <input type="text" name="courts[${nextIdx}][name]" value="Saha ${displayNum}" placeholder="Saha Adı" required class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs focus:border-accent focus:outline-none">
      </div>
      <div>
        <label class="block text-neutral-400 mb-1 text-[11px] font-medium">Spor Türü</label>
        <select name="courts[${nextIdx}][sport]" class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs focus:border-accent focus:outline-none">
          <option value="football">Futbol (Halı Saha)</option>
          <option value="padel">Padel</option>
          <option value="tennis">Tenis</option>
          <option value="basketball">Basketbol</option>
        </select>
      </div>
    </div>
    <div class="pt-1.5 space-y-1.5">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-semibold text-neutral-300">Kamera Yayın URL'leri:</span>
        <button type="button" onclick="addCameraToCourt(this)" class="text-[11px] text-accent hover:underline flex items-center gap-1 font-medium">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          + Kamera Ekle
        </button>
      </div>
      <div class="cameras-container space-y-1.5">
        <div class="camera-row flex items-center gap-1.5">
          <input type="text" name="courts[${nextIdx}][cameras][]" placeholder="1. Kamera Canlı Yayın URL (rtsp://... veya https://...)" class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
          <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="Kamerayı Kaldır">&times;</button>
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
    <input type="text" name="courts[${courtIdx}][cameras][]" placeholder="${count}. Kamera Canlı Yayın URL (rtsp://... veya https://...)" class="w-full rounded-lg border border-white/10 ${inputBgClass} p-2 text-white text-xs font-mono focus:border-accent focus:outline-none">
    <button type="button" onclick="removeCameraRow(this)" class="p-2 bg-rose-500/10 text-rose-400 rounded-lg text-xs hover:bg-rose-500/20 font-bold" title="Kamerayı Kaldır">&times;</button>
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
          label: 'Hasılat (' + currency + ')',
          data: chartData.daily.revenue,
          backgroundColor: 'rgba(250, 204, 21, 0.75)',
          hoverBackgroundColor: 'rgba(250, 204, 21, 1)',
          borderRadius: 8,
          borderSkipped: false,
          yAxisID: 'y'
        },
        {
          label: 'Satılan Maç Adedi',
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
                return ' ' + context.dataset.label + ': ' + Number(context.raw).toLocaleString('tr-TR') + ' ' + currency;
              }
              return ' ' + context.dataset.label + ': ' + context.raw + ' Adet';
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
            callback: function(val) { return Number(val).toLocaleString('tr-TR') + ' ' + currency; }
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
            callback: function(val) { return val + ' ad'; }
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
