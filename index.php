<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('home.hero.title');

// Fetch facilities for quick match finder
$facilities = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, name, city, sport FROM facilities ORDER BY name ASC");
        $facilities = $stmt->fetchAll();
    } catch (Exception $e) {
        $facilities = [];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative min-h-screen bg-ink-900">

  <!-- HERO SECTION (Full-Screen Immersive Banner with Parallax Zoom) -->
  <section id="hero-section" class="relative min-h-[90vh] md:min-h-screen flex items-center justify-center overflow-hidden bg-ink-950 pt-32 pb-24 md:pt-40 md:pb-32">
    <!-- Hero Background Image & Multi-layered Gradients -->
    <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
      <img 
        id="hero-bg-img"
        src="<?= asset('images/gen_hero_friends.jpg') ?>" 
        alt="Replash Hero" 
        class="w-full h-full object-cover object-[center_25%] scale-105 will-change-transform transition-transform duration-75 ease-out" 
        style="opacity: 0.38;"
      />
      <!-- Top subtle vignette -->
      <div class="absolute inset-0 bg-gradient-to-b from-ink-950/60 via-transparent to-transparent"></div>
      <!-- Deep Black Bottom Fade (alt kısım belirgin siyah) -->
      <div class="absolute inset-x-0 bottom-0 h-[80%] bg-gradient-to-t from-ink-950 via-ink-950/90 to-transparent"></div>
      <!-- Side Vignette -->
      <div class="absolute inset-0 bg-gradient-to-r from-ink-950/60 via-transparent to-ink-950/60"></div>
    </div>

    <!-- Radial Glow and Grid Noise -->
    <div class="pointer-events-none absolute -top-60 left-1/2 h-[700px] w-[700px] -translate-x-1/2 rounded-full" style="background: radial-gradient(circle, rgba(253,230,138,0.12) 0%, transparent 70%);" aria-hidden="true"></div>
    <div class="absolute inset-0 grid-noise opacity-10 pointer-events-none" aria-hidden="true"></div>

    <div id="hero-content" class="w-full max-w-6xl mx-auto px-4 sm:px-6 text-center relative z-10 flex flex-col items-center">
      <!-- Main Heading (Centered) -->
      <h1 class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold tracking-tight text-center leading-[1.08] mb-6" style="background: linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.45)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
        <?= e(__('home.hero.title')) ?>
      </h1>

      <!-- Subtitle (Centered) -->
      <p class="text-base sm:text-lg md:text-xl text-neutral-300 max-w-3xl mx-auto text-center leading-relaxed mb-10">
        <?= e(__('home.hero.subtitle')) ?>
      </p>

      <!-- CTA Buttons (Centered) -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full sm:w-auto">
        <a class="group inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] duration-300 hover:bg-accent-neon hover:shadow-[0_0_60px_rgba(254,243,199,0.5)]" href="<?= base_url('contact.php') ?>">
          <?= e(__('home.hero.cta')) ?>
          <svg class="ml-2 h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z"></path>
          </svg>
        </a>
        <a class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/[0.04] px-8 py-3.5 text-base font-medium text-white/70 backdrop-blur transition-colors active:scale-[0.98] duration-200 hover:border-white/20 hover:bg-white/[0.08] hover:text-white" href="<?= base_url('how-it-works.php') ?>">
          <?= e(__('home.hero.ctaSecondary')) ?>
        </a>
      </div>
    </div>
  </section>

  <?php 
  $searchFacilityEnabled = (string)get_setting('search_by_facility_enabled', '1') === '1';
  $searchCodeEnabled = (string)get_setting('search_by_code_enabled', '1') === '1';
  ?>

  <!-- QUICK MATCH FINDER CARD (Contained & Centered) -->
  <?php if (!$searchFacilityEnabled && $searchCodeEnabled): ?>
    <!-- STANDALONE HORIZONTAL CODE SEARCH (Prominent & Wide) -->
    <section class="relative -mt-10 z-20 max-w-4xl mx-auto px-4 sm:px-6 mb-20">
      <div class="card-elevated rounded-3xl border border-white/10 bg-ink-900/95 p-6 sm:p-8 shadow-[0_20px_50px_rgba(0,0,0,0.8)] backdrop-blur-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-white/[0.06]">
          <div>
            <span class="text-xs font-semibold uppercase tracking-widest text-accent"><?= e(__('searchSettings.codeOnly.eyebrow')) ?></span>
            <h3 class="font-display text-2xl font-bold text-white mt-1"><?= e(__('searchSettings.codeOnly.title')) ?></h3>
          </div>
          <p class="text-xs sm:text-sm text-neutral-400 max-w-sm">
            <?= e(__('searchSettings.codeOnly.desc')) ?>
          </p>
        </div>

        <form action="<?= base_url('find.php') ?>" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
          <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-accent">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <input 
              type="text" 
              name="code" 
              required 
              placeholder="<?= e(__('searchSettings.codeOnly.placeholder')) ?>" 
              class="w-full rounded-2xl border border-white/15 bg-ink-950/90 pl-12 pr-4 py-4 text-base sm:text-lg font-mono font-bold tracking-wider text-white uppercase placeholder:normal-case placeholder:font-sans placeholder:text-neutral-500 placeholder:text-sm focus:border-accent focus:outline-none transition shadow-inner"
            >
          </div>

          <button type="submit" class="w-full sm:w-auto rounded-2xl bg-accent px-8 py-4 text-base font-bold text-ink-900 shadow-glow hover:bg-accent-neon transition whitespace-nowrap flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span><?= e(__('searchSettings.codeOnly.button')) ?></span>
            <span>&rarr;</span>
          </button>
        </form>

        <div class="mt-4 pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-neutral-400">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
            <?= e(__('searchSettings.codeOnly.hint')) ?>
          </span>
          <span class="font-mono text-neutral-500 text-[11px]">Örn: AEXF-BFFG / 343434</span>
        </div>
      </div>
    </section>
  <?php else: ?>
    <!-- FACILITY & DATE FILTER FORM (With Optional Code Shortcut) -->
    <section class="relative -mt-10 z-20 max-w-6xl mx-auto px-4 sm:px-6 mb-20">
      <div class="rounded-3xl border border-white/10 bg-ink-900/90 p-6 sm:p-8 shadow-[0_20px_50px_rgba(0,0,0,0.8)] backdrop-blur-2xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6 pb-5 border-b border-white/[0.06]">
          <div>
            <span class="text-xs font-semibold uppercase tracking-widest text-accent"><?= e(__('home.findPreview.eyebrow')) ?></span>
            <h3 class="font-display text-2xl font-bold text-white mt-1"><?= e(__('home.findPreview.title')) ?></h3>
          </div>
          <p class="text-xs sm:text-sm text-white/50 max-w-sm">
            <?= e(__('home.findPreview.desc')) ?>
          </p>
        </div>

        <form action="<?= base_url('find.php') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Facility Select -->
          <div>
            <label class="block text-xs font-medium text-white/50 mb-1.5"><?= e(__('home.findPreview.fields.facility')) ?></label>
            <select name="facility_id" id="home-facility-select" class="w-full rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2.5 text-sm text-white focus:border-accent focus:outline-none transition">
              <option value="" class="bg-ink-900 text-white/70"><?= e(__('home.findPreview.fields.facilityPlaceholder')) ?></option>
              <?php foreach ($facilities as $fac): ?>
                <option value="<?= e($fac['id']) ?>" class="bg-ink-900 text-white"><?= e($fac['name']) ?> (<?= e($fac['city']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Date Select -->
          <div>
            <label class="block text-xs font-medium text-white/50 mb-1.5"><?= e(__('home.findPreview.fields.date')) ?></label>
            <input type="date" name="date" id="home-date-select" value="<?= date('Y-m-d') ?>" class="w-full rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2.5 text-sm text-white focus:border-accent focus:outline-none transition">
          </div>

          <!-- Time Slot Select -->
          <div>
            <label class="block text-xs font-medium text-white/50 mb-1.5"><?= e(__('home.findPreview.fields.time')) ?></label>
            <select name="time_slot" id="home-slot-select" class="w-full rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2.5 text-sm text-white focus:border-accent focus:outline-none transition">
              <option value="" class="bg-ink-900 text-white/70"><?= e(__('home.findPreview.fields.timePlaceholder')) ?></option>
              <option value="17:00–18:00" class="bg-ink-900 text-white">17:00 – 18:00</option>
              <option value="18:00–19:00" class="bg-ink-900 text-white">18:00 – 19:00</option>
              <option value="19:00–20:00" class="bg-ink-900 text-white">19:00 – 20:00</option>
              <option value="20:00–21:00" selected class="bg-ink-900 text-white">20:00 – 21:00</option>
              <option value="21:00–22:00" class="bg-ink-900 text-white">21:00 – 22:00</option>
            </select>
          </div>

          <!-- Submit Button -->
          <div class="flex items-end">
            <button type="submit" class="w-full rounded-xl bg-accent px-4 py-2.5 text-sm font-semibold text-ink-900 shadow-glow transition hover:bg-accent-neon flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <?= e(__('home.findPreview.button')) ?>
            </button>
          </div>
        </form>

        <?php if ($searchCodeEnabled): ?>
          <!-- Access Code Shortcut -->
          <div class="mt-4 pt-4 border-t border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/50">
            <span><?= e(__('home.findPreview.fields.accessCode')) ?></span>
            <form action="<?= base_url('find.php') ?>" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
              <input type="text" name="code" placeholder="AEXF-BFFG" class="rounded-lg border border-white/10 bg-black/40 px-3 py-1.5 text-xs text-white uppercase tracking-widest focus:border-accent focus:outline-none">
              <button type="submit" class="rounded-lg border border-accent/40 bg-accent/10 px-3 py-1.5 text-xs font-semibold text-accent hover:bg-accent hover:text-ink-900 transition">
                &rarr;
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- INTRO / SLOGAN BANNER ("Bizimle Konuşun") -->
  <section class="relative overflow-hidden border-t border-white/5 bg-ink-900 py-20 md:py-28">
    <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse 60% 50% at 50% 50%, rgba(253,230,138,0.08), transparent 70%);" aria-hidden="true"></div>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-accent/40 to-transparent" aria-hidden="true"></div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-accent/20 to-transparent" aria-hidden="true"></div>
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative text-center">
      <h2 class="font-display text-4xl font-bold leading-[1.08] tracking-tight text-balance md:text-5xl lg:text-6xl text-center" style="background: linear-gradient(to bottom, #ffffff 30%, rgba(255,255,255,0.55)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
        <?= e(__('home.intro.slogan')) ?>
      </h2>
      <p class="mt-6 font-display text-xl uppercase tracking-wider text-accent md:text-2xl text-center">
        <?= e(__('home.intro.tagline')) ?>
      </p>
      <p class="mx-auto mt-5 max-w-2xl text-sm leading-relaxed text-white/45 text-center">
        <?= e(__('home.intro.sports')) ?>
      </p>
      <div class="mt-9 flex justify-center">
        <a class="group inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-7 py-3 text-sm font-semibold tracking-wide text-accent transition-colors active:scale-[0.98] hover:bg-accent hover:text-ink-900 hover:shadow-glow" href="<?= base_url('contact.php') ?>">
          <?= e(__('home.intro.cta')) ?>
          <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z"></path>
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- TWO-LAYER VALUE SECTION (Nicely Contained & Centered) -->
  <section class="py-20 md:py-28 border-t border-white/5 bg-section-gradient relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse 50% 60% at 50% 30%, rgba(253,230,138,0.06), transparent 70%);" aria-hidden="true"></div>
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
      
      <!-- Section Header (Centered) -->
      <div class="text-center max-w-3xl mx-auto mb-14">
        <span class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-widest text-accent mb-4">
          <?= e(__('home.twoLayer.eyebrow')) ?>
        </span>
        <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-4 leading-tight">
          <?= e(__('home.twoLayer.title')) ?>
        </h2>
        <p class="text-neutral-400 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto">
          <?= e(__('home.twoLayer.subtitle')) ?>
        </p>
      </div>

      <!-- 2 Balanced Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
        
        <!-- Card 01: Players -->
        <div class="rounded-3xl border border-white/10 bg-white/[0.02] p-7 sm:p-9 flex flex-col justify-between backdrop-blur-md">
          <div>
            <div class="flex items-center justify-between gap-3 mb-6">
              <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-widest text-white/70">
                <?= e(__('home.twoLayer.layer1.label')) ?>
              </span>
              <span class="flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-ink-900 font-display text-sm font-bold text-white/70">
                01
              </span>
            </div>

            <span class="block font-display text-5xl sm:text-6xl font-bold leading-none text-accent mb-2">
              <?= e(__('home.twoLayer.layer1.stat', '0%')) ?>
            </span>
            <h3 class="font-display font-bold text-xl sm:text-2xl text-white mb-3 leading-snug">
              <?= e(__('home.twoLayer.layer1.title')) ?>
            </h3>
            <p class="text-neutral-400 text-sm sm:text-base leading-relaxed">
              <?= e(__('home.twoLayer.layer1.desc')) ?>
            </p>
          </div>
        </div>

        <!-- Card 02: Facility (Elevated Accent Card) -->
        <div class="rounded-3xl border border-accent/25 bg-accent/[0.04] p-7 sm:p-9 flex flex-col justify-between card-elevated backdrop-blur-md">
          <div>
            <div class="flex items-center justify-between gap-3 mb-6">
              <span class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-widest text-accent">
                <?= e(__('home.twoLayer.layer2.label')) ?>
              </span>
              <span class="flex h-9 w-9 items-center justify-center rounded-full border border-accent/40 bg-accent font-display text-sm font-bold text-ink-900 shadow-glow-sm">
                02
              </span>
            </div>

            <h3 class="font-display font-bold text-2xl sm:text-3xl text-white mb-3 leading-snug mt-3">
              <?= e(__('home.twoLayer.layer2.title')) ?>
            </h3>
            <p class="text-neutral-300/80 text-sm sm:text-base leading-relaxed mt-4">
              <?= e(__('home.twoLayer.layer2.desc')) ?>
            </p>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- WHY FACILITIES SECTION (Balanced 2-Column with Compact Grid) -->
  <section class="py-20 md:py-28 border-t border-white/5 bg-ink-900 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
      <img src="<?= asset('images/gen_padel_club_empty.jpg') ?>" alt="" class="h-full w-full object-cover object-center opacity-10" />
      <div class="absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/80 to-ink-900"></div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
        
        <!-- Left Column: Title & CTA -->
        <div class="lg:col-span-5 text-left">
          <span class="text-xs font-semibold uppercase tracking-widest text-accent"><?= e(__('home.whyFacilities.eyebrow')) ?></span>
          <h2 class="font-display text-3xl sm:text-4xl font-bold leading-tight text-white mt-2 mb-6" style="background: linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.55)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            <?= e(__('home.whyFacilities.title')) ?>
          </h2>
          <a class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/10 px-6 py-2.5 text-sm font-semibold text-accent transition hover:bg-accent/20" href="<?= base_url('for-facilities.php') ?>">
            <?= e(__('home.whyFacilities.cta', 'Per i Centri')) ?>
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z"></path>
            </svg>
          </a>
        </div>

        <!-- Right Column: 3 Feature Cards -->
        <div class="lg:col-span-7 flex flex-col gap-3.5">
          <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="flex items-start gap-3.5 rounded-2xl border border-white/8 bg-white/[0.03] p-4 sm:p-5 backdrop-blur-sm">
              <div class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full border border-accent/30 bg-accent/10 text-accent">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-sm sm:text-base font-semibold text-white"><?= e(__("home.whyFacilities.items.{$i}.title")) ?></h3>
                <p class="mt-1 text-xs sm:text-sm leading-relaxed text-white/50"><?= e(__("home.whyFacilities.items.{$i}.desc")) ?></p>
              </div>
            </div>
          <?php endfor; ?>
        </div>

      </div>
    </div>
  </section>

  <!-- WHAT PLAYERS GET SECTION (Balanced 2-Column with Compact Grid) -->
  <section class="py-20 md:py-28 border-t border-white/5 bg-section-gradient relative overflow-hidden">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
        
        <!-- Cards Column (Left on Desktop, Right on Mobile) -->
        <div class="lg:col-span-7 flex flex-col gap-3.5 order-2 lg:order-1">
          <!-- Card 1: Watch -->
          <div class="flex items-start gap-3.5 rounded-2xl border border-white/8 bg-white/[0.03] p-4 sm:p-5 backdrop-blur-sm">
            <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl border border-accent/30 bg-accent/10 text-accent">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
            </div>
            <div>
              <h3 class="text-sm sm:text-base font-semibold text-white"><?= e(__('home.whatPlayersGet.items.1.title')) ?></h3>
              <p class="mt-1 text-xs sm:text-sm leading-relaxed text-white/50"><?= e(__('home.whatPlayersGet.items.1.desc')) ?></p>
            </div>
          </div>

          <!-- Card 2: Clip Highlights -->
          <div class="flex items-start gap-3.5 rounded-2xl border border-white/8 bg-white/[0.03] p-4 sm:p-5 backdrop-blur-sm">
            <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl border border-accent/30 bg-accent/10 text-accent">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle>
                <line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
              </svg>
            </div>
            <div>
              <h3 class="text-sm sm:text-base font-semibold text-white"><?= e(__('home.whatPlayersGet.items.2.title')) ?></h3>
              <p class="mt-1 text-xs sm:text-sm leading-relaxed text-white/50"><?= e(__('home.whatPlayersGet.items.2.desc')) ?></p>
            </div>
          </div>

          <!-- Card 3: Social Share -->
          <div class="flex items-start gap-3.5 rounded-2xl border border-white/8 bg-white/[0.03] p-4 sm:p-5 backdrop-blur-sm">
            <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl border border-accent/30 bg-accent/10 text-accent">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
              </svg>
            </div>
            <div>
              <h3 class="text-sm sm:text-base font-semibold text-white"><?= e(__('home.whatPlayersGet.items.3.title')) ?></h3>
              <p class="mt-1 text-xs sm:text-sm leading-relaxed text-white/50"><?= e(__('home.whatPlayersGet.items.3.desc')) ?></p>
            </div>
          </div>
        </div>

        <!-- Title & CTA Column (Right on Desktop, Left on Mobile) -->
        <div class="lg:col-span-5 text-left order-1 lg:order-2">
          <span class="text-xs font-semibold uppercase tracking-widest text-accent"><?= e(__('home.whatPlayersGet.eyebrow')) ?></span>
          <h2 class="font-display text-3xl sm:text-4xl font-bold leading-tight text-white mt-2 mb-6" style="background: linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.55)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            <?= e(__('home.whatPlayersGet.title')) ?>
          </h2>
          <a class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/10 px-6 py-2.5 text-sm font-semibold text-accent transition hover:bg-accent/20" href="<?= base_url('for-players.php') ?>">
            <?= e(__('home.whatPlayersGet.cta', 'Per i Giocatori')) ?>
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z"></path>
            </svg>
          </a>
        </div>

      </div>
    </div>
  </section>

  <!-- FAQ SECTION (Contained & Centered) -->
  <section class="py-20 md:py-28 border-t border-white/5 bg-ink-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
      
      <div class="text-center mb-12">
        <span class="text-xs font-semibold uppercase tracking-widest text-accent mb-2 block"><?= e(__('home.faq.eyebrow')) ?></span>
        <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl text-white">
          <?= e(__('home.faq.title')) ?>
        </h2>
        <p class="mt-4 text-base sm:text-lg text-white/60">
          <?= e(__('home.faq.subtitle')) ?>
        </p>
      </div>

      <div class="divide-y divide-white/[0.06]">
        <?php for ($i = 1; $i <= 9; $i++): 
          $q = __("home.faq.items.{$i}.q");
          $a = __("home.faq.items.{$i}.a");
          if (!$q || $q === "home.faq.items.{$i}.q") continue;
          $isOpen = ($i === 1);
        ?>
          <div class="faq-item">
            <button type="button" class="faq-accordion-btn flex w-full items-start justify-between gap-4 py-5 text-left transition-colors" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
              <span class="text-base font-semibold leading-snug transition-colors duration-200 <?= $isOpen ? 'text-white' : 'text-white/75 hover:text-white' ?>">
                <?= e($q) ?>
              </span>
              <span class="faq-icon-wrapper mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border transition-all duration-300 <?= $isOpen ? 'border-accent/40 bg-accent/15 text-accent' : 'border-white/15 bg-white/5 text-white/40' ?>">
                <svg class="h-3 w-3 transition-transform duration-300 <?= $isOpen ? 'rotate-45' : 'rotate-0' ?>" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <path d="M6 1v10M1 6h10"></path>
                </svg>
              </span>
            </button>
            <div class="faq-panel overflow-hidden transition-all duration-300 <?= $isOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' ?>">
              <p class="pb-5 pr-8 text-sm leading-relaxed text-white/60">
                <?= e($a) ?>
              </p>
            </div>
          </div>
        <?php endfor; ?>
      </div>

    </div>
  </section>

  <!-- BOTTOM CALL TO ACTION -->
  <section class="py-20 md:py-28 border-t border-white/5 bg-ink-900 relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse 70% 50% at 50% 100%, rgba(253,230,138,0.12), transparent 70%);" aria-hidden="true"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative text-center">
      <h2 class="mx-auto max-w-2xl font-display text-4xl font-bold leading-[1.1] tracking-tight text-balance md:text-5xl" style="background: linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
        <?= e(__('home.cta.title')) ?>
      </h2>
      <div class="mt-8">
        <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-4 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon hover:shadow-[0_0_60px_rgba(254,243,199,0.5)]" href="<?= base_url('for-facilities.php') ?>">
          <?= e(__('home.cta.button')) ?>
        </a>
      </div>
    </div>
  </section>

</main>

<!-- Interactive Hero Background Zoom on Scroll & FAQ Scripts -->
<script>
  (function () {
    // 1. Direct Hero Image Zoom On Scroll (Runs immediately, no caching issues)
    var heroBg = document.getElementById('hero-bg-img');
    var heroSec = document.getElementById('hero-section');
    if (heroBg && heroSec) {
      function applyHeroZoom() {
        var top = window.pageYOffset || document.documentElement.scrollTop || 0;
        var h = heroSec.offsetHeight || 650;
        if (top <= h * 1.5) {
          var ratio = Math.min(1.5, Math.max(0, top / (h * 0.75)));
          // Scales image up dynamically from 1.05 to 1.45
          var s = 1.05 + (ratio * 0.35);
          heroBg.style.transform = 'scale(' + s.toFixed(3) + ')';
        }
      }
      window.addEventListener('scroll', applyHeroZoom, { passive: true });
      window.addEventListener('resize', applyHeroZoom, { passive: true });
      applyHeroZoom();
    }

    // 2. Accordion Script for FAQs
    var faqButtons = document.querySelectorAll('.faq-accordion-btn');
    faqButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var isExpanded = this.getAttribute('aria-expanded') === 'true';
        var panel = this.nextElementSibling;
        var iconWrapper = this.querySelector('.faq-icon-wrapper');
        var icon = this.querySelector('svg');
        var titleSpan = this.querySelector('span:first-child');

        if (isExpanded) {
          this.setAttribute('aria-expanded', 'false');
          panel.classList.remove('max-h-96', 'opacity-100');
          panel.classList.add('max-h-0', 'opacity-0');
          iconWrapper.className = 'faq-icon-wrapper mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border transition-all duration-300 border-white/15 bg-white/5 text-white/40';
          icon.classList.remove('rotate-45');
          icon.classList.add('rotate-0');
          titleSpan.classList.remove('text-white');
          titleSpan.classList.add('text-white/75');
        } else {
          this.setAttribute('aria-expanded', 'true');
          panel.classList.remove('max-h-0', 'opacity-0');
          panel.classList.add('max-h-96', 'opacity-100');
          iconWrapper.className = 'faq-icon-wrapper mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border transition-all duration-300 border-accent/40 bg-accent/15 text-accent';
          icon.classList.remove('rotate-0');
          icon.classList.add('rotate-45');
          titleSpan.classList.remove('text-white/75');
          titleSpan.classList.add('text-white');
        }
      });
    });
  })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
