<?php
/**
 * Replash PDO Database Handler & Auto-Migration
 */

require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // First try connecting to specific DB
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                // Ensure tables and schema are initialized
                self::ensureTablesExist(self::$instance);
                self::ensureSchemaUpdates(self::$instance);
            } catch (PDOException $e) {
                // If database doesn't exist, create it automatically
                if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                    self::initDatabase();
                    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    self::ensureTablesExist(self::$instance);
                    self::ensureSchemaUpdates(self::$instance);
                } else {
                    throw $e;
                }
            }
        }
        return self::$instance;
    }

    public static function ensureTablesExist(PDO $pdo): void {
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'admins'")->fetch();
            if (!$check) {
                $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
                if ($schemaSql) {
                    $pdo->exec($schemaSql);
                }
                self::seedInitialData($pdo);
            } else {
                // Ensure admin exists
                $adminCheck = (int)$pdo->query("SELECT COUNT(*) FROM `admins`")->fetchColumn();
                if ($adminCheck === 0) {
                    $adminPassHash = password_hash('replash2025', PASSWORD_BCRYPT);
                    $insAdmin = $pdo->prepare("INSERT INTO `admins` (`id`, `email`, `name`, `password_hash`, `role`) VALUES (?, ?, ?, ?, ?)");
                    $insAdmin->execute(['admin-root', 'admin@replash.info', 'Replash Super Admin', $adminPassHash, 'superadmin']);
                }
            }
        } catch (Exception $e) {
            error_log("DB ensureTablesExist Error: " . $e->getMessage());
        }
    }

    public static function ensureSchemaUpdates(PDO $pdo): void {
        try {
            // Check if stream_url column exists in cameras
            $check = $pdo->query("SHOW COLUMNS FROM `cameras` LIKE 'stream_url'")->fetch();
            if (!$check) {
                $pdo->exec("ALTER TABLE `cameras` ADD COLUMN `stream_url` TEXT NULL AFTER `channel_id`");
            }

            // Sales control (includes/sales.php): first open finalizes a sale; append-only audit trail
            if (!$pdo->query("SHOW COLUMNS FROM `matches` LIKE 'first_opened_at'")->fetch()) {
                $pdo->exec("ALTER TABLE `matches` ADD COLUMN `first_opened_at` DATETIME NULL DEFAULT NULL");
                // Already watched/downloaded sales are final
                $pdo->exec("UPDATE `matches` SET `first_opened_at` = COALESCE(`sold_at`, `created_at`) WHERE `views` > 0");
            }
            $pdo->exec("CREATE TABLE IF NOT EXISTS `sale_events` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `match_id` VARCHAR(64) NOT NULL,
                `facility_id` VARCHAR(64) NOT NULL,
                `action` VARCHAR(20) NOT NULL,
                `actor` VARCHAR(64) NOT NULL,
                `reason` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`facility_id`, `created_at`),
                INDEX (`match_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Ensure settings table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
                `key_name` VARCHAR(64) NOT NULL PRIMARY KEY,
                `value` LONGTEXT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Exception $e) {
            // Ignore if table doesn't exist yet
        }
    }

    public static function initDatabase(): void {
        try {
            $rootPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $dbPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
            $dbPdo->exec($schemaSql);
            
            // Seed initial data
            self::seedInitialData($dbPdo);
        } catch (Exception $e) {
            error_log("DB Init Error: " . $e->getMessage());
        }
    }

    public static function seedInitialData(PDO $pdo): void {
        // Check if already seeded
        $stmt = $pdo->query("SELECT COUNT(*) FROM `facilities`");
        if ($stmt->fetchColumn() > 0) {
            return;
        }

        // 1. Facilities
        $facilities = [
            [
                'id' => 'fac-milano-padel',
                'name' => 'Milano Padel Club',
                'slug' => 'milano-padel-club',
                'city' => 'Milano',
                'sport' => 'padel',
                'courts' => json_encode(['Campo 1', 'Campo 2', 'Campo 3']),
                'pin' => '482913',
                'unit_price' => 6.00,
                'currency' => 'EUR'
            ],
            [
                'id' => 'fac-navigli-calcio',
                'name' => 'Navigli Calcio Center',
                'slug' => 'navigli-calcio-center',
                'city' => 'Milano',
                'sport' => 'football',
                'courts' => json_encode(['Campo A', 'Campo B']),
                'pin' => '739104',
                'unit_price' => 8.00,
                'currency' => 'EUR'
            ],
            [
                'id' => 'fac-roma-padel',
                'name' => 'Roma Padel Arena',
                'slug' => 'roma-padel-arena',
                'city' => 'Roma',
                'sport' => 'padel',
                'courts' => json_encode(['Pista 1', 'Pista 2']),
                'pin' => '150726',
                'unit_price' => 7.00,
                'currency' => 'EUR'
            ],
            [
                'id' => 'fac-torino-sport',
                'name' => 'Torino Sport Village',
                'slug' => 'torino-sport-village',
                'city' => 'Torino',
                'sport' => 'football',
                'courts' => json_encode(['Campo Nord', 'Campo Sud']),
                'pin' => '901284',
                'unit_price' => 7.50,
                'currency' => 'EUR'
            ],
            [
                'id' => 'fac-istanbul-padel',
                'name' => 'İstanbul Padel Arena',
                'slug' => 'istanbul-padel-arena',
                'city' => 'İstanbul',
                'sport' => 'padel',
                'courts' => json_encode(['Kort 1', 'Kort 2', 'Kort 3']),
                'pin' => '343434',
                'unit_price' => 250.00,
                'currency' => 'TRY'
            ]
        ];

        $insFac = $pdo->prepare("INSERT INTO `facilities` (`id`, `name`, `slug`, `city`, `sport`, `courts`, `pin`, `unit_price`, `currency`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($facilities as $f) {
            $insFac->execute([$f['id'], $f['name'], $f['slug'], $f['city'], $f['sport'], $f['courts'], $f['pin'], $f['unit_price'], $f['currency']]);
        }

        // 2. Cameras
        $insCam = $pdo->prepare("INSERT INTO `cameras` (`id`, `facility_id`, `court`, `name`, `token`, `channel_id`, `stream_url`, `status`, `last_seen_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $cameras = [];
        foreach ($facilities as $f) {
            $courts = json_decode($f['courts'], true);
            foreach ($courts as $court) {
                $cSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $court));
                $camId = "cam-{$f['slug']}-{$cSlug}";
                $channelId = "{$f['slug']}-{$cSlug}";
                $token = 'camk_' . bin2hex(random_bytes(8));
                $camName = "{$f['name']} · {$court}";
                $sampleStreamUrl = "rtsp://camera.replash.net:554/live/{$f['slug']}/{$cSlug}";
                $insCam->execute([$camId, $f['id'], $court, $camName, $token, $channelId, $sampleStreamUrl, 'online']);
                $cameras[] = ['id' => $camId, 'facility_id' => $f['id'], 'court' => $court, 'sport' => $f['sport'], 'channel_id' => $channelId, 'stream_url' => $sampleStreamUrl];
            }
        }

        // 3. Admin Account
        $adminPassHash = password_hash('replash2025', PASSWORD_BCRYPT);
        $insAdmin = $pdo->prepare("INSERT INTO `admins` (`id`, `email`, `name`, `password_hash`, `role`) VALUES (?, ?, ?, ?, ?)");
        $insAdmin->execute(['admin-root', 'admin@replash.info', 'Replash Super Admin', $adminPassHash, 'superadmin']);

        // 4. Sample Matches (Last 6 days)
        $timeSlots = ['17:00–18:00', '18:00–19:00', '19:00–20:00', '20:00–21:00', '21:00–22:00'];
        $insMatch = $pdo->prepare("INSERT INTO `matches` 
            (`id`, `code`, `access_code`, `camera_id`, `facility_id`, `court`, `date`, `time_slot`, `status`, `playback_url`, `playback_type`, `thumbnail_url`, `duration_sec`, `sold_at`, `views`, `billable`, `sport`, `recorded_at`, `available_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $sampleVideo = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $sampleThumb = 'assets/images/gen_hero_main.jpg';

        foreach ($cameras as $cam) {
            for ($offset = 0; $offset <= 5; $offset++) {
                $dateObj = new DateTime("-{$offset} days");
                $dateStr = $dateObj->format('Y-m-d');
                $dateCompact = $dateObj->format('Ymd');

                foreach ($timeSlots as $slot) {
                    // ~65% match creation chance
                    if (crc32($cam['id'] . $dateStr . $slot) % 100 > 65) continue;

                    $slotStart = explode('–', $slot)[0];
                    $slotStartCompact = str_replace(':', '', $slotStart);
                    $matchCode = "MATCH-{$dateCompact}-{$slotStartCompact}";
                    $accessCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4) . '-' . substr(bin2hex(random_bytes(4)), 0, 4));
                    $matchId = 'm-' . bin2hex(random_bytes(5));

                    $sold = (crc32($matchId) % 100) < 60;
                    $status = $sold ? 'UNLOCKABLE' : 'LOCKED';
                    $soldAt = $sold ? date('Y-m-d H:i:s', strtotime("{$dateStr} {$slotStart}:00 + 45 minutes")) : null;
                    $views = $sold ? rand(1, 14) : 0;
                    $recordedAt = date('Y-m-d H:i:s', strtotime("{$dateStr} {$slotStart}:00 + 1 hour"));
                    $availableAt = date('Y-m-d H:i:s', strtotime("{$recordedAt} + 8 minutes"));

                    $insMatch->execute([
                        $matchId,
                        $matchCode,
                        $accessCode,
                        $cam['id'],
                        $cam['facility_id'],
                        $cam['court'],
                        $dateStr,
                        $slot,
                        $status,
                        $sampleVideo,
                        'mp4',
                        $sampleThumb,
                        3600,
                        $soldAt,
                        $views,
                        $sold ? 1 : 0,
                        $cam['sport'],
                        $recordedAt,
                        $availableAt
                    ]);
                }
            }
        }
    }
}
