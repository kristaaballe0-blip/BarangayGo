<?php
declare(strict_types=1);
// Run from the terminal only; never expose database setup as a browser action.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Command-line access only.');
}
$config = require __DIR__ . '/config.php';
if (!preg_match('/^[A-Za-z0-9_]+$/', $config['name'])) {
    throw new RuntimeException('Invalid database name.');
}
$db = new PDO(
    "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",
    $config['user'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);
$db->exec("SET time_zone = '+00:00'");
if (in_array($config['host'], ['127.0.0.1', 'localhost'], true)) {
    $db->exec(
        "CREATE DATABASE IF NOT EXISTS `{$config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
    );
}
$db->exec("USE `{$config['name']}`");
$db->exec(file_get_contents(__DIR__ . '/schema.sql'));
$tables = ['residents', 'users', 'types', 'requests', 'payments', 'logs', 'login_attempts'];
$sourceFile = dirname(__DIR__) . '/storage/barangay.sqlite';
if (!is_file($sourceFile)) {
    echo "Schema ready. Open the application to initialize demo accounts.\n";
    exit();
}
foreach ($tables as $table) {
    if ((int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() > 0) {
        echo "Database already contains data; skipped migration to preserve existing records.\n";
        exit();
    }
}
$source = new PDO('sqlite:' . $sourceFile, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$db->beginTransaction();
try {
    foreach ($tables as $table) {
        $rows = $source->query("SELECT * FROM `$table`")->fetchAll();
        foreach ($rows as $row) {
            $columns = implode(
                ',',
                array_map(fn($column) => '`' . $column . '`', array_keys($row)),
            );
            $placeholders = implode(',', array_fill(0, count($row), '?'));
            $db->prepare("INSERT INTO `$table` ($columns) VALUES ($placeholders)")->execute(
                array_values($row),
            );
        }
        echo $table . ': ' . count($rows) . " records migrated\n";
    }
    $db->commit();
    echo "Migration complete. Original SQLite database retained as a backup.\n";
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
}
