<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.forPlayers');
$metaTitle = __('meta.forPlayers.title');
$metaDescription = __('meta.forPlayers.description');

require_once __DIR__ . '/includes/header.php';
?>

<main id="main" class="relative bg-ink-900">

  <!-- Hero: metin solda; şut anı fotoğrafı sağ kenara taşar ve zemine erir -->
  <section class="relative overflow-hidden bg-ink-900 pt-32 pb-10 md:pt-40 lg:flex lg:min-h-[86svh] lg:items-center lg:pb-24">
  <div class="container-x relative z-10 w-full">
  <div class="max-w-xl">
  <?php [$slA, $slB] = array_pad(preg_split('/(?<=\?)\s+/u', __('v2.sl.goal'), 2), 2, ''); ?>
  <h1 class="font-display text-4xl font-bold leading-[1.05] tracking-tight md:text-5xl xl:text-6xl" data-reveal="up">
  <span class="block text-white"><?= e($slA) ?></span>
  <span class="block text-white"><?= e($slB) ?></span>
  </h1>
  <p class="mt-6 font-display text-xl font-bold text-white md:text-2xl" data-reveal="up"><?= e(__('forPlayers.hero.title')) ?></p>
  <p class="mt-3 max-w-xl text-lg text-white/75 md:text-xl" data-reveal="up">
  <?= e(__('forPlayers.hero.subtitle')) ?>
  </p>
  </div>
  </div>
  <div class="relative mt-6 lg:absolute lg:inset-y-0 lg:right-0 lg:mt-0 lg:w-[64%]" aria-hidden="true">
  <img data-scroll-img="hero" decoding="async" fetchpriority="high" <?= scene_img_attrs('foto-sut', [1200, 2000], '(min-width: 1024px) 64vw, 100vw') ?> width="2000" height="1333" alt="" class="photo-melt-r block aspect-[3/2] w-full object-cover lg:aspect-auto lg:h-full" style="object-position:40% 50%">
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" aria-hidden="true">
  </div>
  <div class="container-x relative">
  <div class="max-w-5xl">
  <div class="max-w-3xl"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('forPlayers.clipping.title')) ?>
  </h2>
  </div>
  <p class="mt-5 max-w-3xl text-base leading-relaxed text-white/75"  data-reveal="up">
  <?= e(__('forPlayers.clipping.desc')) ?>
  </p>
  </div>
  <div class="mt-14"  data-reveal="up">
  <!-- Tek oynatıcı: içindeki kayıt 4 saniyede bir yumuşak geçişle değişir (futbol → padel → tenis) -->
  <?php $pSlides = [
      ['ai-cam-180', 1344, __('forPlayers.mock.meta'), '00:17 / 02:08', 34, 30, '00:17 &rarr; 00:32 &middot; 15s'],
      ['cam-padel',  1344, 'Padel 2 · 19:30–21:00', '00:48 / 01:30', 54, 50, '00:48 &rarr; 01:03 &middot; 15s'],
      ['cam-tennis', 1344, 'Tennis 3 · 18:00–19:30', '01:12 / 01:30', 80, 76, '01:12 &rarr; 01:27 &middot; 15s'],
  ]; ?>
  <div class="max-w-4xl space-y-3" id="fp-player" aria-hidden="true">
  <div class="relative aspect-video w-full overflow-hidden rounded-2xl border border-white/10 bg-black">
  <?php foreach ($pSlides as $si => [$pimg, $pbig]): ?>
  <img <?= $si ? 'loading="lazy" ' : '' ?>decoding="async" src="<?= asset("images/$pimg-$pbig.webp") ?>" srcset="<?= asset("images/$pimg-800.webp") ?> 800w, <?= asset("images/$pimg-$pbig.webp") ?> <?= $pbig ?>w" sizes="(min-width: 1024px) 896px, 100vw" alt="" class="fp-slide absolute inset-0 h-full w-full object-cover transition-opacity duration-700 ease-out" style="opacity:<?= $si ? 0 : 1 ?>">
  <?php endforeach; ?>
  <div class="absolute inset-0 flex items-center justify-center"><div class="flex h-16 w-16 items-center justify-center rounded-full bg-black/45"><svg class="ml-1 h-7 w-7 fill-white/90" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div></div>
  <!-- Durum bilgisi videonun içinde, üstte ince bir şerit -->
  <div class="absolute inset-x-0 top-0 flex items-center gap-2.5 bg-gradient-to-b from-black/60 to-transparent px-3.5 pb-5 pt-2.5 text-[11px]">
  <span class="inline-flex items-center gap-1.5 font-semibold uppercase tracking-wider text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span><?= e(__('forPlayers.mock.unlocked')) ?></span>
  <span class="fp-meta font-mono text-white/80"><?= e($pSlides[0][2]) ?></span>
  </div>
  <!-- Hangi kayıt: üç küçük çizgi -->
  <div class="absolute bottom-3.5 left-3.5 flex gap-1.5">
  <?php foreach ($pSlides as $si => $_): ?><span class="fp-dot h-1 w-5 rounded-full transition-colors duration-500 <?= $si ? 'bg-white/30' : 'bg-white' ?>"></span><?php endforeach; ?>
  </div>
  <div class="absolute bottom-3 right-3 font-mono text-[11px] tracking-wider text-white/90" style="text-shadow:0 1px 3px rgba(0,0,0,.6)">replash.it</div>
  </div>
  <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
  <div class="flex items-center gap-3">
  <span class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-accent text-ink-900"><svg class="ml-0.5 h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
  <div class="relative h-2 flex-1 rounded-full bg-white/15"><div class="fp-prog absolute inset-y-0 left-0 rounded-full bg-white/45 transition-[width] duration-700 ease-out" style="width:<?= $pSlides[0][4] ?>%"></div><div class="fp-clip absolute -inset-y-1.5 w-[12%] rounded-md border-2 border-accent bg-accent/25 transition-[left] duration-700 ease-out" style="left:<?= $pSlides[0][5] ?>%"></div></div>
  <span class="fp-time font-mono text-[11px] text-white/70"><?= $pSlides[0][3] ?></span>
  </div>
  <div class="mt-3 flex items-center justify-between gap-3 border-t border-white/[0.08] pt-3">
  <span class="inline-flex items-center gap-2 rounded-xl bg-accent px-4 py-2.5 text-sm font-semibold text-ink-900"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4L8.1 15.9M14.5 14.5L20 20M8.1 8.1L12 12"/></svg><?= e(__('find.watch.clip.label')) ?></span>
  <span class="fp-range font-mono text-sm text-white/60"><?= $pSlides[0][6] ?></span>
  </div>
  </div>
  </div>
  <script>
  (function () {
    var root = document.getElementById('fp-player'); if (!root) return;
    var data = <?= json_encode(array_map(function ($sl) { return ['meta' => $sl[2], 'time' => $sl[3], 'prog' => $sl[4], 'clip' => $sl[5], 'range' => html_entity_decode($sl[6], ENT_QUOTES, 'UTF-8')]; }, $pSlides), JSON_UNESCAPED_UNICODE) ?>;
    var slides = root.querySelectorAll('.fp-slide'), dots = root.querySelectorAll('.fp-dot');
    var q = function (c) { return root.querySelector(c); };
    var i = 0, visible = true;
    if ('IntersectionObserver' in window) new IntersectionObserver(function (en) { visible = en[0].isIntersecting; }).observe(root);
    setInterval(function () {
      if (!visible || document.hidden) return;
      i = (i + 1) % slides.length;
      slides.forEach(function (s, k) { s.style.opacity = k === i ? 1 : 0; });
      dots.forEach(function (d, k) { d.classList.toggle('bg-white', k === i); d.classList.toggle('bg-white/30', k !== i); });
      var d = data[i];
      q('.fp-meta').textContent = d.meta; q('.fp-time').textContent = d.time; q('.fp-range').textContent = d.range;
      q('.fp-prog').style.width = d.prog + '%'; q('.fp-clip').style.left = d.clip + '%';
    }, 4000);
  })();
  </script>
  </div>
  <div class="mt-12 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
  <div class="flex flex-col gap-2"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex items-center text-accent">
  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
  <path d="M8 5v14l11-7z">
  </path>
  </svg>
  </span>
  <span class="rp-label text-white/60">01</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.1.label')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('forPlayers.clipping.features.1.desc')) ?>
  </p>
  </div>
  <div class="flex flex-col gap-2"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex items-center text-accent">
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
  <span class="rp-label text-white/60">02</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.2.label')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('forPlayers.clipping.features.2.desc')) ?>
  </p>
  </div>
  <div class="flex flex-col gap-2"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex items-center text-accent">
  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4">
  </path>
  <polyline points="7 10 12 15 17 10">
  </polyline>
  <line x1="12" y1="15" x2="12" y2="3">
  </line>
  </svg>
  </span>
  <span class="rp-label text-white/60">03</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.3.label')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('forPlayers.clipping.features.3.desc')) ?>
  </p>
  </div>
  <div class="flex flex-col gap-2"  data-reveal="up">
  <div class="flex items-center gap-2">
  <span class="flex items-center text-accent">
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
  <span class="rp-label text-white/60">04</span>
  </div>
  <h3 class="text-sm font-semibold text-white">
  <?= e(__('forPlayers.clipping.features.4.label')) ?>
  </h3>
  <p class="text-sm leading-relaxed text-white/75">
  <?= e(__('forPlayers.clipping.features.4.desc')) ?>
  </p>
  </div>
  </div>
  </div>
  </section>






  <!-- Slogan bandı (oyuncular) -->
  <section class="relative overflow-hidden border-t border-white/5 bg-ink-900 lg:flex lg:min-h-[760px] lg:items-center">
  <div class="relative lg:absolute lg:inset-y-0 lg:left-0 lg:w-[50%]" aria-hidden="true">
  <img data-scroll-img="band" loading="lazy" decoding="async" <?= scene_img_attrs('foto-mola', [800, 1200], '(min-width: 1024px) 50vw, 100vw') ?> width="1200" height="1600" alt="" class="photo-melt-l mx-auto block aspect-[3/4] w-full max-w-lg object-cover lg:aspect-auto lg:h-full lg:max-w-none" style="object-position:center 62%">
  </div>
  <div class="container-x relative z-10 w-full pb-20 lg:py-28" data-reveal="up">
  <div class="lg:ml-auto lg:w-1/2 lg:pl-8 lg:text-right">
  <h2 class="font-display text-4xl font-bold leading-[1.02] tracking-tight text-balance sm:text-5xl lg:text-6xl xl:text-7xl">
  <span class="block text-white"><?= e(__('v2.sl.nb1')) ?></span>
  <span class="block text-white"><?= e(__('v2.sl.nb2')) ?></span>
  </h2>
  <p class="mt-6 max-w-xl text-lg text-white/80 md:text-xl lg:ml-auto"><?= e(__('v2.sl.nbSub')) ?></p>
  <a class="mt-8 inline-flex items-center justify-center rounded-full bg-accent px-7 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('find') ?>"><?= e(__('v2.sl.nbCta')) ?></a>
  </div>
  </div>
  </section>


  <!-- Domande pratiche dei giocatori -->
  <section class="section-y border-t border-white/5 bg-ink-900" id="domande">
  <div class="container-x">
  <!-- SSS bölümü sayfada ortalı; başlığa tıklayınca sorular açılır (+ simgesi açıkken × olur) -->
  <details class="group/faq mx-auto max-w-3xl">
  <summary class="flex cursor-pointer list-none items-center justify-center gap-4 text-center [&::-webkit-details-marker]:hidden md:gap-5">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('forPlayers.faq.title')) ?></h2>
  <span class="flex h-11 w-11 flex-none items-center justify-center rounded-full border border-white/20 text-white/70 transition-[transform,border-color,color] duration-200 group-open/faq:rotate-45 group-hover/faq:border-accent group-hover/faq:text-accent" aria-hidden="true">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
  </span>
  </summary>
  <div class="mt-10 divide-y divide-white/[0.08] border-y border-white/[0.08]">
  <?php foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $qi): ?>
  <details class="group py-1">
  <summary class="flex min-h-[56px] cursor-pointer items-center justify-between gap-4 py-3 text-left text-base font-semibold text-white"><?= e(__("forPlayers.faq.items.$qi.q")) ?>
  <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full border border-white/20 text-white/60 transition-transform group-open:rotate-45" aria-hidden="true">+</span></summary>
  <p class="pb-4 pr-10 text-sm leading-relaxed text-white/80 md:text-base"><?= e(__("forPlayers.faq.items.$qi.a")) ?></p>
  </details>
  <?php endforeach; ?>
  </div>
  </details>
  <div class="mx-auto mt-10 max-w-3xl text-center">
  <h3 class="font-display text-lg font-bold text-white"><?= e(__('forPlayers.help.title')) ?></h3>
  <p class="mt-1.5 text-sm text-white/80"><?= e(__('forPlayers.help.desc')) ?></p>
  <div class="mt-4 flex flex-col items-center justify-center gap-3 sm:flex-row">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-semibold text-ink-900 transition hover:bg-accent-neon" href="<?= base_url('find') ?>"><?= e(__('find.watch.button')) ?></a>
  <a class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white/85 transition hover:border-white/50 hover:text-white" href="https://wa.me/393447598309?text=<?= urlencode(__('support.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer"><?= e(__('forPlayers.help.button')) ?></a>
  </div>
  </div>
  </div>
  </section>

  <!-- CTA: padel oyuncusu arka planda, kenarlardan zemine erir -->
  <section class="relative flex min-h-[70vh] items-center overflow-hidden border-t border-white/5 bg-ink-900 py-24 md:min-h-[80vh] md:py-32">
  <div class="pointer-events-none absolute inset-0" aria-hidden="true">
  <img data-scroll-img="band" loading="lazy" decoding="async" <?= scene_img_attrs('foto-padel-oyuncu', [800, 1440]) ?> alt="" class="h-full w-full object-cover" style="opacity:0.85;object-position:60% 40%">
  <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/45 to-ink-900/80"></div>
  <div class="absolute inset-0" style="background:radial-gradient(ellipse at 20% 50%,rgba(10,10,10,0.6),transparent 60%)"></div>
  </div>
  <div class="container-x relative w-full">
  <div  data-reveal="up">
  <h2 class="max-w-xl font-display text-3xl font-bold tracking-tight text-balance md:text-4xl">
  <?= e(__('forPlayers.cta.title')) ?>
  </h2>
  <p class="mt-4 max-w-md text-sm text-white/75">
  <?= e(__('forPlayers.cta.subtitle')) ?>
  </p>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('for-facilities') ?>">
  <?= e(__('forPlayers.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
