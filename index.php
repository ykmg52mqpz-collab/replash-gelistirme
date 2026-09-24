<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('home.hero.title');
$metaTitle = __('meta.home.title');
$metaDescription = __('meta.home.description');

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

  <!-- HERO (replash.info) -->
  <section id="hero-section" class="relative flex min-h-[100svh] items-center overflow-hidden bg-ink-900 pt-28 pb-20 md:pt-32 md:pb-24">
  <div id="hero-parallax" class="absolute -inset-y-[25%] inset-x-0 will-change-transform" style="transform:scale(1.1)" aria-hidden="true">
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_hero_friends.webp') ?>" alt="" fetchpriority="high" class="h-full w-full object-cover" style="opacity:0.75;object-position:center 35%"/>
  <div class="absolute inset-0 bg-gradient-to-b from-ink-900/55 via-ink-900/55 to-ink-900/95">
  </div>
  <div class="absolute inset-0 bg-gradient-to-r from-ink-900/40 via-transparent to-ink-900/40">
  </div>
  </div>
  <div class="pointer-events-none absolute -top-60 left-1/2 h-[600px] w-[600px] -translate-x-1/2 rounded-full" style="background:radial-gradient(circle, rgba(253,230,138,0.12) 0%, transparent 70%)" aria-hidden="true">
  </div>
  <div class="absolute inset-0 grid-noise opacity-10" aria-hidden="true">
  </div>
  <div class="container-x relative z-10">
  <div id="hero-text" class="mx-auto max-w-4xl text-center will-change-transform">
  <div  data-reveal="up">
  <h1 class="font-display text-6xl font-bold leading-[1.05] tracking-tight text-balance md:text-7xl lg:text-8xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.45));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" data-reveal="up">
  <?= e(__('home.hero.title')) ?>
  </h1>
  <p class="mt-6 text-lg text-white/55 md:text-xl"  data-reveal="up">
  <?= e(__('home.hero.subtitle')) ?>
  </p>
  <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row"  data-reveal="up">
  <a class="group inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] duration-300 hover:bg-accent-neon hover:shadow-[0_0_60px_rgba(254,243,199,0.5)]" href="<?= base_url('contact.php') ?>">
  <?= e(__('home.hero.cta')) ?>
  <svg class="ml-2 h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
  <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z">
  </path>
  </svg>
  </a>
  <a class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/[0.04] px-8 py-3.5 text-base font-medium text-white/70 backdrop-blur transition-colors active:scale-[0.98] duration-200 hover:border-white/20 hover:bg-white/[0.08] hover:text-white" href="<?= base_url('for-facilities.php') ?>">
  <?= e(__('home.hero.ctaSecondary')) ?>
  </a>
  </div>
  </div>
  </div>
  </div>
  </section>

  <?php 
  $searchFacilityEnabled = (string)get_setting('search_by_facility_enabled', '1') === '1';
  $searchCodeEnabled = (string)get_setting('search_by_code_enabled', '1') === '1';
  ?>

  <!-- QUICK MATCH FINDER CARD (Contained & Centered) -->
  <?php if (!$searchFacilityEnabled && $searchCodeEnabled): ?>
    <!-- KOD KARTI: "Maçını Bul" sayfasındaki kartla aynı -->
    <section class="relative -mt-10 z-20 mx-auto mb-20 max-w-2xl px-4 sm:px-6" data-reveal="up">
      <form action="<?= base_url('find.php') ?>" method="GET" class="card-elevated rounded-3xl border border-white/10 bg-ink-900/95 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.6)] backdrop-blur md:p-8">
        <div class="mb-5 flex items-center gap-2.5">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FDE68A" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 118 0v3"></path></svg>
          <h3 class="font-display text-lg font-bold text-white"><?= e(__('find.watch.title')) ?></h3>
        </div>
        <p class="mb-5 text-sm text-white/50"><?= e(__('find.watch.subtitle')) ?></p>
        <div class="flex flex-col gap-2">
          <label for="home-code" class="text-xs font-semibold uppercase tracking-wider text-white/60"><?= e(__('find.watch.codeLabel')) ?></label>
          <input id="home-code" type="text" name="code" required placeholder="0000" maxlength="9" inputmode="numeric" autocomplete="one-time-code" spellcheck="false" class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 text-center font-mono text-2xl font-bold tracking-[0.3em] text-white outline-none transition placeholder:tracking-[0.3em] placeholder:text-white/25 focus:border-accent" />
        </div>
        <button type="submit" class="mt-5 w-full rounded-xl bg-accent px-6 py-3.5 text-sm font-bold text-ink-900 shadow-glow-sm transition hover:shadow-glow"><?= e(__('find.watch.button')) ?></button>
      </form>
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
              <input type="text" name="code" placeholder="0000" inputmode="numeric" class="rounded-lg border border-white/10 bg-black/40 px-3 py-1.5 text-xs text-white uppercase tracking-widest focus:border-accent focus:outline-none">
              <button type="submit" class="rounded-lg border border-accent/40 bg-accent/10 px-3 py-1.5 text-xs font-semibold text-accent hover:bg-accent hover:text-ink-900 transition">
                &rarr;
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- NASIL ÇALIŞIR ÖZETİ: 3 adım, ayrıntı Nasıl Çalışır sayfasında -->
  <section class="bg-ink-900 pb-16 md:pb-20">
    <div class="container-x">
      <div class="mx-auto max-w-5xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3" data-reveal="up">
          <h2 class="font-display text-2xl font-bold text-white md:text-3xl"><?= e(__('home.flow.title')) ?></h2>
          <a href="<?= base_url('how-it-works.php') ?>" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-accent"><?= e(__('home.flow.link')) ?>
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z"/></svg>
          </a>
        </div>
        <ol class="grid gap-3 md:grid-cols-3">
          <?php foreach ([1, 2, 3] as $i): ?>
            <li class="rounded-2xl border border-white/10 bg-white/[0.02] p-5" data-reveal="up">
              <span class="grid h-9 w-9 place-items-center rounded-full border border-accent/30 font-display text-sm font-bold text-accent"><?= $i ?></span>
              <h3 class="mt-4 font-display text-lg font-bold text-white"><?= e(__("home.flow.items.{$i}.title")) ?></h3>
              <p class="mt-1.5 text-sm leading-relaxed text-white/55"><?= e(__("home.flow.items.{$i}.desc")) ?></p>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </section>

  <!-- INTRO (replash.info) -->


  <!-- TWO-LAYER VALUE SECTION (replash.info ile aynı: yapışkan başlık + zaman çizelgesi) -->
  <section class="section-y border-t border-white/5 bg-section-gradient relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 50% 60% at 15% 30%, rgba(253,230,138,0.06), transparent 70%)" aria-hidden="true">
  </div>
  <div class="container-x relative grid gap-12 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:gap-16">
  <div class="lg:sticky lg:top-28 lg:self-start"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-[1.1] tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('home.twoLayer.title')) ?>
  </h2>
  <p class="mt-5 max-w-md text-lg leading-relaxed text-white/60">
  <?= e(__('home.twoLayer.subtitle')) ?>
  </p>
  </div>
  <div class="relative">
  <div class="absolute bottom-6 left-[27px] top-6 w-px bg-gradient-to-b from-accent/30 via-accent/15 to-transparent" aria-hidden="true">
  </div>
  <div class="flex flex-col gap-6">
  <div class="relative flex gap-5"  data-reveal="right">
  <div class="relative z-10 flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border font-display text-lg font-bold border-white/15 bg-ink-900 text-white/70">01</div>
  </div>
  <div class="flex-1 rounded-2xl border p-6 md:p-7 border-white/8 bg-white/[0.02]">
  <div class="mb-3 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-widest border-white/10 bg-white/5 text-white/50">
  <?= e(__('home.twoLayer.layer1.label')) ?>
  </div>
  <span class="block font-display text-5xl font-bold leading-none text-accent md:text-6xl" data-countup="95" data-suffix="%" data-duration="3.5" data-ease="smooth">95%</span>
  <h3 class="font-display font-bold leading-tight text-white mt-2 text-xl md:text-2xl">
  <?= e(__('home.twoLayer.layer1.title')) ?>
  </h3>
  <p class="mt-3 text-base leading-relaxed text-white/55">
  <?= e(__('home.twoLayer.layer1.desc')) ?>
  </p>
  </div>
  </div>
  <div class="relative flex gap-5"  data-reveal="right">
  <div class="relative z-10 flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border font-display text-lg font-bold border-accent/50 bg-accent text-ink-900 shadow-glow-sm">02</div>
  </div>
  <div class="flex-1 rounded-2xl border p-6 md:p-7 card-elevated border-accent/25 bg-accent/[0.05]">
  <div class="mb-3 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-widest border-accent/25 bg-accent/10 text-accent">
  <?= e(__('home.twoLayer.layer2.label')) ?>
  </div>
  <h3 class="font-display font-bold leading-tight text-white text-2xl">
  <?= e(__('home.twoLayer.layer2.title')) ?>
  </h3>
  <p class="mt-3 text-base leading-relaxed text-white/55">
  <?= e(__('home.twoLayer.layer2.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </div>
  </div>
  </section>

  <!-- WHY FACILITIES SECTION (replash.info) -->
  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute -inset-y-[30%] inset-x-0 will-change-transform" data-parallax="16">
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_padel_club_empty.webp') ?>" alt="" class="h-full w-full object-cover object-center" style="opacity:0.15" />
  </div>
  </div>
  <div class="absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/60 to-ink-900">
  </div>
  </div>
  <div class="container-x relative">
  <div class="grid gap-10 lg:grid-cols-[1fr_1.2fr] lg:items-center">
  <div  data-reveal="left">
  <h2 class="font-display text-3xl font-bold leading-[1.15] tracking-tight md:text-4xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
  <?= e(__('home.whyFacilities.title')) ?>
  </h2>
  <a class="mt-8 inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/10 px-5 py-2.5 text-sm font-semibold text-accent transition hover:bg-accent/20" href="<?= base_url('for-facilities.php') ?>">
  <?= e(__('home.faq.categories.facilities')) ?>
  <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
  <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z">
  </path>
  </svg>
  </a>
  </div>
  <div class="grid gap-3">
  <div class="flex items-start gap-3 rounded-2xl border border-white/8 bg-white/[0.03] p-4"  data-reveal="right">
  <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border border-accent/25 bg-accent/10">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  </div>
  <div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('home.whyFacilities.items.1.title')) ?>
  </h3>
  <p class="mt-0.5 text-xs leading-relaxed text-white/50">
  <?= e(__('home.whyFacilities.items.1.desc')) ?>
  </p>
  </div>
  </div>
  <div class="flex items-start gap-3 rounded-2xl border border-white/8 bg-white/[0.03] p-4"  data-reveal="right">
  <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border border-accent/25 bg-accent/10">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  </div>
  <div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('home.whyFacilities.items.2.title')) ?>
  </h3>
  <p class="mt-0.5 text-xs leading-relaxed text-white/50">
  <?= e(__('home.whyFacilities.items.2.desc')) ?>
  </p>
  </div>
  </div>
  <div class="flex items-start gap-3 rounded-2xl border border-white/8 bg-white/[0.03] p-4"  data-reveal="right">
  <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border border-accent/25 bg-accent/10">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>
  </div>
  <div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('home.whyFacilities.items.5.title')) ?>
  </h3>
  <p class="mt-0.5 text-xs leading-relaxed text-white/50">
  <?= e(__('home.whyFacilities.items.5.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </div>
  </div>
  </section>

  <!-- WHAT PLAYERS GET SECTION (replash.info) -->


  <!-- FAQ (replash.info) -->
  <section class="section-y border-t border-white/5 bg-ink-900">
    <div class="container-x">
      <div class="max-w-3xl mx-auto text-center" data-reveal="up">
        <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('home.faq.title')) ?></h2>
        <p class="mt-4 text-lg text-white/70 md:text-xl"><?= e(__('home.faq.subtitle')) ?></p>
      </div>
      <div class="mx-auto mt-12 max-w-3xl divide-y divide-white/[0.06]">
        <?php foreach ([1, 2, 3, 4] as $i): // tesisin ilk sorduğu dört soru
          $q = __("home.faq.items.{$i}.q");
          $a = __("home.faq.items.{$i}.a");
          if ($q === "home.faq.items.{$i}.q") continue;
          $open = ($i === 1);
        ?>
          <div class="faq-item" data-reveal="up">
            <button type="button" class="faq-btn flex w-full items-start justify-between gap-4 py-5 text-left" aria-expanded="<?= $open ? 'true' : 'false' ?>" aria-controls="faq-panel-<?= $i ?>">
              <span class="faq-q text-base font-semibold leading-snug transition-colors duration-200 <?= $open ? 'text-white' : 'text-white/75' ?>"><?= e($q) ?></span>
              <span class="faq-icon mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border transition-colors duration-300 <?= $open ? 'border-accent/40 bg-accent/15 text-accent' : 'border-white/15 bg-white/5 text-white/40' ?>">
                <svg class="h-3 w-3 transition-transform duration-300 <?= $open ? 'rotate-45' : 'rotate-0' ?>" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 1v10M1 6h10"/></svg>
              </span>
            </button>
            <div id="faq-panel-<?= $i ?>" class="faq-panel overflow-hidden transition-[max-height,opacity] duration-300" style="max-height:<?= $open ? '600px' : '0' ?>;opacity:<?= $open ? '1' : '0' ?>">
              <p class="pb-5 pr-10 text-sm leading-relaxed text-white/60"><?= e($a) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CTA (replash.info) -->
  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 70% 50% at 50% 100%, rgba(253,230,138,0.12), transparent 70%)" aria-hidden="true">
  </div>
  <div class="container-x relative text-center">
  <div  data-reveal="up">
  <h2 class="mx-auto max-w-2xl font-display text-4xl font-bold leading-[1.1] tracking-tight text-balance md:text-5xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
  <?= e(__('home.cta.title')) ?>
  </h2>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-4 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon hover:shadow-[0_0_60px_rgba(254,243,199,0.5)]" href="<?= base_url('for-facilities.php') ?>">
  <?= e(__('home.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<!-- Hero paralaks ve SSS akordeonu (replash.info davranışı) -->
<script>
  (function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Hero (replash.info HomeHero): kaydırdıkça görsel %0→%28 aşağı kayar ve 1.1→1.3 büyür;
    // başlık %0→-%85 yukarı kayar ve 1 → 0.5 (yarıda) → 0 solar.
    var layer = document.getElementById('hero-parallax');
    var text = document.getElementById('hero-text');
    var hero = document.getElementById('hero-section');
    if (layer && hero && !reduce) {
      var ticking = false;
      var update = function () {
        var r = hero.getBoundingClientRect();
        var p = Math.min(1, Math.max(0, -r.top / (r.height || 1)));
        layer.style.transform = 'translate3d(0,' + (p * 28).toFixed(2) + '%,0) scale(' + (1.1 + p * 0.2).toFixed(4) + ')';
        if (text) {
          var o = p < 0.45 ? 1 - (p / 0.45) * 0.5 : 0.5 - ((p - 0.45) / 0.55) * 0.5;
          text.style.transform = 'translate3d(0,' + (-p * 85).toFixed(2) + '%,0)';
          text.style.opacity = o.toFixed(3);
        }
        ticking = false;
      };
      window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
      window.addEventListener('resize', update, { passive: true });
      update();
    }

    // SSS: aynı anda tek soru açık
    var items = document.querySelectorAll('.faq-item');
    var setOpen = function (item, open) {
      var btn = item.querySelector('.faq-btn'), panel = item.querySelector('.faq-panel');
      var q = item.querySelector('.faq-q'), icon = item.querySelector('.faq-icon'), svg = icon.querySelector('svg');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      panel.style.maxHeight = open ? panel.scrollHeight + 'px' : '0';
      panel.style.opacity = open ? '1' : '0';
      q.classList.toggle('text-white', open); q.classList.toggle('text-white/75', !open);
      ['border-accent/40', 'bg-accent/15', 'text-accent'].forEach(function (c) { icon.classList.toggle(c, open); });
      ['border-white/15', 'bg-white/5', 'text-white/40'].forEach(function (c) { icon.classList.toggle(c, !open); });
      svg.classList.toggle('rotate-45', open); svg.classList.toggle('rotate-0', !open);
    };
    items.forEach(function (item) {
      item.querySelector('.faq-btn').addEventListener('click', function () {
        var willOpen = this.getAttribute('aria-expanded') !== 'true';
        items.forEach(function (other) { setOpen(other, false); });
        if (willOpen) setOpen(item, true);
      });
    });
  })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
