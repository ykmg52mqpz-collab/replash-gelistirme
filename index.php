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

<main id="main" class="relative min-h-screen bg-ink-900">

  <!-- HERO (replash.info) -->
  <section id="hero-section" class="relative flex min-h-[60svh] items-end overflow-hidden bg-ink-900 pt-28 pb-16 md:min-h-[100svh] md:items-center md:pt-32 md:pb-24">
  <div id="hero-parallax" class="absolute inset-0 will-change-transform" style="transform:scale(1)" aria-hidden="true">
  <picture><!-- Giriş görseli: orta sahada direğe kurulu 180° kameranın görüntüsü (masaüstü ve mobil aynı) -->
  <img decoding="async" <?= scene_img_attrs('ai-cam-180', [800, 1344, 1920], '170vw') ?> alt="" fetchpriority="high" class="h-full w-full object-cover object-[55%_50%] max-md:mt-[76px] max-md:h-[58%] max-md:object-[55%_40%] max-md:[mask-image:linear-gradient(to_bottom,#000_72%,transparent)]" style="opacity:0.92"/>
  </picture>
  <?php if (is_file(REPLASH_ROOT . '/assets/video/hero.mp4')): ?>
  <!-- Giriş videosu: assets/video/hero.mp4 (+ isteğe bağlı hero.webm) konduğunda görselin üstünde oynar; görsel poster ve yedek olarak kalır -->
  <video class="hero-video absolute inset-0 h-full w-full object-cover object-[55%_50%] max-md:mt-[76px] max-md:h-[58%] max-md:object-[55%_40%] max-md:[mask-image:linear-gradient(to_bottom,#000_72%,transparent)]" style="opacity:0.92" autoplay muted loop playsinline preload="metadata" poster="<?= asset('images/ai-cam-180-1920.webp') ?>">
  <?php if (is_file(REPLASH_ROOT . '/assets/video/hero.webm')): ?><source src="<?= asset('video/hero.webm') ?>" type="video/webm"><?php endif; ?>
  <source src="<?= asset('video/hero.mp4') ?>" type="video/mp4">
  </video>
  <?php endif; ?>
  <!-- Gece görüntüsü: üstte hafif, altta tam koyu; yazının olduğu sol taraf ayrıca koyulaşır -->
  <div class="absolute inset-0 bg-gradient-to-b from-ink-900/35 via-ink-900/15 to-ink-900/95">
  </div>
  <div class="absolute inset-0 bg-gradient-to-r from-ink-900/80 via-ink-900/35 to-transparent max-md:hidden">
  </div>
  </div>
  <div class="pointer-events-none absolute -top-60 left-1/2 h-[600px] w-[600px] -translate-x-1/2 rounded-full" aria-hidden="true">
  </div>
  <div class="absolute inset-0 grid-noise opacity-10" aria-hidden="true">
  </div>
  <div class="container-x relative z-10">
  <div id="hero-text" class="max-w-4xl text-left will-change-transform">
  <div data-reveal="up">
  <!-- Slogan tek satırda (dar ekranda kelimeler alt satıra geçebilir) -->
  <h1 class="font-display text-5xl font-bold leading-[1] tracking-tight sm:text-6xl md:text-7xl lg:whitespace-nowrap xl:text-8xl">
  <?= e(__('v2.home.hero.title')) ?>
  </h1>
  <p class="mt-6 max-w-xl text-base leading-relaxed text-white/80 md:mt-8 md:text-xl">
  <?= e(__('v2.home.hero.sub')) ?>
  </p>
  <div class="mt-6 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center md:mt-10">
  <a class="group inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,box-shadow,transform] active:scale-[0.98] duration-300 hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>">
  <?= e(__('home.hero.cta')) ?>
  <svg class="ml-2 h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
  <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z">
  </path>
  </svg>
  </a>
  <a class="hidden items-center justify-center rounded-full border border-white/10 bg-white/[0.04] px-8 py-3.5 sm:inline-flex text-base font-medium text-white/70 backdrop-blur transition-colors active:scale-[0.98] duration-200 hover:border-white/20 hover:bg-white/[0.08] hover:text-white" href="<?= base_url('for-facilities') ?>">
  <?= e(__('home.hero.ctaSecondary')) ?>
  </a>
  </div>
  <p class="mt-5 hidden text-sm text-white/80 sm:block"><a class="font-semibold text-accent underline underline-offset-4 hover:text-accent-neon" href="<?= base_url('find') ?>"><?= e(__('home.hero.playerLink')) ?> &rarr;</a></p>
  </div>
  </div>
  </div>
  </section>

  <?php 
  $searchFacilityEnabled = false;   // maça yalnızca kodla ulaşılır
  $searchCodeEnabled = (string)get_setting('search_by_code_enabled', '1') === '1';
  ?>

  <!-- QUICK MATCH FINDER CARD (Contained & Centered) -->
  <?php if (!$searchFacilityEnabled && $searchCodeEnabled): ?>
    <!-- KOD KARTI: "Maçını Bul" sayfasındaki kartla aynı -->
    <section class="relative -mt-6 z-20 mx-auto mb-20 max-w-2xl px-4 sm:px-6 md:-mt-10">
      <?php $ccId = 'home-code'; include __DIR__ . '/includes/code_card.php'; ?>
    </section>
  <?php else: ?>
    <!-- FACILITY & DATE FILTER FORM (With Optional Code Shortcut) -->
    <section class="relative -mt-10 z-20 max-w-6xl mx-auto px-4 sm:px-6 mb-20">
      <div class="rounded-3xl border border-white/10 bg-ink-900/90 p-6 sm:p-8 shadow-[0_20px_50px_rgba(0,0,0,0.8)] backdrop-blur-2xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6 pb-5 border-b border-white/[0.06]">
          <div>
            <span class="rp-label text-accent"><?= e(__('home.findPreview.eyebrow')) ?></span>
            <h3 class="font-display text-2xl font-bold text-white mt-1"><?= e(__('home.findPreview.title')) ?></h3>
          </div>
          <p class="text-sm sm:text-sm text-white/75 max-w-sm">
            <?= e(__('home.findPreview.desc')) ?>
          </p>
        </div>

        <form action="<?= base_url('find') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
            <button type="submit" class="w-full rounded-xl bg-accent px-4 py-2.5 text-sm font-semibold text-ink-900 transition hover:bg-accent-neon flex items-center justify-center gap-2">
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
            <form action="<?= base_url('find') ?>" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
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


  <!-- INTRO (replash.info) -->


  <!-- TWO-LAYER VALUE SECTION (replash.info ile aynı: yapışkan başlık + zaman çizelgesi) -->
  <section class="section-y border-t border-white/5 bg-section-gradient relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" aria-hidden="true">
  </div>
  <div class="container-x relative grid gap-12 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:gap-16">
  <div class="lg:sticky lg:top-28 lg:self-start"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-[1.1] tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('home.twoLayer.title')) ?>
  </h2>
  <p class="mt-5 max-w-md text-lg leading-relaxed text-white/75">
  <?= e(__('home.twoLayer.subtitle')) ?>
  </p>
  </div>
  <!-- Yenileme Tasarım 06: daire ve dikey çizgi yok; numara etikete taşındı, oynatıcıdaki zaman kodu gibi -->
  <div class="flex flex-col gap-10">
  <div class="" data-reveal="up">
  <p class="rp-label text-white/75">01 — <?= e(__('home.twoLayer.layer1.label')) ?></p>
  <h3 class="mt-3 font-display text-xl font-bold leading-tight text-white md:text-2xl">
  <?= e(__('home.twoLayer.layer1.title')) ?>
  </h3>
  <p class="mt-3 text-base leading-relaxed text-white/80">
  <?= e(__('home.twoLayer.layer1.desc')) ?>
  </p>
  </div>
  <div class="" data-reveal="up">
  <p class="rp-label text-accent">02 — <?= e(__('home.twoLayer.layer2.label')) ?></p>
  <h3 class="mt-3 font-display text-2xl font-bold leading-tight text-white">
  <?= e(__('home.twoLayer.layer2.title')) ?>
  </h3>
  <p class="mt-3 text-base leading-relaxed text-white/80">
  <?= e(__('v2.home.layer2')) ?>
  </p>
  </div>
  </div>
  </div>
  </section>

  <!-- WHY FACILITIES SECTION: gece padel kortu sağ kenara taşar ve zemine erir -->
  <section class="relative overflow-hidden border-t border-white/5 bg-ink-900 lg:flex lg:min-h-[760px] lg:items-center">
  <div class="relative lg:absolute lg:inset-y-0 lg:right-0 lg:w-[54%]" aria-hidden="true">
  <img data-scroll-img="band" loading="lazy" decoding="async" <?= scene_img_attrs('foto-padel-gece', [800, 1440], '(min-width: 1024px) 54vw, 100vw') ?> width="1440" height="810" alt="" class="photo-melt-r block aspect-[16/10] w-full object-cover lg:aspect-auto lg:h-full" style="object-position:62% 60%">
  </div>
  <div class="container-x relative z-10 w-full pb-20 lg:py-28">
  <div class="lg:w-[48%] lg:pr-8" data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-[1.15] tracking-tight md:text-4xl">
  <?= e(__('home.whyFacilities.title')) ?>
  </h2>
  <!-- Yenileme Tasarım 07: kutu ve tik ikonu yok; maddeler ince çizgilerle ayrılır -->
  <ul class="mt-8 divide-y divide-white/10">
  <li class="py-5 first:pt-0 last:pb-0">
  <h3 class="text-base font-semibold text-white"><?= e(__('home.whyFacilities.items.1.title')) ?></h3>
  <p class="mt-1.5 text-[15.5px] leading-relaxed text-white/80"><?= e(__('home.whyFacilities.items.1.desc')) ?></p>
  </li>
  <li class="py-5 first:pt-0 last:pb-0">
  <h3 class="text-base font-semibold text-white"><?= e(__('v2.home.rev.t')) ?></h3>
  <p class="mt-1.5 text-[15.5px] leading-relaxed text-white/80"><?= e(__('v2.home.rev.d')) ?></p>
  </li>
  <li class="py-5 first:pt-0 last:pb-0">
  <h3 class="text-base font-semibold text-white"><?= e(__('home.whyFacilities.items.5.title')) ?></h3>
  <p class="mt-1.5 text-[15.5px] leading-relaxed text-white/80"><?= e(__('home.whyFacilities.items.5.desc')) ?></p>
  </li>
  </ul>
  <a class="mt-8 inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/10 px-5 py-2.5 text-sm font-semibold text-accent transition hover:bg-accent/20" href="<?= base_url('for-facilities') ?>">
  <?= e(__('home.faq.categories.facilities')) ?>
  <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
  <path d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L13.586 10H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z">
  </path>
  </svg>
  </a>
  </div>
  </div>
  </section>

  <!-- Slogan bandı: yüzde yerine kazanç vurgusu (kademeler tesisler sayfasında) -->
  <section class="relative overflow-hidden border-t border-white/5 bg-ink-900 lg:flex lg:min-h-[860px] lg:items-center">
  <div class="relative lg:absolute lg:inset-y-0 lg:left-0 lg:w-[56%]" aria-hidden="true">
  <img data-scroll-img="band" loading="lazy" decoding="async" <?= scene_img_attrs('foto-sis', [800, 1400], '(min-width: 1024px) 56vw, 100vw') ?> width="1400" height="1400" alt="" class="photo-melt-l block aspect-square w-full object-cover lg:aspect-auto lg:h-full" style="object-position:20% 62%">
  </div>
  <div class="container-x relative z-10 w-full pb-20 lg:py-28">
  <div class="lg:ml-auto lg:w-[52%] lg:pl-6 lg:text-right">
  <div data-reveal="up">
  <h2 class="font-display text-5xl font-bold leading-[0.95] tracking-tight sm:text-6xl lg:text-7xl xl:text-8xl">
  <span class="block text-white"><?= e(__('v2.home.band.l1')) ?></span>
  <span class="block text-white"><?= e(__('v2.home.band.l2')) ?></span>
  </h2>
  <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center lg:flex-row-reverse">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-7 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>"><?= e(__('v2.home.band.pilot')) ?></a>
  <a class="text-sm font-semibold text-accent hover:text-accent-neon" href="<?= base_url('for-facilities#calculator') ?>"><?= e(__('v2.home.band.calc')) ?></a>
  </div>
  </div>
  </div>
  </div>
  </section>

  <!-- WHAT PLAYERS GET SECTION (replash.info) -->


  <!-- FAQ (replash.info) -->
  <section class="section-y border-t border-white/5 bg-ink-900">
    <div class="container-x">
      <!-- SSS: başlık yok, sorular sayfada ortalı (kullanıcı isteği) -->
      <h2 class="sr-only"><?= e(__('home.faq.title')) ?></h2>
      <div class="mx-auto max-w-3xl divide-y divide-white/[0.06]">
        <?php foreach ([1, 2, 4, 6] as $i): // tesisin ilk sorduğu dört soru (yüzdeler tesisler sayfasında)
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
              <p class="pb-5 pr-10 text-sm leading-relaxed text-white/75"><?= e($a) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CTA (replash.info) -->
  <section class="relative flex min-h-[70vh] items-center overflow-hidden border-t border-white/5 bg-ink-900 py-24 md:min-h-[80vh] md:py-32">
  <!-- Sfondo: campo da tennis di sera -->
  <div class="absolute inset-0" aria-hidden="true">
  <img data-scroll-img="band" loading="lazy" decoding="async" <?= scene_img_attrs('foto-tenis-gece', [800, 1280]) ?> alt="" class="h-full w-full object-cover" style="opacity:0.75;object-position:center 60%" />
  <div class="absolute inset-0 bg-gradient-to-b from-ink-900 via-ink-900/35 to-ink-900/70"></div>
  <div class="absolute inset-0"></div>
  </div>
  <div class="container-x relative w-full">
  <div  data-reveal="up">
  <h2 class="max-w-2xl font-display text-4xl font-bold leading-[1.1] tracking-tight text-balance md:text-5xl">
  <?= e(__('home.cta.title')) ?>
  </h2>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-4 text-base font-semibold text-ink-900 transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>">
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
        layer.style.transform = 'translate3d(0,' + (p * 28).toFixed(2) + '%,0) scale(' + (1 + p * 0.3).toFixed(4) + ')';
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
