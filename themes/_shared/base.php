<?php
/**
 * Base HTML layout shared by all themes.
 */
$site  = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
$title = trim(($invitation['judul'] ?? '') . ' — ' . $site, ' —');
$bg    = !empty($invitation['background']) ? upload_url($invitation['background']) : '';
$themeSlug = $themeSlug ?? ($invitation['tema'] ?? 'theme01');
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,maximum-scale=5">
<meta name="theme-color" content="#ffffff">
<title><?= h($title) ?></title>
<meta name="description" content="Undangan Pernikahan <?= h($invitation['judul'] ?? '') ?>">

<!-- OG -->
<meta property="og:title" content="<?= h($title) ?>">
<meta property="og:description" content="Undangan Pernikahan <?= h($invitation['judul'] ?? '') ?>">
<meta property="og:type" content="website">

<!-- Fonts: Baskervville (body) + Philosopher (title/UI) + Great Vibes (script) + Amiri (Arabic) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baskervville:ital@0;1&family=Philosopher:ital,wght@0,400;0,700;1,400&family=Great+Vibes&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

<!-- AOS — loaded synchronously so AOS is available before sections.php inline script -->
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

<link href="<?= h(asset('css/app.css')) ?>" rel="stylesheet">
<link href="<?= h(base_url('themes/' . $themeSlug . '/style.css')) ?>" rel="stylesheet">

<?php if ($bg): ?>
<style>:root { --bg-image: url('<?= h($bg) ?>'); }</style>
<?php endif; ?>
</head>
<body class="theme-<?= h($themeSlug) ?><?= $bg ? ' has-bg' : '' ?>">
<div class="bg-layer"></div>
<div class="bg-overlay"></div>

<main class="invitation-root">
  <?php include __DIR__ . '/sections.php'; ?>
</main>

<?php if (!empty($invitation['musik'])): ?>
  <audio id="bgMusic" src="<?= h(upload_url($invitation['musik'])) ?>" loop></audio>
  <button id="musicToggle" aria-label="Putar/jeda musik" class="music-toggle">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
  </button>
<?php endif; ?>

</body>
</html>
