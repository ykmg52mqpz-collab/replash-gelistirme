<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('forFacilities.hero.eyebrow') ?: __('nav.forFacilities');
$metaTitle = __('meta.forFacilities.title');
$metaDescription = __('meta.forFacilities.description');

/*
 * Hesaplayıcı: fiyatlar ve kademeler includes/billing.php'den (tek kaynak), varsayılan saha/satış/gün aralıkları
 * yönetimdeki hesaplayıcı yapılandırmasından (get_roi_calculator_config) gelir.
 * Onaylı fiyatı olmayan spor (tenis) için başka sporun fiyatı KULLANILMAZ: fiyat null kalır, gelir hesaplanmaz.
 */
$roiConfig = get_roi_calculator_config();
$calcSports = [];
foreach (['padel', 'football', 'tennis'] as $key) {
    $sp = $roiConfig['sports'][$key] ?? null;
    if (!$sp || empty($sp['active'])) continue;
    $price = $key === 'tennis'
        ? (REPLASH_TENNIS_PRICE_APPROVED ? (float)REPLASH_VIDEO_PRICES['tennis'] : null)
        : (float)REPLASH_VIDEO_PRICES[$key];
    $calcSports[$key] = [
        'label' => __('forFacilities.calc.sports.' . $key),
        'price' => $price,
        'courts' => [(int)($sp['courts_min'] ?? 1), (int)($sp['courts_max'] ?? 12), (int)($sp['courts_default'] ?? 4)],
        'sold' => [0, (int)($sp['videos_max'] ?? 15), 1],
        'days' => [(int)($sp['days_min'] ?? 20), (int)($sp['days_max'] ?? 30), (int)($sp['days_default'] ?? 26)],
    ];
    if ($key === 'football') {
        $calcSports['football11'] = $calcSports['football'];
        $calcSports['football11']['label'] = __('forFacilities.calc.sports.football11');
        $calcSports['football11']['price'] = (float)REPLASH_FOOTBALL_11_PRICE;
    }
}
if (!$calcSports) {
    $calcSports['padel'] = ['label' => __('forFacilities.calc.sports.padel'), 'price' => (float)REPLASH_VIDEO_PRICES['padel'], 'courts' => [1, 12, 4], 'sold' => [0, 15, 4], 'days' => [20, 30, 26]];
}
foreach ($calcSports as $k => $v) { $calcSports[$k]['sold'][0] = 0; $calcSports[$k]['courts'][0] = 0; }   // sıfır satış ve sıfır saha senaryosu desteklenir
$calcLocale = ['tr' => 'tr-TR', 'en' => 'en-GB', 'it' => 'it-IT', 'es' => 'es-ES'][$current_lang] ?? 'it-IT';
$calcText = [];
foreach (['sport', 'courts', 'sold', 'days', 'price', 'monthlySales', 'gross', 'add', 'remove', 'included', 'totalTitle', 'totalSales', 'totalGross', 'tier', 'tierWhy', 'afterMonth', 'venueShare', 'perMonth', 'zero', 'noPrice'] as $k) {
    $calcText[$k] = __('forFacilities.calc.' . $k);
}

require_once __DIR__ . '/includes/header.php';
?>

