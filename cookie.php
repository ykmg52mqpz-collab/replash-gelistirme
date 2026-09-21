<?php
require_once __DIR__ . '/includes/init.php';

// replash.info ile aynı: metin İtalyanca (birincil) ve İngilizce; TR/ES için İngilizce gösterilir.
$pageTitle = __('footer.cookie');
$metaTitle = __('footer.cookie') . '. ' . APP_NAME;

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative bg-ink-900">
  <?php if ($current_lang === 'it'): ?>
    <section class="section-y border-t border-white/5">
    <div class="container-x mx-auto max-w-3xl">
    <p class="text-[11px] font-semibold uppercase tracking-widest text-accent/70">Ultimo aggiornamento: 2026</p>
    <h1 class="mt-3 font-display text-3xl font-bold leading-tight tracking-tight md:text-4xl">Cookie Policy</h1>
    <p class="mt-5 text-lg leading-relaxed text-white/60">Questo sito usa un numero minimo di cookie, solo per farlo funzionare correttamente. Ecco quali e perché.</p>
    <div class="mt-12 flex flex-col gap-10">
    <div>
    <h2 class="font-display text-xl font-bold text-white">Cookie tecnici</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Usiamo solo cookie tecnici necessari al funzionamento del sito, ad esempio per ricordare la preferenza di lingua e gestire la sessione di accesso al video.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Nessuna profilazione</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Non utilizziamo cookie di profilazione, pubblicitari o di tracciamento di terze parti.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Gestione dei cookie</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Puoi bloccare o eliminare i cookie in qualsiasi momento dalle impostazioni del tuo browser. La disattivazione dei cookie tecnici può limitare alcune funzioni del sito.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Contatti</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Per qualsiasi domanda sui cookie puoi scriverci a partnerships@replash.eu.</p>
    </div>
    </div>
    </div>
    </div>
    </section>
  <?php else: ?>
    <section class="section-y border-t border-white/5">
    <div class="container-x mx-auto max-w-3xl">
    <p class="text-[11px] font-semibold uppercase tracking-widest text-accent/70">Last updated: 2026</p>
    <h1 class="mt-3 font-display text-3xl font-bold leading-tight tracking-tight md:text-4xl">Cookie Policy</h1>
    <p class="mt-5 text-lg leading-relaxed text-white/60">This site uses a minimal number of cookies, only to make it work properly. Here&#x27;s which ones and why.</p>
    <div class="mt-12 flex flex-col gap-10">
    <div>
    <h2 class="font-display text-xl font-bold text-white">Essential cookies</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">We only use technical cookies required for the site to function, for example to remember your language preference and manage the video access session.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">No profiling</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">We do not use profiling, advertising or third-party tracking cookies.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Managing cookies</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">You can block or delete cookies at any time from your browser settings. Disabling essential cookies may limit some site features.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Contact</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">For any question about cookies, contact us at partnerships@replash.eu.</p>
    </div>
    </div>
    </div>
    </div>
    </section>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
