<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.howItWorks');
$metaTitle = __('meta.howItWorks.title');
$metaDescription = __('meta.howItWorks.description');

require_once __DIR__ . '/includes/header.php';
?>

<main id="main" class="relative bg-ink-900">

  <section class="relative overflow-hidden bg-ink-900 pt-36 pb-20 md:pt-44 md:pb-28">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute inset-0">
  <img data-scroll-img="hero" decoding="async" fetchpriority="high" <?= scene_img_attrs('foto-kaleci', [1200, 2000]) ?> alt="" class="h-full w-full object-cover" style="opacity:0.7;object-position:65% 55%"/>
  </div>
  </div>
  <div class="absolute inset-0 bg-gradient-to-b from-ink-900/60 via-ink-900/30 to-ink-900/90">
  </div>
  </div>
  <div class="pointer-events-none absolute -top-40 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full" aria-hidden="true">
  </div>
  <div class="absolute inset-0 grid-noise opacity-10" aria-hidden="true">
  </div>
  <div class="container-x relative">
  <h1 class="max-w-4xl font-display text-5xl font-bold leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl xl:text-8xl">
  <span class="block text-white"><?= e(__('v2.how.l1')) ?></span>
  <span class="block text-white"><?= e(__('v2.how.l2')) ?></span>
  <span class="block text-white"><?= e(__('v2.how.l3')) ?></span>
  </h1>
  <p class="mt-8 max-w-2xl text-lg text-white/80 md:text-xl"><?= e(__('v2.how.sub')) ?></p>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-4xl">
  <div class="relative">
  <div class="absolute left-[27px] top-2 bottom-2 w-px bg-gradient-to-b from-accent/40 via-accent/15 to-accent/5 sm:left-[35px]">
  </div>
  <div class="flex flex-col gap-8">
  <div class="relative flex gap-5" >
  <div class="relative flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-accent/30 bg-ink-900 text-accent sm:h-[70px] sm:w-[70px]">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M23 7l-7 5 7 5V7z">
  </path>
  <rect x="1" y="5" width="15" height="14" rx="2">
  </rect>
  </svg>
  </div>
  </div>
  <div class="min-w-0 flex-1 pb-2">
  <div class="mb-3">
  <span class="rp-label text-accent/70">
  <?= e(__('howItWorks.flow.1.label')) ?>
  </span>
  <h2 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.1.title')) ?>
  </h2>
  </div>
  <p class="text-sm leading-relaxed text-white/75 md:text-base">
  <?= e(__('howItWorks.flow.1.desc')) ?>
  </p>
  </div>
  </div>
  <div class="relative flex gap-5" >
  <div class="relative flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-accent/30 bg-ink-900 text-accent sm:h-[70px] sm:w-[70px]">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
  <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
  </svg>
  </div>
  </div>
  <div class="min-w-0 flex-1 pb-2">
  <div class="mb-3">
  <span class="rp-label text-accent/70">
  <?= e(__('howItWorks.flow.2.label')) ?>
  </span>
  <h2 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.2.title')) ?>
  </h2>
  </div>
  <p class="text-sm leading-relaxed text-white/75 md:text-base">
  <?= e(__('howItWorks.flow.2.desc')) ?>
  </p>
  </div>
  </div>
  <div class="relative flex gap-5" >
  <div class="relative flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-accent/30 bg-ink-900 text-accent sm:h-[70px] sm:w-[70px]">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z">
  </path>
  <line x1="7" y1="7" x2="7.01" y2="7">
  </line>
  </svg>
  </div>
  </div>
  <div class="min-w-0 flex-1 pb-2">
  <div class="mb-3">
  <span class="rp-label text-accent/70">
  <?= e(__('howItWorks.flow.3.label')) ?>
  </span>
  <h2 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.3.title')) ?>
  </h2>
  </div>
  <p class="text-sm leading-relaxed text-white/75 md:text-base">
  <?= e(__('howItWorks.flow.3.desc')) ?>
  </p>
  </div>
  </div>
  <div class="relative flex gap-5" >
  <div class="relative flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-accent/30 bg-ink-900 text-accent sm:h-[70px] sm:w-[70px]">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="6" cy="6" r="3">
  </circle>
  <circle cx="6" cy="18" r="3">
  </circle>
  <line x1="20" y1="4" x2="8.12" y2="15.88">
  </line>
  <line x1="14.47" y1="14.48" x2="20" y2="20">
  </line>
  <line x1="8.12" y1="8.12" x2="12" y2="12">
  </line>
  </svg>
  </div>
  </div>
  <div class="min-w-0 flex-1 pb-2">
  <div class="mb-3">
  <span class="rp-label text-accent/70">
  <?= e(__('howItWorks.flow.4.label')) ?>
  </span>
  <h2 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.4.title')) ?>
  </h2>
  </div>
  <p class="text-sm leading-relaxed text-white/75 md:text-base">
  <?= e(__('howItWorks.flow.4.desc')) ?>
  </p>
</div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" aria-hidden="true">
  </div>
  <div class="container-x relative">
  <div class="max-w-4xl">
  <h2 class="max-w-xl font-display text-3xl font-bold tracking-tight text-balance md:text-4xl">
  <?= e(__('howItWorks.cta.title')) ?>
  </h2>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>">
  <?= e(__('howItWorks.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
