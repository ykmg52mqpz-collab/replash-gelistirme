<?php
/**
 * Yönetim paneli ortak başlık + sekmeler (replash.info AdminApp düzeni).
 * Kullanım: $adminTab = 'overview'|'facilities'|'matches'|'audit'|'leads'|'settings'; include.
 */
$adminTabs = [
    'overview'   => ['Genel Bakış',     base_url('admin/index.php?tab=overview')],
    'facilities' => ['Tesisler',        base_url('admin/index.php?tab=facilities')],
    'matches'    => ['Maçlar',          base_url('admin/matches.php')],
    'audit'      => ['Satış Denetimi',  base_url('admin/index.php?tab=audit')],
    'leads'      => ['Başvurular',      base_url('admin/index.php?tab=leads')],
    'settings'   => ['Ayarlar',         base_url('admin/index.php?tab=settings')],
];
$adminTab = $adminTab ?? 'overview';
?>
<div class="mb-5 flex flex-wrap items-end justify-between gap-3">
  <div>
    <div class="text-xs font-bold uppercase tracking-widest text-accent">Replash</div>
    <h1 class="font-display text-2xl font-bold"><?= e($adminTabs[$adminTab][0] ?? 'Yönetim') ?></h1>
  </div>
  <div class="flex items-center gap-3">
    <span class="text-xs text-white/40"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
    <a href="<?= base_url('admin/index.php?action=logout') ?>" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-white/70 hover:border-accent hover:text-accent">Çıkış</a>
  </div>
</div>
<nav class="mb-6 flex max-w-full overflow-x-auto" aria-label="Yönetim">
  <div class="inline-flex flex-none overflow-hidden rounded-full border border-white/10">
    <?php foreach ($adminTabs as $key => [$label, $href]): ?>
      <a href="<?= e($href) ?>" class="whitespace-nowrap px-4 py-2 text-sm font-semibold transition <?= $adminTab === $key ? 'bg-accent text-ink-900' : 'text-white/60 hover:text-white' ?>" <?= $adminTab === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
</nav>
