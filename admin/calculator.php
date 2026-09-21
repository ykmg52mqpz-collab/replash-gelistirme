<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = 'Gelir Hesaplayıcı Ayarları · Admin';
$error = null;
$success = null;

// Auth check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$config = get_roi_calculator_config();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Save Full Calculator Settings
    if ($action === 'save_calculator') {
        $submittedSports = $_POST['sports'] ?? [];
        $newSports = [];

        foreach ($submittedSports as $sKey => $sData) {
            $keyClean = strtolower(preg_replace('/[^a-z0-9_-]/i', '', $sKey));
            if (empty($keyClean)) continue;

            $newSports[$keyClean] = [
                'id' => $keyClean,
                'name' => trim($sData['name'] ?? ucfirst($keyClean)),
                'price' => (float)($sData['price'] ?? 8.00),
                'share_pct' => (float)($sData['share_pct'] ?? 15.0),
                'currency' => trim($sData['currency'] ?? 'EUR'),
                'currency_symbol' => trim($sData['currency_symbol'] ?? '€'),
                'courts_default' => (int)($sData['courts_default'] ?? 4),
                'courts_min' => (int)($sData['courts_min'] ?? 1),
                'courts_max' => (int)($sData['courts_max'] ?? 12),
                'videos_default' => (int)($sData['videos_default'] ?? 4),
                'videos_min' => (int)($sData['videos_min'] ?? 1),
                'videos_max' => (int)($sData['videos_max'] ?? 15),
                'days_default' => (int)($sData['days_default'] ?? 26),
                'days_min' => (int)($sData['days_min'] ?? 20),
                'days_max' => (int)($sData['days_max'] ?? 30),
                'active' => isset($sData['active']) ? 1 : 0
            ];
        }

        $defaultSport = $_POST['default_sport'] ?? 'padel';
        if (!isset($newSports[$defaultSport])) {
            $sportKeys = array_keys($newSports);
            $defaultSport = $sportKeys[0] ?? 'padel';
        }

        $config = [
            'default_sport' => $defaultSport,
            'disclaimer_note' => trim($_POST['disclaimer_note'] ?? ''),
            'hook_title' => trim($_POST['hook_title'] ?? ''),
            'hook_desc' => trim($_POST['hook_desc'] ?? ''),
            'hook_cta_text' => trim($_POST['hook_cta_text'] ?? ''),
            'hook_cta_url' => trim($_POST['hook_cta_url'] ?? ''),
            'sports' => $newSports
        ];

        if (set_setting('roi_calculator_config', $config)) {
            $success = "Gelir hesaplayıcı parametreleri ve spor modelleri başarıyla güncellendi!";
        } else {
            $error = "Ayarlar kaydedilirken bir hata oluştu. Veritabanı bağlantısını kontrol edin.";
        }
    }

    // 2. Add New Sport
    if ($action === 'add_sport') {
        $sportName = trim($_POST['new_sport_name'] ?? '');
        $sportKey = strtolower(trim($_POST['new_sport_key'] ?? ''));
        if (empty($sportKey)) {
            $sportKey = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $sportName));
        }

        if (!empty($sportKey) && !empty($sportName)) {
            $config['sports'][$sportKey] = [
                'id' => $sportKey,
                'name' => $sportName,
                'price' => (float)($_POST['new_sport_price'] ?? 10.00),
                'share_pct' => (float)($_POST['new_sport_share_pct'] ?? 15.0),
                'currency' => trim($_POST['new_sport_currency'] ?? 'EUR'),
                'currency_symbol' => trim($_POST['new_sport_currency_symbol'] ?? '€'),
                'courts_default' => (int)($_POST['new_sport_courts_default'] ?? 4),
                'courts_min' => (int)($_POST['new_sport_courts_min'] ?? 1),
                'courts_max' => (int)($_POST['new_sport_courts_max'] ?? 12),
                'videos_default' => (int)($_POST['new_sport_videos_default'] ?? 4),
                'videos_min' => (int)($_POST['new_sport_videos_min'] ?? 1),
                'videos_max' => (int)($_POST['new_sport_videos_max'] ?? 15),
                'days_default' => (int)($_POST['new_sport_days_default'] ?? 26),
                'days_min' => (int)($_POST['new_sport_days_min'] ?? 20),
                'days_max' => (int)($_POST['new_sport_days_max'] ?? 30),
                'active' => 1
            ];

            set_setting('roi_calculator_config', $config);
            $success = "Yeni spor türü '{$sportName}' başarıyla eklendi!";
        } else {
            $error = "Spor adı ve anahtarı boş bırakılamaz.";
        }
    }

    // 3. Delete Sport
    if ($action === 'delete_sport') {
        $sportKey = $_POST['sport_key'] ?? '';
        if (isset($config['sports'][$sportKey])) {
            $deletedName = $config['sports'][$sportKey]['name'];
            unset($config['sports'][$sportKey]);
            if ($config['default_sport'] === $sportKey) {
                $keys = array_keys($config['sports']);
                $config['default_sport'] = $keys[0] ?? 'padel';
            }
            set_setting('roi_calculator_config', $config);
            $success = "'{$deletedName}' spor modeli başarıyla silindi.";
        }
    }

    // 4. Reset Defaults
    if ($action === 'reset_defaults') {
        $config = get_default_roi_calculator_config();
        set_setting('roi_calculator_config', $config);
        $success = "Gelir hesaplayıcı ayarları varsayılan fabrika ayarlarına sıfırlandı.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen pt-28 pb-20 relative">
  <div class="hero-glow"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">

    <!-- Top Breadcrumb & Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-white/10">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <a href="<?= base_url('admin/index.php') ?>" class="text-xs font-semibold text-accent hover:underline flex items-center gap-1">
            &larr; Admin Dashboard
          </a>
          <span class="text-neutral-500">/</span>
          <span class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Gelir Hesaplayıcı</span>
        </div>
        <h1 class="font-display text-3xl font-bold text-white">Gelir Hesaplayıcı (ROI) Yönetimi</h1>
        <p class="text-xs text-neutral-400 mt-1">Tesisler için web sitesinde gösterilen kazanç simülatörünün spor türleri, birim fiyatları, pay yüzdeleri ve varsayılan değerleri</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <a href="<?= base_url('for-facilities.php#calculator') ?>" target="_blank" class="px-3.5 py-2 rounded-xl border border-accent/40 bg-accent/15 text-accent font-semibold text-xs shadow-glow hover:bg-accent/25 transition flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          Sitede Görüntüle
        </a>
        <a href="<?= base_url('admin/matches.php') ?>" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-medium text-neutral-300 hover:text-white transition">
          Satılan Maçlar
        </a>
        <a href="<?= base_url('admin/index.php') ?>" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-medium text-neutral-300 hover:text-white transition">
          Dashboard
        </a>
        <a href="<?= base_url('admin/index.php?action=logout') ?>" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-medium text-rose-300 hover:bg-rose-500/10 transition">
          Çıkış
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
        <div class="w-8 h-8 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div><?= $error ?></div>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

      <!-- Left Column: Form & Sports Settings (7 cols) -->
      <div class="lg:col-span-7 space-y-6">

        <form action="calculator.php" method="POST" id="calculatorSettingsForm" class="space-y-6">
          <input type="hidden" name="action" value="save_calculator">

          <!-- Sports & Models Header Card -->
          <div class="card-elevated rounded-3xl p-6 border border-white/10 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-white/10">
              <div>
                <h2 class="font-display text-xl font-bold text-white flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-accent animate-pulse"></span>
                  Spor Modelleri & Kazanç Parametreleri
                </h2>
                <p class="text-xs text-neutral-400 mt-0.5">Her spor türü için video satış fiyatı, tesis payı oranı ve slider sınırları</p>
              </div>

              <button 
                type="button" 
                onclick="document.getElementById('addSportModal').classList.toggle('hidden')" 
                class="px-3 py-1.5 rounded-xl bg-accent text-ink-900 font-bold text-xs shadow-glow hover:bg-accent-neon transition flex items-center gap-1.5 self-start sm:self-auto"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Yeni Spor Ekle
              </button>
            </div>

            <!-- List of Sports Cards -->
            <div class="space-y-5">
              <?php foreach ($config['sports'] as $key => $sport): 
                // Billing Guide: facility earns 20–30% of the price depending on the monthly tier
                $tiersAll = replash_share_tiers();
                $unitRevenueMin = (float)$sport['price'] * $tiersAll[0]['facility_pct'] / 100;
                $unitRevenueMax = (float)$sport['price'] * end($tiersAll)['facility_pct'] / 100;
                $isStandardPrice = isset(REPLASH_VIDEO_PRICES[strtolower($sport['id'] ?? $key)]);
              ?>
                <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-5 hover:border-accent/30 transition-all space-y-4">
                  <!-- Sport Header -->
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-xl bg-accent/10 border border-accent/30 text-accent flex items-center justify-center font-bold text-sm uppercase">
                        <?= strtoupper(substr($sport['name'], 0, 2)) ?>
                      </div>
                      <div>
                        <div class="flex items-center gap-2">
                          <input type="text" name="sports[<?= e($key) ?>][name]" value="<?= e($sport['name']) ?>" required class="font-bold text-white text-base bg-ink-900 border border-white/10 rounded-lg px-2.5 py-1 focus:border-accent focus:outline-none w-36 sm:w-44">
                          <span class="text-[11px] font-mono text-neutral-400">(key: <?= e($key) ?>)</span>
                        </div>
                      </div>
                    </div>

                    <div class="flex items-center gap-3">
                      <!-- Active Checkbox -->
                      <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-neutral-300">
                        <input type="checkbox" name="sports[<?= e($key) ?>][active]" value="1" <?= !empty($sport['active']) ? 'checked' : '' ?> class="rounded border-white/20 bg-ink-900 text-accent focus:ring-accent">
                        <span>Aktif</span>
                      </label>

                      <!-- Delete Button -->
                      <button 
                        type="button" 
                        onclick="confirmDeleteSport('<?= e($key) ?>', '<?= e($sport['name']) ?>')" 
                        class="p-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 text-xs transition" 
                        title="Bu sporu sil"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </div>
                  </div>

                  <!-- Financial Pricing & Share Rates -->
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-ink-900/60 p-3.5 rounded-xl border border-white/5 text-xs">
                    <div>
                      <label class="block text-neutral-400 mb-1 font-medium">Birim Video Fiyatı</label>
                      <input type="number" step="0.5" min="0" name="sports[<?= e($key) ?>][price]" value="<?= e($sport['price']) ?>" <?= $isStandardPrice ? 'readonly title="Billing Guide sabit fiyatı"' : '' ?> class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white font-mono focus:border-accent focus:outline-none <?= $isStandardPrice ? 'opacity-60 cursor-not-allowed' : '' ?>">
                      <?php if ($isStandardPrice): ?><span class="text-[10px] text-neutral-500">Sabit fiyat (Billing Guide)</span><?php endif; ?>
                    </div>

                    <div>
                      <label class="block text-neutral-400 mb-1 font-medium">Tesis Payı (aylık kademe)</label>
                      <input type="hidden" name="sports[<?= e($key) ?>][share_pct]" value="<?= e($sport['share_pct']) ?>">
                      <div class="p-2 rounded-lg border border-white/10 bg-ink-800 text-neutral-300 font-mono text-[11px] leading-tight">0–49 %20 · 50–99 %25 · 100+ %30</div>
                    </div>

                    <div>
                      <label class="block text-neutral-400 mb-1 font-medium">Para Sembolü</label>
                      <input type="text" name="sports[<?= e($key) ?>][currency_symbol]" value="<?= e($sport['currency_symbol'] ?? '€') ?>" class="w-full rounded-lg border border-white/10 bg-ink-800 p-2 text-white text-center font-bold focus:border-accent focus:outline-none">
                    </div>

                    <div>
                      <label class="block text-neutral-400 mb-1 font-medium">Video Başı Tesis Kazancı</label>
                      <div class="p-2 rounded-lg bg-accent/10 border border-accent/20 text-accent font-bold font-mono text-center">
                        <?= e($sport['currency_symbol'] ?? '€') ?><?= number_format($unitRevenueMin, 2) ?>–<?= number_format($unitRevenueMax, 2) ?>
                      </div>
                    </div>
                  </div>

                  <!-- Sliders Min, Max, Default Matrix -->
                  <div class="space-y-2 pt-1 text-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">Kullanıcı Slider Sınırları & Varsayılanları</span>
                    
                    <div class="grid grid-cols-3 gap-3">
                      <!-- Courts config -->
                      <div class="bg-ink-900/40 p-2.5 rounded-xl border border-white/5 space-y-1.5">
                        <div class="font-semibold text-white">Saha Sayısı</div>
                        <div class="grid grid-cols-3 gap-1 text-[11px]">
                          <div>
                            <span class="text-neutral-500 block">Min</span>
                            <input type="number" name="sports[<?= e($key) ?>][courts_min]" value="<?= (int)$sport['courts_min'] ?>" class="w-full rounded bg-ink-800 border border-white/10 p-1 text-white text-center">
                          </div>
                          <div>
                            <span class="text-neutral-500 block">Varsayılan</span>
                            <input type="number" name="sports[<?= e($key) ?>][courts_default]" value="<?= (int)$sport['courts_default'] ?>" class="w-full rounded bg-ink-800 border border-accent/40 p-1 text-accent text-center font-bold">
                          </div>
                          <div>
                            <span class="text-neutral-500 block">Max</span>
                            <input type="number" name="sports[<?= e($key) ?>][courts_max]" value="<?= (int)$sport['courts_max'] ?>" class="w-full rounded bg-ink-800 border border-white/10 p-1 text-white text-center">
                          </div>
                        </div>
                      </div>

                      <!-- Videos config -->
                      <div class="bg-ink-900/40 p-2.5 rounded-xl border border-white/5 space-y-1.5">
                        <div class="font-semibold text-white">Günlük Satış</div>
                        <div class="grid grid-cols-3 gap-1 text-[11px]">
                          <div>
                            <span class="text-neutral-500 block">Min</span>
                            <input type="number" name="sports[<?= e($key) ?>][videos_min]" value="<?= (int)$sport['videos_min'] ?>" class="w-full rounded bg-ink-800 border border-white/10 p-1 text-white text-center">
                          </div>
                          <div>
                            <span class="text-neutral-500 block">Varsayılan</span>
                            <input type="number" name="sports[<?= e($key) ?>][videos_default]" value="<?= (int)$sport['videos_default'] ?>" class="w-full rounded bg-ink-800 border border-accent/40 p-1 text-accent text-center font-bold">
                          </div>
                          <div>
                            <span class="text-neutral-500 block">Max</span>
                            <input type="number" name="sports[<?= e($key) ?>][videos_max]" value="<?= (int)$sport['videos_max'] ?>" class="w-full rounded bg-ink-800 border border-white/10 p-1 text-white text-center">
                          </div>
                        </div>
                      </div>

                      <!-- Days config -->
                      <div class="bg-ink-900/40 p-2.5 rounded-xl border border-white/5 space-y-1.5">
                        <div class="font-semibold text-white">Aylık Gün</div>
                        <div class="grid grid-cols-3 gap-1 text-[11px]">
                          <div>
                            <span class="text-neutral-500 block">Min</span>
                            <input type="number" name="sports[<?= e($key) ?>][days_min]" value="<?= (int)$sport['days_min'] ?>" class="w-full rounded bg-ink-800 border border-white/10 p-1 text-white text-center">
                          </div>
                          <div>
                            <span class="text-neutral-500 block">Varsayılan</span>
                            <input type="number" name="sports[<?= e($key) ?>][days_default]" value="<?= (int)$sport['days_default'] ?>" class="w-full rounded bg-ink-800 border border-accent/40 p-1 text-accent text-center font-bold">
                          </div>
                          <div>
                            <span class="text-neutral-500 block">Max</span>
                            <input type="number" name="sports[<?= e($key) ?>][days_max]" value="<?= (int)$sport['days_max'] ?>" class="w-full rounded bg-ink-800 border border-white/10 p-1 text-white text-center">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- General Calculator & Hook Settings -->
          <div class="card-elevated rounded-3xl p-6 border border-white/10 space-y-4">
            <h3 class="font-display text-lg font-bold text-white pb-3 border-b border-white/10">Genel Ayarlar & Aksiyon Bloğu</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label class="block text-neutral-400 mb-1 font-medium">Varsayılan Seçili Spor</label>
                <select name="default_sport" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
                  <?php foreach ($config['sports'] as $k => $sp): ?>
                    <option value="<?= e($k) ?>" <?= ($config['default_sport'] === $k) ? 'selected' : '' ?>>
                      <?= e($sp['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block text-neutral-400 mb-1 font-medium">CTA Buton Yönlendirme Linki</label>
                <input type="text" name="hook_cta_url" value="<?= e($config['hook_cta_url'] ?? 'contact.php') ?>" placeholder="contact.php" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
              </div>
            </div>

            <div class="space-y-3 text-xs">
              <div>
                <label class="block text-neutral-400 mb-1 font-medium">Dipnot / Disclaimer Metni</label>
                <input type="text" name="disclaimer_note" value="<?= e($config['disclaimer_note'] ?? '') ?>" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
              </div>

              <div>
                <label class="block text-neutral-400 mb-1 font-medium">Görüşme Kartı Başlığı (Hook Title)</label>
                <input type="text" name="hook_title" value="<?= e($config['hook_title'] ?? '') ?>" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
              </div>

              <div>
                <label class="block text-neutral-400 mb-1 font-medium">Görüşme Kartı Açıklaması (Hook Description)</label>
                <textarea name="hook_desc" rows="2" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none"><?= e($config['hook_desc'] ?? '') ?></textarea>
              </div>

              <div>
                <label class="block text-neutral-400 mb-1 font-medium">CTA Buton Metni</label>
                <input type="text" name="hook_cta_text" value="<?= e($config['hook_cta_text'] ?? 'Görüşme ayarla') ?>" class="w-full rounded-xl border border-white/10 bg-ink-800 p-2.5 text-white focus:border-accent focus:outline-none">
              </div>
            </div>

          </div>

          <!-- Bottom Action Buttons -->
          <div class="flex items-center justify-between gap-4 pt-2">
            <button 
              type="button" 
              onclick="if(confirm('Tüm gelir hesaplayıcı ayarlarını fabrika varsayılanlarına döndürmek istediğinize emin misiniz?')) { document.getElementById('resetDefaultsForm').submit(); }" 
              class="px-4 py-3 rounded-2xl border border-white/10 bg-white/5 text-neutral-400 hover:text-white hover:bg-white/10 text-xs font-semibold transition"
            >
              Varsayılanlara Sıfırla
            </button>

            <button type="submit" class="btn-primary px-8 py-3.5 rounded-2xl text-sm font-bold shadow-glow flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Tüm Değişiklikleri Kaydet</span>
            </button>
          </div>

        </form>

      </div>

      <!-- Right Column: Live Interactive Preview (5 cols sticky) -->
      <div class="lg:col-span-5 space-y-6">

        <div class="sticky top-28 space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-accent flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
              Canlı Simülatör & Önizleme
            </span>
            <span class="text-[11px] text-neutral-400">for-facilities.php görünümü</span>
          </div>

          <!-- Live Calculator Card Preview -->
          <div class="card-elevated rounded-3xl border border-white/15 bg-ink-900/95 p-6 backdrop-blur shadow-2xl space-y-6">
            
            <div class="text-center">
              <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-accent mb-2">
                GELİR HESAPLAYICI
              </div>
              <h3 class="font-display text-xl font-bold text-white">Aylık pasif gelirinizi hesaplayın</h3>
              <p class="text-[11px] text-neutral-400 mt-1">Saha ve satış sayılarınızı belirleyin, payınızı görün.</p>
            </div>

            <!-- Sport Toggle Buttons -->
            <div class="flex justify-center">
              <div id="previewSportContainer" class="inline-flex rounded-full border border-white/10 bg-white/[0.04] p-1 gap-1 flex-wrap justify-center">
                <!-- Dynamically populated by preview JS -->
              </div>
            </div>

            <!-- Sliders -->
            <div class="space-y-4 text-xs">
              <!-- Courts Slider -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-baseline">
                  <span class="text-neutral-400 font-medium">Saha Sayısı</span>
                  <div class="flex items-baseline gap-1">
                    <span id="prevValCourts" class="font-display text-2xl font-bold text-accent">4</span>
                    <span class="text-[10px] text-neutral-400">saha</span>
                  </div>
                </div>
                <input type="range" id="prevSliderCourts" class="h-2 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FDE68A]">
                <div class="flex justify-between text-[10px] text-neutral-500 font-mono">
                  <span id="prevMinCourts">1</span>
                  <span id="prevMaxCourts">12</span>
                </div>
              </div>

              <!-- Videos Slider -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-baseline">
                  <span class="text-neutral-400 font-medium">Saha Başı Günlük Satılan Video</span>
                  <div class="flex items-baseline gap-1">
                    <span id="prevValVideos" class="font-display text-2xl font-bold text-accent">4</span>
                    <span class="text-[10px] text-neutral-400">saha başı</span>
                  </div>
                </div>
                <input type="range" id="prevSliderVideos" class="h-2 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FDE68A]">
                <div class="flex justify-between text-[10px] text-neutral-500 font-mono">
                  <span id="prevMinVideos">1</span>
                  <span id="prevMaxVideos">15</span>
                </div>
              </div>

              <!-- Days Slider -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-baseline">
                  <span class="text-neutral-400 font-medium">Aylık Aktif Gün</span>
                  <div class="flex items-baseline gap-1">
                    <span id="prevValDays" class="font-display text-2xl font-bold text-accent">26</span>
                    <span class="text-[10px] text-neutral-400">gün</span>
                  </div>
                </div>
                <input type="range" id="prevSliderDays" class="h-2 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FDE68A]">
                <div class="flex justify-between text-[10px] text-neutral-500 font-mono">
                  <span id="prevMinDays">20</span>
                  <span id="prevMaxDays">30</span>
                </div>
              </div>
            </div>

            <!-- Result Cards -->
            <div class="space-y-3 pt-2 border-t border-white/10">
              <div class="text-center text-[10px] font-bold uppercase tracking-widest text-accent/80">
                TAHMİNİ GELİRİNİZ
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-white/10 bg-white/[0.025] p-3.5 text-center">
                  <div class="text-[9px] font-semibold uppercase tracking-widest text-neutral-400">Satılan Video</div>
                  <div id="prevResVideos" class="mt-1 font-display text-xl font-bold text-white">416</div>
                  <div class="text-[10px] text-neutral-400">aylık</div>
                </div>

                <div class="rounded-xl border border-accent/40 bg-accent/[0.08] p-3.5 text-center shadow-glow-sm">
                  <div id="prevShareLabel" class="text-[9px] font-semibold uppercase tracking-widest text-accent">Payınız (%20)</div>
                  <div id="prevResMonthly" class="mt-1 font-display text-xl font-bold text-accent">€500</div>
                  <div class="text-[10px] text-neutral-300">aylık kazanç</div>
                </div>
              </div>

              <!-- Yearly Banner -->
              <div class="rounded-xl border border-accent/25 bg-gradient-to-br from-accent/10 to-accent/[0.02] p-4 text-center">
                <div class="text-[9px] font-semibold uppercase tracking-widest text-accent/90">YILLIK POTANSİYEL</div>
                <div class="mt-0.5 font-display text-2xl font-extrabold text-white">
                  ≈ <span id="prevResYearly">€6,000</span>
                  <span class="text-xs font-medium text-neutral-400">/ yıllık</span>
                </div>
                <div class="mt-1 text-[10px] text-neutral-400">Personelinize sıfır ek iş yükü ile.</div>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>

  </div>
</main>

<!-- Add New Sport Modal -->
<div id="addSportModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="max-w-lg w-full card-elevated rounded-3xl p-6 sm:p-8 border border-white/20 bg-ink-950 shadow-2xl relative">
    <div class="flex items-center justify-between mb-5 pb-3 border-b border-white/10">
      <h3 class="font-display text-lg font-bold text-accent">Yeni Spor Türü & Gelir Modeli Ekle</h3>
      <button type="button" onclick="document.getElementById('addSportModal').classList.add('hidden')" class="text-neutral-400 hover:text-white text-lg font-bold">&times;</button>
    </div>

    <form action="calculator.php" method="POST" class="space-y-4 text-xs">
      <input type="hidden" name="action" value="add_sport">

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-neutral-400 mb-1 font-medium">Spor Adı</label>
          <input type="text" name="new_sport_name" placeholder="Örn: Tenis" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
        </div>
        <div>
          <label class="block text-neutral-400 mb-1 font-medium">Anahtar / Slug</label>
          <input type="text" name="new_sport_key" placeholder="tennis" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white font-mono focus:border-accent focus:outline-none">
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="block text-neutral-400 mb-1 font-medium">Birim Fiyat</label>
          <input type="number" step="0.5" name="new_sport_price" value="10.00" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white focus:border-accent focus:outline-none">
        </div>
        <div>
          <label class="block text-neutral-400 mb-1 font-medium">Tesis Payı</label>
          <input type="hidden" name="new_sport_share_pct" value="20">
          <div class="rounded-xl border border-white/10 bg-ink-900 p-2.5 text-neutral-300 text-[11px] leading-tight">Aylık kademe: 0–49 %20 · 50–99 %25 · 100+ %30</div>
        </div>
        <div>
          <label class="block text-neutral-400 mb-1 font-medium">Sembol</label>
          <input type="text" name="new_sport_currency_symbol" value="€" required class="w-full rounded-xl border border-white/10 bg-ink-900 p-2.5 text-white text-center focus:border-accent focus:outline-none">
        </div>
      </div>

      <div class="pt-2 border-t border-white/10">
        <span class="text-[11px] font-semibold text-neutral-400 block mb-2">Varsayılan Slider Değerleri</span>
        <div class="grid grid-cols-3 gap-2">
          <div>
            <label class="block text-neutral-500 mb-0.5">Saha (Vars.)</label>
            <input type="number" name="new_sport_courts_default" value="4" class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-center">
          </div>
          <div>
            <label class="block text-neutral-500 mb-0.5">Video (Vars.)</label>
            <input type="number" name="new_sport_videos_default" value="4" class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-center">
          </div>
          <div>
            <label class="block text-neutral-500 mb-0.5">Gün (Vars.)</label>
            <input type="number" name="new_sport_days_default" value="26" class="w-full rounded-lg border border-white/10 bg-ink-900 p-2 text-white text-center">
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-3 border-t border-white/10">
        <button type="button" onclick="document.getElementById('addSportModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-neutral-300 hover:bg-white/10">İptal</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-accent text-ink-900 font-bold hover:bg-accent-neon shadow-glow">Sporu Ekle</button>
      </div>
    </form>
  </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteSportForm" action="calculator.php" method="POST" class="hidden">
  <input type="hidden" name="action" value="delete_sport">
  <input type="hidden" name="sport_key" id="deleteSportKey">
</form>

<!-- Hidden Reset Form -->
<form id="resetDefaultsForm" action="calculator.php" method="POST" class="hidden">
  <input type="hidden" name="action" value="reset_defaults">
</form>

<script>
const roiConfig = <?= json_encode($config, JSON_UNESCAPED_UNICODE) ?>;
let activeSportKey = roiConfig.default_sport || Object.keys(roiConfig.sports)[0];

function confirmDeleteSport(key, name) {
  if (confirm(`'${name}' spor modelini silmek istediğinize emin misiniz?`)) {
    document.getElementById('deleteSportKey').value = key;
    document.getElementById('deleteSportForm').submit();
  }
}

// Live Preview Simulator Logic
document.addEventListener('DOMContentLoaded', function() {
  const sportContainer = document.getElementById('previewSportContainer');
  const sliderCourts = document.getElementById('prevSliderCourts');
  const sliderVideos = document.getElementById('prevSliderVideos');
  const sliderDays = document.getElementById('prevSliderDays');

  const valCourts = document.getElementById('prevValCourts');
  const valVideos = document.getElementById('prevValVideos');
  const valDays = document.getElementById('prevValDays');

  const minCourts = document.getElementById('prevMinCourts');
  const maxCourts = document.getElementById('prevMaxCourts');
  const minVideos = document.getElementById('prevMinVideos');
  const maxVideos = document.getElementById('prevMaxVideos');
  const minDays = document.getElementById('prevMinDays');
  const maxDays = document.getElementById('prevMaxDays');

  const resVideos = document.getElementById('prevResVideos');
  const resMonthly = document.getElementById('prevResMonthly');
  const resYearly = document.getElementById('prevResYearly');
  const prevShareLabel = document.getElementById('prevShareLabel');

  function renderSportButtons() {
    sportContainer.innerHTML = '';
    const sports = roiConfig.sports || {};
    Object.keys(sports).forEach(key => {
      const sp = sports[key];
      if (sp.active === 0 || sp.active === false) return;

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = sp.name;
      const isActive = key === activeSportKey;
      btn.className = isActive 
        ? 'rounded-full px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider transition bg-accent text-ink-900 shadow-glow'
        : 'rounded-full px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider transition text-neutral-400 hover:text-white';
      
      btn.addEventListener('click', () => {
        activeSportKey = key;
        renderSportButtons();
        applySportSettings();
        calculatePreview();
      });

      sportContainer.appendChild(btn);
    });
  }

  function applySportSettings() {
    const sp = (roiConfig.sports && roiConfig.sports[activeSportKey]) ? roiConfig.sports[activeSportKey] : {};
    
    sliderCourts.min = sp.courts_min || 1;
    sliderCourts.max = sp.courts_max || 12;
    sliderCourts.value = sp.courts_default || 4;
    minCourts.textContent = sliderCourts.min;
    maxCourts.textContent = sliderCourts.max;

    sliderVideos.min = sp.videos_min || 1;
    sliderVideos.max = sp.videos_max || 15;
    sliderVideos.value = sp.videos_default || 4;
    minVideos.textContent = sliderVideos.min;
    maxVideos.textContent = sliderVideos.max;

    sliderDays.min = sp.days_min || 20;
    sliderDays.max = sp.days_max || 30;
    sliderDays.value = sp.days_default || 26;
    minDays.textContent = sliderDays.min;
    maxDays.textContent = sliderDays.max;

  }

  function calculatePreview() {
    const sp = (roiConfig.sports && roiConfig.sports[activeSportKey]) ? roiConfig.sports[activeSportKey] : {};
    const courts = parseInt(sliderCourts.value);
    const videos = parseInt(sliderVideos.value);
    const days = parseInt(sliderDays.value);

    valCourts.textContent = courts;
    valVideos.textContent = videos;
    valDays.textContent = days;

    // Billing Guide: monthly facility-wide sales pick the tier; it applies to all sales of the month
    const totalVideos = courts * videos * days;
    const price = parseFloat(sp.price || 0);
    const tier = (roiConfig.share_tiers || []).filter(t => totalVideos >= t.min).pop() || { facility_pct: 20 };
    const symbol = sp.currency_symbol || '€';

    prevShareLabel.textContent = `Payınız (%${tier.facility_pct})`;
    const monthlyTotal = Math.round(totalVideos * price * tier.facility_pct / 100);
    const yearlyTotal = Math.round(monthlyTotal * 12);

    resVideos.textContent = totalVideos.toLocaleString('tr-TR');
    resMonthly.textContent = symbol + monthlyTotal.toLocaleString('tr-TR');
    resYearly.textContent = symbol + yearlyTotal.toLocaleString('tr-TR');
  }

  sliderCourts.addEventListener('input', calculatePreview);
  sliderVideos.addEventListener('input', calculatePreview);
  sliderDays.addEventListener('input', calculatePreview);

  renderSportButtons();
  applySportSettings();
  calculatePreview();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
