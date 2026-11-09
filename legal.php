<?php
// Yasal bilgiler / Note legali: hizmet sağlayıcının kimliği (AB 2000/31/AT md. 5, İtalya D.Lgs. 70/2003 md. 7)
require_once __DIR__ . '/includes/init.php';
$pageTitle = __('yen.company.title');
$metaTitle = __('yen.company.title') . '. ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
$rows = [
    [__('yen.company.company'), REPLASH_COMPANY_NAME],
    [__('yen.company.regFull'), REPLASH_COMPANY_REG],
    [__('yen.company.seat'), REPLASH_COMPANY_ADDRESS],
];
if (REPLASH_COMPANY_VAT !== '') $rows[] = [__('yen.company.vat'), REPLASH_COMPANY_VAT];
$rows[] = [__('yen.company.brand'), __('yen.company.brandV')];
?>
<main id="main" class="relative bg-ink-900">
  <section class="section-y border-t border-white/5">
    <div class="container-x mx-auto max-w-3xl">
      <h1 class="mt-3 font-display text-3xl font-bold leading-tight tracking-tight md:text-4xl"><?= e(__('yen.company.title')) ?></h1>
      <p class="mt-5 text-lg leading-relaxed text-white/80"><?= e(__('yen.company.intro')) ?></p>
      <dl class="mt-10 divide-y divide-white/10 border-y border-white/10">
        <?php foreach ($rows as [$k, $v]): ?>
          <div class="grid gap-1 py-4 sm:grid-cols-[14rem_1fr] sm:gap-6">
            <dt class="rp-label text-white/60"><?= e($k) ?></dt>
            <dd class="text-base text-white"><?= e($v) ?></dd>
          </div>
        <?php endforeach; ?>
        <div class="grid gap-1 py-4 sm:grid-cols-[14rem_1fr] sm:gap-6">
          <dt class="rp-label text-white/60"><?= e(__('yen.company.email')) ?></dt>
          <dd class="text-base"><a class="text-accent underline underline-offset-4 hover:text-accent-neon" href="mailto:<?= e(REPLASH_CONTACT_EMAIL) ?>"><?= e(REPLASH_CONTACT_EMAIL) ?></a></dd>
        </div>
      </dl>
      <p class="mt-6 text-sm text-white/75"><?= e(__('yen.company.contactNote')) ?></p>
      <p class="mt-6 text-sm"><a class="text-white/80 underline underline-offset-4 hover:text-white" href="<?= base_url('privacy') ?>"><?= e(__('footer.privacy')) ?></a> · <a class="text-white/80 underline underline-offset-4 hover:text-white" href="<?= base_url('cookie') ?>"><?= e(__('footer.cookie')) ?></a></p>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
