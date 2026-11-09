<?php
/** Oyuncu beyanı: maç kodu ile izlemeden önce (ad istenmez). Sunucu tarafı kontrolü ve kayıt find.php içindedir.
 *  Görünen metin kısa; tam beyan ve not "…" ile açılır (aynı sayfada, onaydan önce okunabilir). */
$consentFullId = 'consent-full-' . substr(md5(uniqid('', true)), 0, 6);
?>
<label class="mt-5 flex cursor-pointer items-start gap-3 text-sm leading-relaxed text-white/80">
  <input type="checkbox" name="consent" value="1" required aria-describedby="<?= $consentFullId ?>" class="mt-0.5 h-5 w-5 flex-none cursor-pointer accent-[#FEF1B6]"<?= !empty($consentErrorShown) ? ' aria-invalid="true"' : '' ?><?= !empty($_SESSION['consent_ok']) ? ' checked' : '' ?>>
  <span><?= e(__('v2.consent.short')) ?> <button type="button" class="consent-more font-semibold text-white underline decoration-white/40 underline-offset-2 hover:decoration-white" aria-expanded="false" aria-controls="<?= $consentFullId ?>" data-more="<?= e(__('v2.consent.more')) ?>" data-less="<?= e(__('v2.consent.less')) ?>" onclick="event.preventDefault();var f=document.getElementById(this.getAttribute('aria-controls'));var o=f.hidden;f.hidden=!o;this.setAttribute('aria-expanded',o);this.textContent=o?this.dataset.less:'… '+this.dataset.more;">… <?= e(__('v2.consent.more')) ?></button></span>
</label>
<div id="<?= $consentFullId ?>" hidden class="mt-2 space-y-2 pl-8 text-xs leading-relaxed text-white/65">
  <p><?= e(__('find.consent.pre')) ?><a href="<?= base_url('privacy') ?>" target="_blank" rel="noopener" class="underline decoration-white/40 underline-offset-2 hover:text-white"><?= e(__('find.consent.link')) ?></a><?= e(__('find.consent.post')) ?></p>
  <p class="text-white/50"><?= e(__('find.consent.note')) ?></p>
</div>
<p class="cc-consent-err mt-2 hidden pl-8 text-sm font-semibold text-rose-300" role="alert"></p>
