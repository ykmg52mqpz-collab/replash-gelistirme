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
  
<?php
  // Arama ve paylaşım: her dilin kendi adresi (?lang=xx), paylaşım önizlemesi (Open Graph)
  $seoLangs = ['it' => 'it_IT', 'en' => 'en_US', 'tr' => 'tr_TR', 'es' => 'es_ES'];
  $seoPath = ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
  $appPath = trim((string)parse_url(APP_URL, PHP_URL_PATH), '/');
  if ($appPath !== '' && str_starts_with($seoPath, $appPath)) $seoPath = ltrim(substr($seoPath, strlen($appPath)), '/');
  if ($seoPath === 'index.php') $seoPath = '';
  $seoUrl = function ($lang = null) use ($seoPath) { return base_url($seoPath) . ($lang ? '?lang=' . $lang : ''); };
  // Kodlu maç sayfaları, paneller ve yönetim arama motorlarına kapalı
  $seoNoindex = !empty($internalShell) || (($currentPage === 'find') && !empty($_GET['code']));
  $seoTitle = $metaTitle ?? (isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME . ' | ' . __('home.hero.title'));
  $seoDesc = $metaDescription ?? __('home.hero.subtitle');
?>
<?php if ($seoNoindex): ?>
  <meta name="robots" content="noindex, nofollow">
<?php else: ?>
  <link rel="canonical" href="<?= e($seoUrl($current_lang)) ?>">
<?php foreach ($seoLangs as $sl => $loc): ?>
  <link rel="alternate" hreflang="<?= $sl ?>" href="<?= e($seoUrl($sl)) ?>">
<?php endforeach; ?>
  <link rel="alternate" hreflang="x-default" href="<?= e($seoUrl()) ?>">
<?php endif; ?>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Replash">
  <meta property="og:title" content="<?= e($seoTitle) ?>">
  <meta property="og:description" content="<?= e($seoDesc) ?>">
  <meta property="og:url" content="<?= e($seoUrl($seoNoindex ? null : $current_lang)) ?>">
  <meta property="og:image" content="<?= asset('images/og-image.png') ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale" content="<?= $seoLangs[$current_lang] ?? 'it_IT' ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="theme-color" content="#0a0a0a">

  <!-- Preloads -->
  <link rel="preload" as="image" href="<?= asset('images/replash-logo.webp') ?>">
<?php if ($currentPage === 'index'): ?>
  <link rel="preload" as="image" href="<?= asset('images/gen_hero_friends.webp') ?>">
