<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (admin_login($pdo, $u, $p)) {
        redirect(base_url('admin/'));
    } else {
        $error = 'Username atau password salah.';
    }
}
$site = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login Admin &middot; <?= h($site) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-rose-50 to-amber-50 flex items-center justify-center p-6">
<form method="post" class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm">
  <h1 class="text-xl font-semibold text-rose-700 mb-1">Login Admin</h1>
  <p class="text-sm text-slate-500 mb-5"><?= h($site) ?></p>
  <?php if ($error): ?><div class="bg-red-50 text-red-700 border border-red-200 p-2 rounded mb-3 text-sm"><?= h($error) ?></div><?php endif; ?>
  <label class="block text-sm mb-2">Username
    <input name="username" class="w-full border rounded-lg px-3 py-2 mt-1" required></label>
  <label class="block text-sm mb-4">Password
    <input type="password" name="password" class="w-full border rounded-lg px-3 py-2 mt-1" required></label>
  <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded-lg font-medium">Masuk</button>
</form>
</body>
</html>
