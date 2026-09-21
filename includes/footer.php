  <!-- Footer -->
  <footer class="border-t border-white/5 bg-ink-900 mt-auto">
    <div class="container-x py-14">
      <div class="flex flex-col gap-10 md:flex-row md:justify-between">
        <div class="flex flex-col gap-4">
          <div class="flex items-center gap-2.5">
            <img src="<?= asset('images/replash-logo.png') ?>" alt="REPLASH" class="h-12 w-auto select-none" draggable="false"/>
            <span lang="en" style="font-weight:600" class="font-display text-2xl tracking-tight text-accent">replash</span>
          </div>
          <p class="max-w-xs text-sm text-white/50 leading-relaxed"><?= e(__('footer.tagline')) ?></p>
          <div role="group" aria-label="Language" class="inline-flex w-fit items-center rounded-full border border-white/10 bg-white/5 p-0.5 text-xs font-medium backdrop-blur">
            <?php foreach (array_values(array_intersect(['it', 'en', 'tr', 'es'], $SUPPORTED_LANGUAGES)) as $lng): $isCur = ($current_lang === $lng); ?>
              <a href="<?= e(lang_url($lng)) ?>" aria-pressed="<?= $isCur ? 'true' : 'false' ?>" hreflang="<?= e($lng) ?>" class="rounded-full px-2.5 py-1.5 tracking-wide transition <?= $isCur ? 'bg-white text-ink-900' : 'text-white/70 hover:text-white' ?>"><?= strtoupper(e($lng)) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <nav class="flex flex-col gap-2">
          <span class="text-xs font-semibold uppercase tracking-widest text-white/25 mb-1">Pages</span>
          <a class="text-sm text-white/50 transition hover:text-white" href="<?= base_url('how-it-works.php') ?>"><?= e(__('footer.links.howItWorks')) ?></a>
          <a class="text-sm text-white/50 transition hover:text-white" href="<?= base_url('for-facilities.php') ?>"><?= e(__('footer.links.forFacilities')) ?></a>
          <a class="text-sm text-white/50 transition hover:text-white" href="<?= base_url('for-players.php') ?>"><?= e(__('footer.links.forPlayers')) ?></a>
          <a class="text-sm text-white/50 transition hover:text-white" href="<?= base_url('about.php') ?>"><?= e(__('footer.links.about')) ?></a>
          <a class="text-sm text-white/50 transition hover:text-white" href="<?= base_url('contact.php') ?>"><?= e(__('footer.links.contact')) ?></a>
        </nav>
        <nav class="flex flex-col gap-2">
          <span class="text-xs font-semibold uppercase tracking-widest text-white/25 mb-1"><?= e(__('nav.forPlayers')) ?></span>
          <a class="text-sm text-white/50 transition hover:text-white" href="<?= base_url('find.php') ?>"><?= e(__('footer.links.find')) ?></a>
        </nav>
        <div class="flex flex-col gap-4">
          <span class="text-xs font-semibold uppercase tracking-widest text-white/25">Get Started</span>
          <a class="inline-flex w-fit items-center justify-center rounded-full bg-accent px-6 py-2.5 text-sm font-semibold text-ink-900 shadow-glow transition hover:bg-accent-neon" href="<?= base_url('contact.php') ?>"><?= e(__('nav.bookDemo')) ?></a>
        </div>
      </div>
      <div class="mt-12 flex flex-col items-start justify-between gap-3 border-t border-white/5 pt-6 text-xs text-white/35 md:flex-row md:items-center">
        <span>&copy; <?= date('Y') ?> <span lang="en">replash.it</span>. <?= e(__('footer.rights')) ?></span>
        <div class="flex items-center gap-5">
          <a class="hover:text-white transition" href="<?= base_url('privacy.php') ?>"><?= e(__('footer.privacy')) ?></a>
          <a class="hover:text-white transition" href="<?= base_url('cookie.php') ?>"><?= e(__('footer.cookie')) ?></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp CTA Button -->
  <a href="https://wa.me/393447598309?text=<?= urlencode(__('contact.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(__('contact.whatsapp.aria')) ?>" class="group fixed bottom-5 right-5 z-[70] flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] shadow-[0_8px_30px_-6px_rgba(37,211,102,0.6)] transition-all duration-300 hover:scale-110 active:scale-95 md:bottom-6 md:right-6">
    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#25D366] opacity-30"></span>
    <svg viewBox="0 0 24 24" class="relative h-7 w-7 fill-white" aria-hidden="true">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24.044 12.045.044 5.463.044.104 5.401.101 11.986c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652a11.96 11.96 0 005.71 1.454h.006c6.585 0 11.946-5.359 11.949-11.945 0-3.19-1.245-6.19-3.503-8.457"></path>
    </svg>
  </a>

  <script src="<?= asset('js/main.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
