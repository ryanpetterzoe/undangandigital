<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

function out(array $a, int $code = 200): void {
    http_response_code($code);
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'msg' => 'POST only'], 405);

$action = $_POST['action'] ?? '';
$slug   = trim($_POST['slug'] ?? '');
if ($slug === '') out(['ok' => false, 'msg' => 'slug missing'], 400);

$inv = get_invitation_by_slug($pdo, $slug);
if (!$inv) out(['ok' => false, 'msg' => 'not found'], 404);

$invId = (int)$inv['id'];

// Optional guest token
$guestId = null;
if (!empty($_POST['guest_token'])) {
    $st = $pdo->prepare('SELECT id FROM guests WHERE token=? AND invitation_id=?');
    $st->execute([$_POST['guest_token'], $invId]);
    $g = $st->fetch();
    if ($g) $guestId = (int)$g['id'];
}

if ($action === 'rsvp') {
    $nama  = trim($_POST['nama'] ?? '');
    $hadir = $_POST['hadir'] ?? 'hadir';
    $jumlah = (int)($_POST['jumlah_tamu'] ?? 1);
    if ($nama === '') out(['ok' => false, 'msg' => 'Nama wajib diisi'], 422);
    if (!in_array($hadir, ['hadir','tidak','ragu'], true)) $hadir = 'hadir';
    $st = $pdo->prepare('INSERT INTO rsvp (invitation_id,guest_id,nama,hadir,jumlah_tamu) VALUES (?,?,?,?,?)');
    $st->execute([$invId, $guestId, $nama, $hadir, max(1, $jumlah)]);
    out(['ok' => true, 'msg' => 'Terima kasih atas konfirmasinya.']);
}

if ($action === 'ucapan') {
    $nama  = trim($_POST['nama'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    if ($nama === '' || $pesan === '') out(['ok' => false, 'msg' => 'Nama & pesan wajib diisi'], 422);
    if (mb_strlen($pesan) > 1000) out(['ok' => false, 'msg' => 'Pesan terlalu panjang'], 422);
    $st = $pdo->prepare('INSERT INTO ucapan (invitation_id,guest_id,nama,pesan) VALUES (?,?,?,?)');
    $st->execute([$invId, $guestId, $nama, $pesan]);
    $row = $pdo->prepare('SELECT id,nama,pesan,created_at FROM ucapan WHERE id=?');
    $row->execute([(int)$pdo->lastInsertId()]);
    out(['ok' => true, 'item' => $row->fetch()]);
}

if ($action === 'list_ucapan') {
    $st = $pdo->prepare('SELECT id,nama,pesan,created_at FROM ucapan WHERE invitation_id=? ORDER BY id DESC LIMIT 100');
    $st->execute([$invId]);
    out(['ok' => true, 'items' => $st->fetchAll()]);
}

out(['ok' => false, 'msg' => 'unknown action'], 400);
