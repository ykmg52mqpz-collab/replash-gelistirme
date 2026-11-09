  <!-- Footer -->
  <?php if (!empty($internalShell)): ?>
  <footer class="border-t border-white/5 py-6 text-center text-xs text-white/30">&copy; <?= date('Y') ?> Replash</footer>
  <?php else: ?>
  <footer class="border-t border-white/5 bg-ink-900 mt-auto">
    <div class="container-x py-14">
      <div class="flex flex-col gap-10 md:flex-row md:justify-between">
        <div class="flex flex-col gap-4">
          <!-- Logo: sembol görsel, REPLASH yazısı sitenin başlık fontuyla (sıkı harf aralığı) -->
          <a href="<?= base_url() ?>" class="flex w-fit items-center gap-2.5" aria-label="REPLASH, home">
            <img loading="lazy" decoding="async" src="<?= asset('images/replash-logo.webp') ?>?v=2" alt="" width="232" height="256" class="h-9 w-auto select-none" draggable="false"/>
            <span class="font-display text-[1.75rem] font-bold leading-none tracking-[-0.045em] text-accent">REPLASH</span>
          </a>
          <p class="max-w-xs text-sm text-white/75 leading-relaxed"><?= e(__('footer.tagline')) ?></p>
          <div role="group" aria-label="<?= e(__('common.language')) ?>" class="inline-flex w-fit items-center rounded-full border border-white/10 bg-white/5 p-0.5 text-xs font-medium backdrop-blur">
            <?php foreach (array_values(array_intersect(['it', 'en', 'tr', 'es'], $SUPPORTED_LANGUAGES)) as $lng): $isCur = ($current_lang === $lng); ?>
              <a href="<?= e(lang_url($lng)) ?>" <?= $isCur ? 'aria-current="true"' : '' ?> hreflang="<?= e($lng) ?>" class="rounded-full px-2.5 py-1.5 tracking-wide transition <?= $isCur ? 'bg-white text-ink-900' : 'text-white/70 hover:text-white' ?>"><?= strtoupper(e($lng)) ?></a>
            <?php endforeach; ?>
          </div>
          <?php if (REPLASH_LINKEDIN_URL !== '' || REPLASH_INSTAGRAM_URL !== ''): ?>
          <!-- LinkedIn (Replash'in arkasındaki kişi) ve Instagram (@replash.it) -->
          <div class="flex flex-wrap gap-2">
          <?php if (REPLASH_LINKEDIN_URL !== ''): ?>
          <a href="<?= e(REPLASH_LINKEDIN_URL) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(__('about.linkedin')) ?>" title="<?= e(__('about.linkedin')) ?>" class="group inline-flex items-center gap-2.5 rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 text-sm font-medium text-white/60 transition hover:border-white/25 hover:bg-white/[0.06] hover:text-white">
          <svg class="h-5 w-5 flex-shrink-0 text-white/50 transition group-hover:text-accent" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
          <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
          </svg>
          LinkedIn
          </a>
          <?php endif; ?>
          <?php if (REPLASH_INSTAGRAM_URL !== ''): ?>
          <a href="<?= e(REPLASH_INSTAGRAM_URL) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(__('yen.instagram')) ?>" title="<?= e(__('yen.instagram')) ?>" class="group inline-flex items-center gap-2.5 rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 text-sm font-medium text-white/60 transition hover:border-white/25 hover:bg-white/[0.06] hover:text-white">
          <svg class="h-5 w-5 flex-shrink-0 text-white/50 transition group-hover:text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false">
          <rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/>
          </svg>
          Instagram
          </a>
          <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <nav class="flex flex-col gap-2" aria-label="<?= e(__('footer.col.pages')) ?>">
          <span class="text-xs font-semibold uppercase tracking-widest text-white/60 mb-1"><?= e(__('footer.col.pages')) ?></span>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('how-it-works') ?>"><?= e(__('footer.links.howItWorks')) ?></a>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('for-players') ?>"><?= e(__('footer.links.forPlayers')) ?></a>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('for-facilities') ?>"><?= e(__('footer.links.forFacilities')) ?></a>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('ai-analysis') ?>"><?= e(__('nav.ai')) ?></a>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('about') ?>"><?= e(__('footer.links.about')) ?></a>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('contact') ?>"><?= e(__('footer.links.contact')) ?></a>
        </nav>
        <nav class="flex flex-col gap-2" aria-label="<?= e(__('footer.col.players')) ?>">
          <span class="text-xs font-semibold uppercase tracking-widest text-white/60 mb-1"><?= e(__('footer.col.players')) ?></span>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('find') ?>"><?= e(__('footer.links.find')) ?></a>
          <a class="text-sm text-white/60 transition hover:text-white" href="https://wa.me/393447598309?text=<?= urlencode(__('support.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer"><?= e(__('footer.links.playerHelp')) ?></a>
        </nav>
        <nav class="flex flex-col gap-3" aria-label="<?= e(__('footer.col.centers')) ?>">
          <span class="text-xs font-semibold uppercase tracking-widest text-white/60"><?= e(__('footer.col.centers')) ?></span>
          <a class="inline-flex w-fit items-center justify-center rounded-full bg-accent px-6 py-2.5 text-sm font-semibold text-ink-900 transition hover:bg-accent-neon" href="<?= base_url('contact?pilot=1') ?>"><?= e(__('footer.links.pilot')) ?></a>
          <a class="text-sm text-white/60 transition hover:text-white" href="<?= base_url('panel/') ?>"><?= e(__('footer.links.centerAccess')) ?></a>
        </nav>
      </div>
      <div class="mt-12 flex flex-col items-start justify-between gap-3 border-t border-white/5 pt-6 text-xs text-white/60 md:flex-row md:items-center">
        <span>&copy; <?= date('Y') ?> <span lang="en">replash.it</span>. <?= e(__('footer.rights')) ?></span>
        <div class="flex items-center gap-5">
          <a class="hover:text-white transition" href="<?= base_url('legal') ?>"><?= e(__('yen.company.legal')) ?></a>
          <a class="hover:text-white transition" href="<?= base_url('privacy') ?>"><?= e(__('footer.privacy')) ?></a>
          <a class="hover:text-white transition" href="<?= base_url('cookie') ?>"><?= e(__('footer.cookie')) ?></a>
        </div>
      </div>
      <!-- Şirket bilgileri (AB e-ticaret kuralları) -->
      <p class="mt-4 text-xs leading-relaxed text-white/55"><?= e(str_replace('{company}', REPLASH_COMPANY_NAME, __('yen.company.serviceOf'))) ?> · <?= e(__('yen.company.reg')) ?> <?= e(REPLASH_COMPANY_REG) ?> · <?= e(REPLASH_COMPANY_ADDRESS) ?><?php if (REPLASH_COMPANY_VAT !== ''): ?> · <?= e(__('yen.company.vat')) ?> <?= e(REPLASH_COMPANY_VAT) ?><?php endif; ?> · <a class="underline underline-offset-2 hover:text-white" href="mailto:<?= e(REPLASH_CONTACT_EMAIL) ?>"><?= e(REPLASH_CONTACT_EMAIL) ?></a></p>
    </div>
  </footer>

  <!-- Floating WhatsApp CTA Button (oyuncu maçını izlerken gizli) -->
  <?php if (empty($hideWhatsApp) && $currentPage !== 'find'): ?>
  <aside aria-label="WhatsApp">
  <a href="https://wa.me/393447598309?text=<?= urlencode(in_array($currentPage, ['find', 'for-players'], true) ? __('support.whatsapp.prefill') : __('contact.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(in_array($currentPage, ['find', 'for-players'], true) ? __('support.aria') : __('contact.whatsapp.aria')) ?>" class="group fixed bottom-5 right-5 z-[70] flex h-12 w-12 items-center justify-center rounded-full bg-[#25D366] shadow-[0_4px_14px_-4px_rgba(0,0,0,0.5)] transition-transform duration-150 active:scale-95 md:bottom-6 md:right-6">
    <svg viewBox="0 0 24 24" class="relative h-6 w-6 fill-white" aria-hidden="true">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24.044 12.045.044 5.463.044.104 5.401.101 11.986c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652a11.96 11.96 0 005.71 1.454h.006c6.585 0 11.946-5.359 11.949-11.945 0-3.19-1.245-6.19-3.503-8.457"></path>
    </svg>
  </a>
  </aside>
  <?php endif; ?>
  <?php endif; ?>
  <script src="<?= asset('js/main.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
