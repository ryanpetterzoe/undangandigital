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
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Login Admin &middot; <?= h($site) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= h(asset('css/admin.css')) ?>" rel="stylesheet">
</head>
<body class="auth-page">
<form method="post" class="auth-card" autocomplete="on">
  <div class="auth-logo">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.5-9.5-9C1.2 9 3 5 7 5c2 0 3.5 1 5 3 1.5-2 3-3 5-3 4 0 5.8 4 4.5 7-2 4.5-9.5 9-9.5 9z"/></svg>
  </div>
  <h1 class="page-title" style="margin:0">Masuk Admin</h1>
  <p class="muted mb-4"><?= h($site) ?></p>
  <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
  <div class="form-row">
    <div>
      <label class="label" for="u">Username</label>
      <input id="u" class="input" name="username" required autofocus>
    </div>
    <div>
      <label class="label" for="p">Password</label>
      <input id="p" class="input" name="password" type="password" required>
    </div>
    <button class="btn btn-primary btn-block">Masuk</button>
  </div>
</form>
</body>
</html>
