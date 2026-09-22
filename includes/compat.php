<?php
/**
 * PHP 7.x uyumluluğu: PHP 8.0 ile gelen dize fonksiyonları sunucuda yoksa tanımlanır.
 * (Canlı sunucu PHP 7.x çalıştırıyor; PHP 8'de yerleşik fonksiyonlar kullanılır.)
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        return $needle === '' || ($haystack !== '' && substr_compare($haystack, $needle, -strlen($needle)) === 0);
    }
}
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
