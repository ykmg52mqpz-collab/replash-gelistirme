<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('forPlayers.hero.eyebrow') ?: __('nav.forPlayers');
require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen pt-28 pb-24 relative overflow-hidden bg-ink-900">
  <div class="hero-glow"></div>

  <div class="max-w-5xl mx-auto px-4 sm:px-6 relative z-10 space-y-20 sm:space-y-24">
    
    <!-- Hero Title -->
    <div class="text-center max-w-3xl mx-auto">
      <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent mb-4">
        <?= htmlspecialchars(__('forPlayers.hero.eyebrow') ?: 'Oyuncular İçin') ?>
      </div>
      <h1 class="font-display text-4xl sm:text-6xl font-bold text-white tracking-tight leading-[1.15] mb-6">
        <?= htmlspecialchars(__('forPlayers.hero.title') ?: 'Her Güzel Anınızı Ölümsüzleştirin.') ?>
      </h1>
      <p class="text-neutral-300 text-lg sm:text-xl leading-relaxed">
        <?= htmlspecialchars(__('forPlayers.hero.subtitle') ?: 'Attığınız o müthiş vole, kurtardığınız imkansız penaltı ya da padeldeki kritik smash... Artık sadece anılarda kalmayacak.') ?>
      </p>
    </div>

    <!-- 3 Big Features -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="card-elevated rounded-3xl p-8 border border-white/10 hover:border-accent/30 transition-all duration-300">
        <img src="<?= asset('images/gen_halisaha_shot.jpg') ?>" alt="Match Action" class="rounded-2xl w-full h-44 object-cover mb-6">
        <h3 class="font-display text-xl font-bold text-white mb-2">
          <?= htmlspecialchars(__('forPlayers.features.1.title') ?: 'Geniş Açı HD Kayıt') ?>
        </h3>
        <p class="text-neutral-400 text-sm leading-relaxed">
          <?= htmlspecialchars(__('forPlayers.features.1.desc') ?: 'Kortu ve sahayı kör nokta olmadan bütünüyle yakalayan yüksek çözünürlüklü video akışı.') ?>
        </p>
      </div>

      <div class="card-elevated rounded-3xl p-8 border border-white/10 hover:border-accent/30 transition-all duration-300">
        <img src="<?= asset('images/player-phone-golden.jpg') ?>" alt="Player App" class="rounded-2xl w-full h-44 object-cover mb-6">
        <h3 class="font-display text-xl font-bold text-white mb-2">
          <?= htmlspecialchars(__('forPlayers.features.2.title') ?: 'Saniyeler İçinde Klip Kes') ?>
        </h3>
        <p class="text-neutral-400 text-sm leading-relaxed">
          <?= htmlspecialchars(__('forPlayers.features.2.desc') ?: 'Tüm maçı izlemek zorunda kalmadan en sevdiğiniz 15-30 saniyeyi seçip doğrudan cihazınıza indirin.') ?>
        </p>
      </div>

      <div class="card-elevated rounded-3xl p-8 border border-white/10 hover:border-accent/30 transition-all duration-300">
        <img src="<?= asset('images/gen_friends_celebrating.jpg') ?>" alt="Friends celebrating" class="rounded-2xl w-full h-44 object-cover mb-6">
        <h3 class="font-display text-xl font-bold text-white mb-2">
          <?= htmlspecialchars(__('forPlayers.features.3.title') ?: 'WhatsApp & Hikayeler') ?>
        </h3>
        <p class="text-neutral-400 text-sm leading-relaxed">
          <?= htmlspecialchars(__('forPlayers.features.3.desc') ?: 'Maçtan sonra arkadaş grubunuza atın, en iyi hareketlerinizi Instagram Story veya TikTok\'ta paylaşın.') ?>
        </p>
      </div>
    </div>

    <!-- How to Watch Banner -->
    <div class="card-elevated rounded-3xl p-8 sm:p-12 border border-white/10 bg-ink-800/80 flex flex-col md:flex-row items-center justify-between gap-8">
      <div>
        <span class="text-xs font-semibold uppercase tracking-wider text-accent">
          <?= htmlspecialchars(__('forPlayers.findBanner.eyebrow') ?: 'Hemen Başla') ?>
        </span>
        <h2 class="font-display text-2xl sm:text-3xl font-bold text-white mt-1 mb-2">
          <?= htmlspecialchars(__('forPlayers.findBanner.title') ?: 'Maçını İzlemeye Hazır mısın?') ?>
        </h2>
        <p class="text-neutral-400 text-sm max-w-lg">
          <?= htmlspecialchars(__('forPlayers.findBanner.subtitle') ?: 'Saha görevlisinden aldığınız erişim kodunu girin veya sahanızı seçin.') ?>
        </p>
      </div>
      <a href="<?= base_url('find.php') ?>" class="rounded-full bg-accent px-8 py-3.5 text-sm font-semibold text-ink-900 shadow-glow hover:bg-accent-neon transition whitespace-nowrap">
        <?= htmlspecialchars(__('forPlayers.findBanner.button') ?: 'Maçımı Bul & İzle') ?> &rarr;
      </a>
    </div>

    <!-- ==================== SECTION: WHY PLAYERS WANT THE VIDEO ==================== -->
    <div class="space-y-10 pt-4">
      <div class="max-w-3xl">
        <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-[1.15]">
          <?= htmlspecialchars(__('forPlayers.whyVideo.title') ?: 'Why players want the video.') ?>
        </h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 01 -->
        <div class="card-elevated relative rounded-3xl p-8 sm:p-10 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all flex flex-col justify-between overflow-hidden">
          <div class="absolute top-6 right-8 font-display text-4xl sm:text-5xl font-bold text-accent/25 select-none pointer-events-none">
            <?= htmlspecialchars(__('forPlayers.whyVideo.cards.1.num') ?: '01') ?>
          </div>
          <div>
            <div class="w-12 h-12 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-6">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
            </div>
            <h3 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.1.title') ?: 'A memory to keep') ?>
            </h3>
            <p class="text-neutral-400 text-sm sm:text-base leading-relaxed">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.1.desc') ?: 'After a meal you post a photo. After a workout, a selfie. After a match? Almost nothing. Replash finally gives you something to rewatch and share.') ?>
            </p>
          </div>
        </div>

        <!-- Card 02 -->
        <div class="card-elevated relative rounded-3xl p-8 sm:p-10 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all flex flex-col justify-between overflow-hidden">
          <div class="absolute top-6 right-8 font-display text-4xl sm:text-5xl font-bold text-accent/25 select-none pointer-events-none">
            <?= htmlspecialchars(__('forPlayers.whyVideo.cards.2.num') ?: '02') ?>
          </div>
          <div>
            <div class="w-12 h-12 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-6">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                <circle cx="12" cy="13" r="4"></circle>
              </svg>
            </div>
            <h3 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.2.title') ?: 'The Moment, Captured') ?>
            </h3>
            <p class="text-neutral-400 text-sm sm:text-base leading-relaxed">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.2.desc') ?: 'The look after the first goal. The shot that closes the set. Moments that vanish the instant they happen — unless something is recording. With Replash they stay yours.') ?>
            </p>
          </div>
        </div>

        <!-- Card 03 -->
        <div class="card-elevated relative rounded-3xl p-8 sm:p-10 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all flex flex-col justify-between overflow-hidden">
          <div class="absolute top-6 right-8 font-display text-4xl sm:text-5xl font-bold text-accent/25 select-none pointer-events-none">
            <?= htmlspecialchars(__('forPlayers.whyVideo.cards.3.num') ?: '03') ?>
          </div>
          <div>
            <div class="w-12 h-12 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-6">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="6" cy="6" r="3"></circle>
                <circle cx="6" cy="18" r="3"></circle>
                <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
              </svg>
            </div>
            <h3 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.3.title') ?: 'Clip and Share, No Friction') ?>
            </h3>
            <p class="text-neutral-400 text-sm sm:text-base leading-relaxed">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.3.desc') ?: 'Open the recording. Pick 10 seconds. Download. Share. No account, no app, no profile.') ?>
            </p>
          </div>
        </div>

        <!-- Card 04 -->
        <div class="card-elevated relative rounded-3xl p-8 sm:p-10 border border-white/10 bg-white/[0.02] hover:border-accent/40 transition-all flex flex-col justify-between overflow-hidden">
          <div class="absolute top-6 right-8 font-display text-4xl sm:text-5xl font-bold text-accent/25 select-none pointer-events-none">
            <?= htmlspecialchars(__('forPlayers.whyVideo.cards.4.num') ?: '04') ?>
          </div>
          <div>
            <div class="w-12 h-12 rounded-2xl border border-accent/30 bg-accent/10 text-accent flex items-center justify-center mb-6">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <h3 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.4.title') ?: 'When friends see it') ?>
            </h3>
            <p class="text-neutral-400 text-sm sm:text-base leading-relaxed">
              <?= htmlspecialchars(__('forPlayers.whyVideo.cards.4.desc') ?: 'When you see clips from teams that played at your own facility, adding the video to your next booking just feels natural.') ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== SECTION: HIGHLIGHTS & MEMORIES WITH BACKGROUND ==================== -->
    <div class="relative rounded-3xl overflow-hidden border border-white/10 p-8 sm:p-12 lg:p-16">
      <!-- Background Image with Gradient Overlay -->
      <div class="absolute inset-0 z-0">
        <img src="<?= asset('images/gen_player_watching.jpg') ?>" alt="Highlights Background" class="w-full h-full object-cover object-center opacity-25 filter brightness-90">
        <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/80 to-ink-900/60"></div>
      </div>

      <div class="relative z-10 max-w-3xl space-y-8">
        <div>
          <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.2] mb-4">
            <?= htmlspecialchars(__('forPlayers.highlights.title') ?: 'Goals. Saves. Rallies. Funny moments. All worth watching twice.') ?>
          </h2>
          <p class="text-neutral-300 text-base sm:text-lg leading-relaxed">
            <?= htmlspecialchars(__('forPlayers.highlights.subtitle') ?: 'Scroll through the match and rewatch the moments that made the game. Whether it\'s a last-minute goal, a tough save, a rally you want to study, or a hilarious miss that made everyone laugh. The full recording is there.') ?>
          </p>
        </div>

        <div class="space-y-3 pt-2">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <?php $itemText = __('forPlayers.highlights.items.' . $i); ?>
            <?php if (!empty($itemText)): ?>
              <div class="flex items-center gap-3.5 rounded-2xl border border-white/10 bg-white/[0.04] backdrop-blur-sm px-5 py-3.5 hover:border-accent/30 hover:bg-white/[0.06] transition-all">
                <div class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-accent/40 bg-accent/10 text-accent">
                  <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                </div>
                <span class="text-sm sm:text-base text-neutral-200 font-medium">
                  <?= htmlspecialchars($itemText) ?>
                </span>
              </div>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

