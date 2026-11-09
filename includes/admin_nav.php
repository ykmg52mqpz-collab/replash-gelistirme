<?php
/**
 * Yönetim paneli ortak başlık + sekmeler (replash.info AdminApp düzeni).
 * Kullanım: $adminTab = 'overview'|'facilities'|'matches'|'audit'|'leads'|'settings'; include.
 * Metinler Türkçe / İngilizce (includes/admin_i18n.php).
 */
$adminTabs = [
    'overview'   => [adm('Genel Bakış', 'Overview'),        base_url('admin/?tab=overview')],
    'facilities' => [adm('Tesisler', 'Facilities'),         base_url('admin/?tab=facilities')],
    'matches'    => [adm('Maçlar', 'Matches'),              base_url('admin/matches')],
    'audit'      => [adm('Satış Denetimi', 'Sales audit'),  base_url('admin/?tab=audit')],
    'leads'      => [adm('Başvurular', 'Leads'),            base_url('admin/?tab=leads')],
    'settings'   => [adm('Ayarlar', 'Settings'),            base_url('admin/?tab=settings')],
];
$adminTab = $adminTab ?? 'overview';
?>
<div class="mb-6 border-b border-gray-200">
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Replash · <?= e(adm('Yönetim', 'Admin')) ?></div>
      <h1 class="mt-1 text-2xl font-semibold text-gray-900"><?= e($adminTabs[$adminTab][0] ?? adm('Yönetim', 'Admin')) ?></h1>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-xs text-gray-500"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
      <a href="<?= base_url('admin/?action=logout') ?>" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"><?= e(adm('Çıkış', 'Log out')) ?></a>
    </div>
  </div>
  <nav class="mt-4 flex max-w-full gap-6 overflow-x-auto text-sm" aria-label="<?= e(adm('Yönetim', 'Admin')) ?>">
    <?php foreach ($adminTabs as $key => [$label, $href]): ?>
      <a href="<?= e($href) ?>" class="-mb-px whitespace-nowrap border-b-2 pb-3 <?= $adminTab === $key ? 'border-gray-900 font-semibold text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900' ?>" <?= $adminTab === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
</div>
