<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.ai');
$metaTitle = __('meta.ai.title');
$metaDescription = __('meta.ai.description');

require_once __DIR__ . '/includes/header.php';
?>

<main id="main" class="relative bg-ink-900">

  <!-- HERO: analiz efektleri doğrudan saha görüntüsünün üstünde.
       SVG, görüntüyle aynı koordinatlarda (1344×752) ve "slice" ile object-cover gibi kırpılır; böylece kutular ve ısı haritası oyunculara oturur. -->
  <?php
    // Takip kutuları: [merkez x, üst y, alt y] — ai-cam-180 görüntüsündeki oyuncular
    $aiTracks = [[633, 238, 306], [712, 246, 314], [468, 268, 346], [864, 288, 372], [1080, 268, 334], [95, 256, 304], [406, 206, 252], [940, 206, 258]];
    $aiP7 = [768, 386, 494];   // vurgulanan oyuncu (#7)
  ?>
  <section class="ai-hero relative flex min-h-[92svh] flex-col overflow-hidden bg-ink-900 pt-32 md:pt-36">
  <div class="absolute inset-0" aria-hidden="true">
  <svg data-scroll-img="hero" class="h-full w-full" viewBox="0 0 1344 752" preserveAspectRatio="xMidYMid slice">
  <defs>
  <radialGradient id="aiHeat"><stop offset="0" stop-color="#FEF1B6" stop-opacity="0.9"/><stop offset="0.55" stop-color="#F5C04A" stop-opacity="0.38"/><stop offset="1" stop-color="#F5C04A" stop-opacity="0"/></radialGradient>
  <radialGradient id="aiHeatSoft"><stop offset="0" stop-color="#FEF1B6" stop-opacity="0.45"/><stop offset="1" stop-color="#FEF1B6" stop-opacity="0"/></radialGradient>
  </defs>
  <image href="<?= asset('images/ai-cam-180-1344.webp') ?>" x="0" y="0" width="1344" height="752" preserveAspectRatio="xMidYMid slice" opacity="0.9"/>
  <!-- Isı haritası: #7'nin en çok bulunduğu bölgeler (zemine yatık elipsler) -->
  <g class="ai-heat" style="mix-blend-mode:screen">
  <ellipse cx="800" cy="470" rx="150" ry="62" fill="url(#aiHeat)"/>
  <ellipse cx="900" cy="410" rx="120" ry="48" fill="url(#aiHeat)"/>
  <ellipse cx="690" cy="520" rx="130" ry="50" fill="url(#aiHeatSoft)"/>
  <ellipse cx="1000" cy="470" rx="110" ry="44" fill="url(#aiHeatSoft)"/>
  <ellipse cx="760" cy="360" rx="100" ry="38" fill="url(#aiHeatSoft)"/>
  </g>
  <!-- #7'nin koşu izi -->
  <path class="ai-trail" d="M1010 560 C 960 540, 930 500, 905 470 S 840 470, 800 488 S 740 500, 772 494" fill="none" stroke="#FEF1B6" stroke-width="2.5" stroke-dasharray="2 9" stroke-linecap="round" opacity="0.85"/>
  <!-- Diğer oyuncular: ince köşe çizgili takip kutuları -->
  <g fill="none" stroke="#fff" stroke-opacity="0.55" stroke-width="1.6">
  <?php foreach ($aiTracks as [$cx, $top, $bot]): $h = $bot - $top; $w = round($h * 0.55); $x = $cx - $w / 2; $c = round($h * 0.22); ?>
  <path d="M<?= $x ?> <?= $top + $c ?>V<?= $top ?>H<?= $x + $c ?>M<?= $x + $w - $c ?> <?= $top ?>H<?= $x + $w ?>V<?= $top + $c ?>M<?= $x + $w ?> <?= $bot - $c ?>V<?= $bot ?>H<?= $x + $w - $c ?>M<?= $x + $c ?> <?= $bot ?>H<?= $x ?>V<?= $bot - $c ?>"/>
  <?php endforeach; ?>
  </g>
  <!-- #7: vurgulu kutu ve etiket -->
  <?php [$cx, $top, $bot] = $aiP7; $h = $bot - $top; $w = round($h * 0.6); $x = $cx - $w / 2; ?>
  <g class="ai-p7">
  <rect x="<?= $x ?>" y="<?= $top ?>" width="<?= $w ?>" height="<?= $h ?>" rx="4" fill="#FEF1B6" fill-opacity="0.06" stroke="#FEF1B6" stroke-width="2"/>
  <line x1="<?= $x + $w ?>" y1="<?= $top + 6 ?>" x2="<?= $x + $w + 26 ?>" y2="<?= $top - 18 ?>" stroke="#FEF1B6" stroke-width="1.5"/>
  <rect x="<?= $x + $w + 26 ?>" y="<?= $top - 44 ?>" width="150" height="40" rx="8" fill="#0A0A0A" fill-opacity="0.85" stroke="#FEF1B6" stroke-opacity="0.5"/>
  <text x="<?= $x + $w + 40 ?>" y="<?= $top - 19 ?>" fill="#FEF1B6" font-family="ui-monospace, SFMono-Regular, Menlo, monospace" font-size="17" font-weight="700">#7</text>
  <text x="<?= $x + $w + 78 ?>" y="<?= $top - 19 ?>" fill="#fff" font-family="ui-monospace, SFMono-Regular, Menlo, monospace" font-size="17"><?= e(__('v2.ai.mock.distV')) ?></text>
  </g>
  </svg>
  <!-- Okunabilirlik: yazı solda, zaman çizelgesi altta -->
  <div class="absolute inset-0 bg-gradient-to-r from-ink-900/95 via-ink-900/55 to-transparent"></div>
  <div class="absolute inset-0 bg-gradient-to-b from-ink-900/50 via-transparent to-ink-900"></div>
  </div>

  <div class="container-x relative flex-1">
  <div class="max-w-xl">
  <p class="rp-label text-accent"><?= e(__('v2.ai.eyebrow')) ?></p>
  <h1 class="mt-5 font-display text-4xl font-bold leading-[1.02] tracking-tight text-balance sm:text-5xl lg:text-6xl"><?= e(__('v2.ai.title')) ?></h1>
  <p class="mt-6 text-lg leading-relaxed text-white/80 md:text-xl"><?= e(__('v2.ai.sub')) ?></p>
  <div class="mt-8 flex flex-col gap-3 sm:flex-row">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-7 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('find') ?>"><?= e(__('v2.ai.ctaPlayer')) ?></a>
  <a class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/[0.04] px-7 py-3.5 text-base font-medium text-white/85 transition-colors hover:border-white/40 hover:text-white" href="#centri"><?= e(__('v2.ai.ctaVenue')) ?></a>
  </div>
  </div>
  </div>

  <!-- Zaman çizelgesi: işaretli anlar görüntünün üstünde -->
  <div class="container-x relative mt-16 pb-10 md:pb-14" aria-hidden="true">
  <div class="flex items-center justify-between gap-3 text-xs">
  <span class="font-semibold uppercase tracking-wider text-white/70"><?= e(__('v2.ai.mock.reel')) ?></span>
  <span class="font-mono text-white/60"><?= e(__('v2.ai.mock.reelD')) ?></span>
  </div>
  <div class="relative mt-10 h-1.5 rounded-full bg-white/15">
  <div class="ai-progress absolute inset-y-0 left-0 rounded-full bg-accent/50"></div>
  <?php foreach ([[18, 'goal', '12:04'], [52, 'save', '27:31'], [81, 'assist', '41:18']] as [$pos, $hk, $t]): ?>
  <div class="absolute top-1/2" style="left:<?= $pos ?>%">
  <span class="absolute bottom-4 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md border border-accent/40 bg-ink-900/85 px-2 py-1 font-mono text-[11px] text-white"><span class="text-accent"><?= e(__("v2.ai.hl.$hk")) ?></span> <?= $t ?></span>
  <span class="absolute h-3.5 w-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-ink-900 bg-accent"></span>
  </div>
  <?php endforeach; ?>
  </div>
  </div>
  </section>

  <!-- ÖZELLİKLER -->
  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <h2 class="max-w-2xl font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl" data-reveal="up"><?= e(__('v2.ai.feat.title')) ?></h2>
  <ul class="mt-10 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3" data-reveal="up">
  <?php foreach (['1', '2', '3', '4', '5', '6'] as $fi): ?>
  <li>
  <p class="rp-label text-accent"><?= sprintf('%02d', (int)$fi) ?></p>
  <h3 class="mt-3 font-display text-xl font-bold text-white"><?= e(__("v2.ai.feat.$fi.t")) ?></h3>
  <p class="mt-2 text-[15.5px] leading-relaxed text-white/80"><?= e(__("v2.ai.feat.$fi.d")) ?></p>
  </li>
  <?php endforeach; ?>
  </ul>
  </div>
  </section>

  <!-- NASIL ÇALIŞACAK -->
  <section class="section-y border-t border-white/5 bg-ink-900">
  <div class="container-x">
  <h2 class="max-w-2xl font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl" data-reveal="up"><?= e(__('v2.ai.steps.title')) ?></h2>
  <ol class="mt-10 grid gap-5 md:grid-cols-3" data-reveal="up">
  <?php foreach (['1', '2', '3'] as $si): ?>
  <li>
  <p class="font-mono text-4xl font-bold text-accent"><?= e($si) ?></p>
  <h3 class="mt-4 font-display text-xl font-bold text-white"><?= e(__("v2.ai.steps.$si.t")) ?></h3>
  <p class="mt-2 text-base leading-relaxed text-white/80"><?= e(__("v2.ai.steps.$si.d")) ?></p>
  </li>
  <?php endforeach; ?>
  </ol>
  </div>
  </section>

  <!-- TESİSLER İÇİN -->
  <section id="centri" class="relative scroll-mt-24 overflow-hidden border-t border-white/5 bg-ink-900 lg:flex lg:min-h-[720px] lg:items-center">
  <div class="relative lg:absolute lg:inset-y-0 lg:left-0 lg:w-[52%]" aria-hidden="true">
  <img data-scroll-img="band" loading="lazy" decoding="async" <?= scene_img_attrs('foto-isik', [800, 1440], '(min-width: 1024px) 52vw, 100vw') ?> width="1440" height="1440" alt="" class="photo-melt-l block aspect-square w-full object-cover lg:aspect-auto lg:h-full" style="object-position:center 60%">
  </div>
  <div class="container-x relative z-10 w-full pb-20 lg:py-28">
  <div class="lg:ml-auto lg:w-1/2 lg:pl-8" data-reveal="up">
  <p class="rp-label text-accent"><?= e(__('v2.ai.venue.eyebrow')) ?></p>
  <h2 class="mt-4 font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('v2.ai.venue.title')) ?></h2>
  <p class="mt-6 text-lg leading-relaxed text-white/80"><?= e(__('v2.ai.venue.d')) ?></p>
  <a class="mt-8 inline-flex items-center justify-center rounded-full bg-accent px-7 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>"><?= e(__('v2.ai.venue.cta')) ?></a>
  </div>
  </div>
  </section>

  <!-- SSS -->
  <section class="section-y border-t border-white/5 bg-ink-900">
  <div class="container-x">
  <h2 class="max-w-3xl font-display text-3xl font-bold leading-tight tracking-tight md:text-4xl" data-reveal="up"><?= e(__('v2.ai.faq.title')) ?></h2>
  <dl class="mt-10 max-w-3xl divide-y divide-white/10 border-y border-white/10">
  <?php foreach (['1', '2', '4'] as $qi): ?>
  <div class="py-5">
  <dt class="text-base font-semibold text-white"><?= e(__("v2.ai.faq.$qi.q")) ?></dt>
  <dd class="mt-2 text-[15.5px] leading-relaxed text-white/80"><?= e(__("v2.ai.faq.$qi.a")) ?></dd>
  </div>
  <?php endforeach; ?>
  </dl>
  </div>
  </section>

  <!-- CTA -->
  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <h2 class="max-w-2xl font-display text-3xl font-bold tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('v2.ai.cta.title')) ?></h2>
  <p class="mt-4 max-w-xl text-lg text-white/80"><?= e(__('v2.ai.cta.d')) ?></p>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>"><?= e(__('v2.ai.cta.button')) ?></a>
  </div>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
