<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('forFacilities.hero.eyebrow') ?: __('nav.forFacilities');

// Load dynamic ROI calculator configuration
$roiConfig = get_roi_calculator_config();
$activeSports = array_filter($roiConfig['sports'] ?? [], function($s) { return !empty($s['active']); });
if (empty($activeSports)) {
    $activeSports = get_default_roi_calculator_config()['sports'];
}
$defaultSportKey = $roiConfig['default_sport'] ?? array_key_first($activeSports);
if (!isset($activeSports[$defaultSportKey])) {
    $defaultSportKey = array_key_first($activeSports);
}
$defaultSport = $activeSports[$defaultSportKey];

require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen pt-24 pb-20 relative overflow-hidden bg-ink-900 w-full">

  <!-- ==================== HERO SECTION (FULL-BLEED BACKGROUND) ==================== -->
  <section class="relative overflow-hidden border-b border-white/5 py-16 sm:py-24 w-full">
    <!-- Full-bleed Hero Background Image spanning 100% viewport width edge-to-edge -->
    <div class="absolute inset-0 w-full h-full" aria-hidden="true">
      <div class="absolute inset-0 overflow-hidden">
        <img src="<?= asset('images/gen_padel_golden.jpg') ?>" alt="Facility" class="w-full h-full object-cover object-center opacity-40 scale-105 transition-transform duration-1000">
      </div>
      <div class="absolute inset-0 bg-gradient-to-b from-ink-950/85 via-ink-950/65 to-ink-900"></div>
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_70%_50%_at_50%_40%,rgba(253,230,138,0.12),transparent_70%)]"></div>
    </div>

    <!-- Ambient Glow -->
    <div class="pointer-events-none absolute inset-0 -z-10">
      <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[750px] h-[500px] bg-accent/15 blur-[140px] rounded-full"></div>
      <div class="absolute top-60 right-1/4 w-[400px] h-[300px] bg-emerald-500/10 blur-[130px] rounded-full"></div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center">
      <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-accent mb-6 backdrop-blur-md">
        <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
        <?= htmlspecialchars(__('forFacilities.hero.eyebrow') ?: 'Tesis Sahipleri ve İşletmeciler İçin') ?>
      </div>
      
      <h1 class="font-display text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.15] mb-6">
        <?= htmlspecialchars(__('forFacilities.hero.title') ?: 'Sahanız her ay ek gelir üretsin.') ?>
      </h1>
      
      <p class="text-neutral-300 text-base sm:text-lg lg:text-xl leading-relaxed max-w-2xl mx-auto">
        <?= htmlspecialchars(__('forFacilities.hero.subtitle') ?: 'Sıfır donanım yatırımı, sıfır personel eforu. Replash ile her maçtan dijital gelir elde edin, oyuncu bağlılığını artırın.') ?>
      </p>

      <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
        <a href="<?= base_url('pilot-program.php') ?>" class="btn-primary w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold shadow-glow flex items-center justify-center gap-2">
          <span><?= htmlspecialchars(__('forFacilities.cta.button') ?: 'Pilot Saha Ol') ?></span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
        <a href="#calculator" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold border border-white/15 hover:border-white/30 text-white bg-white/5 hover:bg-white/10 transition-all flex items-center justify-center">
          <?= htmlspecialchars(__('forFacilities.roi.eyebrow') ?: 'Gelir Hesaplayıcı') ?>
        </a>
      </div>
    </div>
  </section>


  <!-- ==================== SECTION 1: ENGAGEMENT ==================== -->
  <section class="section-y border-t border-white/5 relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
          <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent mb-4">
            <?= htmlspecialchars(__('forFacilities.engagement.eyebrow') ?: 'Daha İyi Oyuncu Bağlılığı') ?>
          </div>
          <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight mb-5">
            <?= htmlspecialchars(__('forFacilities.engagement.title') ?: 'Oyuncular en güzel anlarının yaşandığı sahaları hatırlar.') ?>
          </h2>
          <p class="text-base sm:text-lg leading-relaxed text-neutral-300">
            <?= htmlspecialchars(__('forFacilities.engagement.desc') ?: 'Bir oyuncu maçını izleyip paylaşabildiğinde, o anıları sahanızla özdeşleştirir. O duygusal bağ, tek seferlik rezervasyonları düzenli müşterilere, düzenli müşterileri ise sahanızı arkadaşlarına öneren savunuculara dönüştürür.') ?>
          </p>
        </div>

        <div class="flex flex-col gap-3.5">
          <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="flex items-center gap-3.5 rounded-2xl border border-white/10 bg-white/[0.03] px-5 py-4 transition-all duration-300 hover:border-accent/40 hover:bg-white/[0.05]">
              <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-accent/15 text-accent border border-accent/30">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
              <span class="text-sm sm:text-base text-neutral-200 font-medium">
                <?= htmlspecialchars(__('forFacilities.engagement.items.' . $i)) ?>
              </span>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </section>


  <!-- ==================== SECTION 2: ROI CALCULATOR ==================== -->
  <section id="calculator" class="section-y border-t border-white/5 relative overflow-hidden bg-ink-950/60">
    <div class="pointer-events-none absolute inset-0 -z-10">
      <img src="<?= asset('images/gen_facility_owner_pov.jpg') ?>" alt="" class="h-full w-full object-cover opacity-20 filter brightness-110">
      <div class="absolute inset-0 bg-gradient-to-b from-ink-900/90 via-ink-900/60 to-ink-900/95"></div>
      <div class="absolute inset-0" style="background: radial-gradient(ellipse 60% 50% at 50% 50%, rgba(253,230,138,0.10), transparent 70%);"></div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent mb-4">
          <?= htmlspecialchars(__('forFacilities.roi.eyebrow') ?: 'GELİR HESAPLAYICI') ?>
        </div>
        <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
          <?= htmlspecialchars(__('forFacilities.roi.title') ?: 'Aylık pasif gelirinizi hesaplayın') ?>
        </h2>
        <p class="mt-3 text-sm sm:text-base text-neutral-300">
          <?= htmlspecialchars(__('forFacilities.roi.subtitle') ?: 'Kaç sahanız var? Saha başına günlük kaç video satmayı bekliyorsunuz? Rakamlarınızı girin, aylık payınızı görün.') ?>
        </p>
      </div>

      <div class="card-elevated mx-auto max-w-3xl rounded-3xl border border-white/10 bg-ink-900/80 p-6 sm:p-10 backdrop-blur shadow-2xl">
        <!-- Sport Toggle (Dynamic from DB/Config) -->
        <div class="mb-8 flex justify-center">
          <div id="roi-sports-container" class="inline-flex rounded-full border border-white/10 bg-white/[0.04] p-1.5 gap-1 flex-wrap justify-center">
            <?php foreach ($activeSports as $sKey => $sp): 
              $isSelected = ($sKey === $defaultSportKey);
              $btnClass = $isSelected 
                ? 'rounded-full px-6 py-2 text-xs font-bold uppercase tracking-wider transition bg-accent text-ink-900 shadow-glow' 
                : 'rounded-full px-6 py-2 text-xs font-bold uppercase tracking-wider transition text-neutral-400 hover:text-white';
            ?>
              <button type="button" data-sport="<?= e($sKey) ?>" class="roi-sport-btn <?= $btnClass ?>">
                <?= htmlspecialchars(__('forFacilities.roi.sport.' . $sKey) ?: $sp['name']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Sliders -->
        <div class="grid gap-6 md:grid-cols-3">
          <!-- Courts -->
          <div class="flex flex-col gap-2">
            <label class="text-xs font-semibold uppercase tracking-wider text-neutral-400">
              <?= htmlspecialchars(__('forFacilities.roi.inputs.courts') ?: 'Saha sayısı') ?>
            </label>
            <div class="flex items-baseline gap-1.5">
              <span id="val-courts" class="font-display text-3xl font-bold text-accent"><?= (int)$defaultSport['courts_default'] ?></span>
              <span class="text-xs text-neutral-400"><?= htmlspecialchars(__('forFacilities.roi.inputs.courtsSuffix') ?: 'saha') ?></span>
            </div>
            <input type="range" id="slider-courts" min="<?= (int)$defaultSport['courts_min'] ?>" max="<?= (int)$defaultSport['courts_max'] ?>" value="<?= (int)$defaultSport['courts_default'] ?>" class="h-2 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FDE68A]">
            <div class="flex justify-between text-[10px] text-neutral-500 font-mono">
              <span id="min-courts"><?= (int)$defaultSport['courts_min'] ?></span>
              <span id="max-courts"><?= (int)$defaultSport['courts_max'] ?></span>
            </div>
          </div>

          <!-- Videos per day -->
          <div class="flex flex-col gap-2">
            <label class="text-xs font-semibold uppercase tracking-wider text-neutral-400">
              <?= htmlspecialchars(__('forFacilities.roi.inputs.videos') ?: 'Saha başı günlük video') ?>
            </label>
            <div class="flex items-baseline gap-1.5">
              <span id="val-videos" class="font-display text-3xl font-bold text-accent"><?= (int)$defaultSport['videos_default'] ?></span>
              <span class="text-xs text-neutral-400"><?= htmlspecialchars(__('forFacilities.roi.inputs.videosSuffix') ?: 'saha başı') ?></span>
            </div>
            <input type="range" id="slider-videos" min="<?= (int)$defaultSport['videos_min'] ?>" max="<?= (int)$defaultSport['videos_max'] ?>" value="<?= (int)$defaultSport['videos_default'] ?>" class="h-2 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FDE68A]">
            <div class="flex justify-between text-[10px] text-neutral-500 font-mono">
              <span id="min-videos"><?= (int)$defaultSport['videos_min'] ?></span>
              <span id="max-videos"><?= (int)$defaultSport['videos_max'] ?></span>
            </div>
          </div>

          <!-- Active Days -->
          <div class="flex flex-col gap-2">
            <label class="text-xs font-semibold uppercase tracking-wider text-neutral-400">
              <?= htmlspecialchars(__('forFacilities.roi.inputs.days') ?: 'Aylık aktif gün') ?>
            </label>
            <div class="flex items-baseline gap-1.5">
              <span id="val-days" class="font-display text-3xl font-bold text-accent"><?= (int)$defaultSport['days_default'] ?></span>
              <span class="text-xs text-neutral-400"><?= htmlspecialchars(__('forFacilities.roi.inputs.daysSuffix') ?: 'gün') ?></span>
            </div>
            <input type="range" id="slider-days" min="<?= (int)$defaultSport['days_min'] ?>" max="<?= (int)$defaultSport['days_max'] ?>" value="<?= (int)$defaultSport['days_default'] ?>" class="h-2 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FDE68A]">
            <div class="flex justify-between text-[10px] text-neutral-500 font-mono">
              <span id="min-days"><?= (int)$defaultSport['days_min'] ?></span>
              <span id="max-days"><?= (int)$defaultSport['days_max'] ?></span>
            </div>
          </div>
        </div>

        <div class="my-8 h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

        <!-- Result Cards -->
        <div class="mb-3 text-center text-[10px] font-bold uppercase tracking-widest text-accent/80">
          <?= htmlspecialchars(__('forFacilities.roi.results.eyebrow') ?: 'TAHMİNİ GELİRİNİZ') ?>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-5 text-center">
            <div class="text-[10px] font-semibold uppercase tracking-widest text-neutral-400">
              <?= htmlspecialchars(__('forFacilities.roi.results.videos') ?: 'Satılan video') ?>
            </div>
            <div id="res-monthly-videos" class="mt-2 font-display text-2xl sm:text-3xl font-bold text-white">416</div>
            <div class="mt-1 text-[11px] text-neutral-400">
              <?= htmlspecialchars(__('forFacilities.roi.results.videosHint') ?: 'aylık') ?>
            </div>
          </div>

          <div class="rounded-2xl border border-accent/40 bg-accent/[0.08] p-5 text-center shadow-glow-sm">
            <div id="label-monthly-share" class="text-[10px] font-semibold uppercase tracking-widest text-accent">
              <?= htmlspecialchars(__('forFacilities.roi.results.share') ?: 'Payınız (%' . (float)$defaultSport['share_pct'] . ')') ?>
            </div>
            <div id="res-monthly-share" class="mt-2 font-display text-2xl sm:text-3xl font-bold text-accent">€500</div>
            <div class="mt-1 text-[11px] text-neutral-300">
              <?= htmlspecialchars(__('forFacilities.roi.results.shareHint') ?: 'aylık kazanç') ?>
            </div>
          </div>
        </div>

        <!-- Yearly Result Banner -->
        <div class="mt-6 rounded-2xl border border-accent/25 bg-gradient-to-br from-accent/10 to-accent/[0.02] px-6 py-5 text-center">
          <div class="text-[10px] font-semibold uppercase tracking-widest text-accent/90">
            <?= htmlspecialchars(__('forFacilities.roi.results.yearlyEyebrow') ?: 'YILLIK POTANSİYEL') ?>
          </div>
          <div class="mt-1 font-display text-3xl sm:text-4xl font-extrabold text-white">
            ≈ <span id="res-yearly-share">€6,000</span>
            <span class="text-base font-medium text-neutral-400">/ <?= htmlspecialchars(__('forFacilities.roi.results.yearlySuffix') ?: 'yıllık') ?></span>
          </div>
          <div class="mt-2 text-xs text-neutral-400">
            <?= htmlspecialchars(__('forFacilities.roi.results.yearlyNote') ?: 'Personelinize sıfır ek iş yükü ile.') ?>
          </div>
        </div>

        <p class="mx-auto mt-6 max-w-2xl text-center text-[11px] leading-relaxed text-neutral-500">
          <?= !empty($roiConfig['disclaimer_note']) ? htmlspecialchars($roiConfig['disclaimer_note']) : (htmlspecialchars(__('forFacilities.roi.disclaimer') ?: '* Tahmin spor başına ortalama video satış fiyatına dayanmaktadır. Hesaplama tamamen örnektir.')) ?>
        </p>

        <!-- Call Action Hook -->
        <div class="mt-8 rounded-2xl border border-white/10 bg-white/[0.03] px-6 py-6 text-center">
          <h3 class="font-display text-lg font-bold text-white sm:text-xl">
            <?= !empty($roiConfig['hook_title']) ? htmlspecialchars($roiConfig['hook_title']) : (htmlspecialchars(__('forFacilities.roi.hook.title') ?: 'Bu tahmini tesisinin aylık gelirine çevir.')) ?>
          </h3>
          <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-neutral-400">
            <?= !empty($roiConfig['hook_desc']) ? htmlspecialchars($roiConfig['hook_desc']) : (htmlspecialchars(__('forFacilities.roi.hook.desc') ?: 'Kısa bir görüşme ayarla. Bu rakamları senin tesisine göre çıkaralım; kurulum, süre ve şartları birlikte konuşalım.')) ?>
          </p>
          <div class="mt-5 flex justify-center">
            <a href="<?= base_url(!empty($roiConfig['hook_cta_url']) ? $roiConfig['hook_cta_url'] : 'contact.php') ?>" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold shadow-glow">
              <span><?= !empty($roiConfig['hook_cta_text']) ? htmlspecialchars($roiConfig['hook_cta_text']) : (htmlspecialchars(__('forFacilities.roi.hook.cta') ?: 'Görüşme ayarla')) ?></span>
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ==================== SECTION 3: OBJECTIONS (GDPR & WIFI) ==================== -->
  <section class="section-y border-t border-white/5 bg-ink-900 relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent mb-4">
          <?= htmlspecialchars(__('forFacilities.objections.eyebrow') ?: 'Yaygın Endişeler. Cevaplandı') ?>
        </div>
        <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
          <?= htmlspecialchars(__('forFacilities.objections.title') ?: 'Her sahanın sorduğu iki soru. İki net cevap.') ?>
        </h2>
      </div>

      <div class="mx-auto grid gap-6 md:grid-cols-2">
        <!-- GDPR Card -->
        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 hover:border-accent/40 transition-all duration-300">
          <div class="mb-4 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-accent/30 bg-accent/10 text-accent flex-shrink-0">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
              </svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-white">
              &ldquo;<?= htmlspecialchars(__('forFacilities.objections.items.gdpr.question') ?: 'Ya GDPR & KVKK?') ?>&rdquo;
            </h3>
          </div>
          <p class="text-sm leading-relaxed text-neutral-300 mb-6">
            <?= htmlspecialchars(__('forFacilities.objections.items.gdpr.answer') ?: 'Replash gizlilik mimarisi GDPR ve mevzuatlara tam uygundur. Kameralar sadece oyun alanını çeker.') ?>
          </p>

          <ul class="flex flex-col gap-2.5 border-t border-white/10 pt-5">
            <?php for ($i = 1; $i <= 4; $i++): ?>
              <li class="flex items-start gap-2.5">
                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs sm:text-sm text-neutral-300">
                  <?= htmlspecialchars(__('forFacilities.objections.items.gdpr.items.' . $i)) ?>
                </span>
              </li>
            <?php endfor; ?>
          </ul>
        </div>

        <!-- Tech/WiFi Card -->
        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 hover:border-accent/40 transition-all duration-300">
          <div class="mb-4 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-accent/30 bg-accent/10 text-accent flex-shrink-0">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12.55a11 11 0 0 1 14.08 0" />
                <path d="M1.42 9a16 16 0 0 1 21.16 0" />
                <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                <line x1="12" y1="20" x2="12.01" y2="20" />
              </svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-white">
              &ldquo;<?= htmlspecialchars(__('forFacilities.objections.items.tech.question') ?: 'Ya WiFi\'mız yoksa?') ?>&rdquo;
            </h3>
          </div>
          <p class="text-sm leading-relaxed text-neutral-300 mb-6">
            <?= htmlspecialchars(__('forFacilities.objections.items.tech.answer') ?: 'PoE kameralar yerel tampon bellekle çalışır. İnternetsiz açık sahalar için LTE/SIM modem desteği mevcuttur.') ?>
          </p>

          <ul class="flex flex-col gap-2.5 border-t border-white/10 pt-5">
            <?php for ($i = 1; $i <= 4; $i++): ?>
              <li class="flex items-start gap-2.5">
                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs sm:text-sm text-neutral-300">
                  <?= htmlspecialchars(__('forFacilities.objections.items.tech.items.' . $i)) ?>
                </span>
              </li>
            <?php endfor; ?>
          </ul>
        </div>
      </div>
    </div>
  </section>


  <!-- ==================== SECTION 4: MARKETING LAYER ==================== -->
  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden w-full">
    <!-- Full-bleed background spanning 100% screen width edge-to-edge -->
    <div class="absolute inset-0 w-full h-full" aria-hidden="true">
      <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -inset-y-[20%] inset-x-0 w-full h-[140%]">
          <img src="<?= asset('images/gen_marketing_bright.jpg') ?>" alt="Marketing Layer" class="h-full w-full object-cover object-center" style="opacity: 0.65; filter: brightness(1.15);">
        </div>
      </div>
      <div class="absolute inset-0 bg-gradient-to-b from-ink-900/85 via-ink-900/35 to-ink-900/85"></div>
    </div>

    <div class="container-x relative z-10">
      <div class="max-w-3xl mx-auto text-center mb-12">
        <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent mb-4">
          <?= htmlspecialchars(__('forFacilities.marketing.eyebrow') ?: 'Pazarlama Katmanı Dahil') ?>
        </div>
        <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl text-white">
          <?= htmlspecialchars(__('forFacilities.marketing.title') ?: 'Sahanızı sadece müşterilere değil. Kitlelere taşırız.') ?>
        </h2>
        <p class="mt-4 text-lg text-white/70 md:text-xl leading-relaxed">
          <?= htmlspecialchars(__('forFacilities.marketing.desc') ?: 'Replash partner sahalarını görünür kılan bir pazarlama katmanıdır. Web sitemizde logonuzu ve konumunuzu öne çıkarırız. Oyuncular highlight\'larını sosyal medyada paylaştıkça sahanız çevredeki topluluklara organik olarak ulaşır.') ?>
        </p>
      </div>

      <div class="mt-12 grid gap-5 sm:grid-cols-3">
        <!-- Card 1: Featured on Replash -->
        <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30 backdrop-blur-sm shadow-xl">
          <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
          </div>
          <h3 class="mb-2 text-sm font-semibold text-white">
            <?= htmlspecialchars(__('forFacilities.marketing.items.1.title') ?: 'Replash\'te Öne Çıkar') ?>
          </h3>
          <p class="text-sm leading-relaxed text-white/55">
            <?= htmlspecialchars(__('forFacilities.marketing.items.1.desc') ?: 'Sahanızın logosu ve konumu sitemizde ve kurumsal kanallarımızda öne çıkarılır.') ?>
          </p>
        </div>

        <!-- Card 2: Organic Social Spread -->
        <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30 backdrop-blur-sm shadow-xl">
          <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="18" cy="5" r="3"></circle>
              <circle cx="6" cy="12" r="3"></circle>
              <circle cx="18" cy="19" r="3"></circle>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
              <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            </svg>
          </div>
          <h3 class="mb-2 text-sm font-semibold text-white">
            <?= htmlspecialchars(__('forFacilities.marketing.items.2.title') ?: 'Organik Sosyal Yayılım') ?>
          </h3>
          <p class="text-sm leading-relaxed text-white/55">
            <?= htmlspecialchars(__('forFacilities.marketing.items.2.desc') ?: 'Bir oyuncunun paylaştığı her klip sahanızı dolaylı olarak ağına taşır.') ?>
          </p>
        </div>

        <!-- Card 3: Pilot Tournaments -->
        <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30 backdrop-blur-sm shadow-xl">
          <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
              <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
              <path d="M4 22h16"></path>
              <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
              <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
              <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
            </svg>
          </div>
          <h3 class="mb-2 text-sm font-semibold text-white">
            <?= htmlspecialchars(__('forFacilities.marketing.items.3.title') ?: 'Pilot Turnuvalar') ?>
          </h3>
          <p class="text-sm leading-relaxed text-white/55">
            <?= htmlspecialchars(__('forFacilities.marketing.items.3.desc') ?: 'Lansmanda partner sahalarda temalı turnuvalar düzenleriz. Yeni müşteriler ve eklenen görünürlük, bizden hediye.') ?>
          </p>
        </div>
      </div>
    </div>
  </section>


  <!-- ==================== SECTION 5: WHAT YOU DO ==================== -->
  <section class="section-y border-t border-white/5 bg-ink-900 relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="text-center max-w-3xl mx-auto mb-14">
        <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent mb-4">
          <?= htmlspecialchars(__('forFacilities.whatYouDo.eyebrow') ?: 'Sahanın Yapması Gerekenler') ?>
        </div>
        <h2 class="font-display text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
          <?= htmlspecialchars(__('forFacilities.whatYouDo.title') ?: 'Ekibiniz teknolojiyi yönetmez. Ekibiniz sadece deneyimi sunar.') ?>
        </h2>
      </div>

      <div class="grid gap-6 sm:grid-cols-2">
        <?php 
        $nums = ["01", "02", "03", "04"];
        for ($i = 1; $i <= 4; $i++): 
        ?>
          <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/[0.025] p-7 sm:p-8 hover:border-accent/40 transition-all duration-300">
            <span class="absolute right-6 top-5 font-display text-5xl sm:text-6xl font-black text-white/[0.06] select-none pointer-events-none">
              <?= $nums[$i-1] ?>
            </span>
            <h3 class="mb-3 text-lg font-bold text-white pr-12">
              <?= htmlspecialchars(__('forFacilities.whatYouDo.items.' . $i . '.title')) ?>
            </h3>
            <p class="text-sm sm:text-base leading-relaxed text-neutral-400">
              <?= htmlspecialchars(__('forFacilities.whatYouDo.items.' . $i . '.desc')) ?>
            </p>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>


  <!-- ==================== SECTION 6: CTA ==================== -->
  <div class="max-w-6xl mx-auto px-4 sm:px-6 my-16">
    <div class="card-elevated rounded-3xl p-8 sm:p-14 border border-accent/30 text-center relative overflow-hidden bg-gradient-to-b from-ink-900/90 to-ink-950/95 shadow-2xl">
      <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-48 bg-accent/20 blur-[90px] rounded-full pointer-events-none"></div>
      
      <div class="relative z-10 max-w-2xl mx-auto">
        <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-white mb-4">
          <?= htmlspecialchars(__('forFacilities.cta.title') ?: 'Replash\'i sahanıza getirin.') ?>
        </h2>
        <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-8">
          <?= htmlspecialchars(__('forFacilities.cta.subtitle') ?: 'Bizimle kurulum, zamanlama ve sahanız için nasıl çalıştığını konuşun.') ?>
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <a href="<?= base_url('pilot-program.php') ?>" class="btn-primary w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold shadow-glow flex items-center justify-center gap-2">
            <span><?= htmlspecialchars(__('forFacilities.cta.button') ?: 'Pilot Saha Ol') ?></span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
          <a href="<?= base_url('contact.php') ?>" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold border border-white/15 hover:border-white/30 text-white bg-white/5 hover:bg-white/10 transition-all flex items-center justify-center">
            <?= htmlspecialchars(__('nav.contact') ?: 'İletişim') ?>
          </a>
        </div>
      </div>
    </div>
  </div>

</main>

<script>
  const ROI_CONFIG = <?= json_encode($roiConfig, JSON_UNESCAPED_UNICODE) ?>;
  let currentSportKey = '<?= e($defaultSportKey) ?>';

  const sportButtons = document.querySelectorAll('.roi-sport-btn');

  const sliderCourts = document.getElementById('slider-courts');
  const sliderVideos = document.getElementById('slider-videos');
  const sliderDays = document.getElementById('slider-days');

  const valCourts = document.getElementById('val-courts');
  const valVideos = document.getElementById('val-videos');
  const valDays = document.getElementById('val-days');

  const minCourts = document.getElementById('min-courts');
  const maxCourts = document.getElementById('max-courts');
  const minVideos = document.getElementById('min-videos');
  const maxVideos = document.getElementById('max-videos');
  const minDays = document.getElementById('min-days');
  const maxDays = document.getElementById('max-days');

  const labelMonthlyShare = document.getElementById('label-monthly-share');
  const resMonthlyVideos = document.getElementById('res-monthly-videos');
  const resMonthlyShare = document.getElementById('res-monthly-share');
  const resYearlyShare = document.getElementById('res-yearly-share');

  function getActiveSport() {
    return (ROI_CONFIG.sports && ROI_CONFIG.sports[currentSportKey]) ? ROI_CONFIG.sports[currentSportKey] : {};
  }

  function applySportSliders(sp) {
    if (!sp) return;
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

    if (labelMonthlyShare) {
      labelMonthlyShare.textContent = `Payınız (%${sp.share_pct || 15})`;
    }
  }

  function calculate() {
    const sp = getActiveSport();
    const courts = parseInt(sliderCourts.value);
    const videos = parseInt(sliderVideos.value);
    const days = parseInt(sliderDays.value);

    valCourts.textContent = courts;
    valVideos.textContent = videos;
    valDays.textContent = days;

    const totalVideos = courts * videos * days;
    const price = parseFloat(sp.price || 8);
    const sharePct = parseFloat(sp.share_pct || 15) / 100;
    const symbol = sp.currency_symbol || '€';

    const unitRevenue = price * sharePct;
    const monthlyTotal = Math.round(totalVideos * unitRevenue);
    const yearlyTotal = Math.round(monthlyTotal * 12);

    resMonthlyVideos.textContent = totalVideos.toLocaleString('tr-TR');
    resMonthlyShare.textContent = symbol + monthlyTotal.toLocaleString('tr-TR');
    resYearlyShare.textContent = symbol + yearlyTotal.toLocaleString('tr-TR');
  }

  sportButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const sportKey = btn.getAttribute('data-sport');
      if (!sportKey || sportKey === currentSportKey) return;

      currentSportKey = sportKey;

      // Update button styles
      sportButtons.forEach(b => {
        b.className = 'roi-sport-btn rounded-full px-6 py-2 text-xs font-bold uppercase tracking-wider transition text-neutral-400 hover:text-white';
      });
      btn.className = 'roi-sport-btn rounded-full px-6 py-2 text-xs font-bold uppercase tracking-wider transition bg-accent text-ink-900 shadow-glow';

      const sp = getActiveSport();
      applySportSliders(sp);
      calculate();
    });
  });

  sliderCourts.addEventListener('input', calculate);
  sliderVideos.addEventListener('input', calculate);
  sliderDays.addEventListener('input', calculate);

  // Initial calculation
  calculate();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

