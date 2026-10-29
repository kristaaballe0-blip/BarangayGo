<?php
declare(strict_types=1);
// Session storage and cookie settings.
date_default_timezone_set('Asia/Manila');
$dir = __DIR__ . '/storage';
if (!is_dir($dir)) {
    mkdir($dir, 0700, true);
}
if (!is_dir($dir . '/sessions')) {
    mkdir($dir . '/sessions', 0700, true);
}
session_save_path($dir . '/sessions');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
// Database connection (all application queries use prepared statements).
$config = require __DIR__ . '/database/config.php';
$db = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4",
    $config['user'],
    $config['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
);
$db->exec("SET time_zone = '+00:00'");
// Database and display helpers.
function query(string $sql, array $args = []): PDOStatement
{
    global $db;
    $s = $db->prepare($sql);
    $s->execute($args);
    return $s;
}
function e(mixed $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
function audit(string $action): void
{
    query('INSERT INTO logs(user_id,action) VALUES(?,?)', [$_SESSION['uid'] ?? null, $action]);
}
// Explicit database transactions keep each business operation and its audit entry atomic.
function transaction(callable $work): void
{
    global $db;
    $db->beginTransaction();
    try {
        $work();
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
function money(int $cents): string
{
    return '₱' . number_format($cents / 100, 2);
}
// Initial sample data for a fresh installation.
if (!query('SELECT COUNT(*) FROM users')->fetchColumn()) {
    transaction(function () {
        query('INSERT INTO residents(name,address,birthdate,phone) VALUES(?,?,?,?)', [
            'Juan Dela Cruz',
            '24 Mabini Street',
            '1995-05-15',
            '09171234567',
        ]);
        query('INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)', [
            'Barangay Administrator',
            'admin@barangay.local',
            password_hash('BarangayAdmin!2026', PASSWORD_DEFAULT),
            'staff',
        ]);
        query('INSERT INTO users(resident_id,name,email,password,role) VALUES(1,?,?,?,?)', [
            'Juan Dela Cruz',
            'resident@barangay.local',
            password_hash('Resident!2026', PASSWORD_DEFAULT),
            'resident',
        ]);
        foreach (
            [
                ['Barangay Clearance', 10000, 'Valid government-issued ID; proof of residency'],
                ['Certificate of Residency', 5000, 'Valid ID; proof of address'],
                ['Certificate of Indigency', 0, 'Valid ID; household income declaration'],
                [
                    'Business Clearance',
                    50000,
                    'Valid ID; business registration; proof of business address',
                ],
            ]
            as $t
        ) {
            query('INSERT INTO types(name,fee,requirements) VALUES(?,?,?)', $t);
        }
    });
}
// Current account, CSRF token, and idle-session expiry.
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$user = isset($_SESSION['uid'])
    ? query('SELECT * FROM users WHERE id=?', [$_SESSION['uid']])->fetch()
    : false;
if ($user && time() - ($_SESSION['last_active'] ?? time()) > 1800) {
    $_SESSION = [];
    session_regenerate_id(true);
    header('Location: index.php');
    exit();
}
$_SESSION['last_active'] = time();
