<?php
require_once __DIR__ . '/auth.php';
admin_check();
$site = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
$pageTitle = $pageTitle ?? 'Admin';

$navItems = [
  ['key' => 'dashboard', 'label' => 'Dashboard',  'href' => base_url('admin/'),
    'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>'],
  ['key' => 'mempelai', 'label' => 'Undangan',  'href' => base_url('admin/mempelai.php'),
    'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>'],
  ['key' => 'client', 'label' => 'Klien',  'href' => base_url('admin/client.php'),
    'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
];
$active = $activeNav ?? '';
$userInitial = mb_strtoupper(mb_substr((string)($_SESSION['admin_nama'] ?? 'A'), 0, 1));

$LOGO_SVG = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.5-9.5-9C1.2 9 3 5 7 5c2 0 3.5 1 5 3 1.5-2 3-3 5-3 4 0 5.8 4 4.5 7-2 4.5-9.5 9-9.5 9z"/></svg>';
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#ffffff">
<title><?= h($pageTitle) ?> &middot; <?= h($site) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Philosopher:wght@400;700&display=swap" rel="stylesheet">
<link href="<?= h(asset('css/admin.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">

  <!-- Mobile top bar -->
  <header class="app-topbar">
    <a href="<?= h(base_url('admin/')) ?>" class="app-brand">
      <span class="logo"><?= $LOGO_SVG ?></span>
      <span><?= h($site) ?></span>
    </a>
    <button type="button" class="app-burger" id="burger" aria-label="Buka menu">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
  </header>

  <!-- Sidebar -->
  <aside class="app-sidebar" id="sidebar">
    <div class="sidebar-panel" onclick="event.stopPropagation()">
      <div class="sidebar-head">
        <span class="logo"><?= $LOGO_SVG ?></span>
        <div class="who">
          <small>Selamat datang,</small>
          <strong><?= h($_SESSION['admin_nama'] ?? 'Admin') ?></strong>
        </div>
      </div>
      <nav>
        <?php foreach ($navItems as $n): ?>
          <a class="nav-link <?= $active === $n['key'] ? 'active' : '' ?>" href="<?= h($n['href']) ?>">
            <?= $n['icon'] ?><span><?= h($n['label']) ?></span>
          </a>
        <?php endforeach; ?>
        <div class="nav-divider"></div>
        <a class="nav-link" href="<?= h(base_url()) ?>" target="_blank">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15,3 21,3 21,9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          <span>Beranda Situs</span>
        </a>
        <a class="nav-link" href="<?= h(base_url('admin/logout.php')) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span>Keluar</span>
        </a>
      </nav>
    </div>
  </aside>

  <main class="app-main">
<script>
(function(){
  var b = document.getElementById('burger'), s = document.getElementById('sidebar');
  if (b && s) {
    b.addEventListener('click', function(e){ e.stopPropagation(); s.classList.add('open'); });
    s.addEventListener('click', function(){ s.classList.remove('open'); });
  }
  // Modal helpers
  window.uOpenModal = function(id){ var m=document.getElementById(id); if(m){m.classList.add('open');} };
  window.uCloseModal = function(id){ var m=document.getElementById(id); if(m){m.classList.remove('open');} };
})();
</script>
