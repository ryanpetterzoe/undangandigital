<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function admin_login(PDO $pdo, string $username, string $password): bool {
    $st = $pdo->prepare('SELECT * FROM admins WHERE username=? LIMIT 1');
    $st->execute([$username]);
    $row = $st->fetch();
    if (!$row) return false;
    if (!password_verify($password, $row['password_hash'])) return false;
    $_SESSION['admin_id']   = (int)$row['id'];
    $_SESSION['admin_nama'] = $row['nama'];
    return true;
}

function admin_logout(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_nama']);
}

function admin_check(): void {
    if (empty($_SESSION['admin_id'])) {
        redirect(base_url('admin/login.php'));
    }
}

function client_check(PDO $pdo): array {
    $token = $_GET['t'] ?? $_SESSION['client_token'] ?? '';
    if (!$token) {
        http_response_code(401);
        exit('Token klien tidak ditemukan. Hubungi admin.');
    }
    $st = $pdo->prepare('SELECT * FROM clients WHERE token=? LIMIT 1');
    $st->execute([$token]);
    $c = $st->fetch();
    if (!$c) {
        http_response_code(403);
        exit('Token tidak valid.');
    }
    $_SESSION['client_token'] = $token;
    return $c;
}
