<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('howItWorks.hero.eyebrow') ?: __('nav.howItWorks');
require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen pt-24 pb-24 relative overflow-hidden">
  
  <!-- Radial Ambient Glow -->
  <div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[600px] overflow-hidden -z-10">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-accent/15 blur-[140px] rounded-full"></div>
    <div class="absolute top-60 left-1/4 w-[400px] h-[300px] bg-emerald-500/10 blur-[120px] rounded-full"></div>
  </div>

  <!-- Hero Section with Background Banner -->
  <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 pb-16">
    <div class="relative rounded-3xl overflow-hidden border border-white/10 shadow-2xl bg-ink-900/80 mb-12">
      <!-- Background image with dark overlay -->
      <div class="absolute inset-0 z-0">
        <img src="<?= asset('images/gen_football_night.jpg') ?>" alt="Football Pitch Night" class="w-full h-full object-cover object-center opacity-30 scale-105 transition-transform duration-1000">
        <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/80 to-ink-950/40"></div>
      </div>

      <div class="relative z-10 px-6 sm:px-12 py-16 sm:py-20 text-center max-w-3xl mx-auto">
        <div class="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-accent mb-6 backdrop-blur-md">
          <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
          <?= htmlspecialchars(__('howItWorks.hero.eyebrow') ?: 'Nasıl Çalışır') ?>
        </div>
        
        <h1 class="font-display text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.15] mb-6">
          <?= htmlspecialchars(__('howItWorks.hero.title') ?: 'Altı basit adım. Ekibiniz için sıfır karmaşıklık.') ?>
        </h1>
        
        <p class="text-neutral-300 text-base sm:text-lg lg:text-xl leading-relaxed max-w-2xl mx-auto">
          <?= htmlspecialchars(__('howItWorks.hero.subtitle') ?: 'İlk vuruştan maç tekrarına kadar tüm süreci Replash yönetir. Aşağıda bir maç örneğiyle her adım açıklanmıştır.') ?>
        </p>
      </div>
    </div>

    <!-- Connected Timeline Steps Section -->
    <div class="relative max-w-6xl mx-auto">
      
      <!-- Central/Left Timeline Connecting Line -->
      <div class="absolute left-6 sm:left-8 top-10 bottom-10 w-0.5 bg-gradient-to-b from-accent/60 via-accent/30 to-emerald-500/40 -z-0 hidden md:block"></div>

      <div class="space-y-12 sm:space-y-16 relative z-10">

        <!-- ==================== STEP 1 ==================== -->
        <div class="relative flex flex-col md:flex-row gap-6 sm:gap-8 items-start">
          <!-- Step Indicator Badge -->
          <div class="flex-shrink-0 flex items-center gap-4 md:flex-col md:items-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-ink-900 border-2 border-accent/60 shadow-glow flex items-center justify-center text-accent font-display text-xl sm:text-2xl font-black ring-4 ring-ink-950">
              01
            </div>
            <span class="md:hidden text-xs font-bold uppercase tracking-wider text-accent">
              <?= htmlspecialchars(__('howItWorks.steps.1.label') ?: 'Adım 1') ?>
            </span>
          </div>

          <!-- Step Content Card -->
          <div class="flex-grow card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 w-full hover:border-accent/40 transition-all duration-300">
            <div class="hidden md:block text-xs font-bold uppercase tracking-wider text-accent mb-2">
              <?= htmlspecialchars(__('howItWorks.steps.1.label') ?: 'Adım 1') ?>
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('howItWorks.steps.1.title') ?: 'Maç Rezerve Edilir ve Oynanır') ?>
            </h2>
            <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-6">
              <?= htmlspecialchars(__('howItWorks.steps.1.desc') ?: 'Oyuncular sahayı her zamanki gibi rezerve eder ve oynar. Sahadaki deneyimde hiçbir şey değişmez; Replash arka planda sessizce çalışır.') ?>
            </p>

            <!-- Interactive Visual Mock: Booking Card -->
            <div class="bg-ink-950/80 rounded-2xl border border-white/10 p-5 backdrop-blur-sm">
              <div class="flex items-center justify-between pb-3 border-b border-white/10">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-accent/20 border border-accent/40 flex items-center justify-center text-accent">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-white"><?= htmlspecialchars(__('howItWorks.mockups.step1.bookingTitle') ?: 'Salı Akşamı Rezervasyonu') ?></h4>
                    <p class="text-xs text-neutral-400"><?= htmlspecialchars(__('howItWorks.mockups.step1.pitchSlot') ?: 'Saha 2 • 20:00 - 21:00 (60 Dk)') ?></p>
                  </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> <?= htmlspecialchars(__('howItWorks.mockups.step1.confirmed') ?: 'Onaylandı') ?>
                </span>
              </div>
              
              <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/[0.02] p-3 rounded-xl border border-white/5">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  </div>
                  <span class="text-xs font-medium text-neutral-200"><?= htmlspecialchars(__('howItWorks.mockups.step1.recordingLabel') ?: 'Replash Otomatik HD Video Kaydı') ?></span>
                </div>
                <div class="flex items-center gap-2 self-end sm:self-auto">
                  <span class="text-xs text-accent font-semibold"><?= htmlspecialchars(__('howItWorks.mockups.step1.active') ?: 'Aktif') ?></span>
                  <div class="w-9 h-5 bg-accent rounded-full p-0.5 flex items-center justify-end">
                    <div class="w-4 h-4 rounded-full bg-ink-950 shadow-md"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== STEP 2 ==================== -->
        <div class="relative flex flex-col md:flex-row gap-6 sm:gap-8 items-start">
          <!-- Step Indicator Badge -->
          <div class="flex-shrink-0 flex items-center gap-4 md:flex-col md:items-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-ink-900 border-2 border-accent/60 shadow-glow flex items-center justify-center text-accent font-display text-xl sm:text-2xl font-black ring-4 ring-ink-950">
              02
            </div>
            <span class="md:hidden text-xs font-bold uppercase tracking-wider text-accent">
              <?= htmlspecialchars(__('howItWorks.steps.2.label') ?: 'Adım 2') ?>
            </span>
          </div>

          <!-- Step Content Card -->
          <div class="flex-grow card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 w-full hover:border-accent/40 transition-all duration-300">
            <div class="hidden md:block text-xs font-bold uppercase tracking-wider text-accent mb-2">
              <?= htmlspecialchars(__('howItWorks.steps.2.label') ?: 'Adım 2') ?>
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('howItWorks.steps.2.title') ?: 'Replash Otomatik Olarak Kaydeder') ?>
            </h2>
            <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-6">
              <?= htmlspecialchars(__('howItWorks.steps.2.desc') ?: 'Sahaya monte edilen sabit PoE kameralar maçı baştan sona kaydeder. Düğme yok, manuel kontrol yok, personel müdahalesi yok.') ?>
            </p>

            <!-- Visual Mock: Camera Live Feed Simulator -->
            <div class="rounded-2xl border border-white/10 bg-ink-950/90 p-3 sm:p-4 overflow-hidden relative">
              <div class="relative aspect-[16/9] w-full overflow-hidden rounded-xl border border-white/10 shadow-inner">
                <!-- Pitch Background -->
                <div class="absolute inset-0 bg-[#0f2a1c]"></div>
                
                <!-- Field lines and Animated Moving Players / Ball -->
                <svg class="absolute inset-0 h-full w-full" viewBox="0 0 320 180" preserveAspectRatio="none">
                  <!-- Pitch outline -->
                  <g stroke="rgba(255,255,255,0.22)" stroke-width="0.8" fill="none">
                    <rect x="10" y="12" width="300" height="156" rx="2"></rect>
                    <line x1="160" y1="12" x2="160" y2="168"></line>
                    <circle cx="160" cy="90" r="18"></circle>
                    <rect x="10" y="60" width="30" height="60"></rect>
                    <rect x="280" y="60" width="30" height="60"></rect>
                  </g>
                  
                  <!-- Ball (Yellow dot moving in figure-8 across pitch) -->
                  <circle r="2.5" fill="rgb(253,230,138)" cx="160" cy="90">
                    <animate attributeName="cx" values="160; 120; 100; 130; 160; 200; 220; 190; 160" dur="10s" repeatCount="indefinite" />
                    <animate attributeName="cy" values="90; 70; 100; 120; 90; 70; 100; 120; 90" dur="10s" repeatCount="indefinite" />
                  </circle>
                  
                  <!-- Player 1 (White Team) -->
                  <circle r="3" fill="white" cx="90" cy="70">
                    <animate attributeName="cx" values="90; 110; 95; 90" dur="12s" repeatCount="indefinite" />
                    <animate attributeName="cy" values="70; 85; 100; 70" dur="12s" repeatCount="indefinite" />
                  </circle>
                  
                  <!-- Player 2 (White Team) -->
                  <circle r="3" fill="white" cx="140" cy="110">
                    <animate attributeName="cx" values="140; 160; 145; 140" dur="12s" begin="0.5s" repeatCount="indefinite" />
                    <animate attributeName="cy" values="110; 100; 130; 110" dur="12s" begin="0.5s" repeatCount="indefinite" />
                  </circle>
                  
                  <!-- Player 3 (Dark Team) -->
                  <circle r="3" fill="#111" stroke="white" stroke-width="0.5" cx="230" cy="80">
                    <animate attributeName="cx" values="230; 210; 220; 230" dur="12s" repeatCount="indefinite" />
                    <animate attributeName="cy" values="80; 95; 110; 80" dur="12s" repeatCount="indefinite" />
                  </circle>
                  
                  <!-- Player 4 (Dark Team) -->
                  <circle r="3" fill="#111" stroke="white" stroke-width="0.5" cx="180" cy="110">
                    <animate attributeName="cx" values="180; 200; 185; 180" dur="12s" begin="0.8s" repeatCount="indefinite" />
                    <animate attributeName="cy" values="110; 95; 125; 110" dur="12s" begin="0.8s" repeatCount="indefinite" />
                  </circle>
                </svg>

                <!-- REC ticker Top-Right -->
                <div class="absolute right-2 top-2 flex items-center gap-1.5 rounded bg-black/60 px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-widest text-white/90 backdrop-blur-sm border border-white/10">
                  <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-ping"></span>
                  <?= htmlspecialchars(__('howItWorks.mockups.step2.rec') ?: 'Rec 47:12') ?>
                </div>

                <!-- Camera label Bottom-Left -->
                <div class="absolute bottom-2 left-2 font-mono text-[9px] uppercase tracking-widest text-white/60 bg-black/40 px-2 py-0.5 rounded backdrop-blur-sm">
                  <?= htmlspecialchars(__('howItWorks.mockups.step2.cam') ?: 'Cam 01 · Auto') ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== STEP 3 ==================== -->
        <div class="relative flex flex-col md:flex-row gap-6 sm:gap-8 items-start">
          <!-- Step Indicator Badge -->
          <div class="flex-shrink-0 flex items-center gap-4 md:flex-col md:items-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-ink-900 border-2 border-accent/60 shadow-glow flex items-center justify-center text-accent font-display text-xl sm:text-2xl font-black ring-4 ring-ink-950">
              03
            </div>
            <span class="md:hidden text-xs font-bold uppercase tracking-wider text-accent">
              <?= htmlspecialchars(__('howItWorks.steps.3.label') ?: 'Adım 3') ?>
            </span>
          </div>

          <!-- Step Content Card -->
          <div class="flex-grow card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 w-full hover:border-accent/40 transition-all duration-300">
            <div class="hidden md:block text-xs font-bold uppercase tracking-wider text-accent mb-2">
              <?= htmlspecialchars(__('howItWorks.steps.3.label') ?: 'Adım 3') ?>
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('howItWorks.steps.3.title') ?: 'Tesis Videoyu Aktif Eder') ?>
            </h2>
            <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-6">
              <?= htmlspecialchars(__('howItWorks.steps.3.desc') ?: 'Takım videoyu istediğinde tesis görevlisi Replash panelinden tek dokunuşla kaydı onaylar. Gereken tek işlem budur ve sadece birkaç saniye sürer.') ?>
            </p>

            <!-- Visual Mock: Facility Panel One-Click Activation -->
            <div class="bg-ink-950/80 rounded-2xl border border-white/10 p-5">
              <div class="flex items-center justify-between pb-3 border-b border-white/10">
                <div class="flex items-center gap-2">
                  <div class="w-2.5 h-2.5 rounded-full bg-accent"></div>
                  <span class="text-xs font-semibold text-white uppercase tracking-wider"><?= htmlspecialchars(__('howItWorks.mockups.step3.panelTitle') ?: 'Tesis Resepsiyon Paneli') ?></span>
                </div>
                <span class="text-[11px] text-neutral-400"><?= htmlspecialchars(__('howItWorks.mockups.step3.pin') ?: 'PIN: ****') ?></span>
              </div>

              <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-white/[0.03] border border-white/10">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-white"><?= htmlspecialchars(__('howItWorks.mockups.step3.slotName') ?: 'Saha 2 • 20:00 Slotu') ?></span>
                    <span class="text-[10px] px-2 py-0.5 bg-accent/20 text-accent font-semibold rounded-md border border-accent/30"><?= htmlspecialchars(__('howItWorks.mockups.step3.videoRecorded') ?: 'Video Kaydedildi') ?></span>
                  </div>
                  <p class="text-xs text-neutral-400 mt-1"><?= htmlspecialchars(__('howItWorks.mockups.step3.subtitle') ?: 'Takım ödemeyi yaptı • Tek tıkla erişimi açın') ?></p>
                </div>
                
                <button type="button" class="btn-primary text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 shadow-glow flex-shrink-0 cursor-default">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  <span><?= htmlspecialchars(__('howItWorks.mockups.step3.activateBtn') ?: 'Videoyu Aktif Et (1 Tık)') ?></span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== STEP 4 ==================== -->
        <div class="relative flex flex-col md:flex-row gap-6 sm:gap-8 items-start">
          <!-- Step Indicator Badge -->
          <div class="flex-shrink-0 flex items-center gap-4 md:flex-col md:items-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-ink-900 border-2 border-accent/60 shadow-glow flex items-center justify-center text-accent font-display text-xl sm:text-2xl font-black ring-4 ring-ink-950">
              04
            </div>
            <span class="md:hidden text-xs font-bold uppercase tracking-wider text-accent">
              <?= htmlspecialchars(__('howItWorks.steps.4.label') ?: 'Adım 4') ?>
            </span>
          </div>

          <!-- Step Content Card -->
          <div class="flex-grow card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 w-full hover:border-accent/40 transition-all duration-300">
            <div class="hidden md:block text-xs font-bold uppercase tracking-wider text-accent mb-2">
              <?= htmlspecialchars(__('howItWorks.steps.4.label') ?: 'Adım 4') ?>
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('howItWorks.steps.4.title') ?: 'Maç Kodunu Alın') ?>
            </h2>
            <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-6">
              <?= htmlspecialchars(__('howItWorks.steps.4.desc') ?: 'Video aktif edildiğinde takıma benzersiz bir erişim kodu verilir (resepsiyonda veya girişteki QR ile). Tek kod tüm takım için geçerlidir.') ?>
            </p>

            <!-- Visual Mock: Match Ticket / Code Card -->
            <div class="bg-gradient-to-r from-ink-950 via-ink-900 to-ink-950 rounded-2xl border-2 border-dashed border-accent/40 p-5 relative overflow-hidden">
              <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 text-center sm:text-left">
                  <div class="w-12 h-12 rounded-xl bg-accent/10 border border-accent/30 flex items-center justify-center text-accent flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                  </div>
                  <div>
                    <span class="text-[11px] uppercase tracking-wider text-neutral-400 font-semibold"><?= htmlspecialchars(__('howItWorks.mockups.step4.uniqueCode') ?: 'Tüm Takım İçin Benzersiz Kod') ?></span>
                    <div class="font-mono text-2xl sm:text-3xl font-black text-accent tracking-widest mt-0.5">
                      AEXF-BFFG
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs text-neutral-300">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <?= htmlspecialchars(__('howItWorks.mockups.step4.copyShare') ?: 'Kopyala / Paylaş') ?>
                  </span>
                </div>
              </div>
              <p class="text-[11px] text-neutral-400 mt-3 pt-3 border-t border-white/10">
                <?= htmlspecialchars(__('howItWorks.mockups.step4.tip') ?: '💡 WhatsApp grubuna atın, tüm arkadaşlarınız aynı anda kendi cihazından izlesin.') ?>
              </p>
            </div>
          </div>
        </div>

        <!-- ==================== STEP 5 ==================== -->
        <div class="relative flex flex-col md:flex-row gap-6 sm:gap-8 items-start">
          <!-- Step Indicator Badge -->
          <div class="flex-shrink-0 flex items-center gap-4 md:flex-col md:items-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-ink-900 border-2 border-accent/60 shadow-glow flex items-center justify-center text-accent font-display text-xl sm:text-2xl font-black ring-4 ring-ink-950">
              05
            </div>
            <span class="md:hidden text-xs font-bold uppercase tracking-wider text-accent">
              <?= htmlspecialchars(__('howItWorks.steps.5.label') ?: 'Adım 5') ?>
            </span>
          </div>

          <!-- Step Content Card -->
          <div class="flex-grow card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 w-full hover:border-accent/40 transition-all duration-300">
            <div class="hidden md:block text-xs font-bold uppercase tracking-wider text-accent mb-2">
              <?= htmlspecialchars(__('howItWorks.steps.5.label') ?: 'Adım 5') ?>
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('howItWorks.steps.5.title') ?: 'Kodu Replash\'e Girin') ?>
            </h2>
            <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-6">
              <?= htmlspecialchars(__('howItWorks.steps.5.desc') ?: 'Oyuncular web sitesine girer, maç kodunu yazar ve video anında açılır. Hesap açma, uygulama indirme veya halka açık liste yoktur.') ?>
            </p>

            <!-- Visual Mock: Quick Match Finder Box -->
            <div class="bg-ink-950/90 rounded-2xl border border-white/10 p-5">
              <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-grow w-full">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                  </div>
                  <input type="text" readonly value="AEXF-BFFG" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-ink-900 border border-accent/40 text-white font-mono text-sm tracking-wider font-bold focus:outline-none">
                </div>
                <a href="<?= base_url('find.php') ?>" class="btn-primary w-full sm:w-auto px-6 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center justify-center gap-2 shadow-glow">
                  <span><?= htmlspecialchars(__('howItWorks.mockups.step5.button') ?: __('home.findPreview.button')) ?></span>
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
              </div>
              <div class="flex items-center gap-4 mt-3 text-[11px] text-neutral-400">
                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <?= htmlspecialchars(__('howItWorks.mockups.step5.noApp') ?: 'Uygulama Yok') ?></span>
                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <?= htmlspecialchars(__('howItWorks.mockups.step5.noAccount') ?: 'Hesap Açmak Yok') ?></span>
                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <?= htmlspecialchars(__('howItWorks.mockups.step5.instantAccess') ?: 'Anında Erişim') ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== STEP 6 ==================== -->
        <div class="relative flex flex-col md:flex-row gap-6 sm:gap-8 items-start">
          <!-- Step Indicator Badge -->
          <div class="flex-shrink-0 flex items-center gap-4 md:flex-col md:items-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-ink-900 border-2 border-accent/60 shadow-glow flex items-center justify-center text-accent font-display text-xl sm:text-2xl font-black ring-4 ring-ink-950">
              06
            </div>
            <span class="md:hidden text-xs font-bold uppercase tracking-wider text-accent">
              <?= htmlspecialchars(__('howItWorks.steps.6.label') ?: 'Adım 6') ?>
            </span>
          </div>

          <!-- Step Content Card -->
          <div class="flex-grow card-elevated rounded-3xl p-6 sm:p-8 border border-white/10 w-full hover:border-accent/40 transition-all duration-300">
            <div class="hidden md:block text-xs font-bold uppercase tracking-wider text-accent mb-2">
              <?= htmlspecialchars(__('howItWorks.steps.6.label') ?: 'Adım 6') ?>
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-white mb-3">
              <?= htmlspecialchars(__('howItWorks.steps.6.title') ?: 'İzle, Klip Kes, Paylaş') ?>
            </h2>
            <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-6">
              <?= htmlspecialchars(__('howItWorks.steps.6.desc') ?: 'Oyuncular tüm maçı izler, dahili klip kesiciyle 10–45 saniyelik anları keser, telefonlarına indirir ve doğrudan sosyal medyada paylaşır.') ?>
            </p>

            <!-- Visual Mock: Video Player & Clip Highlights Trimmer -->
            <div class="bg-ink-950/90 rounded-2xl border border-white/10 p-3 sm:p-4 shadow-2xl">
              <!-- Window Header Bar -->
              <div class="flex items-center gap-2 pb-3 mb-3 border-b border-white/10 px-1">
                <div class="flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-full bg-red-500/80"></span>
                  <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                  <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                </div>
                <span class="ml-2 font-mono text-[10px] sm:text-xs uppercase tracking-widest text-neutral-400 font-bold"><?= htmlspecialchars(__('howItWorks.mockups.step6.playerTitle') ?: 'VIDEO PLAYER · CLIP & SHARE') ?></span>
              </div>

              <!-- 16:9 Video Player Viewport -->
              <div class="relative aspect-[16/9] w-full overflow-hidden rounded-xl bg-black border border-white/10 group">
                <img src="<?= asset('images/gen_football_night.jpg') ?>" alt="Match Video" class="absolute inset-0 w-full h-full object-cover opacity-90 transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/40 pointer-events-none"></div>

                <!-- Center Play Button (Concentric Rings) -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-black/40 border border-white/20 backdrop-blur-md flex items-center justify-center shadow-2xl ring-4 ring-white/5 transition-transform duration-300 hover:scale-110 cursor-pointer">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/20 backdrop-blur-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-white translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- Resolution Tag Overlay -->
                <div class="absolute top-3 left-3 flex items-center gap-1.5 bg-black/60 backdrop-blur-md px-2.5 py-1 rounded-md border border-white/10 text-[10px] font-mono text-white/90">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  <span><?= htmlspecialchars(__('howItWorks.mockups.step6.quality') ?: '1080p HD · 60 FPS') ?></span>
                </div>
              </div>

              <!-- Animated Timeline Scrubber -->
              <div class="mt-3 px-1">
                <div class="relative w-full h-1.5 bg-white/15 rounded-full overflow-hidden">
                  <!-- Moving Highlight Scrubber Line -->
                  <div class="absolute top-0 bottom-0 rounded-full bg-gradient-to-r from-amber-300 via-amber-200 to-amber-400 shadow-[0_0_12px_rgba(253,230,138,0.9)] animate-scrubber"></div>
                </div>
                <div class="flex items-center justify-between text-[10px] sm:text-xs font-mono text-neutral-400 mt-1.5">
                  <span>17:04</span>
                  <span class="text-accent/90 font-medium"><?= htmlspecialchars(__('howItWorks.mockups.step6.clipSelection') ?: '✂ 00:28 Klip Kesimi') ?></span>
                  <span>17:32</span>
                </div>
              </div>

              <!-- Action Buttons & Sharing Tags -->
              <div class="flex flex-wrap items-center justify-between gap-3 pt-3 mt-3 border-t border-white/10 px-1">
                <button type="button" class="btn-primary text-xs py-2 px-3.5 rounded-lg flex items-center gap-1.5 shadow-glow cursor-default">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                  <span><?= htmlspecialchars(__('howItWorks.mockups.step6.createClip') ?: 'Klip Oluştur (MP4)') ?></span>
                </button>

                <div class="flex items-center gap-1.5 sm:gap-2 text-neutral-400 text-xs">
                  <span class="text-[11px]"><?= htmlspecialchars(__('howItWorks.mockups.step6.share') ?: 'Paylaş:') ?></span>
                  <span class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[10px] sm:text-[11px] text-white">Instagram</span>
                  <span class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[10px] sm:text-[11px] text-white">WhatsApp</span>
                  <span class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[10px] sm:text-[11px] text-white">TikTok</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Bottom High-Impact Call to Action Block -->
    <div class="max-w-6xl mx-auto mt-20">
      <div class="card-elevated rounded-3xl p-8 sm:p-12 border border-accent/30 text-center relative overflow-hidden bg-gradient-to-b from-ink-900/90 to-ink-950/95 shadow-2xl">
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-48 bg-accent/20 blur-[90px] rounded-full pointer-events-none"></div>
        
        <div class="relative z-10 max-w-2xl mx-auto">
          <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-white mb-4">
            <?= htmlspecialchars(__('howItWorks.cta.title') ?: 'Harekete geçmeye hazır mısınız?') ?>
          </h2>
          <p class="text-neutral-300 text-sm sm:text-base leading-relaxed mb-8">
            <?= htmlspecialchars(__('home.cta.subtitle') ?: 'Kurulum ve şartlar hakkında detaylı bilgi almak için bizimle iletişime geçin.') ?>
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= base_url('pilot-program.php') ?>" class="btn-primary w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold shadow-glow flex items-center justify-center gap-2">
              <span><?= htmlspecialchars(__('howItWorks.cta.button') ?: 'Pilot Saha Başvurusu') ?></span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            <a href="<?= base_url('contact.php') ?>" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold border border-white/15 hover:border-white/30 text-white bg-white/5 hover:bg-white/10 transition-all flex items-center justify-center">
              <?= htmlspecialchars(__('nav.contact') ?: 'İletişim') ?>
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
