<?php
require_once __DIR__ . '/includes/init.php';

// Metin 4 dilde (replash.info IT/EN metninden).
$pageTitle = __('footer.cookie');
$metaTitle = __('footer.cookie') . '. ' . APP_NAME;

$doc = json_decode(<<<'JSON'
{
  "it": {
    "updated": "Ultimo aggiornamento: 2026",
    "title": "Cookie Policy",
    "intro": "Questo sito usa un numero minimo di cookie, solo per farlo funzionare correttamente. Ecco quali e perché.",
    "sections": [
      {
        "h": "Cookie tecnici",
        "p": [
          "Usiamo solo cookie tecnici necessari al funzionamento del sito, ad esempio per ricordare la preferenza di lingua e gestire la sessione di accesso al video."
        ]
      },
      {
        "h": "Nessuna profilazione",
        "p": [
          "Non utilizziamo cookie di profilazione, pubblicitari o di tracciamento di terze parti."
        ]
      },
      {
        "h": "Gestione dei cookie",
        "p": [
          "Puoi bloccare o eliminare i cookie in qualsiasi momento dalle impostazioni del tuo browser. La disattivazione dei cookie tecnici può limitare alcune funzioni del sito."
        ]
      },
      {
        "h": "Contatti",
        "p": [
          "Per qualsiasi domanda sui cookie puoi scriverci a partnerships@replash.it."
        ]
      }
    ]
  },
  "tr": {
    "updated": "Son güncelleme: 2026",
    "title": "Çerez Politikası",
    "intro": "Bu site yalnızca düzgün çalışması için gereken en az sayıda çerez kullanır. Hangileri olduğu ve neden kullanıldığı aşağıdadır.",
    "sections": [
      {
        "h": "Teknik çerezler",
        "p": [
          "Yalnızca sitenin çalışması için gerekli teknik çerezleri kullanıyoruz; örneğin dil tercihinizi hatırlamak ve videoya erişim oturumunu yönetmek için."
        ]
      },
      {
        "h": "Profilleme yok",
        "p": [
          "Profilleme, reklam veya üçüncü taraf izleme çerezleri kullanmıyoruz."
        ]
      },
      {
        "h": "Çerezleri yönetme",
        "p": [
          "Çerezleri istediğiniz zaman tarayıcı ayarlarınızdan engelleyebilir veya silebilirsiniz. Teknik çerezleri kapatmak sitenin bazı işlevlerini kısıtlayabilir."
        ]
      },
      {
        "h": "İletişim",
        "p": [
          "Çerezlerle ilgili sorularınız için partnerships@replash.it adresine yazabilirsiniz."
        ]
      }
    ]
  },
  "es": {
    "updated": "Última actualización: 2026",
    "title": "Política de Cookies",
    "intro": "Este sitio usa el mínimo de cookies, solo para que funcione correctamente. Estas son y para qué sirven.",
    "sections": [
      {
        "h": "Cookies técnicas",
        "p": [
          "Solo usamos cookies técnicas necesarias para el funcionamiento del sitio, por ejemplo para recordar tu idioma y gestionar la sesión de acceso al vídeo."
        ]
      },
      {
        "h": "Sin perfiles",
        "p": [
          "No utilizamos cookies de perfilado, publicitarias ni de seguimiento de terceros."
        ]
      },
      {
        "h": "Gestión de cookies",
        "p": [
          "Puedes bloquear o eliminar las cookies en cualquier momento desde la configuración de tu navegador. Desactivar las cookies técnicas puede limitar algunas funciones del sitio."
        ]
      },
      {
        "h": "Contacto",
        "p": [
          "Para cualquier pregunta sobre cookies, escríbenos a partnerships@replash.it."
        ]
      }
    ]
  },
  "en": {
    "updated": "Last updated: 2026",
    "title": "Cookie Policy",
    "intro": "This site uses a minimal number of cookies, only to make it work properly. Here's which ones and why.",
    "sections": [
      {
        "h": "Essential cookies",
        "p": [
          "We only use technical cookies required for the site to function, for example to remember your language preference and manage the video access session."
        ]
      },
      {
        "h": "No profiling",
        "p": [
          "We do not use profiling, advertising or third-party tracking cookies."
        ]
      },
      {
        "h": "Managing cookies",
        "p": [
          "You can block or delete cookies at any time from your browser settings. Disabling essential cookies may limit some site features."
        ]
      },
      {
        "h": "Contact",
        "p": [
          "For any question about cookies, contact us at partnerships@replash.it."
        ]
      }
    ]
  }
}
JSON, true);
$d = $doc[$current_lang] ?? $doc['en'];

require_once __DIR__ . '/includes/header.php';
?>

<main id="main" class="relative bg-ink-900">
  <section class="section-y border-t border-white/5">
    <div class="container-x mx-auto max-w-3xl">
      <p class="text-[11px] font-semibold uppercase tracking-widest text-accent/70"><?= e($d['updated']) ?></p>
      <h1 class="mt-3 font-display text-3xl font-bold leading-tight tracking-tight md:text-4xl"><?= e($d['title']) ?></h1>
      <p class="mt-5 text-lg leading-relaxed text-white/60"><?= e($d['intro']) ?></p>
      <div class="mt-12 flex flex-col gap-10">
        <?php foreach ($d['sections'] as $sec): ?>
          <div>
            <h2 class="font-display text-xl font-bold text-white"><?= e($sec['h']) ?></h2>
            <div class="mt-3 flex flex-col gap-3">
              <?php foreach ($sec['p'] as $para): ?>
                <p class="text-base leading-relaxed text-white/60"><?= e($para) ?></p>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
