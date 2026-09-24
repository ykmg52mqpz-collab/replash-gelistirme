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
  <img loading="lazy" decoding="async" src="<?= asset('images/gen_football_night.webp') ?>" alt="" class="h-full w-full object-cover object-center" style="opacity:0.45"/>
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
  <?= e(__('howItWorks.flow.1.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.1.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.flow.1.desc')) ?>
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
  <?= e(__('howItWorks.flow.2.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.2.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.flow.2.desc')) ?>
  </p>
  <div class="mt-5">
<!-- Gerçek tesis paneli: gün tablosu, personel "Kilidi aç"a basar -->
<div class="overflow-hidden rounded-2xl border border-white/10 bg-white text-gray-900 shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-gray-200 bg-gray-50 px-3 py-2">
    <span class="h-2 w-2 rounded-full bg-gray-300"></span><span class="h-2 w-2 rounded-full bg-gray-300"></span><span class="h-2 w-2 rounded-full bg-gray-300"></span>
    <span lang="en" class="ml-3 font-mono text-[10px] uppercase tracking-widest text-gray-400">Replash · Panel</span>
  </div>
  <div class="p-4">
    <div class="mb-3 flex items-center justify-between text-xs">
      <span class="font-semibold text-gray-900"><?= e(formatDateLocalized(date('Y-m-d'), $current_lang)) ?></span>
      <span class="text-gray-500"><?= e(str_replace(['{t}', '{s}', '{l}'], [4, 1, 3], __('panel.shell.day_summary'))) ?></span>
    </div>
    <div class="overflow-hidden rounded-lg border border-gray-200">
      <table class="w-full border-collapse text-left text-xs">
        <thead><tr class="border-b border-gray-200 bg-gray-50 text-[10px] font-medium uppercase tracking-wide text-gray-500"><th class="w-24 px-3 py-2"><?= e(__('panel.shell.time_col')) ?></th><th class="px-3 py-2"><?= e(__('find.explainer.court', 'Campo 1')) ?> 1</th><th class="px-3 py-2"><?= e(__('find.explainer.court', 'Campo')) ?> 2</th></tr></thead>
        <tbody>
          <tr class="border-b border-gray-100"><td class="whitespace-nowrap px-3 py-2.5 font-mono text-gray-700">18:00–19:00</td><td class="px-3 py-2.5"><div><div class="text-[10px] font-medium uppercase tracking-wide text-emerald-700"><?= e(__('panel.shell.sold_state')) ?></div><div class="font-mono text-[13px] font-semibold tracking-wider text-gray-900">3907</div></div></td><td class="px-3 py-2.5 text-gray-300">—</td></tr>
          <tr class="border-b border-gray-100 hwp-rowflash"><td class="whitespace-nowrap px-3 py-2.5 font-mono text-gray-700">19:00–20:00</td>
            <td class="px-3 py-2.5"><div class="relative h-9">
              <div class="hwp-a absolute inset-0 flex items-center"><div class="flex items-center gap-2"><span class="text-[10px] font-medium uppercase tracking-wide text-gray-500"><?= e(__('panel.shell.locked_state')) ?></span><span class="hwp-press rounded-md bg-gray-900 px-2 py-1 text-[11px] font-medium text-white"><?= e(__('panel.shell.unlock')) ?></span></div></div>
              <div class="hwp-b absolute inset-0 flex items-center"><div><div class="text-[10px] font-medium uppercase tracking-wide text-emerald-700"><?= e(__('panel.shell.sold_state')) ?></div><div class="font-mono text-[13px] font-semibold tracking-wider text-gray-900">4821</div></div></div>
              <svg class="hwp-cursor pointer-events-none absolute h-5 w-5 drop-shadow" style="left:92px;top:14px" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 2l15 9-6.5 1.5L16 20l-2.5 1.2-3.4-7.3L5 18z" fill="#111" stroke="#fff" stroke-width="1.2" stroke-linejoin="round"/></svg>
            </div></td>
            <td class="px-3 py-2.5"><div class="flex items-center gap-2"><span class="text-[10px] font-medium uppercase tracking-wide text-gray-500"><?= e(__('panel.shell.locked_state')) ?></span><span class="rounded-md bg-gray-900 px-2 py-1 text-[11px] font-medium text-white"><?= e(__('panel.shell.unlock')) ?></span></div></td></tr>
          <tr><td class="whitespace-nowrap px-3 py-2.5 font-mono text-gray-700">20:00–21:00</td><td class="px-3 py-2.5"><div class="flex items-center gap-2"><span class="text-[10px] font-medium uppercase tracking-wide text-gray-500"><?= e(__('panel.shell.locked_state')) ?></span><span class="rounded-md bg-gray-900 px-2 py-1 text-[11px] font-medium text-white"><?= e(__('panel.shell.unlock')) ?></span></div></td><td class="px-3 py-2.5 text-gray-300">—</td></tr>
        </tbody>
      </table>
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
  <?= e(__('howItWorks.flow.3.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.3.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.flow.3.desc')) ?>
  </p>
  <div class="mt-5">
