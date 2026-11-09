<?php
require_once __DIR__ . '/includes/init.php';

// Metin 4 dilde (replash.info IT/EN metninden). Saklama bölümü sistemle uyumlu: kilidi açılmayan kayıtlar maç tarihinden 3 gün, kilidi açılanlar 7 gün sonra silinir. Ses kaydı ve AB veri merkezi ifadeleri doğrulanamadığı için çıkarıldı.
$pageTitle = __('footer.privacy');
$metaTitle = __('footer.privacy') . '. ' . APP_NAME;

$doc = json_decode(<<<'JSON'
{
  "it": {
    "updated": "Ultimo aggiornamento: novembre 2026",
    "title": "Informativa sulla Privacy",
    "intro": "Replash registra le partite nei centri sportivi partner tramite telecamere fisse, così i giocatori possono guardare, ritagliare e condividere la propria partita. Questa informativa spiega quali dati trattiamo e come.",
    "sections": [
      {
        "h": "Titolare del trattamento",
        "p": [
          "Il titolare del trattamento è OMSA Group OÜ (codice di registro 17637078, Registro commerciale dell’Estonia), Ruunaoja tn 3, 11415 Tallinn, Estonia, che gestisce il servizio Replash. Per qualsiasi richiesta relativa alla privacy puoi scriverci a partnerships@replash.it."
        ]
      },
      {
        "h": "Avviso sul campo e consenso del giocatore",
        "p": [
          "All'ingresso di ogni campo ripreso è esposto un cartello che segnala la presenza della telecamera. Chi ha pagato il campo accetta che la partita venga registrata.",
          "Per guardare la partita sul sito, il giocatore conferma di aver giocato nella partita del codice e di sapere che è stata registrata, che il gruppo è d'accordo con l'acquisto del video e di aver letto questa informativa. Senza questa conferma il video non viene mostrato. Non chiediamo il nome. Conserviamo data e ora della conferma, la partita a cui si riferisce e un identificativo cifrato della connessione per 12 mesi, come prova della conferma; poi vengono cancellati automaticamente."
        ]
      },
      {
        "h": "Dati che trattiamo",
        "p": [
          "Registrazioni video dell'area di gioco durante gli orari di apertura del centro. Le telecamere inquadrano solo il campo.",
          "Replash non utilizza alcuna forma di riconoscimento facciale."
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
          "Le registrazioni non sbloccate vengono eliminate automaticamente 3 giorni dopo la data della partita; quelle sbloccate dopo 7 giorni. Sbloccare una partita non fa ripartire il conteggio. I file che un giocatore scarica restano sul suo dispositivo."
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
          "Puoi esercitare i diritti di accesso, rettifica, cancellazione e opposizione scrivendo a partnerships@replash.it."
        ]
      }
    ]
  },
  "en": {
    "updated": "Last updated: November 2026",
    "title": "Privacy Policy",
    "intro": "Replash records matches at partner sports facilities through fixed cameras, so players can watch, clip and share their match. This policy explains what data we process and how.",
    "sections": [
      {
        "h": "Data controller",
        "p": [
          "The data controller is OMSA Group OÜ (registry code 17637078, Estonian Commercial Register), Ruunaoja tn 3, 11415 Tallinn, Estonia, which operates the Replash service. For any privacy request, contact us at partnerships@replash.it."
        ]
      },
      {
        "h": "Notice at the court and player consent",
        "p": [
          "A sign at the entrance of every filmed court shows that a camera is in use. Anyone who has paid for the court accepts that the match is recorded.",
          "To watch the match on the site, the player confirms having played in the match for the code and knowing it was recorded, that the group agrees to buy the video and having read this notice. Without this confirmation the video is not shown. We do not ask for a name. We keep the date and time of the confirmation, the match it refers to and an encrypted connection identifier for 12 months as proof of the confirmation; they are then deleted automatically."
        ]
      },
      {
        "h": "Data we process",
        "p": [
          "Video recordings of the playing area during the facility's opening hours. Cameras only frame the court.",
          "Replash does not use any form of facial recognition."
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
          "Recordings that are not unlocked are automatically deleted 3 days after the match date; unlocked ones after 7 days. Unlocking a match does not restart the count. Files a player downloads stay on their own device."
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
          "You can exercise your rights of access, rectification, erasure and objection by contacting partnerships@replash.it."
        ]
      }
    ]
  },
  "tr": {
    "updated": "Son güncelleme: Kasım 2026",
    "title": "Gizlilik Politikası",
    "intro": "Replash, oyuncular maçlarını izleyip kesip paylaşabilsin diye iş ortağı spor tesislerindeki maçları sabit kameralarla kaydeder. Bu politika hangi verileri nasıl işlediğimizi açıklar.",
    "sections": [
      {
        "h": "Veri sorumlusu",
        "p": [
          "Veri sorumlusu, Replash hizmetini işleten OMSA Group OÜ’dür (sicil kodu 17637078, Estonya Ticaret Sicili), Ruunaoja tn 3, 11415 Tallinn, Estonya. Gizlilikle ilgili her talebin için partnerships@replash.it adresine yazabilirsin."
        ]
      },
      {
        "h": "Saha girişi uyarısı ve oyuncu onayı",
        "p": [
          "Kayıt yapılan her sahanın girişinde kamera bulunduğunu belirten bir uyarı levhası asılıdır. Sahayı ödeyen kişi, maçın kaydedilmesini kabul etmiş olur.",
          "Maçı sitede izlemek için oyuncu, bu koda ait maçta oynadığını ve kaydedildiğini bildiğini, grubun videoyu satın almayı kabul ettiğini ve bu bildirimi okuduğunu onaylar. Bu onay olmadan video gösterilmez. Ad istemiyoruz. Onayın tarih ve saatini, ilgili maçı ve bağlantının şifrelenmiş bir tanımlayıcısını onayın kanıtı olarak 12 ay saklarız; sonra otomatik olarak silinir."
        ]
      },
      {
        "h": "İşlediğimiz veriler",
        "p": [
          "Tesisin açık olduğu saatlerde oyun alanının video kayıtları. Kameralar yalnızca sahayı çeker.",
          "Replash hiçbir yüz tanıma yöntemi kullanmaz."
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
          "Kilidi açılmayan kayıtlar maç tarihinden 3 gün, kilidi açılanlar 7 gün sonra otomatik olarak silinir. Bir maçın kilidini açmak süreyi yeniden başlatmaz. Oyuncunun indirdiği dosyalar kendi cihazında kalır."
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
          "Erişim, düzeltme, silme ve itiraz haklarınızı partnerships@replash.it adresine yazarak kullanabilirsiniz."
        ]
      }
    ]
  },
  "es": {
    "updated": "Última actualización: noviembre 2026",
    "title": "Política de Privacidad",
    "intro": "Replash graba los partidos en los centros deportivos asociados mediante cámaras fijas, para que los jugadores puedan ver, recortar y compartir su partido. Esta política explica qué datos tratamos y cómo.",
    "sections": [
      {
        "h": "Responsable del tratamiento",
        "p": [
          "El responsable del tratamiento es OMSA Group OÜ (código de registro 17637078, Registro Mercantil de Estonia), Ruunaoja tn 3, 11415 Tallinn, Estonia, que gestiona el servicio Replash. Para cualquier solicitud sobre privacidad, escríbenos a partnerships@replash.it."
        ]
      },
      {
        "h": "Aviso en la pista y consentimiento del jugador",
        "p": [
          "En la entrada de cada pista grabada hay un cartel que indica la presencia de una cámara. Quien ha pagado la pista acepta que el partido se grabe.",
          "Para ver el partido en el sitio, el jugador confirma que jugó en el partido del código y que sabe que fue grabado, que el grupo está de acuerdo con la compra del vídeo y que ha leído esta información. Sin esta confirmación no se muestra el vídeo. No pedimos el nombre. Guardamos la fecha y hora de la confirmación, el partido al que se refiere y un identificador cifrado de la conexión durante 12 meses como prueba de la confirmación; después se eliminan automáticamente."
        ]
      },
      {
        "h": "Datos que tratamos",
        "p": [
          "Grabaciones de vídeo del área de juego durante el horario de apertura del centro. Las cámaras solo encuadran la pista.",
          "Replash no utiliza ninguna forma de reconocimiento facial."
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
          "Las grabaciones sin desbloquear se eliminan automáticamente 3 días después de la fecha del partido; las desbloqueadas, a los 7 días. Desbloquear un partido no reinicia la cuenta. Los archivos que descarga un jugador permanecen en su propio dispositivo."
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
          "Puedes ejercer tus derechos de acceso, rectificación, supresión y oposición escribiendo a partnerships@replash.it."
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
