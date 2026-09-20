<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = __('nav.contact');
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

<main class="min-h-screen pt-28 pb-20 relative">
  <div class="hero-glow"></div>

  <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10">
    
    <!-- Hero Title -->
    <div class="text-center mb-12">
      <span class="text-xs font-semibold uppercase tracking-wider text-accent"><?= e(__('contact.hero.eyebrow')) ?></span>
      <h1 class="font-display text-4xl sm:text-5xl font-bold text-white mt-2 mb-4">
        <?= e(__('contact.hero.title')) ?>
      </h1>
      <p class="text-neutral-400 text-sm sm:text-base">
        <?= e(__('contact.hero.subtitle')) ?>
      </p>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($success): ?>
      <div class="card-elevated rounded-3xl p-8 border border-emerald-500/30 bg-emerald-500/10 text-center mb-8">
        <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="font-display text-xl font-bold text-white mb-1"><?= e(__('contact.success.title')) ?></h3>
        <p class="text-sm text-neutral-300"><?= e(__('contact.success.desc')) ?></p>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-center text-rose-300 text-sm font-medium mb-8">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <!-- Contact / Lead Form -->
    <div class="card-elevated rounded-3xl p-8 sm:p-10 border border-white/10 bg-ink-800/90">
      <form action="<?= base_url('contact.php') ?>" method="POST" class="space-y-6">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.name')) ?></label>
            <input type="text" name="name" required class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.email')) ?></label>
            <input type="email" name="email" required class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.phone')) ?></label>
            <input type="tel" name="phone" placeholder="+90 5XX XXX XX XX" class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.facility_name')) ?></label>
            <input type="text" name="facility_name" class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.city')) ?></label>
            <input type="text" name="city" placeholder="<?= e(__('contact.form.cityPlaceholder')) ?>" class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.sport')) ?></label>
            <select name="sport" class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none">
              <option value="padel"><?= e(__('contact.form.sports.padel')) ?></option>
              <option value="football"><?= e(__('contact.form.sports.football')) ?></option>
              <option value="both"><?= e(__('contact.form.sports.both')) ?></option>
              <option value="other"><?= e(__('contact.form.sports.other')) ?></option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.court_count')) ?></label>
            <input type="number" name="court_count" value="2" min="1" max="50" class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2"><?= e(__('contact.form.message')) ?></label>
          <textarea name="message" rows="4" placeholder="<?= e(__('contact.form.messagePlaceholder')) ?>" class="w-full rounded-xl border border-white/10 bg-ink-900 px-4 py-3 text-sm text-white focus:border-accent focus:outline-none"></textarea>
        </div>

        <button type="submit" class="w-full rounded-xl bg-accent py-3.5 text-sm font-semibold text-ink-900 shadow-glow hover:bg-accent-neon transition">
          <?= e(__('contact.form.submit')) ?> &rarr;
        </button>

      </form>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
