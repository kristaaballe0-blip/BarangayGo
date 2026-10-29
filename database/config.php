<?php
declare(strict_types=1);
// Hosting defaults. A private local file can override these for XAMPP development.
$config = [
    'host' => 'sql309.infinityfree.com',
    'port' => '3306',
    'name' => 'if0_43266472_BarangayGo',
    'user' => 'if0_43266472',
    'password' => '',
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $config = array_replace($config, require $localConfig);
}

// Explicit server environment settings take precedence, including empty passwords.
foreach (array_keys($config) as $key) {
    $value = getenv('BARANGAY_DB_' . strtoupper($key));
    if ($value !== false) {
        $config[$key] = $value;
    }
}

return $config;
