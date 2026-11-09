<?php
// Yenileme İşlevsellik 07: sitenin görünümünde ve dilinde "sayfa bulunamadı"
// ErrorDocument ile açıldığında asıl adresin sorgu dizesi (ör. ?lang=tr) $_GET'e aktarılır
if (empty($_GET) && !empty($_SERVER['REDIRECT_QUERY_STRING'])) parse_str($_SERVER['REDIRECT_QUERY_STRING'], $_GET);
require_once __DIR__ . '/includes/init.php';
http_response_code(404);
$pageTitle = __('yen.notFound.title');
$pageNoindex = true;
require_once __DIR__ . '/includes/header.php';
?>
<main id="main" class="relative flex min-h-[80vh] items-center bg-ink-900 pt-32 pb-24">
  <div class="container-x">
    <div class="max-w-2xl">
      <p class="rp-label text-accent"><?= e(__('yen.notFound.eyebrow')) ?></p>
      <h1 class="mt-4 font-display text-5xl font-bold leading-[1.05] tracking-tight md:text-6xl"><?= e(__('yen.notFound.title')) ?></h1>
      <p class="mt-5 text-lg text-white/80"><?= e(__('yen.notFound.text')) ?></p>
      <div class="mt-9 flex flex-col gap-3 sm:flex-row">
        <a class="inline-flex items-center justify-center rounded-full bg-accent px-7 py-3.5 text-base font-semibold text-ink-900 transition hover:bg-accent-neon active:scale-[0.98]" href="<?= base_url('find') ?>"><?= e(__('nav.find')) ?></a>
        <a class="inline-flex items-center justify-center rounded-full border border-white/15 px-7 py-3.5 text-base font-medium text-white/85 transition hover:border-white/30 hover:text-white" href="<?= base_url() ?>"><?= e(__('yen.notFound.home')) ?></a>
      </div>
      <p class="mt-8 text-sm text-white/75"><a class="underline underline-offset-4 hover:text-white" href="https://wa.me/393447598309?text=<?= urlencode(__('support.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer"><?= e(__('yen.notFound.help')) ?></a></p>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
