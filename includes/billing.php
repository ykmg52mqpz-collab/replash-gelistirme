<?php
/**
 * Replash Billing Rules — single source for all in-site calculations.
 *
 * Source documents (19 September 2026):
 *  - Replash Facility Billing Guide (EN v2): sales, revenue share & monthly billing
 *  - Replash Platform Brief v3.4, section 05 "Commercial model"
 *
 * Rules:
 *  - Video price per sport: football €15, padel €10, tennis €10.
 *  - Facility share depends on the facility's TOTAL Valid Video Sales in the calendar
 *    month (all fields together, never divided by field count):
 *      0–49 → 20%, 50–99 → 25%, 100+ → 30%. Replash gets the rest.
 *  - The achieved tier applies to ALL valid sales of that month; resets next month.
 *  - Shares are calculated on Gross Valid Video Sales (VAT is not deducted first).
 *  - A Valid Sale = paid and unlocked. Free/test, refunded, cancelled or re-locked
 *    videos are excluded (billable = 0 or status LOCKED).
 */

const REPLASH_BILLING_CURRENCY = 'EUR';

// Standard video price per sport (EUR). Other sports fall back to the facility/court price.
const REPLASH_VIDEO_PRICES = [
    'football' => 15.00,
    'padel'    => 10.00,
    'tennis'   => 10.00,
];

// Monthly facility-wide valid sales tiers, highest first: [min_sales, facility_pct]
const REPLASH_SHARE_TIERS = [
    [100, 30],
    [50, 25],
    [0, 20],
];

/** Sports a facility can offer (key => admin label). */
const REPLASH_SPORTS = [
    'football'   => 'Futbol',
    'padel'      => 'Padel',
    'tennis'     => 'Tenis',
    'basketball' => 'Basketbol',
];

/**
 * A facility's own per-sport video prices from facilities.sport_prices (JSON).
 */
function replash_facility_prices($facility): array {
    $raw = is_array($facility) ? ($facility['sport_prices'] ?? null) : null;
    $decoded = $raw ? json_decode((string)$raw, true) : null;
    $out = [];
    if (is_array($decoded)) {
        foreach ($decoded as $sport => $price) {
            if (is_numeric($price) && $price >= 0) $out[strtolower((string)$sport)] = (float)$price;
        }
    }
    return $out;
}

/**
 * Video price for a sport: the facility's own price if set, else the standard
 * (Billing Guide) price, else $fallback (legacy court/facility price).
 */
function replash_video_price($sport, $fallback = 0.0, $facility = null): float {
    $sport = strtolower(trim((string)$sport));
    $own = replash_facility_prices($facility);
    if (isset($own[$sport])) return $own[$sport];
    return (float)(REPLASH_VIDEO_PRICES[$sport] ?? $fallback);
}

/**
 * Revenue-share tier for a monthly facility-wide valid sales total.
 * Returns ['min' => int, 'max' => ?int, 'facility_pct' => int, 'replash_pct' => int]
 */
function replash_share_tier(int $monthlyValidSales): array {
    $upper = null;
    foreach (REPLASH_SHARE_TIERS as [$min, $pct]) {
        if ($monthlyValidSales >= $min) {
            return ['min' => $min, 'max' => $upper, 'facility_pct' => $pct, 'replash_pct' => 100 - $pct];
        }
        $upper = $min - 1;
    }
    return ['min' => 0, 'max' => null, 'facility_pct' => 0, 'replash_pct' => 100];
}

/**
 * All tiers for display: [['min'=>0,'max'=>49,'facility_pct'=>20,'replash_pct'=>80], ...] ascending.
 */
function replash_share_tiers(): array {
    $tiers = [];
    $upper = null;
    foreach (REPLASH_SHARE_TIERS as [$min, $pct]) {
        $tiers[] = ['min' => $min, 'max' => $upper, 'facility_pct' => $pct, 'replash_pct' => 100 - $pct];
        $upper = $min - 1;
    }
    return array_reverse($tiers);
}

/**
 * Split a gross amount by the tier reached with $monthlyValidSales.
 */