<?php endif; ?>

  <!-- Marka yazı tipi: Neue Haas Grotesk Display (Adobe Fonts kit qqj8ayc) -->
  <link rel="preconnect" href="https://use.typekit.net" crossorigin>
  <link rel="stylesheet" href="https://use.typekit.net/qqj8ayc.css">

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
          },
          fontFamily: {
            display: ["neue-haas-grotesk-display", "system-ui", "sans-serif"],
            body: ["neue-haas-grotesk-display", "system-ui", "sans-serif"],
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= @filemtime(REPLASH_ROOT . '/assets/css/style.css') ?>">
</head>
<body class="font-body bg-ink-900 text-white antialiased min-h-screen flex flex-col selection:bg-accent selection:text-ink-900<?= !empty($internalShell) ? ' theme-desk' : '' ?>">

  <?php if (!empty($internalShell)): ?>
  <!-- İç araç üst çubuğu (replash.info InternalShell) -->
  <header class="sticky top-0 z-40 border-b border-gray-200 bg-white">
    <div class="container-x flex h-16 items-center gap-3">
      <a href="<?= base_url() ?>" class="flex shrink-0 items-center gap-2.5 font-display text-lg font-bold">
        <img src="<?= asset('images/icon-512.png') ?>" alt="Replash" width="28" height="28" class="rounded">
        Replash<span class="text-accent">.</span>
      </a>
      <span class="hidden whitespace-nowrap rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-600 sm:inline-block"><?= e($internalShell) ?></span>
      <!-- Dil seçimi: açık temada görünür renkler (beyaz üstüne beyazdı) -->
      <div role="group" aria-label="<?= e(__('common.language', 'Language')) ?>" class="ml-auto inline-flex shrink-0 items-center rounded-full border border-gray-300 bg-white p-0.5 text-xs font-semibold">
        <?php
          // Yönetim paneli yalnız Türkçe/İngilizce; diğer iç ekranlar dört dil.
          $shellLangs = !empty($adminShell) ? ['tr', 'en'] : ['it', 'en', 'tr', 'es'];
          foreach (array_values(array_intersect($shellLangs, $SUPPORTED_LANGUAGES)) as $lng): $isCur = ($current_lang === $lng); ?>
          <a href="<?= e(lang_url($lng)) ?>" hreflang="<?= e($lng) ?>" aria-pressed="<?= $isCur ? 'true' : 'false' ?>" class="rounded-full px-2.5 py-1.5 tracking-wide transition <?= $isCur ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>"><?= strtoupper(e($lng)) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </header>
  <?php else: ?>
  <!-- Floating Pill Header (Centered Container) -->
  <header class="pointer-events-none fixed inset-x-0 top-0 z-50 pt-3">
    <div class="container-x">
      <div class="pointer-events-auto mx-auto flex h-14 items-center justify-between gap-4 rounded-full border px-3 pl-4 pr-2 transition-[background-color,border-color,box-shadow,backdrop-filter] duration-500 md:px-4 md:pl-5 md:pr-2.5 border-white/[0.06] bg-ink-900/50 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.5)] backdrop-blur-xl">
        
        <!-- Logo -->
        <a class="flex items-center gap-2" href="<?= base_url() ?>">
          <img src="<?= asset('images/replash-logo.webp') ?>" alt="REPLASH" class="h-8 w-auto select-none md:h-9" draggable="false"/>
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

          <!-- Language Toggle (replash.info stili; 4 dil) -->
          <div role="group" aria-label="Language" class="inline-flex items-center rounded-full border border-white/10 bg-white/5 p-0.5 text-xs font-medium backdrop-blur">
            <?php foreach (array_values(array_intersect(['it', 'en', 'tr', 'es'], $SUPPORTED_LANGUAGES)) as $lng): $isCur = ($current_lang === $lng); ?>
              <a href="<?= e(lang_url($lng)) ?>" aria-pressed="<?= $isCur ? 'true' : 'false' ?>" hreflang="<?= e($lng) ?>" class="rounded-full px-2.5 py-1.5 tracking-wide transition <?= $isCur ? 'bg-white text-ink-900' : 'text-white/70 hover:text-white' ?>"><?= strtoupper(e($lng)) ?></a>
            <?php endforeach; ?>
          </div>

          <!-- CTA Demo Button -->
          <a class="hidden rounded-full bg-accent px-4 py-2 text-sm font-semibold text-ink-900 shadow-glow transition-[background-color,box-shadow,transform] active:scale-[0.98] duration-200 hover:bg-accent-neon hover:shadow-[0_0_30px_rgba(253,230,138,0.4)] md:inline-flex" href="<?= base_url('contact.php') ?>">
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
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'index' ? 'bg-accent/10 text-accent' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url() ?>"><?= e(__('nav.home')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'how-it-works' ? 'bg-accent/10 text-accent' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('how-it-works.php') ?>"><?= e(__('nav.howItWorks')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'for-facilities' ? 'bg-accent/10 text-accent' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('for-facilities.php') ?>"><?= e(__('nav.forFacilities')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'for-players' ? 'bg-accent/10 text-accent' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('for-players.php') ?>"><?= e(__('nav.forPlayers')) ?></a>
          <a class="rounded-lg px-3 py-2.5 text-sm transition <?= $currentPage === 'about' ? 'bg-accent/10 text-accent' : 'text-white/65 hover:bg-white/5 hover:text-white' ?>" href="<?= base_url('about.php') ?>"><?= e(__('nav.about')) ?></a>

          <a class="mt-2 rounded-full border border-accent/40 px-5 py-2.5 text-center text-sm font-semibold text-accent transition hover:border-accent hover:bg-accent/10" href="<?= base_url('find.php') ?>">
            <?= e(__('nav.find')) ?>
          </a>
          <a class="rounded-full bg-accent px-5 py-2.5 text-center text-sm font-semibold text-ink-900 shadow-glow transition hover:bg-accent-neon" href="<?= base_url('contact.php') ?>">
            <?= e(__('nav.bookDemo')) ?>
          </a>
        </nav>
      </div>

    </div>
  </header>
  <?php endif; ?>
