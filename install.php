<?php
declare(strict_types=1);
session_start();

$installed = file_exists(__DIR__ . '/config.php');
$step      = (int)($_GET['step'] ?? ($installed ? 9 : 1));
$errors    = [];

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function write_config(array $cfg): bool {
    $code = "<?php\nreturn " . var_export($cfg, true) . ";\n";
    return (bool) file_put_contents(__DIR__ . '/config.php', $code);
}

function run_schema(PDO $pdo, string $sqlFile): void {
    $sql = file_get_contents($sqlFile);
    if ($sql === false) throw new RuntimeException('schema.sql tidak ditemukan');
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        if ($stmt === '') continue;
        $pdo->exec($stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$installed) {
    $action = $_POST['action'] ?? '';
    if ($action === 'db') {
        $db = [
            'host' => trim($_POST['db_host'] ?? 'localhost'),
            'port' => (int)($_POST['db_port'] ?? 3306),
            'name' => trim($_POST['db_name'] ?? ''),
            'user' => trim($_POST['db_user'] ?? 'root'),
            'pass' => $_POST['db_pass'] ?? '',
            'charset' => 'utf8mb4',
        ];
        try {
            $dsn = "mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['name']}` DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$db['name']}`");
            run_schema($pdo, __DIR__ . '/schema.sql');
            $_SESSION['install_db'] = $db;
            header('Location: install.php?step=2'); exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal koneksi/instal DB: ' . $e->getMessage();
        }
    }
    if ($action === 'admin') {
        $username = trim($_POST['username'] ?? '');
        $nama     = trim($_POST['nama'] ?? '');
        $password = $_POST['password'] ?? '';
        $site     = trim($_POST['site_name'] ?? 'Undangan Digital');
        if ($username === '' || strlen($password) < 6 || $nama === '') {
            $errors[] = 'Lengkapi data admin (password minimal 6 karakter).';
        } elseif (empty($_SESSION['install_db'])) {
            $errors[] = 'Sesi instalasi hilang. Mulai ulang.';
        } else {
            try {
                $db = $_SESSION['install_db'];
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
                $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO admins (username,password_hash,nama) VALUES (?,?,?)');
                $stmt->execute([$username, $hash, $nama]);
                $cfg = [
                    'db' => $db,
                    'app' => [
                        'base_url'      => '',
                        'site_name'     => $site,
                        'timezone'      => 'Asia/Jakarta',
                        'session_name'  => 'ud_sess',
                        'upload_max_mb' => 10,
                    ],
                    'security' => ['app_key' => bin2hex(random_bytes(24))],
                ];
                if (!write_config($cfg)) {
                    $errors[] = 'Gagal menulis config.php (cek permission folder).';
                } else {
                    unset($_SESSION['install_db']);
                    header('Location: install.php?step=9'); exit;
                }
            } catch (Throwable $e) {
                $errors[] = 'Gagal menyimpan admin: ' . $e->getMessage();
            }
        }
    }
}
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Installer · Undangan Digital</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/admin.css" rel="stylesheet">
<style>
  .install-wrap { min-height:100vh; background: linear-gradient(135deg,#fff1f2,#fef3c7); padding: 24px; display:flex; align-items:center; justify-content:center; }
  .install-card { width: 100%; max-width: 460px; }
  .steps { display:flex; gap:8px; margin-bottom: 16px; }
  .step { flex:1; height: 4px; background: #e2e8f0; border-radius: 4px; }
  .step.active { background: var(--p); }
</style>
</head>
<body>
<div class="install-wrap">
  <div class="install-card">
    <div style="text-align:center;margin-bottom:18px">
      <div class="auth-logo" style="margin:0 auto 10px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.5-9.5-9C1.2 9 3 5 7 5c2 0 3.5 1 5 3 1.5-2 3-3 5-3 4 0 5.8 4 4.5 7-2 4.5-9.5 9-9.5 9z"/></svg>
      </div>
      <h1 class="page-title" style="margin:0">Installer Undangan Digital</h1>
      <p class="muted">Konfigurasi database & admin pertama</p>
    </div>

    <div class="steps">
      <div class="step <?= $step>=1?'active':'' ?>"></div>
      <div class="step <?= $step>=2?'active':'' ?>"></div>
      <div class="step <?= $step>=9?'active':'' ?>"></div>
    </div>

    <?php if ($errors): ?>
      <div class="alert error"><?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <?php if ($step === 9 || $installed): ?>
      <div class="card">
        <div class="card-body" style="text-align:center">
          <div class="auth-logo" style="margin:0 auto 12px;background:linear-gradient(135deg,#34d399,#059669)">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>
          </div>
          <h2 style="margin:0 0 6px">Instalasi Selesai!</h2>
          <p class="muted mb-4">Untuk keamanan, hapus file <code>install.php</code>.</p>
          <a class="btn btn-primary btn-block" href="admin/login.php">Masuk Admin</a>
        </div>
      </div>
    <?php elseif ($step === 1): ?>
      <form method="post" class="card">
        <div class="card-head"><h2>Step 1 — Database</h2></div>
        <div class="card-body form-row">
          <input type="hidden" name="action" value="db">
          <div><label class="label">Host</label><input class="input" name="db_host" value="localhost"></div>
          <div class="grid-2">
            <div><label class="label">Port</label><input class="input" name="db_port" value="3306"></div>
            <div><label class="label">Database</label><input class="input" name="db_name" value="undangandigital" required></div>
          </div>
          <div class="grid-2">
            <div><label class="label">User</label><input class="input" name="db_user" value="root"></div>
            <div><label class="label">Password</label><input class="input" type="password" name="db_pass"></div>
          </div>
          <button class="btn btn-primary btn-block">Lanjut</button>
        </div>
      </form>
    <?php else: ?>
      <form method="post" class="card">
        <div class="card-head"><h2>Step 2 — Akun Admin</h2></div>
        <div class="card-body form-row">
          <input type="hidden" name="action" value="admin">
          <div><label class="label">Nama Situs</label><input class="input" name="site_name" value="Undangan Digital"></div>
          <div><label class="label">Nama Admin</label><input class="input" name="nama" required></div>
          <div><label class="label">Username</label><input class="input" name="username" required></div>
          <div>
            <label class="label">Password</label>
            <input class="input" type="password" name="password" required>
            <p class="field-help">Minimal 6 karakter</p>
          </div>
          <button class="btn btn-primary btn-block">Selesaikan Instalasi</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
