<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('forFacilities.hero.eyebrow') ?: __('nav.forFacilities');
$metaTitle = __('meta.forFacilities.title');
$metaDescription = __('meta.forFacilities.description');

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

<main class="relative bg-ink-900">

  <section class="relative overflow-hidden bg-ink-900 pt-36 pb-20 md:pt-44 md:pb-28">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute -inset-y-[30%] inset-x-0 will-change-transform" >
  <img src="<?= asset('images/gen_padel_golden.jpg') ?>" alt="" class="h-full w-full object-cover object-center" style="opacity:0.5"/>
  </div>
  </div>
  <div class="absolute inset-0 bg-gradient-to-b from-ink-900/60 via-ink-900/30 to-ink-900/90">
  </div>
  </div>
  <div class="pointer-events-none absolute -top-40 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full" style="background:radial-gradient(circle, rgba(253,230,138,0.12) 0%, transparent 70%)" aria-hidden="true">
  </div>
  <div class="absolute inset-0 grid-noise opacity-10" aria-hidden="true">
  </div>
  <div class="container-x relative text-center">
  <h1 class="mx-auto max-w-3xl font-display text-4xl font-bold leading-[1.1] tracking-tight text-balance md:text-5xl lg:text-6xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" data-reveal="up">
  <?= e(__('forFacilities.hero.title')) ?>
  </h1>
  <p class="mx-auto mt-6 max-w-2xl text-lg text-white/55 md:text-xl"  data-reveal="up">
  <?= e(__('forFacilities.hero.subtitle')) ?>
  </p>
  </div>
  </section>

  <!-- GELİR HESAPLAYICI (replash.info tasarımı; hesap: includes/billing.php kademeleri) -->
  <?php
    // Yönetimden özel metin girilmişse o, yoksa dil dosyasındaki (replash.info) metin
    $roiDefaults = get_default_roi_calculator_config();
    $roiText = function ($configKey, $langKey) use ($roiConfig, $roiDefaults) {
        $custom = $roiConfig[$configKey] ?? '';
        return ($custom !== '' && $custom !== ($roiDefaults[$configKey] ?? '')) ? $custom : __($langKey);
    };
    $sliders = [
        'courts' => ['label' => __('forFacilities.roi.inputs.courts'), 'suffix' => __('forFacilities.roi.inputs.courtsSuffix'), 'min' => 'courts_min', 'max' => 'courts_max', 'def' => 'courts_default'],
        'videos' => ['label' => __('forFacilities.roi.inputs.videos'), 'suffix' => __('forFacilities.roi.inputs.videosSuffix'), 'min' => 'videos_min', 'max' => 'videos_max', 'def' => 'videos_default'],
        'days'   => ['label' => __('forFacilities.roi.inputs.days'),   'suffix' => __('forFacilities.roi.inputs.daysSuffix'),   'min' => 'days_min',   'max' => 'days_max',   'def' => 'days_default'],
    ];
  ?>
  <section id="calculator" class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
      <div class="absolute -inset-y-[30%] inset-x-0">
        <img src="<?= asset('images/gen_facility_owner_pov.jpg') ?>" alt="" class="h-full w-full object-cover opacity-30" style="filter:brightness(1.1)" />
      </div>
    </div>
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-ink-900/90 via-ink-900/55 to-ink-900/95" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 60% 50% at 50% 50%, rgba(253,230,138,0.10), transparent 70%)" aria-hidden="true"></div>

    <div class="container-x relative z-10">
      <div class="max-w-3xl mx-auto text-center" data-reveal="up">
        <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('forFacilities.roi.title')) ?></h2>
      </div>

      <div class="card-elevated mx-auto mt-12 max-w-3xl rounded-3xl border border-white/10 bg-ink-900/70 p-6 backdrop-blur md:p-10" data-reveal="up">
        <!-- Spor seçimi -->
        <div class="mb-8 flex justify-center">
          <div class="inline-flex rounded-full border border-white/10 bg-white/[0.04] p-1">
            <?php foreach ($activeSports as $sKey => $sp): $isSelected = ($sKey === $defaultSportKey); ?>
              <button type="button" data-sport="<?= e($sKey) ?>" class="roi-sport-btn rounded-full px-5 py-2 text-xs font-semibold uppercase tracking-wider transition <?= $isSelected ? 'bg-accent text-ink-900' : 'text-white/55 hover:text-white' ?>">
                <?= e(__('forFacilities.roi.sport.' . $sKey, $sp['name'] ?? ucfirst($sKey))) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Kaydırıcılar -->
        <div class="grid gap-6 md:grid-cols-3">
          <?php foreach ($sliders as $key => $sl): ?>
            <div class="flex flex-col gap-2">
              <label for="slider-<?= $key ?>" class="text-xs font-semibold uppercase tracking-wider text-white/55"><?= e($sl['label']) ?></label>
              <div class="flex items-baseline gap-1.5">
                <span id="val-<?= $key ?>" class="font-display text-3xl font-bold text-accent"><?= (int)$defaultSport[$sl['def']] ?></span>
                <span class="text-xs text-white/40"><?= e($sl['suffix']) ?></span>
              </div>
              <input type="range" id="slider-<?= $key ?>" min="<?= (int)$defaultSport[$sl['min']] ?>" max="<?= (int)$defaultSport[$sl['max']] ?>" value="<?= (int)$defaultSport[$sl['def']] ?>" class="h-1.5 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FDE68A]">
              <div class="flex justify-between text-[10px] text-white/30">
                <span id="min-<?= $key ?>"><?= (int)$defaultSport[$sl['min']] ?></span>
                <span id="max-<?= $key ?>"><?= (int)$defaultSport[$sl['max']] ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="my-8 h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

        <!-- Sonuçlar -->
        <div class="mb-3 text-center text-[10px] font-bold uppercase tracking-widest text-accent/70"><?= e(__('forFacilities.roi.results.eyebrow')) ?></div>
        <div class="grid gap-3 md:grid-cols-2">
          <div class="rounded-2xl border p-5 text-center border-white/8 bg-white/[0.025]">
            <div class="text-[10px] font-semibold uppercase tracking-widest text-white/45"><?= e(__('forFacilities.roi.results.videos')) ?></div>
            <div id="res-monthly-videos" class="mt-2 font-display text-2xl font-bold md:text-3xl text-white">&nbsp;</div>
            <div class="mt-1 text-[11px] text-white/40"><?= e(__('forFacilities.roi.results.videosHint')) ?></div>
          </div>
          <div class="rounded-2xl border p-5 text-center border-accent/30 bg-accent/[0.06]">
            <div id="label-monthly-share" class="text-[10px] font-semibold uppercase tracking-widest text-white/45"><?= e(str_replace('{pct}', '20', __('forFacilities.roi.results.share'))) ?></div>
            <div id="res-monthly-share" class="mt-2 font-display text-2xl font-bold md:text-3xl text-accent">&nbsp;</div>
            <div class="mt-1 text-[11px] text-white/40"><?= e(__('forFacilities.roi.results.shareHint')) ?></div>
          </div>
        </div>

        <div class="mt-6 rounded-2xl border border-accent/25 bg-gradient-to-br from-accent/10 to-accent/[0.02] px-6 py-5 text-center">
          <div class="text-[10px] font-semibold uppercase tracking-widest text-accent/80"><?= e(__('forFacilities.roi.results.yearlyEyebrow')) ?></div>
          <div class="mt-1 font-display text-3xl font-bold text-white md:text-4xl">≈ <span id="res-yearly-share">&nbsp;</span>
            <span class="text-base font-medium text-white/55"><?= e(__('forFacilities.roi.results.yearlySuffix')) ?></span>
          </div>
          <div class="mt-2 text-xs text-white/45"><?= e(__('forFacilities.roi.results.yearlyNote')) ?></div>
        </div>

        <p class="mx-auto mt-6 max-w-2xl text-center text-[11px] leading-relaxed text-white/35"><?= e($roiText('disclaimer_note', 'forFacilities.roi.disclaimer')) ?></p>

        <!-- Görüşme çağrısı -->
        <div class="mt-8 rounded-2xl border border-white/10 bg-white/[0.03] px-6 py-6 text-center">
          <h3 class="font-display text-lg font-bold text-white md:text-xl"><?= e($roiText('hook_title', 'forFacilities.roi.hook.title')) ?></h3>
          <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-white/55"><?= e($roiText('hook_desc', 'forFacilities.roi.hook.desc')) ?></p>
          <div class="mt-5 flex justify-center">
            <a class="group inline-flex items-center gap-2 rounded-full bg-accent px-7 py-3 text-sm font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] hover:bg-accent-neon active:scale-[0.98]" href="<?= base_url('contact.php') ?>">
              <?= e($roiText('hook_cta_text', 'forFacilities.roi.hook.cta')) ?>
              <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z"/></svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="grid gap-12 lg:grid-cols-2 lg:items-start">
  <div>
  <div class="max-w-3xl "  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('forFacilities.engagement.title')) ?>
  </h2>
  </div>
  <p class="mt-5 text-base leading-relaxed text-white/60"  data-reveal="up">
  <?= e(__('forFacilities.engagement.desc')) ?>
  </p>
  </div>
  <div class="flex flex-col gap-3">
  <div class="flex items-center gap-3 rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5"  data-reveal="right">
  <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-accent/15">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  </div>
  <span class="text-sm text-white/70">
  <?= e(__('forFacilities.engagement.items.1')) ?>
  </span>
  </div>
  <div class="flex items-center gap-3 rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5"  data-reveal="right">
  <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-accent/15">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  </div>
  <span class="text-sm text-white/70">
  <?= e(__('forFacilities.engagement.items.2')) ?>
  </span>
  </div>
  <div class="flex items-center gap-3 rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5"  data-reveal="right">
  <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-accent/15">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  </div>
  <span class="text-sm text-white/70">
  <?= e(__('forFacilities.engagement.items.3')) ?>
  </span>
  </div>
  <div class="flex items-center gap-3 rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5"  data-reveal="right">
  <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-accent/15">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  </div>
  <span class="text-sm text-white/70">
  <?= e(__('forFacilities.engagement.items.4')) ?>
  </span>
  </div>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900">
  <div class="container-x">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('forFacilities.objections.title')) ?>
  </h2>
  </div>
  <div class="mx-auto mt-12 max-w-5xl grid gap-5 md:grid-cols-2">
  <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 md:p-7"  data-reveal="up">
  <div class="mb-4 flex items-center gap-3">
  <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z">
  </path>
  </svg>
  </div>
  <h3 class="text-base font-bold text-white">“<?= e(__('forFacilities.objections.items.gdpr.question')) ?>”</h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55"><?= e(__('forFacilities.objections.items.gdpr.answer')) ?></p>
  <ul class="mt-5 flex flex-col gap-2">
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.gdpr.items.1')) ?>
  </span>
  </li>
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.gdpr.items.2')) ?>
  </span>
  </li>
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.gdpr.items.3')) ?>
  </span>
  </li>
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.gdpr.items.4')) ?>
  </span>
  </li>
  </ul>
  </div>
  <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 md:p-7"  data-reveal="up">
  <div class="mb-4 flex items-center gap-3">
  <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M5 12.55a11 11 0 0 1 14.08 0">
  </path>
  <path d="M1.42 9a16 16 0 0 1 21.16 0">
  </path>
  <path d="M8.53 16.11a6 6 0 0 1 6.95 0">
  </path>
  <line x1="12" y1="20" x2="12.01" y2="20">
  </line>
  </svg>
  </div>
  <h3 class="text-base font-bold text-white">“<?= e(__('forFacilities.objections.items.tech.question')) ?>”</h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.objections.items.tech.answer')) ?>
  </p>
  <ul class="mt-5 flex flex-col gap-2">
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.tech.items.1')) ?>
  </span>
  </li>
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.tech.items.2')) ?>
  </span>
  </li>
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.tech.items.3')) ?>
  </span>
  </li>
  <li class="flex items-start gap-2">
  <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  <span class="text-xs text-white/60">
  <?= e(__('forFacilities.objections.items.tech.items.4')) ?>
  </span>
  </li>
  </ul>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute -inset-y-[30%] inset-x-0 will-change-transform" >
  <img src="<?= asset('images/gen_marketing_bright.jpg') ?>" alt="" class="h-full w-full object-cover object-center" style="opacity:0.65;filter:brightness(1.15)"/>
  </div>
  </div>
  <div class="absolute inset-0 bg-gradient-to-b from-ink-900/85 via-ink-900/35 to-ink-900/85">
  </div>
  </div>
  <div class="container-x relative">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('forFacilities.marketing.title')) ?>
  </h2>
  <p class="mt-4 text-lg text-white/70 md:text-xl">
  <?= e(__('forFacilities.marketing.desc')) ?>
  </p>
  </div>
  <div class="mt-12 grid gap-5 sm:grid-cols-3">
  <div class="rounded-2xl border border-white/8 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30"  data-reveal="up">
  <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
  </polygon>
  </svg>
  </div>
  <h3 class="mb-2 text-sm font-semibold text-white">
  <?= e(__('forFacilities.marketing.items.1.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.marketing.items.1.desc')) ?>
  </p>
  </div>
  <div class="rounded-2xl border border-white/8 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30"  data-reveal="up">
  <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="18" cy="5" r="3">
  </circle>
  <circle cx="6" cy="12" r="3">
  </circle>
  <circle cx="18" cy="19" r="3">
  </circle>
  <line x1="8.59" y1="13.51" x2="15.42" y2="17.49">
  </line>
  <line x1="15.41" y1="6.51" x2="8.59" y2="10.49">
  </line>
  </svg>
  </div>
  <h3 class="mb-2 text-sm font-semibold text-white">
  <?= e(__('forFacilities.marketing.items.2.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.marketing.items.2.desc')) ?>
  </p>
  </div>
  <div class="rounded-2xl border border-white/8 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30"  data-reveal="up">
  <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6">
  </path>
  <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18">
  </path>
  <path d="M4 22h16">
  </path>
  <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22">
  </path>
  <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22">
  </path>
  <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z">
  </path>
  </svg>
  </div>
  <h3 class="mb-2 text-sm font-semibold text-white">
  <?= e(__('forFacilities.marketing.items.3.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.marketing.items.3.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('forFacilities.whatYouDo.title')) ?>
  </h2>
  </div>
  <div class="mt-12 grid gap-5 sm:grid-cols-2">
  <div class="relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-5xl font-bold text-white/[0.04] select-none">01</span>
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forFacilities.whatYouDo.items.1.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.whatYouDo.items.1.desc')) ?>
  </p>
  </div>
  <div class="relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-5xl font-bold text-white/[0.04] select-none">02</span>
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forFacilities.whatYouDo.items.2.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.whatYouDo.items.2.desc')) ?>
  </p>
  </div>
  <div class="relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-5xl font-bold text-white/[0.04] select-none">03</span>
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forFacilities.whatYouDo.items.3.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.whatYouDo.items.3.desc')) ?>
  </p>
  </div>
  <div class="relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-5xl font-bold text-white/[0.04] select-none">04</span>
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forFacilities.whatYouDo.items.4.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forFacilities.whatYouDo.items.4.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 70% 50% at 50% 100%, rgba(253,230,138,0.12), transparent 70%)" aria-hidden="true">
  </div>
  <div class="container-x relative text-center">
  <div  data-reveal="up">
  <h2 class="mx-auto max-w-xl font-display text-3xl font-bold tracking-tight text-balance md:text-4xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
  <?= e(__('forFacilities.cta.title')) ?>
  </h2>
  <p class="mx-auto mt-4 max-w-md text-base text-white/50">
  <?= e(__('forFacilities.cta.subtitle')) ?>
  </p>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon hover:shadow-[0_0_50px_rgba(254,243,199,0.5)]" href="<?= base_url('contact.php') ?>">
  <?= e(__('forFacilities.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<script>
  const ROI_CONFIG = <?= json_encode($roiConfig, JSON_UNESCAPED_UNICODE) ?>;
  const SHARE_LABEL = <?= json_encode(__('forFacilities.roi.results.share'), JSON_UNESCAPED_UNICODE) ?>;
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

    // Billing Guide: monthly facility-wide sales pick the tier; it applies to all sales of the month
    const totalVideos = courts * videos * days;
    const price = parseFloat(sp.price || 0);
    const tier = (ROI_CONFIG.share_tiers || []).filter(t => totalVideos >= t.min).pop() || { facility_pct: 20 };
    const symbol = sp.currency_symbol || '€';

    const monthlyTotal = Math.round(totalVideos * price * tier.facility_pct / 100);
    const yearlyTotal = Math.round(monthlyTotal * 12);

    if (labelMonthlyShare) {
      labelMonthlyShare.textContent = SHARE_LABEL.replace('{pct}', tier.facility_pct);
    }

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
        b.className = 'roi-sport-btn rounded-full px-5 py-2 text-xs font-semibold uppercase tracking-wider transition text-white/55 hover:text-white';
      });
      btn.className = 'roi-sport-btn rounded-full px-5 py-2 text-xs font-semibold uppercase tracking-wider transition bg-accent text-ink-900';

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