<!-- Gerçek "Maçını bul" kartı: kod yazılır, Maçı izle -->
<div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
    <span class="h-2 w-2 rounded-full bg-white/15"></span><span class="h-2 w-2 rounded-full bg-white/15"></span><span class="h-2 w-2 rounded-full bg-white/15"></span>
    <span lang="en" class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">replash.it</span>
  </div>
  <div class="p-4">
    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
      <div class="mb-3 flex items-center gap-2.5">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FDE68A" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 118 0v3"></path></svg>
        <span class="font-display text-base font-bold text-white"><?= e(__('find.watch.title')) ?></span>
      </div>
      <p class="mb-4 text-xs text-white/50"><?= e(__('find.watch.subtitle')) ?></p>
      <span class="text-[10px] font-semibold uppercase tracking-wider text-white/60"><?= e(__('find.watch.codeLabel')) ?></span>
      <div class="hwp-input mt-1.5 flex h-12 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] font-mono text-lg font-bold tracking-[0.3em] text-white"><span class="hwp-type">4821</span><span class="hwp-caret ml-0.5 h-5 w-px bg-accent"></span></div>
      <div class="hwp-go mt-4 w-full rounded-xl bg-accent px-4 py-2.5 text-center text-sm font-bold text-ink-900"><?= e(__('find.watch.button')) ?></div>
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
  <?= e(__('howItWorks.flow.4.label')) ?>
  </span>
  <h3 class="mt-1 font-display text-xl font-bold text-white md:text-2xl">
  <?= e(__('howItWorks.flow.4.title')) ?>
  </h3>
  </div>
  <p class="text-sm leading-relaxed text-white/55 md:text-base">
  <?= e(__('howItWorks.flow.4.desc')) ?>
  </p>
  <div class="mt-5">
<!-- Gerçek oynatıcı: UNLOCKED, filigranlı video, aralık seç, Klip kes, paylaş -->
<div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d0d11] shadow-[0_25px_70px_-30px_rgba(0,0,0,0.85)]">
  <div class="flex items-center gap-1.5 border-b border-white/[0.06] bg-black/40 px-3 py-2">
    <span class="h-2 w-2 rounded-full bg-white/15"></span><span class="h-2 w-2 rounded-full bg-white/15"></span><span class="h-2 w-2 rounded-full bg-white/15"></span>
    <span lang="en" class="ml-3 font-mono text-[10px] uppercase tracking-widest text-white/35">replash.it · <?= e(__('find.watch.title')) ?></span>
  </div>
  <div class="p-4">
    <div class="mb-2 flex items-center gap-2 rounded-xl border border-emerald-400/25 bg-emerald-400/10 px-3 py-1.5">
      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-400/15 px-2 py-0.5 text-[9px] font-bold tracking-wide text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>UNLOCKED</span>
      <span class="truncate font-mono text-[10px] text-white/70">Replash Demo Arena · <?= e(__('find.explainer.court', 'Campo')) ?> 1 · 19:00–20:00</span>
    </div>
    <div class="relative aspect-video w-full overflow-hidden rounded-lg bg-black">
      <img loading="lazy" decoding="async" src="<?= asset('images/gen_football_night.webp') ?>" alt="" loading="lazy" class="h-full w-full object-cover opacity-90">
      <div class="absolute right-2.5 top-2 flex items-center gap-1.5 font-mono text-[10px] tracking-wider text-white/90" style="text-shadow:0 1px 3px rgba(0,0,0,.6)">replash.it<img loading="lazy" decoding="async" src="<?= asset('images/replash-logo.webp') ?>" alt="" class="h-3.5 w-auto"></div>
      <div class="absolute bottom-2 right-2.5 font-mono text-[8px] tracking-wider text-white/80" style="text-shadow:0 1px 3px rgba(0,0,0,.6)">REPLASH DEMO ARENA · SAHA 1</div>
      <div class="absolute inset-0 flex items-center justify-center"><div class="hwp-playicon flex h-10 w-10 items-center justify-center rounded-full bg-white/15 backdrop-blur-sm"><svg class="h-4 w-4 translate-x-[1px] text-white" viewBox="0 0 20 20" fill="currentColor"><path d="M8 5v10l7-5-7-5z"></path></svg></div></div>
    </div>
    <div class="mt-2 rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2.5">
      <div class="flex items-center gap-2.5">
        <span class="grid h-7 w-7 flex-none place-items-center rounded-full bg-accent text-ink-900"><svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
        <div class="relative h-1.5 flex-1 rounded-full bg-white/20">
          <div class="absolute inset-y-0 left-0 w-[30%] rounded-full bg-accent"></div>
          <div class="hw-clip absolute -bottom-1 -top-1 rounded border border-accent bg-accent/20" style="left:18%;width:8%"></div>
        </div>
        <span class="font-mono text-[10px] text-white/80">17:04 / 58:00</span>
      </div>
      <div class="mt-2 flex items-center justify-between gap-2">
        <span class="hwp-cut rounded-full border border-white/10 px-2.5 py-1 text-[10px] font-semibold text-white/40"><?= e(__('find.watch.clip.label')) ?></span>
        <span class="font-mono text-[10px] text-white/50">17:04 → 17:32 · 28s</span>
      </div>
    </div>
    <div class="hw-share mt-2 flex items-center justify-between gap-2">
      <div class="flex gap-1.5">
        <span class="rounded-lg bg-accent px-2.5 py-1 text-[10px] font-bold text-ink-900"><?= e(__('find.modal.save_to_gallery')) ?></span>
        <span class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-2.5 py-1 text-[10px] font-bold text-emerald-300"><?= e(__('find.modal.share_clip')) ?></span>
      </div>
      <div class="flex gap-1">
        <span class="grid h-6 w-6 place-items-center rounded-md border border-[#25D366]/40 bg-[#25D366]/15 font-mono text-[8px] font-bold text-[#25D366]">WA</span>
        <span class="grid h-6 w-6 place-items-center rounded-md border border-[#E1306C]/40 bg-[#E1306C]/15 font-mono text-[8px] font-bold text-[#E1306C]">IG</span>
        <span class="grid h-6 w-6 place-items-center rounded-md border border-white/15 bg-white/10 font-mono text-[8px] font-bold text-white">TT</span>
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
