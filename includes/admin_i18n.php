<?php
/**
 * Yönetim paneli dili — yalnız Türkçe ve İngilizce.
 *
 * Yönetim paneli iç ekrandır; site dili TR ise Türkçe, diğer tüm dillerde
 * İngilizce gösterilir. Metinler dil dosyalarına değil, doğrudan çağrı
 * yerine yazılır: adm('Genel Bakış', 'Overview').
 */

/** Yönetim panelinin aktif dili: 'tr' veya 'en'. */
function admin_lang(): string
{
    global $current_lang;
    return (isset($current_lang) && $current_lang === 'tr') ? 'tr' : 'en';
}

/** Türkçe / İngilizce metin çifti. İngilizcesi boşsa Türkçesi kullanılır. */
function adm(string $tr, string $en = ''): string
{
    return admin_lang() === 'tr' ? $tr : ($en !== '' ? $en : $tr);
}

/** Yüzde biçimi: Türkçe "%30", İngilizce "30%". */
function adm_pct($value): string
{
    $n = is_float($value) ? rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') : (string)(int)$value;
    return admin_lang() === 'tr' ? '%' . $n : $n . '%';
}
