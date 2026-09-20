<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('find.title');

$code = strtoupper(trim($_GET['code'] ?? ''));
$facilityId = $_GET['facility_id'] ?? '';
$court = $_GET['court'] ?? '';
$date = $_GET['date'] ?? '';
$timeSlot = $_GET['time_slot'] ?? '';

$match = null;
$error = null;
$codeValidated = false;

if ($pdo) {
    try {
        if (!empty($facilityId) && !empty($date)) {
            // 1. User searched by Facility & Date (and optional Time Slot / Court)
            $sql = "SELECT m.*, f.name as facility_name, f.city, f.sport as facility_sport FROM matches m JOIN facilities f ON m.facility_id = f.id WHERE m.facility_id = ? AND m.date = ?";
            $params = [$facilityId, $date];
            if (!empty($court)) {
                $sql .= " AND m.court = ?";
                $params[] = $court;
            }
            if (!empty($timeSlot)) {
                $sql .= " AND m.time_slot = ?";
                $params[] = $timeSlot;
            }
            $sql .= " ORDER BY m.time_slot DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $match = $stmt->fetch();

            if (!$match) {
                $error = __('find.errors.not_found');
            } else {
                // Check 7-day retention expiry
                if (is_match_expired($match['date']) || $match['status'] === 'EXPIRED') {
                    $match = null;
                    $error = __('find.errors.expired', 'Bu maçın 7 günlük saklama süresi dolduğu için video silinmiştir.');
                } elseif ($match['status'] !== 'UNLOCKABLE') {
                    // Stealth mode: Never show details or indicate locked status to public
                    $match = null;
                    $error = __('find.errors.not_found');
                } else {
                    // Match is UNLOCKABLE. If user submitted code, verify it
                    if (!empty($code)) {
                        $matchAccessCode = strtoupper(trim($match['access_code'] ?? ''));
                        if ($matchAccessCode !== '' && hash_equals($matchAccessCode, $code)) {
                            $codeValidated = true;
                        } else {
                            $codeValidated = false;
                            $error = __('find.errors.code_mismatch');
                        }
                    }
                }
            }
        } elseif (!empty($code)) {
            // 2. User searched solely with an access code (direct code search)
            $stmt = $pdo->prepare("SELECT m.*, f.name as facility_name, f.city, f.sport as facility_sport FROM matches m JOIN facilities f ON m.facility_id = f.id WHERE UPPER(m.access_code) = ? LIMIT 1");
            $stmt->execute([$code]);
            $match = $stmt->fetch();

            if (!$match) {
                $error = __('find.errors.invalid_code');
            } else {
                // Check 7-day retention expiry
                if (is_match_expired($match['date']) || $match['status'] === 'EXPIRED') {
                    $match = null;
                    $error = __('find.errors.expired', 'Bu maçın 7 günlük saklama süresi dolduğu için video silinmiştir.');
                } elseif ($match['status'] !== 'UNLOCKABLE') {
                    // Stealth mode: Never show locked match details
                    $match = null;
                    $error = __('find.errors.invalid_code');
                } else {
                    $codeValidated = true;
                    $facilityId = $match['facility_id'];
                    $date = $match['date'];
                    $timeSlot = $match['time_slot'];
                    $court = $match['court'];
                }
            }
        }

        $matchCameras = [];
        if ($match) {
            $camStmt = $pdo->prepare("SELECT * FROM cameras WHERE facility_id = ? AND court = ? ORDER BY id ASC");
            $camStmt->execute([$match['facility_id'], $match['court']]);
            $matchCameras = $camStmt->fetchAll();
        }

        // Fetch facilities for dropdown
        $stmtFac = $pdo->query("SELECT id, name, city, courts, sport FROM facilities ORDER BY name ASC");
        $facilities = $stmtFac->fetchAll();
    } catch (Exception $e) {
        $error = "Hata / Error: " . $e->getMessage();
        $facilities = [];
        $matchCameras = [];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen pt-28 pb-20 relative">
  <div class="hero-glow"></div>
  
  <div class="max-w-5xl mx-auto px-4 sm:px-6 relative z-10">

    <?php 
    $searchFacilityEnabled = (string)get_setting('search_by_facility_enabled', '1') === '1';
    $searchCodeEnabled = (string)get_setting('search_by_code_enabled', '1') === '1';

    $renderSearchSection = function($isSubSection = false) use ($searchFacilityEnabled, $searchCodeEnabled, $code, $facilities, $facilityId, $date, $timeSlot) {
    ?>
      <?php if ($isSubSection): ?>
        <!-- HORIZONTAL QUICK CODE SEARCH BAR (Displayed only under active player) -->
        <div class="card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 bg-ink-900/95 shadow-2xl mb-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-white/10">
            <div>
              <span class="text-xs font-bold uppercase tracking-wider text-accent"><?= e(__('searchSettings.codeOnly.eyebrow', 'KOD İLE HIZLI ERİŞİM')) ?></span>
              <h3 class="font-display text-2xl sm:text-3xl font-bold text-white mt-1"><?= e(__('searchSettings.codeOnly.title', 'Maçını Kod ile Bul')) ?></h3>
            </div>
            <div class="md:max-w-xs text-xs sm:text-sm text-neutral-400">
              <p><?= e(__('searchSettings.codeOnly.desc', 'Saha görevlisinden aldığınız erişim kodunu girerek maçınızı anında izleyin.')) ?></p>
            </div>
          </div>

          <form action="find.php" method="GET" class="flex flex-col sm:flex-row items-center gap-3.5 mt-6">
            <div class="relative w-full flex-1">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-accent">
                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <input 
                type="text" 
                name="code" 
                placeholder="<?= e(__('searchSettings.codeOnly.placeholder', 'Örn: AEXF-BFFG veya 6 Haneli Kod')) ?>" 
                required 
                style="color: #000000 !important;"
                class="w-full rounded-2xl bg-white text-black font-bold text-sm sm:text-base pl-12 pr-4 py-4 focus:outline-none focus:ring-2 focus:ring-accent placeholder:text-neutral-500 placeholder:font-normal uppercase tracking-wider shadow-inner"
              >
            </div>

            <button type="submit" style="color: #000000 !important;" class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-[#FDE68A] hover:bg-accent-neon text-black font-bold text-sm sm:text-base flex items-center justify-center gap-2.5 shadow-glow transition flex-shrink-0">
              <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <span class="text-black font-bold"><?= e(__('searchSettings.codeOnly.button', 'Maçımı Bul & İzle')) ?> &rarr;</span>
            </button>
          </form>

          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mt-5 pt-1 text-xs text-neutral-400">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-accent flex-shrink-0"></span>
              <span><?= e(__('searchSettings.codeOnly.hint', 'Hesap yok, uygulama yok. Sadece kodunuzu girin ve anında izleyin.')) ?></span>
            </div>
            <div class="font-mono text-[11px] text-neutral-500">
              Örnek Kod: <strong class="text-neutral-400">AEXF-BFFG</strong> / <strong class="text-neutral-400">343434</strong>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- STANDALONE / DEFAULT TOP SEARCH AREA (When no match is displayed) -->
        <div class="text-center max-w-2xl mx-auto mb-10">
          <span class="text-xs font-semibold uppercase tracking-wider text-accent">Replash Video Player</span>
          <h1 class="font-display text-4xl sm:text-5xl font-bold text-white mt-2 mb-3">
            <?= e(__('find.title')) ?>
          </h1>
          <p class="text-neutral-400 text-sm sm:text-base">
            <?= e(__('find.subtitle')) ?>
          </p>
        </div>

        <!-- Search / Filter Card -->
        <?php if (!$searchFacilityEnabled && $searchCodeEnabled): ?>
          <!-- CENTERED LARGE PIN / ACCESS CODE CARD -->
          <div class="max-w-md mx-auto card-elevated rounded-3xl p-8 sm:p-10 border border-white/10 bg-ink-900/95 shadow-2xl text-center mb-10">
            <div class="w-16 h-16 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mx-auto mb-6 shadow-[0_0_30px_rgba(253,230,138,0.15)]">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            
            <h2 class="font-display text-2xl sm:text-3xl font-bold text-white mb-2">
              <?= e(__('searchSettings.codeOnly.title')) ?>
            </h2>
            <p class="text-neutral-400 text-xs sm:text-sm mb-6 leading-relaxed">
              <?= e(__('searchSettings.codeOnly.subtitle')) ?>
            </p>

            <form action="find.php" method="GET" class="space-y-4">
              <div>
                <input 
                  type="text" 
                  name="code" 
                  value="<?= e($code) ?>" 
                  required 
                  placeholder="<?= e(__('searchSettings.codeOnly.placeholder')) ?>" 
                  class="w-full rounded-2xl border border-white/15 bg-ink-950 px-4 py-4 text-center font-mono text-xl sm:text-2xl font-bold tracking-[0.25em] text-accent uppercase focus:border-accent focus:outline-none shadow-inner placeholder:normal-case placeholder:font-sans placeholder:tracking-normal placeholder:text-neutral-500 placeholder:text-sm"
                >
              </div>

              <button type="submit" class="w-full rounded-2xl bg-accent py-4 text-base font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition flex items-center justify-center gap-2">
                <span><?= e(__('searchSettings.codeOnly.button')) ?></span>
                <span>&rarr;</span>
              </button>
            </form>

            <div class="mt-6 pt-4 border-t border-white/5 text-[11px] text-neutral-500 font-mono">
              Örnek Kod: <strong class="text-neutral-400">AEXF-BFFG</strong> / <strong class="text-neutral-400">343434</strong>
            </div>
          </div>
        <?php else: ?>
          <div class="card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 mb-10">
            <form action="find.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label class="block text-xs font-medium text-neutral-400 mb-1.5"><?= e(__('find.fields.facility')) ?></label>
                <select name="facility_id" class="w-full rounded-xl border border-white/10 bg-ink-900 px-3.5 py-2.5 text-sm text-neutral-200 focus:border-accent focus:outline-none">
                  <option value=""><?= e(__('find.fields.select_facility')) ?></option>
                  <?php foreach ($facilities as $f): ?>
                    <option value="<?= e($f['id']) ?>" <?= $facilityId === $f['id'] ? 'selected' : '' ?>>
                      <?= e($f['name']) ?> (<?= e($f['city']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block text-xs font-medium text-neutral-400 mb-1.5"><?= e(__('find.fields.date')) ?></label>
                <input type="date" name="date" value="<?= e(!empty($date) ? $date : date('Y-m-d')) ?>" class="w-full rounded-xl border border-white/10 bg-ink-900 px-3.5 py-2.5 text-sm text-neutral-200 focus:border-accent focus:outline-none">
              </div>

              <div>
                <label class="block text-xs font-medium text-neutral-400 mb-1.5"><?= e(__('find.fields.time')) ?></label>
                <select name="time_slot" class="w-full rounded-xl border border-white/10 bg-ink-900 px-3.5 py-2.5 text-sm text-neutral-200 focus:border-accent focus:outline-none">
                  <option value=""><?= e(__('find.fields.all_hours')) ?></option>
                  <option value="17:00–18:00" <?= $timeSlot === '17:00–18:00' ? 'selected' : '' ?>>17:00 – 18:00</option>
                  <option value="18:00–19:00" <?= $timeSlot === '18:00–19:00' ? 'selected' : '' ?>>18:00 – 19:00</option>
                  <option value="19:00–20:00" <?= $timeSlot === '19:00–20:00' ? 'selected' : '' ?>>19:00 – 20:00</option>
                  <option value="20:00–21:00" <?= $timeSlot === '20:00–21:00' ? 'selected' : '' ?>>20:00 – 21:00</option>
                  <option value="21:00–22:00" <?= $timeSlot === '21:00–22:00' ? 'selected' : '' ?>>21:00 – 22:00</option>
                  <option value="22:00–23:00" <?= $timeSlot === '22:00–23:00' ? 'selected' : '' ?>>22:00 – 23:00</option>
                </select>
              </div>

              <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-accent px-4 py-2.5 text-sm font-semibold text-ink-900 shadow-glow hover:bg-accent-neon transition">
                  <?= e(__('find.fields.search_btn')) ?> &rarr;
                </button>
              </div>
            </form>

            <?php if ($searchCodeEnabled): ?>
              <div class="mt-4 pt-4 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-neutral-400">
                <span><?= e(__('find.fields.or_code')) ?></span>
                <form action="find.php" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                  <input type="text" name="code" value="<?= e($code) ?>" placeholder="<?= e(__('find.fields.code_placeholder')) ?>" class="rounded-lg border border-white/10 bg-ink-900 px-3 py-1.5 text-xs text-neutral-200 uppercase tracking-widest focus:border-accent focus:outline-none">
                  <button type="submit" class="rounded-lg bg-accent/20 border border-accent/40 text-accent px-3 py-1.5 font-semibold hover:bg-accent hover:text-ink-900 transition">
                    <?= e(__('find.fields.open_code_btn')) ?> &rarr;
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    <?php
    };
    ?>

    <?php if (!$match): ?>
      <?php $renderSearchSection(false); ?>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-5 text-center text-rose-300 text-sm font-medium mb-8">
        <p><?= e($error) ?></p>
      </div>
    <?php endif; ?>

    <!-- MATCH RESULT & PLAYER CONTAINER -->
    <?php if ($match): ?>
      <div class="card-elevated rounded-3xl overflow-hidden border border-white/10 bg-ink-800/90 shadow-2xl">
        
        <!-- Match Header Info Bar -->
        <div class="p-6 sm:p-8 border-b border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2.5 mb-1.5">
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-accent/10 text-accent border border-accent/20">
                <?= e(strtoupper($match['facility_sport'] ?? $match['sport'])) ?>
              </span>
              <?php if ($codeValidated): ?>
                <span class="text-xs text-neutral-400"><?= e(__('find.status.verified_code')) ?> <strong class="text-accent font-mono tracking-wider"><?= e($match['access_code']) ?></strong></span>
              <?php endif; ?>
            </div>
            <h2 class="font-display text-2xl sm:text-3xl font-bold text-white">
              <?= e($match['facility_name'] ?? 'Replash Facility') ?> · <?= e($match['court']) ?>
            </h2>
            <p class="text-xs sm:text-sm text-neutral-400 mt-1">
              <?= formatDateLocalized($match['date'], $current_lang) ?> · <?= e($match['time_slot']) ?>
            </p>
          </div>

          <div class="flex items-center gap-3">
            <?php if ($match['status'] === 'UNLOCKABLE'): ?>
              <?php if ($codeValidated): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  <?= e(__('find.status.unlocked')) ?>
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-accent/10 text-accent border border-accent/20">
                  <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>
                  <?= e(__('find.status.pending_code')) ?>
                </span>
              <?php endif; ?>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <?= e(__('find.status.locked')) ?>
              </span>
            <?php endif; ?>
            <span class="text-xs text-neutral-400"><?= e($match['views']) ?> <?= e(__('common.views')) ?></span>
          </div>
        </div>

        <!-- Video Screen or Gate Lock Screen -->
        <?php if ($match['status'] === 'UNLOCKABLE' && $codeValidated): ?>
          <?php 
          $cameraList = [];
          $directVideoSrc = '';
          $camTemplate = __('find.camera_select.camera_n', __('camera_select.camera_n', '{n}. Kamera'));
          $camSectionLabel = __('find.camera_select.label', __('camera_select.label', 'Kamera Açısı:'));

          if (!empty($matchCameras)) {
              $camIdx = 1;
              foreach ($matchCameras as $camItem) {
                  $cStream = $camItem['stream_url'] ?: '';
                  $pUrl = buildMatchPlaybackUrl($cStream, $match['date'], $match['time_slot'], (int)($match['duration_sec'] ?? 3600));
                  if (empty($pUrl)) {
                      $pUrl = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
                  }

                  $camDisplayName = str_replace('{n}', (string)$camIdx, $camTemplate);
                  if (str_contains($camDisplayName, '%d')) {
                      $camDisplayName = sprintf($camDisplayName, $camIdx);
                  }

                  $cameraList[] = [
                      'id' => $camItem['id'],
                      'name' => $camDisplayName,
                      'url' => $pUrl,
                      'channel_id' => $camItem['channel_id'] ?? ''
                  ];
                  $camIdx++;
              }
          }

          if (empty($cameraList)) {
              $rawPlayback = !empty($match['playback_url']) ? $match['playback_url'] : '';
              if (empty($rawPlayback) && !empty($match['camera_id']) && $pdo) {
                  $camStmt = $pdo->prepare("SELECT stream_url FROM cameras WHERE id = ? LIMIT 1");
                  $camStmt->execute([$match['camera_id']]);
                  $camStream = $camStmt->fetchColumn();
                  if ($camStream) {
                      $rawPlayback = $camStream;
                  }
              }
              $fallbackUrl = buildMatchPlaybackUrl($rawPlayback, $match['date'], $match['time_slot'], (int)($match['duration_sec'] ?? 3600));
              if (empty($fallbackUrl)) {
                  $fallbackUrl = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
              }

              $defaultCamName = str_replace('{n}', '1', $camTemplate);
              if (str_contains($defaultCamName, '%d')) {
                  $defaultCamName = sprintf($defaultCamName, 1);
              }

              $cameraList[] = [
                  'id' => $match['camera_id'] ?? 'cam-default',
                  'name' => $defaultCamName,
                  'url' => $fallbackUrl,
                  'channel_id' => 'default'
              ];
          }
          $directVideoSrc = $cameraList[0]['url'];
          ?>

          <div class="relative bg-black aspect-video flex items-center justify-center overflow-hidden w-full group">
            <div id="clappr-player" class="w-full h-full relative"></div>

            <!-- Floating Recording Indicator Overlay -->
            <div id="recording-overlay" class="hidden absolute top-4 left-4 z-20 flex items-center gap-2 bg-rose-600/90 border border-rose-400/60 text-white text-xs font-bold px-3.5 py-1.5 rounded-full shadow-2xl backdrop-blur-md animate-pulse">
              <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
              <span><?= e(__('find.tools.recording_active')) ?> — <span id="record-timer" class="font-mono text-accent">00:00</span></span>
            </div>
          </div>

          <!-- Multiple Camera Angles Selection Bar -->
          <?php if (count($cameraList) >= 1): ?>
            <div class="px-6 py-3.5 bg-ink-950/95 border-t border-white/10 flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-accent animate-pulse"></span>
                <span class="text-xs font-bold text-white uppercase tracking-wider"><?= e($camSectionLabel) ?></span>
              </div>
              <div class="flex flex-wrap items-center gap-2" id="camera-angles-bar">
                <?php foreach ($cameraList as $cIdx => $cItem): ?>
                  <button 
                    type="button" 
                    onclick="switchCamera(<?= $cIdx ?>)"
                    id="btn-cam-<?= $cIdx ?>"
                    class="cam-angle-btn inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm <?= $cIdx === 0 ? 'bg-accent text-ink-900 shadow-glow' : 'bg-ink-800 border border-white/10 text-neutral-300 hover:bg-white/10 hover:text-white' ?>"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2v8a2 2 0 002 2z"/></svg>
                    <span><?= e($cItem['name']) ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Video Tools Bar: Precision Clip Trimming, Live Recording & Sharing -->
          <div class="p-6 bg-ink-900/80 border-t border-white/10 space-y-4">
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
              
              <!-- Clip Tool Header Info -->
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-accent/10 border border-accent/30 flex items-center justify-center text-accent flex-shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-semibold text-white flex items-center gap-2">
                    <?= e(__('find.tools.clip_title')) ?>
                    <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-300 border border-rose-500/30">MP4</span>
                  </h4>
                  <p class="text-xs text-neutral-400"><?= e(__('find.tools.clip_desc')) ?></p>
                </div>
              </div>

              <!-- Top Quick Action Buttons -->
              <div class="flex flex-wrap items-center gap-2">
                <!-- Full Match Download Button -->
                <a 
                  id="btn-full-match-download"
                  href="<?= base_url('api/video.php?id=' . urlencode($match['id']) . '&code=' . urlencode($match['access_code']) . (!empty($cameraList[0]['id']) ? '&camera_id=' . urlencode($cameraList[0]['id']) : '') . '&download=1&filename=replash.mp4') ?>" 
                  download="replash.mp4"
                  class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white/10 px-3.5 py-2 text-xs font-semibold text-white hover:bg-white/15 transition shadow-sm"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                  <?= e(__('find.tools.full_match')) ?>
                </a>

                <!-- Share Link Button -->
                <button 
                  type="button" 
                  onclick="navigator.clipboard.writeText(window.location.href); alert(<?= json_encode(__('find.tools.copied')) ?>);"
                  class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-accent px-3.5 py-2 text-xs font-semibold text-ink-900 shadow-glow hover:bg-accent-neon transition"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                  </svg>
                  <?= e(__('find.tools.share')) ?>
                </button>
              </div>

            </div>

            <!-- Precision Trimmer Controls Bar -->
            <div class="p-3.5 rounded-2xl bg-ink-950/70 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-3">
              <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Start Time Range Group -->
                <div class="flex items-center gap-1.5">
                  <span class="text-xs text-neutral-400 font-medium"><?= e(__('find.tools.start_time')) ?>:</span>
                  <input 
                    type="text" 
                    id="clip-start-input" 
                    value="00:00" 
                    placeholder="00:00"
                    class="w-16 rounded-lg border border-white/15 bg-ink-900 px-2 py-1.5 text-center font-mono text-xs font-bold text-accent focus:border-accent focus:outline-none shadow-inner"
                  >
                  <button 
                    type="button" 
                    onclick="setClipStartFromPlayer()" 
                    title="<?= e(__('find.tools.set_start_btn')) ?>"
                    class="px-2.5 py-1.5 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 text-[11px] font-semibold text-neutral-300 transition flex items-center gap-1"
                  >
                    <span><?= e(__('find.tools.set_start_btn')) ?></span>
                  </button>
                </div>

                <span class="text-neutral-500 font-bold hidden sm:inline">→</span>

                <!-- End Time Range Group -->
                <div class="flex items-center gap-1.5">
                  <span class="text-xs text-neutral-400 font-medium"><?= e(__('find.tools.end_time')) ?>:</span>
                  <input 
                    type="text" 
                    id="clip-end-input" 
                    value="00:30" 
                    placeholder="00:30"
                    class="w-16 rounded-lg border border-white/15 bg-ink-900 px-2 py-1.5 text-center font-mono text-xs font-bold text-accent focus:border-accent focus:outline-none shadow-inner"
                  >
                  <button 
                    type="button" 
                    onclick="setClipEndFromPlayer()" 
                    title="<?= e(__('find.tools.set_end_btn')) ?>"
                    class="px-2.5 py-1.5 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 text-[11px] font-semibold text-neutral-300 transition flex items-center gap-1"
                  >
                    <span><?= e(__('find.tools.set_end_btn')) ?></span>
                  </button>
                </div>
              </div>

              <!-- Trimmer Execution Buttons -->
              <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <!-- Precision Cut & Download Button -->
                <button 
                  type="button" 
                  onclick="createClipFromRangeInputs()"
                  class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-accent to-accent-neon px-4 py-2 text-xs font-bold text-ink-900 shadow-glow hover:brightness-110 transition"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/>
                  </svg>
                  <span><?= e(__('find.tools.cut_clip_btn')) ?></span>
                </button>

                <!-- Live Record Toggle Button -->
                <button 
                  type="button" 
                  id="btn-record-clip" 
                  onclick="toggleClipRecording()"
                  class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-500/15 border border-rose-500/40 px-3.5 py-2 text-xs font-bold text-rose-300 hover:bg-rose-500/25 transition shadow-glow"
                >
                  <span class="w-2 h-2 rounded-full bg-rose-500" id="btn-record-dot"></span>
                  <span id="btn-record-text"><?= e(__('find.tools.live_record_btn')) ?></span>
                </button>
              </div>
            </div>

          </div>

          <!-- Clip Output Ready Modal -->
          <div id="clip-modal" class="fixed inset-0 z-50 hidden bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
            <div class="card-elevated rounded-3xl p-6 sm:p-8 border border-white/15 bg-ink-800 max-w-md w-full shadow-2xl relative text-left">
              
              <div class="flex items-center justify-between pb-4 mb-5 border-b border-white/10">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-2xl bg-accent/15 border border-accent/30 text-accent flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  </div>
                  <div>
                    <h3 class="font-display text-lg font-bold text-white"><?= e(__('find.modal.title')) ?></h3>
                    <p class="text-xs text-neutral-400"><?= e(__('find.modal.desc')) ?></p>
                  </div>
                </div>
                <button type="button" onclick="closeClipModal()" class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/10 text-neutral-400 hover:text-white flex items-center justify-center transition">&times;</button>
              </div>

              <!-- Clip Details -->
              <div class="rounded-2xl bg-ink-900/90 border border-white/10 p-4 space-y-2.5 mb-6 text-xs">
                <div class="flex justify-between items-center text-neutral-300">
                  <span class="text-neutral-400"><?= e(__('find.modal.court')) ?></span>
                  <span class="font-semibold text-white"><?= e($match['facility_name'] ?? 'Replash') ?> · <?= e($match['court']) ?></span>
                </div>
                <div class="flex justify-between items-center text-neutral-300">
                  <span class="text-neutral-400"><?= e(__('find.modal.range')) ?></span>
                  <span class="font-mono font-bold text-accent text-sm" id="modal-clip-range">00:00 → 00:00</span>
                </div>
                <div class="flex justify-between items-center text-neutral-300">
                  <span class="text-neutral-400"><?= e(__('find.modal.duration')) ?></span>
                  <span class="font-bold text-emerald-400" id="modal-clip-duration">0 <?= e(__('find.modal.seconds')) ?></span>
                </div>
                <div class="flex justify-between items-center text-neutral-300">
                  <span class="text-neutral-400"><?= e(__('find.modal.format')) ?></span>
                  <span class="font-mono text-neutral-300">MP4 Video</span>
                </div>
              </div>

              <!-- Modal Action Buttons (Side by Side: Download MP4 & Share) -->
              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                  <a 
                    id="modal-download-btn"
                    href="#"
                    download="replash_clip.mp4"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent py-3 px-3 text-xs font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition text-center"
                  >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span><?= e(__('find.modal.download_mp4')) ?></span>
                  </a>

                  <button 
                    type="button" 
                    onclick="shareClipFileOrLink()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-500/40 bg-emerald-500/15 py-3 px-3 text-xs font-bold text-emerald-300 hover:bg-emerald-500/25 transition text-center"
                  >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span><?= e(__('find.modal.share_clip')) ?></span>
                  </button>
                </div>

                <!-- Social Media Direct Share Buttons -->
                <div class="pt-3 border-t border-white/10">
                  <span class="block text-[11px] font-semibold uppercase tracking-wider text-neutral-400 mb-2.5 text-center">
                    <?= e(__('find.modal.share_social_title')) ?>
                  </span>
                  <div class="flex items-center justify-center gap-2.5 sm:gap-3">
                    <!-- WhatsApp -->
                    <button 
                      type="button" 
                      onclick="shareToSocial('whatsapp')" 
                      title="WhatsApp" 
                      class="w-10 h-10 rounded-xl bg-[#25D366]/15 hover:bg-[#25D366]/30 border border-[#25D366]/40 text-[#25D366] flex items-center justify-center transition hover:scale-105"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                      </svg>
                    </button>

                    <!-- Instagram -->
                    <button 
                      type="button" 
                      onclick="shareToSocial('instagram')" 
                      title="Instagram" 
                      class="w-10 h-10 rounded-xl bg-[#E4405F]/15 hover:bg-[#E4405F]/30 border border-[#E4405F]/40 text-[#E4405F] flex items-center justify-center transition hover:scale-105"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                      </svg>
                    </button>

                    <!-- TikTok -->
                    <button 
                      type="button" 
                      onclick="shareToSocial('tiktok')" 
                      title="TikTok" 
                      class="w-10 h-10 rounded-xl bg-cyan-400/15 hover:bg-cyan-400/30 border border-cyan-400/40 text-cyan-300 flex items-center justify-center transition hover:scale-105"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                      </svg>
                    </button>

                    <!-- YouTube -->
                    <button 
                      type="button" 
                      onclick="shareToSocial('youtube')" 
                      title="YouTube" 
                      class="w-10 h-10 rounded-xl bg-[#FF0000]/15 hover:bg-[#FF0000]/30 border border-[#FF0000]/40 text-[#FF0000] flex items-center justify-center transition hover:scale-105"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                      </svg>
                    </button>

                    <!-- X (Twitter) -->
                    <button 
                      type="button" 
                      onclick="shareToSocial('x')" 
                      title="X" 
                      class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center transition hover:scale-105"
                    >
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                      </svg>
                    </button>

                    <!-- Facebook -->
                    <button 
                      type="button" 
                      onclick="shareToSocial('facebook')" 
                      title="Facebook" 
                      class="w-10 h-10 rounded-xl bg-[#1877F2]/15 hover:bg-[#1877F2]/30 border border-[#1877F2]/40 text-[#1877F2] flex items-center justify-center transition hover:scale-105"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </div>

        <?php elseif ($match['status'] === 'UNLOCKABLE' && !$codeValidated): ?>
          <!-- 2. MATCH FOUND BUT ACCESS CODE REQUIRED BEFORE WATCHING -->
          <div class="p-10 sm:p-14 text-center bg-ink-900/95 flex flex-col items-center justify-center">
            <div class="w-16 h-16 rounded-2xl bg-accent/10 border border-accent/30 flex items-center justify-center text-accent mb-4 shadow-glow">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            
            <h3 class="font-display text-2xl font-bold text-white mb-2"><?= e(__('find.gate.title')) ?></h3>
            <p class="text-neutral-300 max-w-md text-sm mb-6">
              <?= e(__('find.gate.desc')) ?>
            </p>

            <form action="find.php" method="GET" class="w-full max-w-sm space-y-3">
              <input type="hidden" name="facility_id" value="<?= e($match['facility_id']) ?>">
              <input type="hidden" name="date" value="<?= e($match['date']) ?>">
              <input type="hidden" name="time_slot" value="<?= e($match['time_slot']) ?>">
              <?php if (!empty($court)): ?>
                <input type="hidden" name="court" value="<?= e($court) ?>">
              <?php endif; ?>

              <div>
                <input 
                  type="text" 
                  name="code" 
                  placeholder="<?= e(__('find.gate.placeholder')) ?>" 
                  required 
                  autofocus
                  class="w-full text-center tracking-[0.25em] font-mono font-bold text-lg py-3 px-4 rounded-xl border border-white/10 bg-ink-800 text-accent uppercase focus:border-accent focus:outline-none shadow-inner"
                >
              </div>

              <button type="submit" class="w-full rounded-xl bg-accent py-3 text-sm font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition">
                <?= e(__('find.gate.verify_btn')) ?> &rarr;
              </button>
            </form>
          </div>
        <?php endif; ?>

      </div>

      <!-- SEARCH AREA UNDER THE PLAYER -->
      <div class="mt-14 pt-10 border-t border-white/10">
        <?php $renderSearchSection(true); ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php if ($match && $match['status'] === 'UNLOCKABLE' && $codeValidated): ?>
<!-- Clappr Video Player Integration & Multi-Camera Switcher & Clip Recording -->
<script src="https://cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js"></script>
<script>
var I18N = {
  btnStart: <?= json_encode(__('find.tools.live_record_btn', '🔴 Canlı Kayıt')) ?>,
  btnStop: <?= json_encode(__('find.tools.btn_stop')) ?>,
  tooShort: <?= json_encode(__('find.errors.too_short')) ?>,
  secondsUnit: <?= json_encode(__('find.modal.seconds')) ?>,
  copied: <?= json_encode(__('find.tools.copied')) ?>,
  preparingFile: <?= json_encode(__('find.modal.preparing_file')) ?>,
  fileShareNotSupported: <?= json_encode(__('find.modal.file_share_not_supported')) ?>
};

var cameraList = <?= json_encode($cameraList) ?>;
var activeCameraIndex = 0;
var clapprPlayer = null;
var isRecording = false;
var recordStartTime = 0;
var recordTimerInterval = null;
var currentClip = null;

function formatTime(s) {
  s = Math.max(0, Math.floor(s));
  var m = Math.floor(s / 60);
  var sec = s % 60;
  return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
}

function parseTimeToSec(tStr) {
  if (!tStr) return 0;
  if (typeof tStr === 'number') return tStr;
  var parts = String(tStr).trim().split(':');
  if (parts.length === 3) {
    return (parseInt(parts[0], 10) * 3600) + (parseInt(parts[1], 10) * 60) + parseInt(parts[2], 10);
  } else if (parts.length === 2) {
    return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
  } else if (parts.length === 1) {
    return parseInt(parts[0], 10) || 0;
  }
  return 0;
}

function setClipStartFromPlayer() {
  if (!clapprPlayer) return;
  var cur = 0;
  try { cur = clapprPlayer.getCurrentTime() || 0; } catch(e){}
  var input = document.getElementById('clip-start-input');
  if (input) input.value = formatTime(cur);
}

function setClipEndFromPlayer() {
  if (!clapprPlayer) return;
  var cur = 0;
  try { cur = clapprPlayer.getCurrentTime() || 0; } catch(e){}
  var input = document.getElementById('clip-end-input');
  if (input) input.value = formatTime(cur);
}

function createClipFromRangeInputs() {
  var sVal = document.getElementById('clip-start-input') ? document.getElementById('clip-start-input').value : '00:00';
  var eVal = document.getElementById('clip-end-input') ? document.getElementById('clip-end-input').value : '00:30';

  var startSec = parseTimeToSec(sVal);
  var endSec = parseTimeToSec(eVal);

  if (endSec <= startSec) {
    alert("Bitiş zamanı başlangıç zamanından büyük olmalıdır! / End time must be greater than start time.");
    return;
  }

  var duration = endSec - startSec;
  if (duration < 2) {
    alert(I18N.tooShort);
    return;
  }

  currentClip = {
    start: startSec,
    end: endSec,
    duration: duration,
    cameraId: (cameraList[activeCameraIndex] && cameraList[activeCameraIndex].id) ? cameraList[activeCameraIndex].id : ''
  };

  openClipModal(currentClip);
}

function updateFullMatchDownloadLink() {
  var activeCam = cameraList[activeCameraIndex];
  var camId = (activeCam && activeCam.id) ? activeCam.id : '';
  var el = document.getElementById('btn-full-match-download');
  if (el) {
    var matchId = <?= json_encode($match['id'] ?? '') ?>;
    var accessCode = <?= json_encode($match['access_code'] ?? '') ?>;
    el.href = "<?= base_url('api/video.php') ?>?id=" + encodeURIComponent(matchId) + "&code=" + encodeURIComponent(accessCode) + (camId ? "&camera_id=" + encodeURIComponent(camId) : "") + "&download=1&filename=replash.mp4";
  }
}

function switchCamera(index) {
  if (!clapprPlayer || !cameraList[index]) return;
  if (activeCameraIndex === index) return;

  var curTime = 0;
  var isPlaying = true;
  try {
    curTime = clapprPlayer.getCurrentTime() || 0;
    isPlaying = clapprPlayer.isPlaying();
  } catch(e) {}

  activeCameraIndex = index;
  var targetCam = cameraList[index];

  // Update button active styles
  document.querySelectorAll('.cam-angle-btn').forEach(function(btn, i) {
    if (i === index) {
      btn.className = 'cam-angle-btn inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm bg-accent text-ink-900 shadow-glow';
    } else {
      btn.className = 'cam-angle-btn inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm bg-ink-800 border border-white/10 text-neutral-300 hover:bg-white/10 hover:text-white';
    }
  });

  updateFullMatchDownloadLink();

  // Load new camera stream and seek to saved position
  var mimeType = targetCam.url.indexOf('.m3u8') !== -1 ? 'application/x-mpegURL' : 'video/mp4';
  clapprPlayer.load(targetCam.url, mimeType);
  
  if (curTime > 0) {
    setTimeout(function() {
      try {
        clapprPlayer.seek(curTime);
        if (isPlaying) {
          clapprPlayer.play();
        }
      } catch(e) {}
    }, 350);
  }
}

function toggleClipRecording() {
  if (!clapprPlayer) return;
  if (!isRecording) {
    startClipRecording();
  } else {
    stopClipRecording();
  }
}

function startClipRecording() {
  var cur = 0;
  try {
    cur = clapprPlayer.getCurrentTime() || 0;
  } catch(e) {
    cur = 0;
  }
  recordStartTime = Math.max(0, Math.floor(cur));
  isRecording = true;

  try {
    if (!clapprPlayer.isPlaying()) {
      clapprPlayer.play();
    }
  } catch(e){}

  var overlay = document.getElementById('recording-overlay');
  if (overlay) overlay.classList.remove('hidden');

  var btn = document.getElementById('btn-record-clip');
  var btnDot = document.getElementById('btn-record-dot');
  var btnText = document.getElementById('btn-record-text');
  var timerEl = document.getElementById('record-timer');

  if (btn) {
    btn.className = "inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 border border-rose-400 px-4 py-2.5 text-xs font-bold text-white shadow-glow";
  }
  if (btnDot) {
    btnDot.className = "w-2.5 h-2.5 rounded-full bg-white animate-ping";
  }

  var elapsedSec = 0;
  if (timerEl) timerEl.textContent = formatTime(elapsedSec);
  if (btnText) btnText.textContent = I18N.btnStop + " (" + formatTime(elapsedSec) + ")";

  clearInterval(recordTimerInterval);
  recordTimerInterval = setInterval(function() {
    elapsedSec++;
    if (timerEl) timerEl.textContent = formatTime(elapsedSec);
    if (btnText) btnText.textContent = I18N.btnStop + " (" + formatTime(elapsedSec) + ")";

    if (elapsedSec >= 600) {
      stopClipRecording();
    }
  }, 1000);
}

function stopClipRecording() {
  if (!isRecording) return;
  clearInterval(recordTimerInterval);
  isRecording = false;

  var cur = 0;
  try {
    cur = clapprPlayer.getCurrentTime() || 0;
  } catch(e) {
    cur = recordStartTime + 10;
  }
  var recordEndTime = Math.max(recordStartTime + 1, Math.floor(cur));
  var duration = recordEndTime - recordStartTime;

  var overlay = document.getElementById('recording-overlay');
  if (overlay) overlay.classList.add('hidden');

  var btn = document.getElementById('btn-record-clip');
  var btnDot = document.getElementById('btn-record-dot');
  var btnText = document.getElementById('btn-record-text');

  if (btn) {
    btn.className = "inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-500/15 border border-rose-500/40 px-3.5 py-2 text-xs font-bold text-rose-300 hover:bg-rose-500/25 transition shadow-glow";
  }
  if (btnDot) {
    btnDot.className = "w-2 h-2 rounded-full bg-rose-500";
  }
  if (btnText) {
    btnText.textContent = I18N.btnStart;
  }

  if (duration < 2) {
    alert(I18N.tooShort);
    return;
  }

  currentClip = {
    start: recordStartTime,
    end: recordEndTime,
    duration: duration,
    cameraId: (cameraList[activeCameraIndex] && cameraList[activeCameraIndex].id) ? cameraList[activeCameraIndex].id : ''
  };

  openClipModal(currentClip);
}

function openClipModal(clip) {
  var modal = document.getElementById('clip-modal');
  var rangeEl = document.getElementById('modal-clip-range');
  var durEl = document.getElementById('modal-clip-duration');
  var dlBtn = document.getElementById('modal-download-btn');

  if (rangeEl) rangeEl.textContent = formatTime(clip.start) + " → " + formatTime(clip.end);
  if (durEl) durEl.textContent = clip.duration + " " + I18N.secondsUnit;

  var matchId = <?= json_encode($match['id'] ?? '') ?>;
  var accessCode = <?= json_encode($match['access_code'] ?? '') ?>;
  var camId = clip.cameraId || ((cameraList[activeCameraIndex] && cameraList[activeCameraIndex].id) ? cameraList[activeCameraIndex].id : '');
  var filename = "replash_klip_" + formatTime(clip.start).replace(':', '') + "_" + clip.duration + "s.mp4";
  var dlUrl = "<?= base_url('api/video.php') ?>?id=" + encodeURIComponent(matchId) + "&code=" + encodeURIComponent(accessCode) + (camId ? "&camera_id=" + encodeURIComponent(camId) : "") + "&start_offset=" + clip.start + "&duration=" + clip.duration + "&download=1&filename=" + encodeURIComponent(filename);

  if (dlBtn) {
    dlBtn.href = dlUrl;
    dlBtn.setAttribute('download', filename);
  }

  if (modal) modal.classList.remove('hidden');
}

function closeClipModal() {
  var modal = document.getElementById('clip-modal');
  if (modal) modal.classList.add('hidden');
}

// Helper to fetch the clip video file as a Blob/File
async function getClipVideoFile() {
  if (!currentClip) return null;
  var matchId = <?= json_encode($match['id'] ?? '') ?>;
  var accessCode = <?= json_encode($match['access_code'] ?? '') ?>;
  var camId = currentClip.cameraId || ((cameraList[activeCameraIndex] && cameraList[activeCameraIndex].id) ? cameraList[activeCameraIndex].id : '');
  var filename = "replash_klip_" + formatTime(currentClip.start).replace(':', '') + "_" + currentClip.duration + "s.mp4";
  var dlUrl = "<?= base_url('api/video.php') ?>?id=" + encodeURIComponent(matchId) + "&code=" + encodeURIComponent(accessCode) + (camId ? "&camera_id=" + encodeURIComponent(camId) : "") + "&start_offset=" + currentClip.start + "&duration=" + currentClip.duration + "&download=1&filename=" + encodeURIComponent(filename);

  try {
    var response = await fetch(dlUrl);
    if (!response.ok) throw new Error("Video fetch failed");
    var blob = await response.blob();
    return new File([blob], filename, { type: 'video/mp4' });
  } catch (err) {
    console.warn("Could not get clip as file:", err);
    return null;
  }
}

// Share clip as actual video file via Web Share API level 2
async function shareClipFileOrLink() {
  if (!currentClip) return;
  var accessCode = <?= json_encode($match['access_code'] ?? '') ?>;
  var clipUrl = window.location.origin + window.location.pathname + "?code=" + encodeURIComponent(accessCode) + "&t=" + currentClip.start;
  var title = "Replash Clip (" + formatTime(currentClip.start) + " - " + formatTime(currentClip.end) + ")";

  // Try file sharing first if supported
  if (navigator.canShare) {
    try {
      var file = await getClipVideoFile();
      if (file && navigator.canShare({ files: [file] })) {
        await navigator.share({
          title: title,
          text: title + " " + clipUrl,
          files: [file]
        });
        return;
      }
    } catch (e) {
      if (e.name === 'AbortError') return;
      console.log("File share fallback:", e);
    }
  }

  // Fallback: standard navigator.share or clipboard
  if (navigator.share) {
    navigator.share({
      title: title,
      text: title,
      url: clipUrl
    }).catch(function(){});
  } else {
    navigator.clipboard.writeText(clipUrl);
    alert(I18N.copied);
  }
}

// Direct Social Sharing (WhatsApp, Instagram, TikTok, YouTube, X, Facebook)
async function shareToSocial(platform) {
  if (!currentClip) return;
  var accessCode = <?= json_encode($match['access_code'] ?? '') ?>;
  var clipUrl = window.location.origin + window.location.pathname + "?code=" + encodeURIComponent(accessCode) + "&t=" + currentClip.start;
  var shareText = "Replash Maç Klibim (" + formatTime(currentClip.start) + " - " + formatTime(currentClip.end) + ")";
  var fullText = shareText + "\n" + clipUrl;

  // 1. If Web Share API supports file sharing, invoke it directly
  if (navigator.canShare) {
    try {
      var file = await getClipVideoFile();
      if (file && navigator.canShare({ files: [file] })) {
        await navigator.share({
          title: shareText,
          text: fullText,
          files: [file]
        });
        return;
      }
    } catch (e) {
      if (e.name === 'AbortError') return;
      console.log("Social share file fallback:", e);
    }
  }

  // 2. Platform-specific fallbacks: Trigger file download so user has the MP4, then open target platform
  var matchId = <?= json_encode($match['id'] ?? '') ?>;
  var camId = currentClip.cameraId || ((cameraList[activeCameraIndex] && cameraList[activeCameraIndex].id) ? cameraList[activeCameraIndex].id : '');
  var filename = "replash_klip_" + formatTime(currentClip.start).replace(':', '') + "_" + currentClip.duration + "s.mp4";
  var dlUrl = "<?= base_url('api/video.php') ?>?id=" + encodeURIComponent(matchId) + "&code=" + encodeURIComponent(accessCode) + (camId ? "&camera_id=" + encodeURIComponent(camId) : "") + "&start_offset=" + currentClip.start + "&duration=" + currentClip.duration + "&download=1&filename=" + encodeURIComponent(filename);

  // Auto-download file for platforms requiring file upload (Instagram, TikTok, YouTube)
  var triggerDownload = function() {
    var a = document.createElement('a');
    a.href = dlUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  };

  switch (platform) {
    case 'whatsapp':
      var waUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(fullText);
      window.open(waUrl, '_blank');
      break;

    case 'x':
      var xUrl = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(shareText) + "&url=" + encodeURIComponent(clipUrl);
      window.open(xUrl, '_blank');
      break;

    case 'facebook':
      var fbUrl = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(clipUrl);
      window.open(fbUrl, '_blank');
      break;

    case 'instagram':
      triggerDownload();
      alert(I18N.fileShareNotSupported);
      window.open("https://www.instagram.com/", '_blank');
      break;

    case 'tiktok':
      triggerDownload();
      alert(I18N.fileShareNotSupported);
      window.open("https://www.tiktok.com/upload", '_blank');
      break;

    case 'youtube':
      triggerDownload();
      alert(I18N.fileShareNotSupported);
      window.open("https://studio.youtube.com/", '_blank');
      break;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var container = document.getElementById('clappr-player');
  if (container) {
    var videoSrc = <?= json_encode($directVideoSrc) ?>;
    var posterImg = <?= json_encode(!empty($match['thumbnail_url']) ? $match['thumbnail_url'] : asset('images/gen_hero_main.jpg')) ?>;
    var mimeType = videoSrc.indexOf('.m3u8') !== -1 ? 'application/x-mpegURL' : 'video/mp4';

    clapprPlayer = new Clappr.Player({
      source: videoSrc,
      mimeType: mimeType,
      parentId: '#clappr-player',
      poster: posterImg,
      width: '100%',
      height: '100%',
      autoPlay: true,
      playback: {
        playInline: true,
        recycleVideo: true,
        controls: false,
        preload: 'auto'
      },
      mediacontrol: {
        seekbar: "#FDE68A",
        buttons: "#FFFFFF"
      }
    });

    // Auto-seek if timestamp parameter 't' is present
    var urlParams = new URLSearchParams(window.location.search);
    var seekTime = parseFloat(urlParams.get('t'));
    if (!isNaN(seekTime) && seekTime > 0) {
      setTimeout(function() {
        try { clapprPlayer.seek(seekTime); } catch(e){}
      }, 1200);
    }
  }
});
</script>
<style>
#clappr-player,
#clappr-player [data-player],
#clappr-player .container[data-container],
#clappr-player video {
  width: 100% !important;
  height: 100% !important;
  max-width: 100% !important;
  object-fit: contain !important;
}
/* Fix Tailwind reset (svg display:block) affecting Clappr center alignment */
#clappr-player svg {
  display: inline-block !important;
  vertical-align: middle !important;
}
</style>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
