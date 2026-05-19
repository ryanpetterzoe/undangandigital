<?php
require_once __DIR__ . '/auth.php';
admin_check();
$site = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
$pageTitle = $pageTitle ?? 'Admin';
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($pageTitle) ?> &middot; Admin <?= h($site) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">
<header class="bg-white border-b">
  <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-4">
    <a href="<?= h(base_url('admin/')) ?>" class="font-semibold text-rose-700">Admin <?= h($site) ?></a>
    <nav class="flex gap-3 text-sm">
      <a class="hover:text-rose-700" href="<?= h(base_url('admin/')) ?>">Dashboard</a>
      <a class="hover:text-rose-700" href="<?= h(base_url('admin/mempelai.php')) ?>">Undangan</a>
      <a class="hover:text-rose-700" href="<?= h(base_url('admin/client.php')) ?>">Klien</a>
    </nav>
    <div class="ml-auto text-sm flex items-center gap-3">
      <span><?= h($_SESSION['admin_nama'] ?? '') ?></span>
      <a href="<?= h(base_url('admin/logout.php')) ?>" class="text-rose-600">Keluar</a>
    </div>
  </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-6">
