<?php
require_once __DIR__ . '/includes/init.php';

// replash.info ile aynı: metin İtalyanca (birincil) ve İngilizce; TR/ES için İngilizce gösterilir.
$pageTitle = __('footer.privacy');
$metaTitle = __('footer.privacy') . '. ' . APP_NAME;

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative bg-ink-900">
  <?php if ($current_lang === 'it'): ?>
    <section class="section-y border-t border-white/5">
    <div class="container-x mx-auto max-w-3xl">
    <p class="text-[11px] font-semibold uppercase tracking-widest text-accent/70">Ultimo aggiornamento: 2026</p>
    <h1 class="mt-3 font-display text-3xl font-bold leading-tight tracking-tight md:text-4xl">Informativa sulla Privacy</h1>
    <p class="mt-5 text-lg leading-relaxed text-white/60">Replash registra le partite nei centri sportivi partner tramite telecamere fisse, così i giocatori possono guardare, ritagliare e condividere la propria partita. Questa informativa spiega quali dati trattiamo e come.</p>
    <div class="mt-12 flex flex-col gap-10">
    <div>
    <h2 class="font-display text-xl font-bold text-white">Titolare del trattamento</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Replash è titolare del trattamento dei dati raccolti tramite il servizio. Per qualsiasi richiesta relativa alla privacy puoi scriverci a partnerships@replash.eu.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Dati che trattiamo</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Registrazioni video dell&#x27;area di gioco durante gli orari di apertura del centro. Le telecamere inquadrano solo il campo.</p>
    <p class="text-base leading-relaxed text-white/60">Non registriamo audio e non utilizziamo alcuna forma di riconoscimento facciale.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Base giuridica</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Trattiamo i dati sulla base del legittimo interesse (art. 6, par. 1, lett. f del GDPR) e della necessità contrattuale (art. 6, par. 1, lett. b).</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Conservazione</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Le registrazioni vengono eliminate automaticamente entro 7 giorni, salvo quelle che il centro abilita su richiesta della squadra per la visione.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Accesso ai video</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">I video sono privati e accessibili solo tramite il codice della partita consegnato dal centro. Non vengono elencati pubblicamente né indicizzati dai motori di ricerca.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">I tuoi diritti</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Puoi esercitare i diritti di accesso, rettifica, cancellazione e opposizione scrivendo a partnerships@replash.eu.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Dove vengono trattati i dati</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Le registrazioni vengono elaborate e archiviate in data center situati nell&#x27;Unione Europea.</p>
    </div>
    </div>
    </div>
    </div>
    </section>
  <?php else: ?>
    <section class="section-y border-t border-white/5">
    <div class="container-x mx-auto max-w-3xl">
    <p class="text-[11px] font-semibold uppercase tracking-widest text-accent/70">Last updated: 2026</p>
    <h1 class="mt-3 font-display text-3xl font-bold leading-tight tracking-tight md:text-4xl">Privacy Policy</h1>
    <p class="mt-5 text-lg leading-relaxed text-white/60">Replash records matches at partner sports facilities through fixed cameras, so players can watch, clip and share their match. This policy explains what data we process and how.</p>
    <div class="mt-12 flex flex-col gap-10">
    <div>
    <h2 class="font-display text-xl font-bold text-white">Data controller</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Replash is the controller of the data collected through the service. For any privacy request, contact us at partnerships@replash.eu.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Data we process</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Video recordings of the playing area during the facility&#x27;s opening hours. Cameras only frame the court.</p>
    <p class="text-base leading-relaxed text-white/60">We do not record audio and we do not use any form of facial recognition.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Legal basis</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">We process data on the basis of legitimate interest (Art. 6(1)(f) GDPR) and contractual necessity (Art. 6(1)(b)).</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Retention</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Recordings are automatically deleted within 7 days, except those the facility enables for viewing at the team&#x27;s request.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Access to videos</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Videos are private and accessible only through the match code handed out by the facility. They are never publicly listed or indexed by search engines.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Your rights</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">You can exercise your rights of access, rectification, erasure and objection by contacting partnerships@replash.eu.</p>
    </div>
    </div>
    <div>
    <h2 class="font-display text-xl font-bold text-white">Where data is processed</h2>
    <div class="mt-3 flex flex-col gap-3">
    <p class="text-base leading-relaxed text-white/60">Recordings are processed and stored in data centers located in the European Union.</p>
    </div>
    </div>
    </div>
    </div>
    </section>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
