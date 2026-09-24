<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.forPlayers');
$metaTitle = __('meta.forPlayers.title');
$metaDescription = __('meta.forPlayers.description');

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative bg-ink-900">

  <section class="relative overflow-hidden bg-ink-900 pt-36 pb-20 md:pt-44 md:pb-28">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute -inset-y-[30%] inset-x-0 will-change-transform" data-parallax="16">
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_player_phone_v2.webp') ?>" alt="" class="h-full w-full object-cover object-center" style="opacity:0.5"/>
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
  <?= e(__('forPlayers.hero.title')) ?>
  </h1>
  <p class="mx-auto mt-6 max-w-2xl text-lg text-white/55 md:text-xl"  data-reveal="up">
  <?= e(__('forPlayers.hero.subtitle')) ?>
  </p>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 60% 50% at 50% 30%, rgba(253,230,138,0.08), transparent 70%)" aria-hidden="true">
  </div>
  <div class="container-x relative">
  <div class="mx-auto max-w-3xl text-center">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('forPlayers.clipping.title')) ?>
  </h2>
  </div>
  <p class="mt-5 text-base leading-relaxed text-white/55"  data-reveal="up">
  <?= e(__('forPlayers.clipping.desc')) ?>
  </p>
  </div>
  <div class="mx-auto mt-14 max-w-5xl"  data-reveal="up">
  <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr] lg:items-center">
  <div class="overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1709] to-ink-900 shadow-[0_30px_100px_-20px_rgba(0,0,0,0.7)]">
  <div class="flex items-center gap-1.5 border-b border-white/10 bg-white/[0.03] px-3 py-2">
  <span class="h-2 w-2 rounded-full bg-red-500/60">
  </span>
  <span class="h-2 w-2 rounded-full bg-yellow-500/60">
  </span>
  <span class="h-2 w-2 rounded-full bg-green-500/60">
  </span>
  <span class="ml-3 text-[10px] text-white/30">replash.it/match/M-1234</span>
  </div>
  <div class="relative aspect-video bg-gradient-to-br from-[#201a08] to-[#110d08] overflow-hidden">
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_halisaha_camera_v2.webp') ?>" alt="" class="h-full w-full object-cover opacity-85"/>
  <div class="absolute inset-0 bg-gradient-to-t from-ink-900/70 via-transparent to-transparent">
  </div>
  <div class="absolute inset-0 flex items-center justify-center">
  <div class="flex h-14 w-14 items-center justify-center rounded-full border border-white/20 bg-white/10 backdrop-blur">
  <svg class="h-6 w-6 text-white translate-x-0.5" viewBox="0 0 24 24" fill="currentColor">
  <path d="M8 5v14l11-7z">
  </path>
  </svg>
  </div>
  </div>
  <div class="absolute left-3 top-3 flex items-center gap-1 rounded-full border border-accent/30 bg-accent/15 px-2 py-0.5 backdrop-blur">
  <span class="h-1.5 w-1.5 rounded-full bg-accent">
  </span>
  <span class="text-[9px] font-semibold uppercase tracking-wider text-accent">Match · 1:24:35</span>
  </div>
  <div class="absolute right-3 top-3 rounded-md border border-white/10 bg-white/5 px-1.5 py-0.5 text-[9px] text-white/60 backdrop-blur">1080p</div>
  </div>
  <div class="border-t border-white/8 p-3">
  <div class="mb-2 flex items-center justify-between gap-2 text-[10px]">
  <div class="flex items-center gap-2">
  <span class="text-white/40">Timeline</span>
  <span class="flex items-center gap-1 rounded-full border border-accent/30 bg-accent/15 px-2 py-0.5">
  <span class="h-1.5 w-1.5 rounded-full bg-accent">
  </span>
  <span class="text-[9px] font-semibold uppercase tracking-wider text-accent">AI Highlights · Soon</span>
  </span>
  </div>
  <span class="font-mono text-accent">Selected: 00:17:08 → 00:17:23</span>
  </div>
  <div class="relative h-8 rounded-md bg-white/[0.03] overflow-hidden">
  <div class="absolute inset-0 flex items-end gap-0.5 px-1">
  <div class="flex-1 bg-white/15" style="height:30.00%">
  </div>
  <div class="flex-1 bg-white/15" style="height:60.68%">
  </div>
  <div class="flex-1 bg-white/15" style="height:69.52%">
  </div>
  <div class="flex-1 bg-white/15" style="height:74.96%">
  </div>
  <div class="flex-1 bg-white/15" style="height:75.99%">
  </div>
  <div class="flex-1 bg-white/15" style="height:72.28%">
  </div>
  <div class="flex-1 bg-white/15" style="height:64.26%">
  </div>
  <div class="flex-1 bg-white/15" style="height:53.05%">
  </div>
  <div class="flex-1 bg-white/15" style="height:40.25%">
  </div>
  <div class="flex-1 bg-white/15" style="height:27.72%">
  </div>
  <div class="flex-1 bg-white/15" style="height:17.30%">
  </div>
  <div class="flex-1 bg-white/15" style="height:10.45%">
  </div>
  <div class="flex-1 bg-white/15" style="height:8.12%">
  </div>
  <div class="flex-1 bg-white/15" style="height:10.50%">
  </div>
  <div class="flex-1 bg-white/15" style="height:17.06%">
  </div>
  <div class="flex-1 bg-white/15" style="height:26.62%">
  </div>
  <div class="flex-1 bg-white/15" style="height:37.50%">
  </div>
  <div class="flex-1 bg-white/15" style="height:47.82%">
  </div>
  <div class="flex-1 bg-white/15" style="height:55.81%">
  </div>
  <div class="flex-1 bg-white/15" style="height:60.04%">
  </div>
  <div class="flex-1 bg-white/15" style="height:59.68%">
  </div>
  <div class="flex-1 bg-white/15" style="height:74.64%">
  </div>
  <div class="flex-1 bg-white/15" style="height:65.55%">
  </div>
  <div class="flex-1 bg-white/15" style="height:53.69%">
  </div>
  <div class="flex-1 bg-white/15" style="height:40.77%">
  </div>
  <div class="flex-1 bg-white/15" style="height:28.68%">
  </div>
  <div class="flex-1 bg-white/15" style="height:19.17%">
  </div>
  <div class="flex-1 bg-white/15" style="height:13.57%">
  </div>
  <div class="flex-1 bg-white/15" style="height:12.62%">
  </div>
  <div class="flex-1 bg-white/15" style="height:16.32%">
  </div>
  <div class="flex-1 bg-white/15" style="height:23.90%">
  </div>
  <div class="flex-1 bg-white/15" style="height:34.03%">
  </div>
  <div class="flex-1 bg-white/15" style="height:44.95%">
  </div>
  <div class="flex-1 bg-white/15" style="height:54.76%">
  </div>
  <div class="flex-1 bg-white/15" style="height:61.77%">
  </div>
  <div class="flex-1 bg-white/15" style="height:64.72%">
  </div>
  <div class="flex-1 bg-white/15" style="height:62.97%">
  </div>
  <div class="flex-1 bg-white/15" style="height:56.65%">
  </div>
  <div class="flex-1 bg-white/15" style="height:46.59%">
  </div>
  <div class="flex-1 bg-white/15" style="height:34.23%">
  </div>
  <div class="flex-1 bg-white/15" style="height:21.36%">
  </div>
  <div class="flex-1 bg-white/15" style="height:29.86%">
  </div>
  <div class="flex-1 bg-white/15" style="height:21.37%">
  </div>
  <div class="flex-1 bg-white/15" style="height:17.09%">
  </div>
  <div class="flex-1 bg-white/15" style="height:17.53%">
  </div>
  <div class="flex-1 bg-white/15" style="height:22.47%">
  </div>
  <div class="flex-1 bg-white/15" style="height:30.96%">
  </div>
  <div class="flex-1 bg-white/15" style="height:41.51%">
  </div>
  <div class="flex-1 bg-white/15" style="height:52.30%">
  </div>
  <div class="flex-1 bg-white/15" style="height:61.46%">
  </div>
  <div class="flex-1 bg-white/15" style="height:67.39%">
  </div>
  <div class="flex-1 bg-white/15" style="height:68.99%">
  </div>
  <div class="flex-1 bg-white/15" style="height:65.86%">
  </div>
  <div class="flex-1 bg-white/15" style="height:58.33%">
  </div>
  <div class="flex-1 bg-white/15" style="height:47.44%">
  </div>
  <div class="flex-1 bg-white/15" style="height:34.73%">
  </div>
  <div class="flex-1 bg-white/15" style="height:22.07%">
  </div>
  <div class="flex-1 bg-white/15" style="height:11.30%">
  </div>
  <div class="flex-1 bg-white/15" style="height:3.94%">
  </div>
  <div class="flex-1 bg-white/15" style="height:1.02%">
  </div>
  </div>
  <div aria-hidden="true" class="absolute inset-y-0 w-0.5 rounded-full bg-accent shadow-[0_0_6px_rgba(253,230,138,0.8)]" style="left:32%">
  </div>
  <div aria-hidden="true" class="absolute inset-y-0 w-0.5 rounded-full bg-accent/50" style="left:58%">
  </div>
  <div aria-hidden="true" class="absolute inset-y-0 w-0.5 rounded-full bg-accent/50" style="left:81%">
  </div>
  <div class="absolute inset-y-0 border-x-2 border-accent bg-accent/20" style="left:28%;width:8%">
  <div class="absolute -top-1 left-1/2 h-3 w-3 -translate-x-1/2 rounded-full bg-accent shadow-glow-sm">
  </div>
  </div>
  </div>
  <div class="relative mt-1 h-3" aria-hidden="true">
  <span class="absolute -translate-x-1/2 font-mono text-[9px] font-semibold text-accent" style="left:32%">17&#x27;</span>
  <span class="absolute -translate-x-1/2 font-mono text-[9px] text-white/35" style="left:58%">49&#x27;</span>
  <span class="absolute -translate-x-1/2 font-mono text-[9px] text-white/35" style="left:81%">68&#x27;</span>
  </div>
  <div class="mt-3 flex items-center justify-between gap-2">
  <div class="flex items-center gap-1.5">
  <span class="rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[10px] text-white/50">10s</span>
  <span class="rounded-md border border-accent/30 bg-accent/15 px-2 py-1 text-[10px] font-semibold text-accent">15s</span>
  <span class="rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[10px] text-white/50">45s</span>
  </div>
  <button type="button" class="inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-[10px] font-semibold text-ink-900">
  <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4">
  </path>
  <polyline points="7 10 12 15 17 10">
  </polyline>
  <line x1="12" y1="15" x2="12" y2="3">
  </line>
  </svg>Download Clip</button>
  </div>
  </div>
  </div>
  <div class="mx-auto w-full max-w-[260px] sm:max-w-[300px]">
  <div class="relative">
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_padel_story_blue.webp') ?>" alt="REPLASH match clip posted to Instagram story" class="w-full h-auto rounded-[2rem] shadow-[0_30px_80px_-15px_rgba(0,0,0,0.8)]"/>
  </div>
  <p class="mt-3 text-center text-[10px] text-white/35">Posted to player&#x27;s own Instagram story</p>
  </div>
  </div>
  </div>
  <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
  <div class="flex flex-col gap-2 rounded-xl border border-white/8 bg-white/[0.025] p-4"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
  <path d="M8 5v14l11-7z">
  </path>
  </svg>
  </span>
  <span class="text-[10px] font-semibold uppercase tracking-widest text-white/40">01</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.1.label')) ?>
  </h3>
  <p class="text-xs leading-relaxed text-white/45">
  <?= e(__('forPlayers.clipping.features.1.desc')) ?>
  </p>
  </div>
  <div class="flex flex-col gap-2 rounded-xl border border-white/8 bg-white/[0.025] p-4"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
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
  </span>
  <span class="text-[10px] font-semibold uppercase tracking-widest text-white/40">02</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.2.label')) ?>
  </h3>
  <p class="text-xs leading-relaxed text-white/45">
  <?= e(__('forPlayers.clipping.features.2.desc')) ?>
  </p>
  </div>
  <div class="flex flex-col gap-2 rounded-xl border border-white/8 bg-white/[0.025] p-4"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4">
  </path>
  <polyline points="7 10 12 15 17 10">
  </polyline>
  <line x1="12" y1="15" x2="12" y2="3">
  </line>
  </svg>
  </span>
  <span class="text-[10px] font-semibold uppercase tracking-widest text-white/40">03</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.3.label')) ?>
  </h3>
  <p class="text-xs leading-relaxed text-white/45">
  <?= e(__('forPlayers.clipping.features.3.desc')) ?>
  </p>
  </div>
  <div class="flex flex-col gap-2 rounded-xl border border-white/8 bg-white/[0.025] p-4"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
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
  </span>
  <span class="text-[10px] font-semibold uppercase tracking-widest text-white/40">04</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.4.label')) ?>
  </h3>
  <p class="text-xs leading-relaxed text-white/45">
  <?= e(__('forPlayers.clipping.features.4.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('forPlayers.whyVideo.title')) ?>
  </h2>
  </div>
  <div class="mt-12 grid gap-5 sm:grid-cols-2">
  <div class="group relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-6xl font-bold text-white/[0.04] select-none">01</span>
  <div class="relative flex items-start gap-4">
  <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
  </path>
  </svg>
  </div>
  <div class="flex-1">
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forPlayers.whyVideo.reasons.1.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forPlayers.whyVideo.reasons.1.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  <div class="group relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-6xl font-bold text-white/[0.04] select-none">02</span>
  <div class="relative flex items-start gap-4">
  <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
  </path>
  <circle cx="12" cy="13" r="4">
  </circle>
  </svg>
  </div>
  <div class="flex-1">
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forPlayers.whyVideo.reasons.2.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forPlayers.whyVideo.reasons.2.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  <div class="group relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-6xl font-bold text-white/[0.04] select-none">03</span>
  <div class="relative flex items-start gap-4">
  <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <line x1="22" y1="2" x2="11" y2="13">
  </line>
  <polygon points="22 2 15 22 11 13 2 9 22 2">
  </polygon>
  </svg>
  </div>
  <div class="flex-1">
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forPlayers.whyVideo.reasons.3.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forPlayers.whyVideo.reasons.3.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  <div class="group relative overflow-hidden rounded-2xl border border-white/8 bg-white/[0.025] p-6 transition-colors duration-300 hover:border-accent/30"  data-reveal="up">
  <span class="absolute right-5 top-4 font-display text-6xl font-bold text-white/[0.04] select-none">04</span>
  <div class="relative flex items-start gap-4">
  <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-accent/25 bg-accent/10 text-accent">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
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
  <div class="flex-1">
  <h3 class="mb-2 text-base font-semibold text-white">
  <?= e(__('forPlayers.whyVideo.reasons.4.title')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/55">
  <?= e(__('forPlayers.whyVideo.reasons.4.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </div>
  </div>
  </section>





  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 70% 50% at 50% 100%, rgba(253,230,138,0.10), transparent 70%)" aria-hidden="true">
  </div>
  <div class="container-x relative text-center">
  <div  data-reveal="up">
  <h2 class="mx-auto max-w-xl font-display text-3xl font-bold tracking-tight text-balance md:text-4xl" style="background:linear-gradient(to bottom, #ffffff 40%, rgba(255,255,255,0.5));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
  <?= e(__('forPlayers.cta.title')) ?>
  </h2>
  <p class="mx-auto mt-4 max-w-md text-sm text-white/45">
  <?= e(__('forPlayers.cta.subtitle')) ?>
  </p>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon hover:shadow-[0_0_50px_rgba(254,243,199,0.5)]" href="<?= base_url('for-facilities.php') ?>">
  <?= e(__('forPlayers.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
