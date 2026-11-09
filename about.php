<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.about');
$metaTitle = __('meta.about.title');
$metaDescription = __('meta.about.description');

require_once __DIR__ . '/includes/header.php';
?>

<main id="main" class="relative bg-ink-900">

  <!-- Hero: metin solda; ağın arkasından gece maçı sağ kenara taşar ve zemine erir -->
  <section class="relative overflow-hidden bg-ink-900 pt-32 pb-10 md:pt-40 lg:flex lg:min-h-[86svh] lg:items-center lg:pb-24">
  <div class="container-x relative z-10 w-full">
  <div class="max-w-xl">
  <h1 class="font-display text-4xl font-bold leading-[1.1] tracking-tight text-balance md:text-5xl lg:text-6xl" data-reveal="up">
  <?= e(__('about.hero.title')) ?>
  </h1>
  <p class="mt-6 text-lg text-white/75 md:text-xl" data-reveal="up">
  <?= e(__('about.hero.subtitle')) ?>
  </p>
  </div>
  </div>
  <div class="relative mt-6 lg:absolute lg:inset-y-0 lg:right-0 lg:mt-0 lg:w-[56%]" aria-hidden="true">
  <img data-scroll-img="hero" decoding="async" fetchpriority="high" <?= scene_img_attrs('foto-ag', [800, 1129], '(min-width: 1024px) 56vw, 100vw') ?> width="1129" height="1693" alt="" class="photo-melt-r mx-auto block aspect-[4/5] w-full max-w-lg object-cover lg:aspect-auto lg:h-full lg:max-w-none" style="object-position:center 58%">
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="grid gap-10 md:grid-cols-2 md:gap-16">
  <div class=""  data-reveal="up">
  <span class="rp-label text-accent">
  <?= e(__('about.mission.eyebrow')) ?>
  </span>
  <p class="mt-3 text-lg font-medium leading-relaxed text-white/80">“<?= e(__('about.mission.text')) ?>”</p>
  </div>
  <div class=""  data-reveal="up">
  <span class="rp-label text-accent">
  <?= e(__('about.vision.eyebrow')) ?>
  </span>
  <p class="mt-3 text-lg font-medium leading-relaxed text-white/80">“<?= e(__('about.vision.text')) ?>”</p>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <!-- Başlık ve metin solda, üç madde sağdaki boş alanda -->
  <div class="grid gap-10 lg:grid-cols-[1.15fr_1fr] lg:items-center lg:gap-16" data-reveal="up">
  <div>
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('about.founders.title')) ?>
  </h2>
  <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75">
  <?= e(__('about.founders.desc')) ?>
  </p>
  </div>
  <ul class="divide-y divide-white/10 border-y border-white/10">
  <?php foreach ([1, 2, 3] as $fv): ?>
  <li class="flex items-baseline gap-3 py-5 text-lg leading-snug text-white/85">
  <span class="h-2 w-2 flex-none -translate-y-0.5 rounded-full bg-accent" aria-hidden="true"></span>
  <?= e(__("about.founders.values.$fv")) ?>
  </li>
  <?php endforeach; ?>
  </ul>
  </div>
  </div>
  </section>



  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-3xl"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('about.whoWeServe.title')) ?>
  </h2>
  </div>
  <div class="mt-12 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
  <div class=""  data-reveal="up">
  <div class="mb-3 inline-flex text-accent">
  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="12" cy="12" r="10">
  </circle>
  <path d="M12 2l3 6-3 4-3-4 3-6z">
  </path>
  <path d="M22 12l-6 3-4-3 4-3 6 3z">
  </path>
  <path d="M12 22l-3-6 3-4 3 4-3 6z">
  </path>
  <path d="M2 12l6-3 4 3-4 3-6-3z">
  </path>
  </svg>
  </div>
  <h3 class="mb-2 text-sm font-semibold text-white">
  <?= e(__('about.whoWeServe.items.1.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('about.whoWeServe.items.1.desc')) ?>
  </p>
  </div>
  <div class=""  data-reveal="up">
  <div class="mb-3 inline-flex text-accent">
  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <ellipse cx="12" cy="9" rx="6" ry="7">
  </ellipse>
  <line x1="12" y1="16" x2="12" y2="22">
  </line>
  <circle cx="9" cy="8" r="0.5" fill="currentColor">
  </circle>
  <circle cx="14" cy="7" r="0.5" fill="currentColor">
  </circle>
  <circle cx="11" cy="11" r="0.5" fill="currentColor">
  </circle>
  </svg>
  </div>
  <h3 class="mb-2 text-sm font-semibold text-white">
  <?= e(__('about.whoWeServe.items.2.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('about.whoWeServe.items.2.desc')) ?>
  </p>
  </div>
  <div class=""  data-reveal="up">
  <div class="mb-3 inline-flex text-accent">
  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M3 21h18">
  </path>
  <path d="M5 21V7l7-4 7 4v14">
  </path>
  <path d="M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01">
  </path>
  </svg>
  </div>
  <h3 class="mb-2 text-sm font-semibold text-white">
  <?= e(__('about.whoWeServe.items.3.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('about.whoWeServe.items.3.desc')) ?>
  </p>
  </div>
  <div class=""  data-reveal="up">
  <div class="mb-3 inline-flex text-accent">
  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2">
  </path>
  <circle cx="9" cy="7" r="4">
  </circle>
  <path d="M23 21v-2a4 4 0 0 0-3-3.87">
  </path>
  <path d="M16 3.13a4 4 0 0 1 0 7.75">
  </path>
  </svg>
  </div>
  <h3 class="mb-2 text-sm font-semibold text-white">
  <?= e(__('about.whoWeServe.items.4.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('about.whoWeServe.items.4.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </section>

  <!-- İş birliği yapılan tesisler (liste: includes/partner_venues.php) -->
  <?php $partnerVenues = require __DIR__ . '/includes/partner_venues.php'; ?>
  <section class="section-y border-t border-white/5 bg-ink-900" id="centri-partner">
  <div class="container-x">
  <div class="max-w-3xl" data-reveal="up">
  <p class="rp-label text-accent"><?= e(__('v2.partners.eyebrow')) ?></p>
  <h2 class="mt-4 font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('v2.partners.title')) ?></h2>
  </div>
  <?php if ($partnerVenues): ?>
  <ul class="mt-12 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3" data-reveal="up">
  <?php foreach ($partnerVenues as $pv): ?>
  <li class="border-t border-white/10 pt-6">
  <?php if (!empty($pv['logo'])): ?><img loading="lazy" decoding="async" src="<?= asset($pv['logo']) ?>" alt="" class="mb-5 h-12 w-auto max-w-[180px] object-contain"><?php endif; ?>
  <h3 class="font-display text-xl font-bold text-white"><?= e($pv['name']) ?></h3>
  <p class="mt-1.5 text-sm text-white/65"><?= e($pv['city'] ?? '') ?><?php if (!empty($pv['sports'])): ?> · <?= e(implode(', ', array_map(function ($sp) { return __('forFacilities.calc.sports.' . $sp); }, $pv['sports']))) ?><?php endif; ?></p>
  <?php if (!empty($pv['url'])): ?><a class="mt-3 inline-block text-sm font-semibold text-accent hover:text-accent-neon" href="<?= e($pv['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(__('v2.partners.visit')) ?> &rarr;</a><?php endif; ?>
  </li>
  <?php endforeach; ?>
  </ul>
  <?php else: ?>
  <div class="mt-10 max-w-3xl border-t border-white/10 pt-8" data-reveal="up">
  <p class="text-lg leading-relaxed text-white/75"><?= e(__('v2.partners.empty')) ?></p>
  </div>
  <?php endif; ?>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" aria-hidden="true">
  </div>
  <div class="container-x relative">
  <div  data-reveal="up">
  <h2 class="max-w-lg font-display text-3xl font-bold tracking-tight text-balance md:text-4xl">
  <?= e(__('about.cta.title')) ?>
  </h2>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>">
  <?= e(__('about.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
