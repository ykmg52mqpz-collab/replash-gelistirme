<?php
/**
 * Maç kodu kartı (Yenileme İşlevsellik 02–04): dört rakam kutusu, son açılan maça tek dokunuş, hata kartın içinde.
 * Ana sayfa ve find.php kullanır. Değişkenler (isteğe bağlı): $ccId, $ccError, $ccErrKind, $ccValue, $ccTitleTag.
 * Gönderilen alan tek "code" (gizli); kod her zaman 4 rakamdır.
 */
$ccId = $ccId ?? 'cc';
$ccError = $ccError ?? null;
$ccErrKind = $ccErrKind ?? null;
$ccValue = (string)($ccValue ?? '');
$ccTitleTag = $ccTitleTag ?? 'h2';
$ccIsDigits = (bool)preg_match('/^\d{1,4}$/', $ccValue);
$ccCodeErr = in_array($ccErrKind, ['empty', 'short', 'notfound', 'blocked'], true);
$ccSupport = in_array($ccErrKind, ['notfound', 'expired', 'technical'], true);
$ccTitleErr = ['notfound' => 'find.errors.video_not_found', 'expired' => 'find.errors.expiredTitle', 'technical' => 'find.errors.technicalTitle'][$ccErrKind] ?? null;
$ccBox = 'cc-d h-14 w-12 rounded-xl border bg-white/[0.04] text-center font-mono text-3xl font-bold text-white outline-none transition focus:border-accent sm:h-16 sm:w-14 ' . ($ccCodeErr ? 'border-rose-400/80' : 'border-white/15');
?>
<form action="<?= base_url('find') ?>" method="POST" novalidate data-cc
      data-msg-empty="<?= e(__('find.errors.empty')) ?>" data-msg-short="<?= e(__('find.errors.short')) ?>" data-msg-consent="<?= e(__('yen.code.consentFirst')) ?>"
      class="rounded-3xl border border-white/10 bg-ink-900/95 p-6 md:p-8">
  <div class="mb-4 flex items-center gap-2.5">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FEF1B6" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 118 0v3"></path></svg>
    <<?= $ccTitleTag ?> class="font-display text-lg font-bold text-white"><?= e(__('find.watch.title')) ?></<?= $ccTitleTag ?>>
  </div>
  <p class="mb-5 text-[15px] leading-relaxed text-white/75"><?= e(__('find.watch.subtitle')) ?></p>

  <label for="<?= e($ccId) ?>-d1" class="cc-label text-xs font-semibold uppercase tracking-wider text-white/70" data-or-new="<?= e(__('yen.code.orNew')) ?>"><?= e(__('find.watch.codeLabel')) ?></label>
  <div class="cc-boxes mt-2 flex justify-center gap-2.5 sm:gap-3" role="group" aria-label="<?= e(__('find.watch.codeLabel')) ?>">
    <?php for ($i = 1; $i <= 4; $i++): ?>
      <input id="<?= e($ccId) ?>-d<?= $i ?>" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" <?= $i === 1 ? 'autocomplete="one-time-code"' : 'autocomplete="off"' ?> aria-label="<?= e(__('yen.code.digit')) ?> <?= $i ?>"
             value="<?= $ccIsDigits ? e(substr($ccValue, $i - 1, 1)) : '' ?>"<?= $ccCodeErr ? ' aria-invalid="true"' : '' ?> class="<?= $ccBox ?>">
    <?php endfor; ?>
  </div>
  <input type="hidden" name="code" class="cc-hidden" value="<?= $ccIsDigits ? e($ccValue) : '' ?>">

  <p class="cc-err mt-3 text-sm font-semibold text-rose-300<?= $ccError ? '' : ' hidden' ?>" role="alert"><?php if ($ccTitleErr): ?><span class="block text-rose-200"><?= e(__($ccTitleErr)) ?></span><span class="font-normal text-rose-200/90"><?php endif; ?><?= e((string)$ccError) ?><?php if ($ccTitleErr): ?></span><?php endif; ?></p>
  <?php if ($ccSupport): ?>
    <a class="mt-3 inline-flex min-h-[44px] items-center rounded-full border border-rose-200/40 px-5 py-2 text-sm font-semibold text-rose-100 transition hover:bg-rose-200/10" href="https://wa.me/393447598309?text=<?= urlencode(__('support.whatsapp.prefill')) ?>" target="_blank" rel="noopener noreferrer"><?= e(__('support.button')) ?></a>
  <?php endif; ?>

  <?php include __DIR__ . '/consent_field.php'; ?>
  <button type="submit" class="mt-5 w-full rounded-xl bg-accent px-6 py-3.5 text-sm font-bold text-ink-900 transition hover:bg-accent-neon active:scale-[0.99] disabled:opacity-60"><?= e(__('find.watch.button')) ?></button>
</form>