function replash_split_gross(float $gross, int $monthlyValidSales): array {
    $tier = replash_share_tier($monthlyValidSales);
    $facilityShare = round($gross * $tier['facility_pct'] / 100, 2);
    return $tier + [
        'gross' => round($gross, 2),
        'facility_share' => $facilityShare,
        'replash_share' => round($gross - $facilityShare, 2),
    ];
}

/**
 * SQL condition: the match row counts as a Valid Video Sale.
 * Sold matches stay valid after the 7-day viewing window (status EXPIRED with sold_at).
 */
function replash_valid_sale_sql(string $m = 'm'): string {
    return "({$m}.billable = 1 AND ({$m}.status = 'UNLOCKABLE' OR ({$m}.status = 'EXPIRED' AND {$m}.sold_at IS NOT NULL)))";
}

/**
 * SQL expression: price of one valid sale for the match row.
 * $fallbackSql is used for sports without a standard price (e.g. "f.unit_price").
 */
function replash_sale_price_sql(string $m = 'm', string $fallbackSql = 'f.unit_price', ?array $facilityPrices = null, ?string $f = 'f'): string {
    $cases = '';
    foreach (REPLASH_VIDEO_PRICES as $sport => $price) {
        $cases .= " WHEN '" . $sport . "' THEN " . number_format($price, 2, '.', '');
    }
    $standard = "(CASE LOWER({$m}.sport){$cases} ELSE COALESCE({$fallbackSql}, 0) END)";
    if ($facilityPrices !== null) {
        // One facility (panel): its own prices as literals
        $own = '';
        foreach ($facilityPrices as $sport => $price) {
            if (!preg_match('/^[a-z]+$/', $sport)) continue;
            $own .= " WHEN '" . $sport . "' THEN " . number_format((float)$price, 2, '.', '');
        }
        return $own === '' ? $standard : "(CASE LOWER({$m}.sport){$own} ELSE {$standard} END)";
    }
    if ($f !== null) {
        // Joined facilities table: read facilities.sport_prices JSON per row
        return "COALESCE(CAST(JSON_VALUE({$f}.sport_prices, CONCAT('$.', LOWER({$m}.sport))) AS DECIMAL(10,2)), {$standard})";
    }
    return $standard;
}

/**
 * SQL expression: the date a sale belongs to (billing month/day).
 */
function replash_sale_date_sql(string $m = 'm'): string {
    return "DATE(COALESCE({$m}.sold_at, {$m}.date))";
}

/**
 * Monthly statement for one facility, as described in the billing guide.
 * $yearMonth: 'YYYY-MM'
 */
function replash_monthly_statement(PDO $pdo, string $facilityId, string $yearMonth): array {
    $valid = replash_valid_sale_sql('m');
    $price = replash_sale_price_sql('m', 'f.unit_price');
    $saleDate = replash_sale_date_sql('m');

    $stmt = $pdo->prepare("SELECT LOWER(m.sport) AS sport, COUNT(*) AS cnt, COALESCE(SUM({$price}), 0) AS gross
        FROM matches m JOIN facilities f ON f.id = m.facility_id
        WHERE m.facility_id = ? AND {$valid} AND DATE_FORMAT({$saleDate}, '%Y-%m') = ?
        GROUP BY LOWER(m.sport)");
    $stmt->execute([$facilityId, $yearMonth]);

    $bySport = [];
    $validSales = 0;
    $gross = 0.0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $bySport[$row['sport']] = ['sales' => (int)$row['cnt'], 'gross' => round((float)$row['gross'], 2)];
        $validSales += (int)$row['cnt'];
        $gross += (float)$row['gross'];
    }

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE facility_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmtTotal->execute([$facilityId, $yearMonth]);

    return [
        'month' => $yearMonth,
        'currency' => REPLASH_BILLING_CURRENCY,
        'total_matches' => (int)$stmtTotal->fetchColumn(),
        'valid_sales' => $validSales,
        'by_sport' => $bySport,
    ] + replash_split_gross($gross, $validSales);
}

/**
 * Format a money amount in the billing currency, e.g. "€2.415,00".
 */
function replash_money(float $amount, int $decimals = 2): string {
    return '€' . number_format($amount, $decimals, ',', '.');
}
