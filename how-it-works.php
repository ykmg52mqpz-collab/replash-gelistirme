<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.howItWorks');
$metaTitle = __('meta.howItWorks.title');
$metaDescription = __('meta.howItWorks.description');

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative bg-ink-900">

  <section class="relative overflow-hidden bg-ink-900 pt-36 pb-20 md:pt-44 md:pb-28">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute -inset-y-[30%] inset-x-0 will-change-transform" data-parallax="16">
  <img src="<?= asset('images/gen_football_night.jpg') ?>" alt="" class="h-full w-full object-cover object-center" style="opacity:0.45"/>
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
  <?= e(__('howItWorks.hero.title')) ?>
  </h1>
  <p class="mx-auto mt-6 max-w-2xl text-lg text-white/55 md:text-xl"  data-reveal="up">
  <?= e(__('howItWorks.hero.subtitle')) ?>
  </p>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="mx-auto max-w-4xl">
  <div class="relative">
  <div class="absolute left-[27px] top-2 bottom-2 w-px bg-gradient-to-b from-accent/40 via-accent/15 to-accent/5 sm:left-[35px]">
  </div>
  <div class="flex flex-col gap-8">
  <div class="relative flex gap-5"  data-reveal="up">
  <div class="relative flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-accent/30 bg-ink-900 text-accent sm:h-[70px] sm:w-[70px]">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <rect x="3" y="4" width="18" height="18" rx="2">
  </rect>
  <line x1="16" y1="2" x2="16" y2="6">
  </line>
  <line x1="8" y1="2" x2="8" y2="6">
  </line>
  <line x1="3" y1="10" x2="21" y2="10">
  </line>
  <polygon points="10 14 15 17 10 20" fill="currentColor">
  </polygon>
  </svg>
  </div>
  </div>
  <div class="flex-1 pb-2">
  <div class="mb-3">
  <span class="text-[10px] font-bold uppercase tracking-widest text-accent/70">
  <?= e(__('howItWorks.steps.1.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.steps.1.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.steps.1.desc')) ?>
  </p>
  <div class="mt-5">
  <div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">Facility · Book slot</span>
  </div>
  <div class="relative p-4">
  <div class="mb-3 flex items-center justify-between text-xs">
  <span class="text-white/50">Tuesday · Nov 12</span>
  <span class="rounded-full bg-white/5 px-2 py-0.5 text-white/40">Football · 5v5</span>
  </div>
  <div class="flex flex-col gap-1.5">
  <div class="flex items-center justify-between rounded-lg border px-3 py-2.5 text-sm" style="border-color:rgba(255,255,255,0.08);background-color:rgba(255,255,255,0.02)">
  <span class="text-white/85">18:00 – 19:00</span>
  </div>
  <div class="flex items-center justify-between rounded-lg border px-3 py-2.5 text-sm hw-slot" style="border-color:rgba(255,255,255,0.08);background-color:rgba(255,255,255,0.02)">
  <span class="text-white/85">19:00 – 20:00</span>
  </div>
  <div class="flex items-center justify-between rounded-lg border px-3 py-2.5 text-sm" style="border-color:rgba(255,255,255,0.08);background-color:rgba(255,255,255,0.02)">
  <span class="text-white/85">20:00 – 21:00</span>
  </div>
  </div>
  <div class="mt-4 flex items-center justify-between rounded-lg border border-accent/25 bg-accent/[0.05] px-3 py-2.5">
  <div class="flex items-center gap-2">
  <svg class="h-3.5 w-3.5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
  <path d="M23 7l-7 5 7 5V7z">
  </path>
  <rect x="1" y="5" width="15" height="14" rx="2">
  </rect>
  </svg>
  <span class="text-sm text-white/85">+ Match Video</span>
  </div>
  <div class="relative h-5 w-9 rounded-full hw-toggle">
  <div class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow hw-knob">
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  <div class="relative flex gap-5"  data-reveal="up">
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
  <div class="flex-1 pb-2">
  <div class="mb-3">
  <span class="text-[10px] font-bold uppercase tracking-widest text-accent/70">
  <?= e(__('howItWorks.steps.2.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.steps.2.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.steps.2.desc')) ?>
  </p>
  <div class="mt-5">
  <div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">Replash Cam · Live feed</span>
  </div>
  <div class="relative p-4">
  <div class="relative aspect-[16/9] w-full overflow-hidden rounded-lg">
  <div class="absolute inset-0 bg-[#0f2a1c]">
  </div>
  <svg class="absolute inset-0 h-full w-full" viewBox="0 0 320 180" preserveAspectRatio="none">
  <g stroke="rgba(255,255,255,0.22)" stroke-width="0.8" fill="none">
  <rect x="10" y="12" width="300" height="156" rx="2">
  </rect>
  <line x1="160" y1="12" x2="160" y2="168">
  </line>
  <circle cx="160" cy="90" r="18">
  </circle>
  <rect x="10" y="60" width="30" height="60">
  </rect>
  <rect x="280" y="60" width="30" height="60">
  </rect>
  </g>
  <circle r="2.5" fill="rgb(253,230,138)" cx="160" cy="90"><animate attributeName="cx" values="160;120;100;130;160;200;220;190;160" dur="10s" begin="0s" repeatCount="indefinite"/><animate attributeName="cy" values="90;70;100;120;90;70;100;120;90" dur="10s" begin="0s" repeatCount="indefinite"/>
  </circle>
  <circle r="3" fill="white" cx="90" cy="70"><animate attributeName="cx" values="90;110;95;90" dur="12s" begin="0s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/><animate attributeName="cy" values="70;85;100;70" dur="12s" begin="0s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/>
  </circle>
  <circle r="3" fill="white" cx="140" cy="110"><animate attributeName="cx" values="140;160;145;140" dur="12s" begin="0.5s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/><animate attributeName="cy" values="110;100;130;110" dur="12s" begin="0.5s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/>
  </circle>
  <circle r="3" fill="#111" stroke="white" stroke-width="0.5" cx="230" cy="80"><animate attributeName="cx" values="230;210;220;230" dur="12s" begin="0s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/><animate attributeName="cy" values="80;95;110;80" dur="12s" begin="0s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/>
  </circle>
  <circle r="3" fill="#111" stroke="white" stroke-width="0.5" cx="180" cy="110"><animate attributeName="cx" values="180;200;185;180" dur="12s" begin="0.8s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/><animate attributeName="cy" values="110;95;125;110" dur="12s" begin="0.8s" repeatCount="indefinite" calcMode="spline" keySplines="0.42 0 0.58 1;0.42 0 0.58 1;0.42 0 0.58 1"/>
  </circle>
  </svg>
  <div class="absolute right-2 top-2 flex items-center gap-1.5 rounded bg-black/50 px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-widest text-white/85 backdrop-blur-sm">
  <span class="h-1.5 w-1.5 rounded-full bg-red-500 hw-blink">
  </span>Rec 47:12</div>
  <div class="absolute bottom-2 left-2 font-mono text-[9px] uppercase tracking-widest text-white/50">Cam 01 · Auto</div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  <div class="relative flex gap-5"  data-reveal="up">
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
  <div class="flex-1 pb-2">
  <div class="mb-3">
  <span class="text-[10px] font-bold uppercase tracking-widest text-accent/70">
  <?= e(__('howItWorks.steps.3.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.steps.3.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.steps.3.desc')) ?>
  </p>
  <div class="mt-5">
  <div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">Auto-tagging</span>
  </div>
  <div class="relative p-4">
  <div class="mb-3 flex items-center gap-2 text-xs text-white/50">
  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
  <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z">
  </path>
  <line x1="7" y1="7" x2="7.01" y2="7">
  </line>
  </svg>Recording · 00:47:12</div>
  <div class="flex flex-col gap-2">
  <div class="flex items-center justify-between rounded-lg border border-white/[0.08] bg-white/[0.02] px-3 py-2 hw-chip" style="animation-delay:0s">
  <span class="font-mono text-[10px] uppercase tracking-widest text-white/45">Facility</span>
  <span class="text-sm text-white/85">Example FC</span>
  </div>
  <div class="flex items-center justify-between rounded-lg border border-white/[0.08] bg-white/[0.02] px-3 py-2 hw-chip" style="animation-delay:0.4s">
  <span class="font-mono text-[10px] uppercase tracking-widest text-white/45">Date</span>
  <span class="text-sm text-white/85">Tue · Nov 12</span>
  </div>
  <div class="flex items-center justify-between rounded-lg border border-white/[0.08] bg-white/[0.02] px-3 py-2 hw-chip" style="animation-delay:0.8s">
  <span class="font-mono text-[10px] uppercase tracking-widest text-white/45">Slot</span>
  <span class="text-sm text-white/85">19:00 – 20:00</span>
  </div>
  </div>
  <div class="mt-3 flex items-center gap-2 text-xs text-accent hw-indexed">
  <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd">
  </path>
  </svg>Match indexed</div>
  </div>
  </div>
  </div>
  </div>
  </div>
  <div class="relative flex gap-5"  data-reveal="up">
  <div class="relative flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-accent/30 bg-ink-900 text-accent sm:h-[70px] sm:w-[70px]">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14">
  </path>
  <polyline points="22 4 12 14.01 9 11.01">
  </polyline>
  </svg>
  </div>
  </div>
  <div class="flex-1 pb-2">
  <div class="mb-3">
  <span class="text-[10px] font-bold uppercase tracking-widest text-accent/70">
  <?= e(__('howItWorks.steps.4.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.steps.4.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.steps.4.desc')) ?>
  </p>
  <div class="mt-5">
  <div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">Admin panel · Today</span>
  </div>
  <div class="relative p-4">
  <div class="mb-2 grid grid-cols-[80px_1fr_100px] gap-2 border-b border-white/[0.06] pb-2 font-mono text-[10px] uppercase tracking-widest text-white/40">
  <span>Slot</span>
  <span>Players</span>
  <span class="text-right">Status</span>
  </div>
  <div class="flex flex-col gap-1">
  <div class="grid grid-cols-[80px_1fr_100px] items-center gap-2 rounded-md px-2 py-2 text-sm" style="opacity:0.55">
  <span class="text-white/80">18:00</span>
  <span class="text-white/60">4/4</span>
  <span class="justify-self-end rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white/8 text-white/40">SOLD</span>
  </div>
  <div class="grid grid-cols-[80px_1fr_100px] items-center gap-2 rounded-md px-2 py-2 text-sm hw-row">
  <span class="text-white/80">19:00</span>
  <span class="text-white/60">4/4</span>
  <span class="justify-self-end rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider hw-badge">
  <span class="hw-out" style="position:absolute">Pending</span>
  <span class="hw-in">Unlockable</span>
  </span>
  </div>
  <div class="grid grid-cols-[80px_1fr_100px] items-center gap-2 rounded-md px-2 py-2 text-sm">
  <span class="text-white/80">20:00</span>
  <span class="text-white/60">3/4</span>
  <span class="justify-self-end rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white/5 text-white/55">OPEN</span>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  <div class="relative flex gap-5"  data-reveal="up">
  <div class="relative flex-shrink-0">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-accent/30 bg-ink-900 text-accent sm:h-[70px] sm:w-[70px]">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="11" cy="11" r="8">
  </circle>
  <line x1="21" y1="21" x2="16.65" y2="16.65">
  </line>
  </svg>
  </div>
  </div>
  <div class="flex-1 pb-2">
  <div class="mb-3">
  <span class="text-[10px] font-bold uppercase tracking-widest text-accent/70">
  <?= e(__('howItWorks.steps.5.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.steps.5.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.steps.5.desc')) ?>
  </p>
  <div class="mt-5">
  <div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">Find your match</span>
  </div>
  <div class="relative p-4">
  <div class="flex flex-col gap-2">
  <div class="flex flex-col gap-1">
  <span class="font-mono text-[10px] uppercase tracking-widest text-white/40">Facility</span>
  <div class="rounded-lg border border-white/[0.08] bg-white/[0.02] px-3 py-2.5 text-sm">
  <span class="text-white/85 hw-field" style="opacity:0.25;animation-delay:0s">Example FC</span>
  </div>
  </div>
  <div class="flex flex-col gap-1">
  <span class="font-mono text-[10px] uppercase tracking-widest text-white/40">Date</span>
  <div class="rounded-lg border border-white/[0.08] bg-white/[0.02] px-3 py-2.5 text-sm">
  <span class="text-white/85 hw-field" style="opacity:0.25;animation-delay:0.8s">Nov 12</span>
  </div>
  </div>
  <div class="flex flex-col gap-1">
  <span class="font-mono text-[10px] uppercase tracking-widest text-white/40">Time slot</span>
  <div class="rounded-lg border border-white/[0.08] bg-white/[0.02] px-3 py-2.5 text-sm">
  <span class="text-white/85 hw-field" style="opacity:0.25;animation-delay:1.6s">19:00 – 20:00</span>
  </div>
  </div>
  </div>
  <div class="mt-3 flex items-center justify-center gap-2 rounded-lg bg-accent px-3 py-2.5 text-sm font-semibold text-ink-900 hw-watch" style="opacity:0.3">
  <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
  <path d="M8 5v10l7-5-7-5z">
  </path>
  </svg>Watch match</div>
  </div>
  </div>
  </div>
  </div>
  </div>
  <div class="relative flex gap-5"  data-reveal="up">
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
  <div class="flex-1 pb-2">
  <div class="mb-3">
  <span class="text-[10px] font-bold uppercase tracking-widest text-accent/70">
  <?= e(__('howItWorks.steps.6.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.steps.6.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.steps.6.desc')) ?>
  </p>
  <div class="mt-5">
  <div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="h-2 w-2 rounded-full bg-white/15">
  </span>
  <span class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">Video player · Clip &amp; share</span>
  </div>
  <div class="relative p-4">
  <div class="relative aspect-[16/9] w-full overflow-hidden rounded-lg bg-[#0f2a1c]">
  <svg class="absolute inset-0 h-full w-full opacity-30" viewBox="0 0 320 180" preserveAspectRatio="none">
  <g stroke="rgba(255,255,255,0.3)" stroke-width="0.8" fill="none">
  <rect x="10" y="12" width="300" height="156" rx="2">
  </rect>
  <line x1="160" y1="12" x2="160" y2="168">
  </line>
  <circle cx="160" cy="90" r="18">
  </circle>
  </g>
  </svg>
  <div class="absolute inset-0 flex items-center justify-center">
  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 backdrop-blur-sm">
  <svg class="h-4 w-4 translate-x-[1px] text-white" viewBox="0 0 20 20" fill="currentColor">
  <path d="M8 5v10l7-5-7-5z">
  </path>
  </svg>
  </div>
  </div>
  <div class="absolute inset-x-3 bottom-3">
  <div class="relative h-1 rounded-full bg-white/15">
  <div class="absolute top-0 h-1 rounded-full bg-accent hw-clip" style="left:18%;width:8%">
  </div>
  </div>
  <div class="mt-1.5 flex justify-between font-mono text-[9px] uppercase tracking-widest text-white/50">
  <span>17:04</span>
  <span>17:32</span>
  </div>
  </div>
  </div>
  <div class="mt-3 flex items-center justify-between">
  <span class="text-xs text-white/50">Clip 00:28</span>
  <div class="flex items-center gap-2 hw-share">
  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/8 font-mono text-[9px] font-bold uppercase text-white/70">IG</span>
  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/8 font-mono text-[9px] font-bold uppercase text-white/70">TT</span>
  <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/8 font-mono text-[9px] font-bold uppercase text-white/70">WA</span>
  <span class="rounded-full bg-accent px-3 py-1 text-[11px] font-semibold text-ink-900">Share</span>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 60% 60% at 50% 100%, rgba(253,230,138,0.10), transparent 70%)" aria-hidden="true">
  </div>
  <div class="container-x relative text-center">
  <div  data-reveal="up">
  <h2 class="mx-auto max-w-xl font-display text-3xl font-bold tracking-tight text-balance md:text-4xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
  <?= e(__('howItWorks.cta.title')) ?>
  </h2>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon hover:shadow-[0_0_50px_rgba(254,243,199,0.5)]" href="<?= base_url('contact.php') ?>">
  <?= e(__('howItWorks.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
