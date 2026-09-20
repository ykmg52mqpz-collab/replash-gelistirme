<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('about.hero.eyebrow') ?: __('nav.about');
require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen pt-28 pb-24 relative overflow-hidden bg-ink-900">
  <div class="hero-glow"></div>

  <div class="max-w-5xl mx-auto px-4 sm:px-6 relative z-10 space-y-20 sm:space-y-24">
    
    <!-- Hero Title -->
    <div class="text-center max-w-3xl mx-auto">
      <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent mb-4">
        <?= htmlspecialchars(__('about.hero.eyebrow') ?: 'Hakkımızda') ?>
      </div>
      <h1 class="font-display text-4xl sm:text-6xl font-bold text-white tracking-tight leading-[1.15] mb-6">
        <?= htmlspecialchars(__('about.hero.title') ?: 'Amatör Sporu Dijitalleştiren Teknoloji') ?>
      </h1>
      <p class="text-neutral-300 text-lg sm:text-xl leading-relaxed">
        <?= htmlspecialchars(__('about.hero.subtitle') ?: 'Replash, sahalarda oynanan milyonlarca amatör maçın kaybolup gitmesini engellemek ve modern spor tesislerine dijital güç kazandırmak için kuruldu.') ?>
      </p>
    </div>

    <!-- ==================== MISSION & VISION CARDS ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Mission Card -->
      <div class="card-elevated rounded-3xl p-8 sm:p-10 border border-white/10 bg-white/[0.02] hover:border-accent/30 transition-all">
        <div class="w-12 h-12 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-6">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <circle cx="12" cy="12" r="6"></circle>
            <circle cx="12" cy="12" r="2"></circle>
          </svg>
        </div>
        <div class="text-xs font-bold uppercase tracking-widest text-accent mb-3">
          <?= htmlspecialchars(__('about.missionVision.mission.eyebrow') ?: 'MİSYONUMUZ') ?>
        </div>
        <p class="text-neutral-300 text-base sm:text-lg leading-relaxed font-light">
          <?= htmlspecialchars(__('about.missionVision.mission.quote') ?: '"Her amatör maçı daha unutulmaz, daha paylaşılabilir ve daha değerli kılmak."') ?>
        </p>
      </div>

      <!-- Vision Card -->
      <div class="card-elevated rounded-3xl p-8 sm:p-10 border border-white/10 bg-white/[0.02] hover:border-accent/30 transition-all">
        <div class="w-12 h-12 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-6">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2v2"></path>
            <path d="M12 20v2"></path>
            <path d="M4.93 4.93l1.41 1.41"></path>
            <path d="M17.66 17.66l1.41 1.41"></path>
            <path d="M2 12h2"></path>
            <path d="M20 12h2"></path>
            <path d="M6.34 17.66l-1.41 1.41"></path>
            <path d="M19.07 4.93l-1.41 1.41"></path>
            <circle cx="12" cy="12" r="4"></circle>
          </svg>
        </div>
        <div class="text-xs font-bold uppercase tracking-widest text-accent mb-3">
          <?= htmlspecialchars(__('about.missionVision.vision.eyebrow') ?: 'VİZYONUMUZ') ?>
        </div>
        <p class="text-neutral-300 text-base sm:text-lg leading-relaxed font-light">
          <?= htmlspecialchars(__('about.missionVision.vision.quote') ?: '"Her spor tesisinin ışıklandırma, soyunma odası veya online rezervasyon sunması kadar doğal bir şekilde maç kaydı sunabildiği bir gelecek."') ?>
        </p>
      </div>
    </div>

    <!-- ==================== STORY SECTION ==================== -->
    <div class="pt-4 max-w-4xl">
      <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-[1.15] mb-6">
        <?= htmlspecialchars(__('about.story.title') ?: 'Oynamayı sevenlerden, oynamayı sevenler için.') ?>
      </h2>
      <p class="text-neutral-300 text-base sm:text-lg leading-relaxed mb-8">
        <?= htmlspecialchars(__('about.story.desc') ?: 'Replash\'i her hafta gördüğümüz basit bir ihtiyaçtan kurduk: Harika bir maç oynuyorsunuz ve geriye tekrar izleyecek hiçbir şey kalmıyor. Amatör futbol ve padel, profesyonel sporun sahip olduğu en güzel anları yeniden yaşama şansını hak ediyor.') ?>
      </p>

      <div class="space-y-4">
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <div class="flex items-center gap-3.5">
            <div class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-accent/40 bg-accent/10 text-accent">
              <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
            </div>
            <span class="text-sm sm:text-base text-neutral-300 font-medium">
              <?= htmlspecialchars(__('about.story.bullets.' . $i)) ?>
            </span>
          </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- ==================== STATS ROW ==================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
      <div class="card-elevated rounded-3xl p-6 sm:p-8 border border-white/10">
        <div class="font-display text-4xl sm:text-5xl font-bold text-accent mb-2">
          <?= htmlspecialchars(__('about.stats.1.val') ?: '%100') ?>
        </div>
        <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">
          <?= htmlspecialchars(__('about.stats.1.label') ?: 'Otomatik Kayıt') ?>
        </div>
      </div>
      <div class="card-elevated rounded-3xl p-6 sm:p-8 border border-white/10">
        <div class="font-display text-4xl sm:text-5xl font-bold text-accent mb-2">
          <?= htmlspecialchars(__('about.stats.2.val') ?: '< 8 dk') ?>
        </div>
        <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">
          <?= htmlspecialchars(__('about.stats.2.label') ?: 'Hazır Olma Süresi') ?>
        </div>
      </div>
      <div class="card-elevated rounded-3xl p-6 sm:p-8 border border-white/10">
        <div class="font-display text-4xl sm:text-5xl font-bold text-accent mb-2">
          <?= htmlspecialchars(__('about.stats.3.val') ?: '0 Efor') ?>
        </div>
        <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">
          <?= htmlspecialchars(__('about.stats.3.label') ?: 'Tesis Personeli İçin') ?>
        </div>
      </div>
    </div>

    <!-- ==================== BUILT FOR EVERYDAY SPORT ==================== -->
    <div class="pt-8 sm:pt-12 border-t border-white/5 space-y-12">
      <div class="text-center">
        <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
          <?= htmlspecialchars(__('about.everydaySport.title') ?: 'Gündelik spor için tasarlandı.') ?>
        </h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Football Facilities -->
        <div class="card-elevated rounded-3xl p-6 sm:p-7 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all text-center flex flex-col items-center">
          <div class="w-14 h-14 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-5">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
              <path d="M2 12h20"></path>
            </svg>
          </div>
          <h3 class="font-display text-base sm:text-lg font-bold text-white mb-2">
            <?= htmlspecialchars(__('about.everydaySport.cards.football.title') ?: 'Futbol Tesisleri') ?>
          </h3>
          <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed">
            <?= htmlspecialchars(__('about.everydaySport.cards.football.desc') ?: 'Premium bir deneyim sunmak isteyen halı saha, 7\'ye 7 ve futsal tesisleri.') ?>
          </p>
        </div>

        <!-- Padel Clubs -->
        <div class="card-elevated rounded-3xl p-6 sm:p-7 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all text-center flex flex-col items-center">
          <div class="w-14 h-14 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-5">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <ellipse cx="12" cy="9" rx="6" ry="7"></ellipse>
              <path d="M12 16v6"></path>
              <path d="M10 22h4"></path>
              <circle cx="10" cy="7" r="0.8" fill="currentColor"></circle>
              <circle cx="14" cy="7" r="0.8" fill="currentColor"></circle>
              <circle cx="12" cy="10" r="0.8" fill="currentColor"></circle>
            </svg>
          </div>
          <h3 class="font-display text-base sm:text-lg font-bold text-white mb-2">
            <?= htmlspecialchars(__('about.everydaySport.cards.padel.title') ?: 'Padel Kulüpleri') ?>
          </h3>
          <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed">
            <?= htmlspecialchars(__('about.everydaySport.cards.padel.desc') ?: 'Hızla büyüyen pazarda öne çıkmak isteyen modern padel tesisleri.') ?>
          </p>
        </div>

        <!-- Amateur Sports Venues -->
        <div class="card-elevated rounded-3xl p-6 sm:p-7 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all text-center flex flex-col items-center">
          <div class="w-14 h-14 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-5">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="2" width="16" height="20" rx="2"></rect>
              <path d="M9 22v-4h6v4"></path>
              <path d="M8 6h.01"></path>
              <path d="M16 6h.01"></path>
              <path d="M8 10h.01"></path>
              <path d="M16 10h.01"></path>
              <path d="M8 14h.01"></path>
              <path d="M16 14h.01"></path>
            </svg>
          </div>
          <h3 class="font-display text-base sm:text-lg font-bold text-white mb-2">
            <?= htmlspecialchars(__('about.everydaySport.cards.venues.title') ?: 'Amatör Spor Tesisleri') ?>
          </h3>
          <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed">
            <?= htmlspecialchars(__('about.everydaySport.cards.venues.desc') ?: 'Düzenli, tekrarlayan maçlara ev sahipliği yapan ve unutulmaz bir deneyim sunmak isteyen her tesis.') ?>
          </p>
        </div>

        <!-- Players -->
        <div class="card-elevated rounded-3xl p-6 sm:p-7 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all text-center flex flex-col items-center">
          <div class="w-14 h-14 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-5">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
          <h3 class="font-display text-base sm:text-lg font-bold text-white mb-2">
            <?= htmlspecialchars(__('about.everydaySport.cards.players.title') ?: 'Oyuncular') ?>
          </h3>
          <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed">
            <?= htmlspecialchars(__('about.everydaySport.cards.players.desc') ?: 'Kendi kimlikleri altında maçlarını izlemek, klip kesmek ve paylaşmak isteyen amatör oyuncular.') ?>
          </p>
        </div>
      </div>
    </div>

    <!-- Bottom Contact CTA -->
    <div class="text-center pt-8">
      <a href="<?= base_url('contact.php') ?>" class="inline-flex rounded-full bg-accent px-8 py-3.5 text-sm font-semibold text-ink-900 shadow-glow hover:bg-accent-neon transition">
        <?= htmlspecialchars(__('about.cta') ?: 'Bizimle İletişime Geçin') ?> &rarr;
      </a>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

