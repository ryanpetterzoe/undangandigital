<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    // Landing page
    $site = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
    ?><!doctype html>
    <html lang="id">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title><?= h($site) ?></title>
      <script src="https://cdn.tailwindcss.com"></script>
      <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
    </head>
    <body class="min-h-screen bg-gradient-to-br from-rose-50 via-amber-50 to-pink-100 flex items-center justify-center p-6">
      <div class="max-w-lg text-center bg-white/70 backdrop-blur rounded-2xl shadow-xl p-8 border border-rose-100">
        <p style="font-family:'Great Vibes',cursive" class="text-5xl text-rose-700 mb-2">The Wedding</p>
        <h1 class="text-2xl font-semibold text-slate-800 mb-2"><?= h($site) ?></h1>
        <p class="text-slate-600 mb-6">Buat undangan pernikahan digital yang elegan, cepat, dan dapat dibagikan ke seluruh tamu Anda.</p>
        <div class="flex justify-center gap-3">
          <a href="<?= h(base_url('admin/login.php')) ?>" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-full">Masuk Admin</a>
        </div>
      </div>
    </body>
    </html><?php
    exit;
}

// Render invitation by slug
$invitation = get_invitation_by_slug($pdo, $slug);
if (!$invitation) {
    http_response_code(404);
    echo '<!doctype html><meta charset="utf-8"><title>Tidak Ditemukan</title>';
    echo '<div style="font-family:sans-serif;text-align:center;padding:80px">';
    echo '<h1>404 - Undangan tidak ditemukan</h1>';
    echo '<p><a href="'.h(base_url()).'">Kembali ke beranda</a></p></div>';
    exit;
}

$data  = get_invitation_full($pdo, (int)$invitation['id']);
$theme = preg_replace('/[^a-z0-9_]/i', '', $invitation['tema'] ?: 'theme01');
$layout = __DIR__ . '/themes/' . $theme . '/layout.php';
if (!file_exists($layout)) {
    $layout = __DIR__ . '/themes/theme01/layout.php';
}

// Optional guest token (link generator)
$guestName = '';
if (!empty($_GET['to'])) {
    $tk = $_GET['to'];
    $st = $pdo->prepare('SELECT nama FROM guests WHERE token=? AND invitation_id=? LIMIT 1');
    $st->execute([$tk, $invitation['id']]);
    $g = $st->fetch();
    if ($g) $guestName = $g['nama'];
}

// Variables for theme
$inv      = $data['invitation'];
$mempelai = $data['mempelai'];
$events   = $data['events'];
$kisah    = $data['kisah'];
$galeri   = $data['galeri'];
$ucapan   = $data['ucapan'];

// Find pria & wanita
$pria = null; $wanita = null;
foreach ($mempelai as $m) {
    if ($m['peran'] === 'pria') $pria = $m;
    if ($m['peran'] === 'wanita') $wanita = $m;
}

include $layout;
