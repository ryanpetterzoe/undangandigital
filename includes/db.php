<?php
declare(strict_types=1);

if (!defined('UD_BOOT')) define('UD_BOOT', true);

$cfgPath = __DIR__ . '/../config.php';
if (!file_exists($cfgPath)) {
    http_response_code(503);
    echo '<!doctype html><meta charset="utf-8"><title>Belum Terinstal</title>';
    echo '<div style="font-family:sans-serif;max-width:520px;margin:80px auto;padding:24px;border:1px solid #fbcfe8;border-radius:12px;background:#fff5f7">';
    echo '<h1 style="color:#be185d">Belum terinstal</h1>';
    echo '<p>Silakan jalankan <a href="install.php">install.php</a> terlebih dahulu.</p></div>';
    exit;
}
$CONFIG = require $cfgPath;

date_default_timezone_set($CONFIG['app']['timezone'] ?? 'Asia/Jakarta');
session_name($CONFIG['app']['session_name'] ?? 'ud_sess');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

try {
    $dbc = $CONFIG['db'];
    $dsn = "mysql:host={$dbc['host']};port={$dbc['port']};dbname={$dbc['name']};charset={$dbc['charset']}";
    $pdo = new PDO($dsn, $dbc['user'], $dbc['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Database error: ' . htmlspecialchars($e->getMessage());
    exit;
}

$GLOBALS['pdo']    = $pdo;
$GLOBALS['CONFIG'] = $CONFIG;
