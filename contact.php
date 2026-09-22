<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.contact');
$metaTitle = __('meta.contact.title', __('nav.contact') . '. ' . APP_NAME);
$metaDescription = __('meta.contact.description', __('contact.subtitle'));
$success = false;
$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $facility_name = trim($_POST['facility_name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $sport = trim($_POST['sport'] ?? 'padel');
    $court_count = (int)($_POST['court_count'] ?? 1);
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email)) {
        $error = __('contact.errors.required');
    } else {
        try {
            if ($pdo) {
                $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, facility_name, city, sport, court_count, message, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $facility_name, $city, $sport, $court_count, $message, $_SERVER['REMOTE_ADDR'] ?? '']);
                $success = true;
            } else {
                $success = true;
            }
        } catch (Exception $e) {
            $error = __('contact.errors.general') . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="relative bg-ink-900 pt-16">

  <section id="contact" class="section-y border-t border-white/5 bg-section-gradient">
  <div class="container-x">
  <div class="max-w-3xl mx-auto text-center"  data-reveal="up">
  <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-balance md:text-4xl lg:text-5xl">
  <?= e(__('contact.title')) ?>
  </h2>
  <p class="mt-4 text-lg text-white/70 md:text-xl">
  <?= e(__('contact.subtitle')) ?>
  </p>
  </div>
  <?php if ($success): ?>
    <div class="card-elevated mx-auto mt-12 max-w-2xl rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-center md:p-8">
      <p class="font-display text-xl font-bold text-emerald-300"><?= e(__('contact.success')) ?></p>
    </div>
  <?php else: ?>
  <?php if ($error): ?>
    <div class="mx-auto mt-12 max-w-2xl rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-center text-sm text-rose-200"><?= e($error) ?></div>
  <?php endif; ?>
  <form action="<?= base_url('contact.php') ?>" method="POST" class="card-elevated mx-auto mt-12 max-w-2xl rounded-2xl border border-white/10 bg-white/[0.03] p-6 md:p-8"  data-reveal="up">
  <div class="grid gap-5 sm:grid-cols-2">
  <div class="flex flex-col gap-2">
  <label class="text-xs font-medium uppercase tracking-wider text-white/60">
  <?= e(__('contact.fields.name')) ?>
  </label>
  <input type="text" required="" class="rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2.5 text-sm text-white placeholder:text-white/30 outline-none focus:border-accent" name="name"/>
  </div>
  <div class="flex flex-col gap-2">
  <label class="text-xs font-medium uppercase tracking-wider text-white/60">
  <?= e(__('contact.fields.facilityName')) ?>
  </label>
  <input type="text" required="" class="rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2.5 text-sm text-white placeholder:text-white/30 outline-none focus:border-accent" name="facility_name"/>
  </div>
  <div class="flex flex-col gap-2">
  <label class="text-xs font-medium uppercase tracking-wider text-white/60">
  <?= e(__('contact.fields.email')) ?>
  </label>
  <input type="email" required="" class="rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2.5 text-sm text-white placeholder:text-white/30 outline-none focus:border-accent" name="email"/>
  </div>
  <div class="flex flex-col gap-2">
  <label class="text-xs font-medium uppercase tracking-wider text-white/60">
  <?= e(__('contact.fields.phone')) ?>
  </label>
  <input type="tel" class="rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2.5 text-sm text-white placeholder:text-white/30 outline-none focus:border-accent" name="phone"/>
  </div>
  <div class="flex flex-col gap-2 sm:col-span-2">
  <label class="text-xs font-medium uppercase tracking-wider text-white/60">
  <?= e(__('contact.fields.sport')) ?>
  </label>
  <select name="sport" required="" class="rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2.5 text-sm text-white outline-none focus:border-accent">
  <option value="" disabled="" class="bg-ink-900" selected="">—</option>
  <option value="football" class="bg-ink-900">
  <?= e(__('contact.fields.sportOptions.football')) ?>
  </option>
  <option value="padel" class="bg-ink-900">
  <?= e(__('contact.fields.sportOptions.padel')) ?>
  </option>
  <option value="other" class="bg-ink-900">
  <?= e(__('contact.fields.sportOptions.other')) ?>
  </option>
  </select>
  </div>
  </div>
  <div class="mt-5 flex flex-col gap-2">
  <label class="text-xs font-medium uppercase tracking-wider text-white/60">
  <?= e(__('contact.fields.message')) ?>
  </label>
  <textarea name="message" rows="4" class="rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2.5 text-sm text-white outline-none focus:border-accent">
  </textarea>
  </div>
  <button type="submit" class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3 text-base font-semibold text-ink-900 shadow-glow transition hover:bg-accent-neon active:scale-[0.98] sm:w-auto">
  <?= e(__('contact.submit')) ?>
  </button>
  <a href="https://wa.me/393447598309?text=<?= urlencode(__('contact.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(__('contact.whatsapp.aria')) ?>" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-full border border-[#25D366]/40 bg-[#25D366]/10 px-6 py-3 text-base font-semibold text-[#25D366] transition hover:bg-[#25D366]/20 active:scale-[0.98] sm:ml-3 sm:w-auto">
  <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current" aria-hidden="true">
  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24.044 12.045.044 5.463.044.104 5.401.101 11.986c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652a11.96 11.96 0 005.71 1.454h.006c6.585 0 11.946-5.359 11.949-11.945 0-3.19-1.245-6.19-3.503-8.457">
  </path>
  </svg>
  <?= e(__('contact.whatsappSubmit')) ?>
  </a>
  <p class="mt-5 flex items-center justify-center gap-2 text-xs text-white/35">
  <svg class="h-3.5 w-3.5 text-accent/60" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd">
  </path>
  </svg>
  <?= e(__('contact.trust')) ?>
  </p>
  </form>
  </div>
  </section>
  </div>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