<main id="main" class="relative bg-ink-900">

  <section class="relative overflow-hidden bg-ink-900 pt-28 pb-8 md:pt-32 md:pb-10">
  <div class="absolute inset-0" aria-hidden="true">
  <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
  <div class="absolute inset-0">
  <img data-scroll-img="hero" loading="lazy" decoding="async" <?= scene_img_attrs('sahne-reception', [1200, 1920]) ?> alt="" class="h-full w-full object-cover" style="opacity:0.55;object-position:center 35%"/>
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
  <h1 class="max-w-3xl font-display text-4xl font-bold leading-[1.1] tracking-tight text-balance md:text-5xl lg:text-6xl" data-reveal="up">
  <?= e(__('forFacilities.hero.title')) ?>
  </h1>
  <p class="mt-6 max-w-2xl text-lg text-white/75 md:text-xl"  data-reveal="up">
  <?= e(__('forFacilities.hero.subtitle')) ?>
  </p>
  <div class="mt-8 flex flex-col items-start gap-3 sm:flex-row">
  <a class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/[0.04] px-8 py-3.5 text-base font-medium text-white/85 transition-colors hover:border-white/40 hover:text-white" href="#calculator"><?= e(__('forFacilities.hero.cta2')) ?></a>
  </div>
  </div>
  </section>

  <!-- L'OFFERTA: dört kısa madde + pilot tesis payları (değerler includes/billing.php) -->
  <section class="section-y border-t border-white/5 bg-ink-900">
  <div class="container-x">
  <div class="max-w-5xl">
  <span class="rp-label text-accent"><?= e(__('forFacilities.offer.eyebrow')) ?></span>
  <h2 class="mt-3 max-w-3xl font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('forFacilities.offer.title')) ?></h2>

  <ul class="mt-10 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
  <?php foreach (['1', '2', '3', '4'] as $oi): ?>
  <li>
  <p class="font-display text-3xl font-bold leading-none text-accent"><?= e(__("v2.offer.$oi.k")) ?></p>
  <h3 class="mt-4 text-base font-semibold text-white"><?= e(__("v2.offer.$oi.t")) ?></h3>
  <p class="mt-1.5 text-sm leading-relaxed text-white/70"><?= e(__("v2.offer.$oi.d")) ?></p>
  </li>
  <?php endforeach; ?>
  </ul>

  <div class="mt-16">
  <div class="grid gap-6 lg:grid-cols-[1fr_1.5fr] lg:items-center">
  <div>
  <h3 class="font-display text-2xl font-bold text-white"><?= e(__('v2.offer.tiersT')) ?></h3>
  <p class="mt-2 text-sm leading-relaxed text-white/70"><?= e(__('v2.offer.tiersSub')) ?></p>
  </div>
  <dl class="grid grid-cols-3 gap-3">
  <?php foreach (replash_share_tiers() as $t): ?>
  <div>
  <dt class="text-xs text-white/60"><span class="font-semibold text-white"><?= (int)$t['min'] ?><?= $t['max'] === null ? '+' : '–' . (int)$t['max'] ?></span> <?= e(__('v2.offer.unit')) ?></dt>
  <dd class="mt-2"><span class="font-display text-3xl font-bold text-accent"><?= (int)$t['facility_pct'] ?>%</span> <span class="block text-xs text-white/60"><?= e(__('v2.offer.share')) ?></span></dd>
  </div>
  <?php endforeach; ?>
  </dl>
  </div>
  </div>
  </div>
  </div>
  </section>

  <!-- KARŞILAŞTIRMA: satın alınan/abonelikli kamera vs Replash. Rakip adı yok, oyuncu fiyatı gösterilmez. -->
  <section class="section-y border-t border-white/5 bg-ink-900" id="confronto">
  <div class="container-x">
  <div class="max-w-5xl" data-reveal="up">
  <p class="rp-label text-accent"><?= e(__('v2.cmp.eyebrow')) ?></p>
  <h2 class="mt-3 max-w-3xl font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('v2.cmp.title')) ?></h2>

  <div class="relative mt-12">
  <!-- Replash sütunu: çerçevesiz, hafif sarı zemin -->
  <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[34%] rounded-2xl bg-accent/[0.06] md:block" aria-hidden="true"></div>
  <table class="relative w-full border-collapse text-left">
  <thead class="max-md:hidden">
  <tr>
  <th scope="col" class="w-[32%] pb-5 align-bottom"><span class="sr-only"><?= e(__('v2.cmp.eyebrow')) ?></span></th>
  <th scope="col" class="w-[34%] pb-5 pr-6 align-bottom text-sm font-semibold text-white/55"><?= e(__('v2.cmp.other')) ?></th>
  <th scope="col" class="w-[34%] px-6 pb-5 pt-6 align-bottom">
  <span class="inline-flex items-center gap-2.5"><img src="<?= asset('images/replash-logo.webp') ?>?v=2" alt="" width="232" height="256" class="h-6 w-auto"><span class="font-display text-xl font-bold tracking-[-0.03em] text-accent"><?= e(__('v2.cmp.replash')) ?></span></span>
  </th>
  </tr>
  </thead>
  <tbody class="divide-y divide-white/10 border-y border-white/10 md:border-b-0">
  <?php foreach (['1', '2', '3', '4', '5', '6'] as $ci): ?>
  <tr class="block py-5 md:table-row md:py-0">
  <th scope="row" class="block pb-3 font-display text-lg font-bold text-white md:table-cell md:py-6 md:pr-6 md:align-top md:text-base"><?= e(__("v2.cmp.rows.$ci.f")) ?></th>
  <td class="flex gap-3 pb-2 text-[15px] leading-relaxed text-white/55 md:table-cell md:py-6 md:pr-6 md:align-top">
  <span class="mt-[0.55em] h-px w-3 flex-none bg-white/35 md:hidden" aria-hidden="true"></span>
  <span><span class="sr-only md:hidden"><?= e(__('v2.cmp.other')) ?>: </span><?= e(__("v2.cmp.rows.$ci.o")) ?></span>
  </td>
  <td class="flex gap-3 text-[15px] font-semibold leading-relaxed text-white md:table-cell md:px-6 md:py-6 md:align-top">
  <svg class="mt-1 h-4 w-4 flex-none text-accent md:mr-2 md:inline md:-translate-y-px" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
  <span><span class="sr-only md:hidden"><?= e(__('v2.cmp.replash')) ?>: </span><?= e(__("v2.cmp.rows.$ci.r")) ?></span>
  </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
  </table>
  </div>
  </div>
  </div>
  </section>

  <!-- GELİR HESAPLAYICI (sade): sporlar aç/kapa, spor başına iki kaydırıcı, tek sonuç kartı. Kademe tüm sporların aylık toplamına göre. -->
  <section id="calculator" class="section-y scroll-mt-20 border-t border-white/5 bg-ink-900">
  <div class="container-x">
  <div class="max-w-5xl">
  <h2 class="max-w-3xl font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('forFacilities.calc.title')) ?></h2>
  <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/75 md:text-lg"><?= e(__('v2.calc.intro')) ?></p>
  <noscript><p class="mt-6 rounded-lg border border-white/20 p-4 text-white/80"><?= e(__('forFacilities.calc.noscript')) ?></p></noscript>

  <div class="mt-10 grid gap-5 lg:grid-cols-[1.35fr_1fr] lg:items-start">
  <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5 md:p-7">
  <p id="calc-sports-label" class="text-xs font-semibold uppercase tracking-wider text-white/60"><?= e(__('v2.calc.sports')) ?></p>
  <div id="calc-sports" class="mt-3 flex flex-wrap gap-2" role="group" aria-labelledby="calc-sports-label"></div>
  <div id="calc-rows" class="mt-2 divide-y divide-white/10"></div>
  </div>

  <div class="rounded-2xl border border-accent/30 bg-accent/[0.06] p-6 md:p-8 lg:sticky lg:top-28" aria-live="polite">
  <p class="text-sm text-white/75"><?= e(__('v2.calc.result')) ?></p>
  <p id="calc-after-venue" class="mt-2 font-display text-5xl font-bold tracking-tight text-accent md:text-6xl">€0</p>
  <p class="mt-1 text-sm text-white/60"><?= e(__('v2.calc.perMonth')) ?></p>
  <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-white/10 pt-5">
  <div><dt class="text-xs text-white/55"><?= e(__('v2.calc.videos')) ?></dt><dd id="calc-total-sales" class="mt-1 font-display text-2xl font-bold text-white">0</dd></div>
  <div><dt class="text-xs text-white/55"><?= e(__('v2.calc.share')) ?></dt><dd id="calc-tier" class="mt-1 font-display text-2xl font-bold text-white">20%</dd></div>
  </dl>
  <p id="calc-zero" class="mt-4 hidden text-sm text-white/80"><?= e(__('forFacilities.calc.zero')) ?></p>
  <p id="calc-noprice" class="mt-4 hidden text-sm text-white/80"><?= e(__('forFacilities.calc.noPrice')) ?></p>
  <a id="calc-cta" data-base="<?= e(base_url('contact?pilot=1')) ?>" class="mt-7 inline-flex w-full items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,transform] hover:bg-accent-neon active:scale-[0.98]" href="<?= base_url('contact?pilot=1') ?>"><?= e(__('forFacilities.calc.cta')) ?></a>
  </div>
  </div>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-5xl" data-reveal="up">
  <h2 class="max-w-3xl font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('forFacilities.whatYouDo.title')) ?></h2>
  <ol class="mt-10 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
  <?php foreach ([1, 2, 3, 4] as $wi): ?>
  <li>
  <p class="font-mono text-sm font-bold text-accent"><?= sprintf('%02d', $wi) ?></p>
  <h3 class="mt-3 text-base font-semibold text-white"><?= e(__("forFacilities.whatYouDo.items.$wi.title")) ?></h3>
  <p class="mt-1.5 text-sm leading-relaxed text-white/70"><?= e(__("forFacilities.whatYouDo.items.$wi.desc")) ?></p>
  </li>
  <?php endforeach; ?>
  </ol>
  <!-- Panel görüntüleri gizli tutulur (kullanıcı isteği): sitede gösterilmiyor -->
  <p class="mt-6 max-w-3xl text-sm leading-relaxed text-white/65"><?= e(__('forFacilities.whatYouDo.note')) ?></p>
  </div>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900">
  <div class="container-x">
  <!-- Gizlilik ve kurulum: başlığa tıklayınca açılır; + simgesi açıkken × olur -->
  <details class="group max-w-5xl" data-reveal="up">
  <summary class="flex cursor-pointer list-none items-center gap-4 [&::-webkit-details-marker]:hidden md:gap-5">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('forFacilities.objections.title')) ?></h2>
  <span class="flex h-11 w-11 flex-none items-center justify-center rounded-full border border-white/20 text-white/70 transition-[transform,border-color,color] duration-200 group-open:rotate-45 group-hover:border-accent group-hover:text-accent" aria-hidden="true">
  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
  </span>
  </summary>
  <div class="mt-10 grid gap-10 md:grid-cols-2 md:gap-12">
  <?php foreach (['gdpr', 'tech'] as $ok): ?>
  <div>
  <h3 class="font-display text-xl font-bold text-white"><?= e(__("forFacilities.objections.items.$ok.question")) ?></h3>
  <p class="mt-3 text-[15.5px] leading-relaxed text-white/75"><?= e(__("forFacilities.objections.items.$ok.answer")) ?></p>
  <ul class="mt-5 divide-y divide-white/10 border-y border-white/10">
  <?php
  // Gizlilik: "3 gün sonra silinir" maddesi yerine ödeme = kayıt izni maddesi (kullanıcı isteği)
  $objItems = $ok === 'gdpr'
      ? [__('forFacilities.objections.items.gdpr.items.1'), __('v2.privacyPay'), __('forFacilities.objections.items.gdpr.items.3'), __('forFacilities.objections.items.gdpr.items.4')]
      : array_map(function ($i) use ($ok) { return __("forFacilities.objections.items.$ok.items.$i"); }, [1, 2, 3, 4]);
  foreach ($objItems as $objItem): ?>
  <li class="py-3 text-sm leading-relaxed text-white/65"><?= e($objItem) ?></li>
  <?php endforeach; ?>
  </ul>
  </div>
  <?php endforeach; ?>
  </div>
  </details>
  </div>
  </section>

  <section class="section-y border-t border-white/5 bg-ink-900 relative overflow-hidden">
  <div class="pointer-events-none absolute inset-0" aria-hidden="true">
  </div>
  <div class="container-x relative">
  <div  data-reveal="up">
  <h2 class="max-w-xl font-display text-3xl font-bold tracking-tight text-balance md:text-4xl">
  <?= e(__('forFacilities.cta.title')) ?>
  </h2>
  <p class="mt-4 max-w-md text-base text-white/75">
  <?= e(__('forFacilities.cta.subtitle')) ?>
  </p>
  <div class="mt-8">
  <a class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-3.5 text-base font-semibold text-ink-900 transition-[background-color,box-shadow,transform] active:scale-[0.98] hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>">
  <?= e(__('forFacilities.cta.button')) ?>
  </a>
  </div>
  </div>
  </div>
  </section>

