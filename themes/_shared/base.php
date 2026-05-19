<?php
/**
 * Base HTML layout shared by all themes.
 * Theme's layout.php should:
 *   $themeSlug = 'theme01';
 *   include __DIR__ . '/../_shared/base.php';
 */
$site  = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
$title = trim(($invitation['judul'] ?? '') . ' — ' . $site, ' —');
$bg    = !empty($invitation['background']) ? upload_url($invitation['background']) : '';
$themeSlug = $themeSlug ?? ($invitation['tema'] ?? 'theme01');
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?></title>
<meta name="description" content="Undangan Pernikahan <?= h($invitation['judul'] ?? '') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Great+Vibes&family=Playfair+Display:wght@500;700&family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<link href="<?= h(asset('css/app.css')) ?>" rel="stylesheet">
<link href="<?= h(base_url('themes/' . $themeSlug . '/style.css')) ?>" rel="stylesheet">
<?php if ($bg): ?>
<style>:root{--bg-image:url('<?= h($bg) ?>');}</style>
<?php endif; ?>
</head>
<body class="theme-<?= h($themeSlug) ?>">
<div class="bg-layer"></div>
<div class="bg-overlay"></div>

<main class="invitation-root relative">
  <?php include __DIR__ . '/sections.php'; ?>
</main>

<?php if (!empty($invitation['musik'])): ?>
  <audio id="bgMusic" src="<?= h(upload_url($invitation['musik'])) ?>" loop></audio>
<?php endif; ?>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
</body>
</html>
