<?php
require_once __DIR__ . '/init.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?= e($current_lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($metaTitle ?? (isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME . ' | ' . __('home.hero.title'))) ?></title>
  <meta name="description" content="<?= e($metaDescription ?? __('home.hero.subtitle')) ?>">
  <link rel="icon" type="image/x-icon" href="<?= asset('images/icon-512.png') ?>">
  <link rel="apple-touch-icon" href="<?= asset('images/icon-512.png') ?>">
  
  <!-- Preloads -->
  <link rel="preload" as="image" href="<?= asset('images/replash-logo.png') ?>">
  <link rel="preload" as="image" href="<?= asset('images/gen_hero_friends.jpg') ?>">
  <link rel="preload" as="image" href="<?= asset('images/gen_padel_club_empty.jpg') ?>">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            ink: {
              900: "#0A0A0A",
              800: "#131313",
              700: "#1a1709",
              600: "#241f0d",
            },
            accent: {
              DEFAULT: "#FDE68A",
              neon: "#FEF3C7",
              glow: "rgba(253, 230, 138, 0.35)",
            }
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= @filemtime(REPLASH_ROOT . '/assets/css/style.css') ?>">
</head>
<body class="font-body bg-ink-900 text-white antialiased min-h-screen flex flex-col selection:bg-accent selection:text-ink-900">

  <!-- Floating Pill Header (Centered Container) -->
  <header class="pointer-events-none fixed inset-x-0 top-0 z-50 pt-3 sm:pt-4">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="pointer-events-auto mx-auto flex h-14 items-center justify-between gap-3 sm:gap-4 rounded-full border px-3 pl-4 pr-2 transition-[background-color,border-color,box-shadow,backdrop-filter] duration-500 md:px-4 md:pl-5 md:pr-2.5 border-white/[0.08] bg-ink-900/80 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.7)] backdrop-blur-xl">
        
        <!-- Logo -->
        <a class="flex items-center gap-2" href="<?= base_url() ?>">
          <img src="<?= asset('images/replash-logo.png') ?>" alt="REPLASH" class="h-8 w-auto select-none md:h-9" draggable="false"/>
          <span lang="en" style="font-weight:600" class="font-display text-xl tracking-tight text-accent md:text-2xl">replash</span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="hidden items-center gap-1 md:flex">
          <a class="group relative rounded-full px-3 py-1.5 text-sm transition-colors duration-200 <?= $currentPage === 'index' ? 'text-white' : 'text-white/60 hover:text-white' ?>" href="<?= base_url() ?>">
            <?= e(__('nav.home')) ?>
            <span class="absolute inset-x-3 bottom-1 h-px rounded-full bg-accent transition-transform duration-200 <?= $currentPage === 'index' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' ?>"></span>
          </a>
          <a class="group relative rounded-full px-3 py-1.5 text-sm transition-colors duration-200 <?= $currentPage === 'how-it-works' ? 'text-white' : 'text-white/60 hover:text-white' ?>" href="<?= base_url('how-it-works.php') ?>">
            <?= e(__('nav.howItWorks')) ?>
            <span class="absolute inset-x-3 bottom-1 h-px rounded-full bg-accent transition-transform duration-200 <?= $currentPage === 'how-it-works' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' ?>"></span>
          </a>
          <a class="group relative rounded-full px-3 py-1.5 text-sm transition-colors duration-200 <?= $currentPage === 'for-facilities' ? 'text-white' : 'text-white/60 hover:text-white' ?>" href="<?= base_url('for-facilities.php') ?>">
            <?= e(__('nav.forFacilities')) ?>
            <span class="absolute inset-x-3 bottom-1 h-px rounded-full bg-accent transition-transform duration-200 <?= $currentPage === 'for-facilities' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' ?>"></span>
          </a>
          <a class="group relative rounded-full px-3 py-1.5 text-sm transition-colors duration-200 <?= $currentPage === 'for-players' ? 'text-white' : 'text-white/60 hover:text-white' ?>" href="<?= base_url('for-players.php') ?>">
            <?= e(__('nav.forPlayers')) ?>
            <span class="absolute inset-x-3 bottom-1 h-px rounded-full bg-accent transition-transform duration-200 <?= $currentPage === 'for-players' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' ?>"></span>
          </a>
          <a class="group relative rounded-full px-3 py-1.5 text-sm transition-colors duration-200 <?= $currentPage === 'about' ? 'text-white' : 'text-white/60 hover:text-white' ?>" href="<?= base_url('about.php') ?>">
            <?= e(__('nav.about')) ?>
            <span class="absolute inset-x-3 bottom-1 h-px rounded-full bg-accent transition-transform duration-200 <?= $currentPage === 'about' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' ?>"></span>
          </a>
        </nav>

        <!-- Right Side Controls -->
        <div class="flex items-center gap-2">
          <!-- Find Match Link -->
          <a class="hidden rounded-full border border-accent/40 px-4 py-2 text-sm font-semibold text-accent transition-[background-color,border-color,transform] active:scale-[0.98] duration-200 hover:border-accent hover:bg-accent/10 lg:inline-flex" href="<?= base_url('find.php') ?>">
            <?= e(__('nav.find')) ?>
          </a>

          <!-- Language Selector Dropdown -->
          <div class="relative">
            <button type="button" id="lang-btn" class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-white/90 transition hover:border-white/20 hover:bg-white/10 backdrop-blur-md" aria-expanded="false" aria-haspopup="true">
              <span class="tracking-wide uppercase font-bold text-accent"><?= strtoupper(e($current_lang)) ?></span>
              <svg class="h-3.5 w-3.5 text-white/60 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div id="lang-dropdown" class="hidden absolute right-0 top-full mt-2 w-32 origin-top-right rounded-2xl border border-white/10 bg-ink-900/95 p-1.5 shadow-[0_10px_30px_rgba(0,0,0,0.8)] backdrop-blur-2xl z-50">
              <a href="<?= lang_url('it') ?>" class="flex items-center justify-between rounded-xl px-2.5 py-1.5 text-xs transition <?= $current_lang === 'it' ? 'bg-accent/15 text-accent font-bold' : 'text-neutral-300 hover:bg-white/5 hover:text-white' ?>">
                <span>Italiano</span>
                <span class="font-mono text-[10px] text-neutral-400">IT</span>
              </a>
              <a href="<?= lang_url('en') ?>" class="flex items-center justify-between rounded-xl px-2.5 py-1.5 text-xs transition <?= $current_lang === 'en' ? 'bg-accent/15 text-accent font-bold' : 'text-neutral-300 hover:bg-white/5 hover:text-white' ?>">
                <span>English</span>
                <span class="font-mono text-[10px] text-neutral-400">EN</span>
              </a>
              <a href="<?= lang_url('tr') ?>" class="flex items-center justify-between rounded-xl px-2.5 py-1.5 text-xs transition <?= $current_lang === 'tr' ? 'bg-accent/15 text-accent font-bold' : 'text-neutral-300 hover:bg-white/5 hover:text-white' ?>">
                <span>Türkçe</span>
                <span class="font-mono text-[10px] text-neutral-400">TR</span>
              </a>
              <a href="<?= lang_url('es') ?>" class="flex items-center justify-between rounded-xl px-2.5 py-1.5 text-xs transition <?= $current_lang === 'es' ? 'bg-accent/15 text-accent font-bold' : 'text-neutral-300 hover:bg-white/5 hover:text-white' ?>">
                <span>Español</span>
                <span class="font-mono text-[10px] text-neutral-400">ES</span>
              </a>
            </div>
          </div>

          <!-- CTA Demo Button -->
          <a class="hidden rounded-full bg-accent px-4 py-1.5 text-xs font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] duration-200 hover:bg-accent-neon hover:shadow-[0_0_30px_rgba(253,230,138,0.4)] md:inline-flex" href="<?= base_url('contact.php') ?>">
            <?= e(__('nav.bookDemo')) ?>
          </a>

          <!-- Mobile Hamburger Toggle -->
          <button type="button" id="mobile-menu-btn" class="flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/70 transition hover:bg-white/10 hover:text-white md:hidden" aria-label="Toggle menu">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Dropdown Menu -->
      <div id="mobile-menu" class="hidden pointer-events-auto mx-auto mt-2 overflow-hidden rounded-2xl border border-white/[0.08] bg-ink-900/95 backdrop-blur-2xl transition-[max-height,opacity] duration-300 md:hidden">
        <nav class="flex flex-col gap-1 p-3">
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'index' ? 'bg-accent/10 text-accent font-semibold' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url() ?>"><?= e(__('nav.home')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'how-it-works' ? 'bg-accent/10 text-accent font-semibold' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('how-it-works.php') ?>"><?= e(__('nav.howItWorks')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'for-facilities' ? 'bg-accent/10 text-accent font-semibold' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('for-facilities.php') ?>"><?= e(__('nav.forFacilities')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'for-players' ? 'bg-accent/10 text-accent font-semibold' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('for-players.php') ?>"><?= e(__('nav.forPlayers')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'about' ? 'bg-accent/10 text-accent font-semibold' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('about.php') ?>"><?= e(__('nav.about')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'contact' ? 'bg-accent/10 text-accent font-semibold' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('contact.php') ?>"><?= e(__('nav.contact')) ?></a>

          <a class="mt-2 rounded-full border border-accent/40 px-5 py-2.5 text-center text-sm font-semibold text-accent transition hover:border-accent hover:bg-accent/10" href="<?= base_url('find.php') ?>">
            <?= e(__('nav.find')) ?>
          </a>
          <a class="rounded-full bg-accent px-5 py-2.5 text-center text-sm font-semibold text-ink-900 shadow-glow transition hover:bg-accent-neon" href="<?= base_url('contact.php') ?>">
            <?= e(__('nav.bookDemo')) ?>
          </a>

          <!-- Language Selector in Mobile Menu -->
          <div class="mt-2 flex items-center justify-around border-t border-white/10 pt-3">
            <a href="<?= lang_url('it') ?>" class="rounded-lg px-3 py-1 text-xs font-semibold <?= $current_lang === 'it' ? 'bg-accent text-ink-900 font-bold' : 'text-white/70 hover:bg-white/5' ?>">IT</a>
            <a href="<?= lang_url('en') ?>" class="rounded-lg px-3 py-1 text-xs font-semibold <?= $current_lang === 'en' ? 'bg-accent text-ink-900 font-bold' : 'text-white/70 hover:bg-white/5' ?>">EN</a>
            <a href="<?= lang_url('tr') ?>" class="rounded-lg px-3 py-1 text-xs font-semibold <?= $current_lang === 'tr' ? 'bg-accent text-ink-900 font-bold' : 'text-white/70 hover:bg-white/5' ?>">TR</a>
            <a href="<?= lang_url('es') ?>" class="rounded-lg px-3 py-1 text-xs font-semibold <?= $current_lang === 'es' ? 'bg-accent text-ink-900 font-bold' : 'text-white/70 hover:bg-white/5' ?>">ES</a>
          </div>
        </nav>
      </div>

    </div>
  </header>
