<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.contact');
$metaTitle = __('meta.contact.title');
$metaDescription = __('meta.contact.description');

$sportOptions = ['football', 'padel', 'tennis', 'other'];
$isPilot = (($_GET['pilot'] ?? $_POST['pilot'] ?? '') === '1');
$success = false;
$error = null;          // genel hata (sunucu/gönderim)
$fieldErrors = [];      // alan => mesaj
$old = ['name' => '', 'facility_name' => '', 'email' => '', 'phone' => '', 'city' => '', 'court_count' => '', 'message' => '', 'sports' => []];

// Yenileme İşlevsellik 05: hesaplayıcıdan gelen seçimler formu doldurur (?pilot=1&sport=padel,football&courts=4&est=312)
$calcIn = null;
$calcSportMap = ['football' => 'football', 'football11' => 'football', 'padel' => 'padel', 'tennis' => 'tennis'];
$calcRaw = (string)($_GET['sport'] ?? $_POST['calc_sport'] ?? '');
if ($calcRaw !== '') {
    $calcKeys = array_values(array_unique(array_filter(array_map(function ($k) use ($calcSportMap) { return $calcSportMap[trim($k)] ?? null; }, explode(',', $calcRaw)))));
    $calcCourts = max(0, min(999, (int)($_GET['courts'] ?? $_POST['calc_courts'] ?? 0)));
    $calcEst = max(0, min(1000000, (int)($_GET['est'] ?? $_POST['calc_est'] ?? 0)));
    if ($calcKeys) {
        $calcIn = ['sports' => $calcKeys, 'courts' => $calcCourts, 'est' => $calcEst];
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $old['sports'] = $calcKeys;
            if ($calcCourts > 0) $old['court_count'] = (string)$calcCourts;
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    foreach (['name', 'facility_name', 'email', 'phone', 'city', 'court_count', 'message'] as $f) {
        $old[$f] = trim((string)($_POST[$f] ?? ''));
    }
    $old['sports'] = array_values(array_intersect($sportOptions, (array)($_POST['sports'] ?? [])));
    $courts = max(1, min(999, (int)$old['court_count']));

    if (trim((string)($_POST['website'] ?? '')) !== '') {
        // Honeypot: botlar doldurur. Bota başarı gösterilir, hiçbir şey kaydedilmez.
        $success = true;
    } else {
        foreach (['name', 'facility_name', 'email', 'city'] as $f) {
            if ($old[$f] === '') $fieldErrors[$f] = __('contact.errors.field');
        }
        if (!isset($fieldErrors['email']) && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $fieldErrors['email'] = __('contact.errors.email');
        if (!$old['sports']) $fieldErrors['sports'] = __('contact.errors.sports');

        if (!$fieldErrors) {
            if (!$pdo) {
                $error = __('contact.errors.general');
            } else {
                try {
                    $msg = ($isPilot ? '[Centro pilota] ' : '') . ($calcIn ? '[Calcolatore: ' . implode('+', $calcIn['sports']) . ', ' . $calcIn['courts'] . ' campi, ~€' . $calcIn['est'] . '/mese] ' : '') . mb_substr($old['message'], 0, 4000);
                    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, facility_name, city, sport, court_count, message, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $ok = $stmt->execute([
                        mb_substr($old['name'], 0, 128), mb_substr($old['email'], 0, 128), mb_substr($old['phone'], 0, 64),
                        mb_substr($old['facility_name'], 0, 128), mb_substr($old['city'], 0, 128),
                        implode(',', $old['sports']), $courts, $msg, $_SERVER['REMOTE_ADDR'] ?? '',
                    ]);
                    if ($ok) $success = true; else $error = __('contact.errors.general');
                } catch (Exception $e) {
                    error_log('contact.php: ' . $e->getMessage());
                    $error = __('contact.errors.general');   // teknik ayrıntı kullanıcıya gösterilmez
                }
            }
        }
    }
}

$fieldClass = 'rounded-lg border bg-white/[0.04] px-3 py-3 text-base text-white placeholder:text-white/30 outline-none focus:border-accent';
$errId = function ($f) use ($fieldErrors) { return isset($fieldErrors[$f]) ? ' aria-invalid="true" aria-describedby="err-' . $f . '"' : ''; };
$bd = function ($f) use ($fieldErrors) { return isset($fieldErrors[$f]) ? 'border-red-400' : 'border-white/10'; };

require_once __DIR__ . '/includes/header.php';
?>

<main id="main" class="relative bg-ink-900 pt-16">

  <section id="contact" class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-2xl">
  <h1 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl"><?= e(__('contact.title')) ?></h1>
  <p class="mt-4 text-lg text-white/80 md:text-xl"><?= e(__('contact.subtitle')) ?></p>
  </div>

  <?php if ($success): ?>
    <div role="status" class="mt-12 max-w-2xl rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-center md:p-8">
      <h2 class="font-display text-2xl font-bold text-emerald-300"><?= e(__('contact.successTitle')) ?></h2>
      <p class="mt-2 text-white/80"><?= e(__('contact.success')) ?></p>
    </div>
  <?php else: ?>
    <?php if ($error || $fieldErrors): ?>
      <div role="alert" id="form-error" tabindex="-1" class="mt-12 max-w-2xl rounded-2xl border border-rose-400/40 bg-rose-500/10 p-4 text-sm font-semibold text-rose-100">
        <?= e($error ?: __('contact.errors.summary')) ?>
      </div>
    <?php endif; ?>
    <?php if ($isPilot): ?>
      <p class="mt-6 max-w-2xl rounded-xl border border-accent/30 bg-accent/10 px-4 py-3 text-sm font-semibold text-accent"><?= e(__('contact.pilotBanner')) ?></p>
    <?php endif; ?>

    <form action="<?= base_url('contact' . ($isPilot ? '?pilot=1' : '')) ?>" method="POST" class="card-elevated mt-8 max-w-2xl rounded-2xl border border-white/10 bg-white/[0.03] p-6 md:p-8">
      <?php if ($isPilot): ?><input type="hidden" name="pilot" value="1"><?php endif; ?>
      <?php if ($calcIn): ?>
        <input type="hidden" name="calc_sport" value="<?= e(implode(',', $calcIn['sports'])) ?>"><input type="hidden" name="calc_courts" value="<?= (int)$calcIn['courts'] ?>"><input type="hidden" name="calc_est" value="<?= (int)$calcIn['est'] ?>">
        <!-- Hesaplayıcı özeti (Yenileme İşlevsellik 05) -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3">
          <p class="text-sm text-white/80"><span class="rp-label mr-2 text-white/60"><?= e(__('yen.calc.from')) ?></span>
            <?= e(implode(' + ', array_map(function ($k) { return __('contact.fields.sportOptions.' . $k); }, $calcIn['sports']))) ?><?php if ($calcIn['courts']): ?> · <?= (int)$calcIn['courts'] ?> <?= e(__('yen.calc.courts')) ?><?php endif; ?><?php if ($calcIn['est']): ?> · <?= e(__('yen.calc.est')) ?> <span class="font-mono font-bold text-accent">€<?= number_format($calcIn['est'], 0, ',', '.') ?></span> <?= e(__('yen.calc.month')) ?><?php endif; ?></p>
          <a class="text-sm font-semibold text-accent hover:text-accent-neon" href="<?= base_url('for-facilities#calculator') ?>"><?= e(__('yen.calc.change')) ?></a>
        </div>
      <?php endif; ?>
      <div class="grid gap-5 sm:grid-cols-2">
        <div class="flex flex-col gap-2">
          <label for="f-name" class="text-sm font-semibold text-white/80"><?= e(__('contact.fields.name')) ?></label>
          <input id="f-name" name="name" type="text" required autocomplete="name" value="<?= e($old['name']) ?>" class="<?= $fieldClass ?> <?= $bd('name') ?>"<?= $errId('name') ?>>
          <?php if (isset($fieldErrors['name'])): ?><p id="err-name" class="text-xs text-rose-300"><?= e($fieldErrors['name']) ?></p><?php endif; ?>
        </div>
        <div class="flex flex-col gap-2">
          <label for="f-facility" class="text-sm font-semibold text-white/80"><?= e(__('contact.fields.facilityName')) ?></label>
          <input id="f-facility" name="facility_name" type="text" required autocomplete="organization" value="<?= e($old['facility_name']) ?>" class="<?= $fieldClass ?> <?= $bd('facility_name') ?>"<?= $errId('facility_name') ?>>
          <?php if (isset($fieldErrors['facility_name'])): ?><p id="err-facility_name" class="text-xs text-rose-300"><?= e($fieldErrors['facility_name']) ?></p><?php endif; ?>
        </div>
        <div class="flex flex-col gap-2">
          <label for="f-email" class="text-sm font-semibold text-white/80"><?= e(__('contact.fields.email')) ?></label>
          <input id="f-email" name="email" type="email" required autocomplete="email" value="<?= e($old['email']) ?>" class="<?= $fieldClass ?> <?= $bd('email') ?>"<?= $errId('email') ?>>
          <?php if (isset($fieldErrors['email'])): ?><p id="err-email" class="text-xs text-rose-300"><?= e($fieldErrors['email']) ?></p><?php endif; ?>
        </div>
        <div class="flex flex-col gap-2">
          <label for="f-phone" class="text-sm font-semibold text-white/80"><?= e(__('contact.fields.phone')) ?></label>
          <input id="f-phone" name="phone" type="tel" autocomplete="tel" value="<?= e($old['phone']) ?>" class="<?= $fieldClass ?> border-white/10">
        </div>
        <div class="flex flex-col gap-2">
          <label for="f-city" class="text-sm font-semibold text-white/80"><?= e(__('contact.fields.city')) ?></label>
          <input id="f-city" name="city" type="text" required autocomplete="address-level2" value="<?= e($old['city']) ?>" class="<?= $fieldClass ?> <?= $bd('city') ?>"<?= $errId('city') ?>>
          <?php if (isset($fieldErrors['city'])): ?><p id="err-city" class="text-xs text-rose-300"><?= e($fieldErrors['city']) ?></p><?php endif; ?>
        </div>
        <div class="flex flex-col gap-2">
          <label for="f-courts" class="text-sm font-semibold text-white/80"><?= e(__('contact.fields.courts')) ?></label>
          <input id="f-courts" name="court_count" type="number" inputmode="numeric" min="1" max="999" value="<?= e($old['court_count']) ?>" class="<?= $fieldClass ?> border-white/10">
        </div>
      </div>

      <fieldset class="mt-5" <?= isset($fieldErrors['sports']) ? 'aria-describedby="err-sports"' : '' ?>>
        <legend class="mb-2 text-sm font-semibold text-white/80"><?= e(__('contact.fields.sports')) ?></legend>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($sportOptions as $sp): $on = in_array($sp, $old['sports'], true); ?>
            <label>
              <input type="checkbox" name="sports[]" value="<?= e($sp) ?>" class="peer sr-only" <?= $on ? 'checked' : '' ?>>
              <span class="inline-flex min-h-[44px] cursor-pointer items-center rounded-full border <?= isset($fieldErrors['sports']) ? 'border-red-400' : 'border-white/20' ?> px-4 text-sm font-semibold text-white/75 transition peer-checked:border-accent peer-checked:bg-accent peer-checked:text-ink-900 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent"><?= e(__('contact.fields.sportOptions.' . $sp)) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
        <?php if (isset($fieldErrors['sports'])): ?><p id="err-sports" class="mt-2 text-xs text-rose-300"><?= e($fieldErrors['sports']) ?></p><?php endif; ?>
      </fieldset>

      <div class="mt-5 flex flex-col gap-2">
        <label for="f-message" class="text-sm font-semibold text-white/80"><?= e(__('contact.fields.message')) ?></label>
        <textarea id="f-message" name="message" rows="4" class="<?= $fieldClass ?> border-white/10"><?= e($old['message']) ?></textarea>
      </div>

      <!-- Honeypot: kullanıcıya görünmez -->
      <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <button type="submit" class="mt-7 inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3 text-base font-semibold text-ink-900 transition hover:bg-accent-neon active:scale-[0.98] sm:w-auto"><?= e(__('contact.submit')) ?></button>
      <a href="https://wa.me/393447598309?text=<?= urlencode(__('contact.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-full border border-white/20 px-6 py-3 text-base font-medium text-white/80 transition hover:border-white/40 hover:text-white sm:ml-3 sm:mt-7 sm:w-auto"><?= e(__('contact.whatsappSubmit')) ?></a>
      <p class="mt-5 text-sm text-white/75"><?= e(__('contact.trust')) ?></p>
    </form>
    <?php if ($error || $fieldErrors): ?><script>var fe=document.getElementById('form-error'); if (fe) fe.focus();</script><?php endif; ?>
  <?php endif; ?>
  </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
