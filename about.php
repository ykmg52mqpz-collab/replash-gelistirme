<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.about');
$metaTitle = __('meta.about.title');
$metaDescription = __('meta.about.description');

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative bg-ink-900">

  <section class="relative overflow-hidden bg-ink-900 pt-36 pb-20 md:pt-44 md:pb-28">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute -inset-y-[30%] inset-x-0 will-change-transform" data-parallax="16">
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_padel_club_empty.webp') ?>" alt="" class="h-full w-full object-cover object-center" style="opacity:0.5"/>
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
  <?= e(__('about.hero.title')) ?>
  </h1>
  <p class="mx-auto mt-6 max-w-2xl text-lg text-white/55 md:text-xl"  data-reveal="up">
  <?= e(__('about.hero.subtitle')) ?>
  </p>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="grid gap-6 md:grid-cols-2">
  <div class="rounded-2xl border border-white/8 bg-white/[0.03] p-8"  data-reveal="up">
  <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="12" cy="12" r="10">
  </circle>
  <circle cx="12" cy="12" r="6">
  </circle>
  <circle cx="12" cy="12" r="2" fill="currentColor">
  </circle>
  </svg>
  </div>
  <span class="text-xs font-semibold uppercase tracking-widest text-accent">
  <?= e(__('about.mission.eyebrow')) ?>
  </span>
  <p class="mt-3 text-lg font-medium leading-relaxed text-white/80">“<?= e(__('about.mission.text')) ?>”</p>
  </div>
  <div class="rounded-2xl border border-white/8 bg-white/[0.03] p-8"  data-reveal="up">
  <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M2 12h20">
  </path>
  <path d="M2 12c0-5.523 4.477-10 10-10s10 4.477 10 10">
  </path>
  <circle cx="12" cy="12" r="3">
  </circle>
  </svg>
  </div>
  <span class="text-xs font-semibold uppercase tracking-widest text-accent">
  <?= e(__('about.vision.eyebrow')) ?>
  </span>
  <p class="mt-3 text-lg font-medium leading-relaxed text-white/80">“<?= e(__('about.vision.text')) ?>”</p>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="mx-auto max-w-2xl"  data-reveal="up">
  <div class="max-w-3xl "  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('about.founders.title')) ?>
  </h2>
  </div>
  <p class="mt-5 text-base leading-relaxed text-white/60">
  <?= e(__('about.founders.desc')) ?>
  </p>
  <div class="mt-7 flex flex-col gap-3">
  <div class="flex items-center gap-2.5">
  <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-accent/30 bg-accent/10">
  <span class="h-1.5 w-1.5 rounded-full bg-accent">
  </span>
  </span>
  <span class="text-sm text-white/65">
  <?= e(__('about.founders.values.1')) ?>
  </span>
  </div>
  <div class="flex items-center gap-2.5">
  <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-accent/30 bg-accent/10">
  <span class="h-1.5 w-1.5 rounded-full bg-accent">
  </span>
  </span>
  <span class="text-sm text-white/65">
  <?= e(__('about.founders.values.2')) ?>
  </span>
  </div>
  <div class="flex items-center gap-2.5">
  <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-accent/30 bg-accent/10">
  <span class="h-1.5 w-1.5 rounded-full bg-accent">
  </span>
  </span>
  <span class="text-sm text-white/65">
  <?= e(__('about.founders.values.3')) ?>
  </span>
  </div>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute -inset-y-[30%] inset-x-0 will-change-transform" data-parallax="16">
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_padel_club_empty.webp') ?>" alt="" class="h-full w-full object-cover" style="opacity:0.12"/>
  </div>
  </div>
  <div class="absolute inset-0 bg-ink-900/80">
  </div>
  </div>
  <div class="container-x relative">
  <div class="mx-auto max-w-2xl text-center">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('about.story.title')) ?>
  </h2>
  </div>
  <p class="mt-5 text-base leading-relaxed text-white/60"  data-reveal="up">
  <?= e(__('about.story.desc')) ?>
  </p>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('about.whoWeServe.title')) ?>
  </h2>
  </div>
  <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
  <div class="rounded-2xl border border-white/8 bg-white/[0.025] p-6 text-center"  data-reveal="up">
  <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
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
  <p class="text-xs leading-relaxed text-white/50">
  <?= e(__('about.whoWeServe.items.1.desc')) ?>
  </p>
  </div>
  <div class="rounded-2xl border border-white/8 bg-white/[0.025] p-6 text-center"  data-reveal="up">
  <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
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
  <p class="text-xs leading-relaxed text-white/50">
  <?= e(__('about.whoWeServe.items.2.desc')) ?>
  </p>
  </div>
  <div class="rounded-2xl border border-white/8 bg-white/[0.025] p-6 text-center"  data-reveal="up">
  <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
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
  <p class="text-xs leading-relaxed text-white/50">
  <?= e(__('about.whoWeServe.items.3.desc')) ?>
  </p>
  </div>
  <div class="rounded-2xl border border-white/8 bg-white/[0.025] p-6 text-center"  data-reveal="up">
  <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
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
  <p class="text-xs leading-relaxed text-white/50">
  <?= e(__('about.whoWeServe.items.4.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 70% 50% at 50% 100%, rgba(253,230,138,0.10), transparent 70%)" aria-hidden="true">
  </div>
  <div class="container-x relative text-center">
  <div  data-reveal="up">
  <h2 class="mx-auto max-w-lg font-display text-3xl font-bold tracking-tight text-balance md:text-4xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
  <?= e(__('about.cta.title')) ?>
  </h2>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon hover:shadow-[0_0_50px_rgba(254,243,199,0.5)]" href="<?= base_url('contact.php') ?>">
  <?= e(__('about.cta.button')) ?>
  </a>
  </div>
  <?php if (REPLASH_LINKEDIN_URL !== ''): ?>
  <!-- LinkedIn: Replash'in arkasındaki kişinin profili -->
  <div class="mt-10 flex justify-center">
  <a href="<?= e(REPLASH_LINKEDIN_URL) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(__('about.linkedin')) ?>" title="<?= e(__('about.linkedin')) ?>" class="group inline-flex items-center gap-2.5 rounded-full border border-white/10 bg-white/[0.03] px-5 py-2.5 text-sm font-medium text-white/60 transition hover:border-white/25 hover:bg-white/[0.06] hover:text-white">
  <svg class="h-5 w-5 flex-shrink-0 text-white/50 transition group-hover:text-accent" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
  <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
  </svg>
  LinkedIn
  </a>
  </div>
  <?php endif; ?>
  </div>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
