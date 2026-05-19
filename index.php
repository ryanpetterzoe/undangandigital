<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    $site = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
    ?><!doctype html>
    <html lang="id">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
      <title><?= h($site) ?></title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Philosopher:wght@400;700&family=Great+Vibes&display=swap" rel="stylesheet">
      <link href="<?= h(asset('css/admin.css')) ?>" rel="stylesheet">
      <style>
        .hero { min-height: 100vh; min-height: 100dvh; background: linear-gradient(135deg,#eef2f9,#f3e8ee); display: flex; align-items: center; justify-content: center; padding: 24px; }
        .hero-card { width: 100%; max-width: 480px; text-align: center; background: rgba(255,255,255,.85); border-radius: 22px; padding: 40px 28px; box-shadow: 0 20px 60px rgba(15,23,42,.08); border: 1px solid #fff; }
        .hero .script { font-family: 'Great Vibes', cursive; font-size: 3.6rem; color: var(--p); line-height: 1; margin: 0 0 6px; }
        .hero h1 { font-family: 'Philosopher', serif; font-size: 1.7rem; font-weight: 700; margin: 0 0 10px; color: var(--text); }
        .hero p { color: var(--muted); margin: 0 0 24px; line-height: 1.7; }
      </style>
    </head>
    <body>
      <div class="hero">
        <div class="hero-card">
          <p class="script">The Wedding</p>
          <h1><?= h($site) ?></h1>
          <p>Buat undangan pernikahan digital yang elegan, cepat, dan mudah dibagikan ke seluruh tamu Anda.</p>
          <a href="<?= h(base_url('admin/login.php')) ?>" class="btn btn-primary btn-block">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10,17 15,12 10,7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            <span>Masuk Admin</span>
          </a>
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

// Find pria & wanita (with fallback if peran not set)
$pria = null; $wanita = null;
foreach ($mempelai as $m) {
    if ($m['peran'] === 'pria')   $pria   = $m;
    if ($m['peran'] === 'wanita') $wanita = $m;
}
// Safety net: if rows exist but peran missing/swapped, just take the first two
if (!$pria   && !empty($mempelai[0])) $pria   = $mempelai[0];
if (!$wanita && !empty($mempelai[1])) $wanita = $mempelai[1];
$pria   = $pria   ?: ['nama'=>'', 'nama_panggilan'=>'', 'foto'=>'', 'ayah'=>'', 'ibu'=>'', 'deskripsi'=>'', 'instagram'=>''];
$wanita = $wanita ?: ['nama'=>'', 'nama_panggilan'=>'', 'foto'=>'', 'ayah'=>'', 'ibu'=>'', 'deskripsi'=>'', 'instagram'=>''];

include $layout;
