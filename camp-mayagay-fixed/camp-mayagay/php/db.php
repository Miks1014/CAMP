<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Single MySQL connection used by the whole app.
 * Settings come from environment variables (see .env.example /
 * docker-compose.yml) so no credentials live in the source code.
 */
function envOr(string $key, string $default = ''): string
{
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

$DB_HOST    = envOr('DB_HOST', '127.0.0.1');
$DB_PORT    = envOr('DB_PORT', '3306');
$DB_NAME    = envOr('DB_NAME', 'camp_mayagay');
$DB_USER    = envOr('DB_USER', 'camp');
$DB_PASS    = envOr('DB_PASS', '');
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset={$DB_CHARSET}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
    $pdo->exec("SET time_zone = '" . envOr('DB_TIMEZONE', '+08:00') . "'");
} catch (PDOException $e) {
    error_log('[camp-mayagay] DB connection failed: ' . $e->getMessage());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Database connection failed: " . $e->getMessage() . "\n");
        exit(1);
    }
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]));
}
