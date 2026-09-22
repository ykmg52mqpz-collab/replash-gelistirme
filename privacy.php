<?php
require_once __DIR__ . '/includes/init.php';

// Metin 4 dilde (replash.info IT/EN metninden). Saklama bölümü sistemle uyumlu: tüm kayıtlar 7 günde silinir.
$pageTitle = __('footer.privacy');
$metaTitle = __('footer.privacy') . '. ' . APP_NAME;

$doc = json_decode(<<<'JSON'
{
  "it": {
    "updated": "Ultimo aggiornamento: 2026",
    "title": "Informativa sulla Privacy",
    "intro": "Replash registra le partite nei centri sportivi partner tramite telecamere fisse, così i giocatori possono guardare, ritagliare e condividere la propria partita. Questa informativa spiega quali dati trattiamo e come.",
    "sections": [
      {
        "h": "Titolare del trattamento",
        "p": [
          "Replash è titolare del trattamento dei dati raccolti tramite il servizio. Per qualsiasi richiesta relativa alla privacy puoi scriverci a partnerships@replash.eu."
        ]
      },
      {
        "h": "Dati che trattiamo",
        "p": [
          "Registrazioni video dell'area di gioco durante gli orari di apertura del centro. Le telecamere inquadrano solo il campo.",
          "Non registriamo audio e non utilizziamo alcuna forma di riconoscimento facciale."
        ]
      },
      {
        "h": "Base giuridica",
        "p": [
          "Trattiamo i dati sulla base del legittimo interesse (art. 6, par. 1, lett. f del GDPR) e della necessità contrattuale (art. 6, par. 1, lett. b)."
        ]
      },
      {
        "h": "Conservazione",
        "p": [
          "Tutte le registrazioni, sbloccate o no, vengono eliminate automaticamente 7 giorni dopo la data della partita. I file che un giocatore scarica restano sul suo dispositivo."
        ]
      },
      {
        "h": "Accesso ai video",
        "p": [
          "I video sono privati e accessibili solo tramite il codice della partita consegnato dal centro. Non vengono elencati pubblicamente né indicizzati dai motori di ricerca."
        ]
      },
      {
        "h": "I tuoi diritti",
        "p": [
          "Puoi esercitare i diritti di accesso, rettifica, cancellazione e opposizione scrivendo a partnerships@replash.eu."
        ]
      },
      {
        "h": "Dove vengono trattati i dati",
        "p": [
          "Le registrazioni vengono elaborate e archiviate in data center situati nell'Unione Europea."
        ]
      }
    ]
  },
  "en": {
    "updated": "Last updated: 2026",
    "title": "Privacy Policy",
    "intro": "Replash records matches at partner sports facilities through fixed cameras, so players can watch, clip and share their match. This policy explains what data we process and how.",
    "sections": [
      {
        "h": "Data controller",
        "p": [
          "Replash is the controller of the data collected through the service. For any privacy request, contact us at partnerships@replash.eu."
        ]
      },
      {
        "h": "Data we process",
        "p": [
          "Video recordings of the playing area during the facility's opening hours. Cameras only frame the court.",
          "We do not record audio and we do not use any form of facial recognition."
        ]
      },
      {
        "h": "Legal basis",
        "p": [
          "We process data on the basis of legitimate interest (Art. 6(1)(f) GDPR) and contractual necessity (Art. 6(1)(b))."
        ]
      },
      {
        "h": "Retention",
        "p": [
          "All recordings, unlocked or not, are automatically deleted 7 days after the match date. Files a player downloads stay on their own device."
        ]
      },
      {
        "h": "Access to videos",
        "p": [
          "Videos are private and accessible only through the match code handed out by the facility. They are never publicly listed or indexed by search engines."
        ]
      },
      {
        "h": "Your rights",
        "p": [
          "You can exercise your rights of access, rectification, erasure and objection by contacting partnerships@replash.eu."
        ]
      },
      {
        "h": "Where data is processed",
        "p": [
          "Recordings are processed and stored in data centers located in the European Union."
        ]
      }
    ]
  },
  "tr": {
    "updated": "Son güncelleme: 2026",
    "title": "Gizlilik Politikası",
    "intro": "Replash, oyuncular maçlarını izleyip kesip paylaşabilsin diye iş ortağı spor tesislerindeki maçları sabit kameralarla kaydeder. Bu politika hangi verileri nasıl işlediğimizi açıklar.",
    "sections": [
      {
        "h": "Veri sorumlusu",
        "p": [
          "Hizmet aracılığıyla toplanan verilerin sorumlusu Replash’tir. Gizlilikle ilgili her talebiniz için partnerships@replash.eu adresine yazabilirsiniz."
        ]
      },
      {
        "h": "İşlediğimiz veriler",
        "p": [
          "Tesisin açık olduğu saatlerde oyun alanının video kayıtları. Kameralar yalnızca sahayı çeker.",
          "Ses kaydetmiyoruz ve hiçbir yüz tanıma yöntemi kullanmıyoruz."
        ]
      },
      {
        "h": "Hukuki dayanak",
        "p": [
          "Verileri meşru menfaat (GDPR md. 6/1-f) ve sözleşmenin ifası (md. 6/1-b) dayanaklarıyla işliyoruz."
        ]
      },
      {
        "h": "Saklama süresi",
        "p": [
          "Kilidi açılmış olsun olmasın tüm kayıtlar maç tarihinden 7 gün sonra otomatik olarak silinir. Oyuncunun indirdiği dosyalar kendi cihazında kalır."
        ]
      },
      {
        "h": "Videolara erişim",
        "p": [
          "Videolar özeldir ve yalnızca tesisin verdiği maç koduyla açılır. Herkese açık listelenmez, arama motorlarında dizine eklenmez."
        ]
      },
      {
        "h": "Haklarınız",
        "p": [
          "Erişim, düzeltme, silme ve itiraz haklarınızı partnerships@replash.eu adresine yazarak kullanabilirsiniz."
        ]
      },
      {
        "h": "Verilerin işlendiği yer",
        "p": [
          "Kayıtlar Avrupa Birliği’nde bulunan veri merkezlerinde işlenir ve saklanır."
        ]
      }
    ]
  },
  "es": {
    "updated": "Última actualización: 2026",
    "title": "Política de Privacidad",
    "intro": "Replash graba los partidos en los centros deportivos asociados mediante cámaras fijas, para que los jugadores puedan ver, recortar y compartir su partido. Esta política explica qué datos tratamos y cómo.",
    "sections": [
      {
        "h": "Responsable del tratamiento",
        "p": [
          "Replash es responsable del tratamiento de los datos recogidos a través del servicio. Para cualquier solicitud sobre privacidad, escríbenos a partnerships@replash.eu."
        ]
      },
      {
        "h": "Datos que tratamos",
        "p": [
          "Grabaciones de vídeo del área de juego durante el horario de apertura del centro. Las cámaras solo encuadran la pista.",
          "No grabamos audio y no utilizamos ninguna forma de reconocimiento facial."
        ]
      },
      {
        "h": "Base jurídica",
        "p": [
          "Tratamos los datos sobre la base del interés legítimo (art. 6.1.f del RGPD) y de la necesidad contractual (art. 6.1.b)."
        ]
      },
      {
        "h": "Conservación",
        "p": [
          "Todas las grabaciones, desbloqueadas o no, se eliminan automáticamente 7 días después de la fecha del partido. Los archivos que descarga un jugador permanecen en su propio dispositivo."
        ]
      },
      {
        "h": "Acceso a los vídeos",
        "p": [
          "Los vídeos son privados y solo se abren con el código de partido que entrega el centro. No se publican en listas ni se indexan en buscadores."
        ]
      },
      {
        "h": "Tus derechos",
        "p": [
          "Puedes ejercer tus derechos de acceso, rectificación, supresión y oposición escribiendo a partnerships@replash.eu."
        ]
      },
      {
        "h": "Dónde se tratan los datos",
        "p": [
          "Las grabaciones se procesan y almacenan en centros de datos situados en la Unión Europea."
        ]
      }
    ]
  }
}
JSON, true);
$d = $doc[$current_lang] ?? $doc['en'];

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative bg-ink-900">
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