</main>

<script>
(function () {
  var SPORTS = <?= json_encode($calcSports, JSON_UNESCAPED_UNICODE) ?>;   // price null = prezzo non approvato (nessun incasso calcolato)
  var TIERS = <?= json_encode(array_map(function ($t) { return ['min' => $t['min'], 'max' => $t['max'], 'facility_pct' => $t['facility_pct']]; }, replash_share_tiers()), JSON_UNESCAPED_UNICODE) ?>;   // yalnızca tesis payı: [{min,max,facility_pct}]
  var TXT = <?= json_encode($calcText, JSON_UNESCAPED_UNICODE) ?>;
  var LOCALE = <?= json_encode($calcLocale) ?>;
  var eur = new Intl.NumberFormat(LOCALE, { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 2 });
  var num = new Intl.NumberFormat(LOCALE);
  var pctFmt = function (p) { return LOCALE.indexOf('tr') === 0 ? '%' + p : p + '%'; };
  var keys = Object.keys(SPORTS);
  var rows = [];
  var seq = 0;

  function tierFor(total) {
    var t = TIERS[0];
    TIERS.forEach(function (x) { if (total >= x.min) t = x; });
    return t;
  }
  // Saf hesap. Satır: video satışı = saha × (saha başına günlük satılan video) × gün. Oynanan maç sayısı kullanılmaz.
  // Kademe, TÜM satırların toplam aylık satışına göre belirlenir. Pilot ay tahmine dahil edilmez (karışıklık yaratıyordu).
  function compute(list) {
    var out = list.map(function (r) {
      var sp = SPORTS[r.sport];
      var sales = r.courts * r.sold * r.days;
      var hasPrice = sp.price !== null && sp.price !== undefined;
      return { sales: sales, hasPrice: hasPrice, price: hasPrice ? sp.price : null, gross: hasPrice ? sales * sp.price : 0 };
    });
    var totalSales = out.reduce(function (a, o) { return a + o.sales; }, 0);
    var totalGross = out.reduce(function (a, o) { return a + o.gross; }, 0);
    var tier = tierFor(totalSales);
    var venue = totalGross * tier.facility_pct / 100;
    out.forEach(function (o) { o.share = o.gross * tier.facility_pct / 100; });   // solo la quota del centro viene mostrata
    return {
      rows: out, totalSales: totalSales, totalGross: totalGross, pct: tier.facility_pct,
      venue: venue,
      anyNoPrice: out.some(function (o) { return !o.hasPrice && o.sales > 0; })
    };
  }
  window.replashCalc = { compute: compute, tierFor: tierFor, get rows() { return rows; } };

  function slider(id, label, range, value, fmt) {
    return '<div class="flex flex-col gap-2">' +
      '<div class="flex items-baseline justify-between gap-2"><label for="' + id + '" class="text-xs font-semibold text-white/60">' + label + '</label>' +
      '<output id="' + id + '-out" for="' + id + '" class="font-display text-xl font-bold text-white">' + fmt(value) + '</output></div>' +
      '<input type="range" id="' + id + '" min="' + range[0] + '" max="' + range[1] + '" step="1" value="' + value + '" class="h-6 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-[#FEF1B6]"></div>';
  }

  // Spor düğmeleri: aç/kapa; en az bir spor seçili kalır
  function renderSports() {
    var used = rows.map(function (r) { return r.sport; });
    document.getElementById('calc-sports').innerHTML = keys.map(function (k) {
      var on = used.indexOf(k) !== -1;
      return '<button type="button" data-sport="' + k + '" aria-pressed="' + on + '" class="inline-flex min-h-[44px] items-center rounded-full border px-4 text-sm font-semibold transition ' +
        (on ? 'border-accent bg-accent text-ink-900' : 'border-white/15 text-white/70 hover:border-white/35 hover:text-white') + '">' + SPORTS[k].label + '</button>';
    }).join('');
  }

  function render() {
    renderSports();
    document.getElementById('calc-rows').innerHTML = rows.map(function (r) {
      var sp = SPORTS[r.sport], id = 'row' + r.id;
      return '<div class="py-5 last:pb-0" data-row="' + r.id + '">' +
        '<p class="text-sm font-semibold text-white">' + sp.label + '</p>' +
        '<div class="mt-4 grid gap-6 sm:grid-cols-2">' +
          slider(id + '-courts', TXT.courts, sp.courts, r.courts, num.format.bind(num)) +
          slider(id + '-sold', TXT.sold, sp.sold, r.sold, num.format.bind(num)) +
        '</div></div>';
    }).join('');
    update();
  }

  function update() {
    var c = compute(rows);
    document.getElementById('calc-total-sales').textContent = num.format(c.totalSales);
    document.getElementById('calc-tier').textContent = pctFmt(c.pct);
    document.getElementById('calc-after-venue').textContent = eur.format(Math.round(c.venue));
    document.getElementById('calc-zero').classList.toggle('hidden', c.totalSales !== 0);
    document.getElementById('calc-noprice').classList.toggle('hidden', !c.anyNoPrice);
    // Yenileme İşlevsellik 05: seçimler başvuru formuna taşınır
    var cta = document.getElementById('calc-cta');
    if (cta) {
      var courts = rows.reduce(function (a, r) { return a + r.courts; }, 0);
      cta.href = cta.dataset.base + '&sport=' + encodeURIComponent(rows.map(function (r) { return r.sport; }).join(',')) + '&courts=' + courts + '&est=' + Math.round(c.venue);
    }
  }

  // Gün sayısı kaydırıcısı yok: yapılandırmadaki varsayılan gün kullanılır
  function newRow(sport) {
    var sp = SPORTS[sport];
    return { id: ++seq, sport: sport, courts: sp.courts[2], sold: sp.sold[2], days: sp.days[2] };
  }

  document.getElementById('calc-rows').addEventListener('input', function (e) {
    var m = /^row(\d+)-(courts|sold)$/.exec(e.target.id || '');
    if (!m) return;
    var r = rows.filter(function (x) { return x.id === +m[1]; })[0];
    if (!r) return;
    r[m[2]] = parseInt(e.target.value, 10);
    var out = document.getElementById(e.target.id + '-out');
    if (out) out.textContent = num.format(r[m[2]]);
    update();
  });
  document.getElementById('calc-sports').addEventListener('click', function (e) {
    var b = e.target.closest('[data-sport]');
    if (!b) return;
    var k = b.getAttribute('data-sport');
    var has = rows.some(function (r) { return r.sport === k; });
    if (has) {
      if (rows.length === 1) return;
      rows = rows.filter(function (r) { return r.sport !== k; });   // satır tamamen kalkar: gizli değer kalmaz
    } else {
      rows.push(newRow(k));
      rows.sort(function (x, y) { return keys.indexOf(x.sport) - keys.indexOf(y.sport); });
    }
    render();
    var again = document.querySelector('#calc-sports [data-sport="' + k + '"]'); if (again) again.focus();
  });

  rows.push(newRow(keys[0]));
  render();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
