<?php
// install.php - Installer ala WordPress
// Jalankan sekali: http://yourhost/install.php
// Setelah selesai, file ini akan menulis config.php dan dapat dihapus.

declare(strict_types=1);
session_start();

$installed = file_exists(__DIR__ . '/config.php');
$step      = (int)($_GET['step'] ?? ($installed ? 9 : 1));
$errors    = [];
$success   = '';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function write_config(array $cfg): bool {
    $code = "<?php\nreturn " . var_export($cfg, true) . ";\n";
    return (bool) file_put_contents(__DIR__ . '/config.php', $code);
}

function run_schema(PDO $pdo, string $sqlFile): void {
    $sql = file_get_contents($sqlFile);
    if ($sql === false) throw new RuntimeException('schema.sql tidak ditemukan');
    // Split per statement (cukup utk schema kita yg sederhana)
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
                    'security' => [
                        'app_key' => bin2hex(random_bytes(24)),
                    ],
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
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installer - Undangan Digital</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-rose-50 to-amber-50 min-h-screen">
<div class="max-w-xl mx-auto p-6">
  <h1 class="text-2xl font-bold text-rose-700 mb-2">Installer Undangan Digital</h1>
  <p class="text-sm text-gray-600 mb-6">Konfigurasi database & admin pertama.</p>

  <?php if ($errors): ?>
    <div class="bg-red-100 border border-red-300 text-red-800 p-3 rounded mb-4">
      <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($step === 9 || $installed): ?>
    <div class="bg-white rounded-xl shadow p-6 border border-emerald-200">
      <h2 class="text-xl font-semibold text-emerald-700 mb-2">Instalasi Selesai</h2>
      <p class="text-gray-700 mb-4">Sistem siap digunakan. Untuk keamanan, hapus <code>install.php</code>.</p>
      <a class="inline-block px-4 py-2 bg-rose-600 text-white rounded" href="admin/login.php">Masuk Admin</a>
    </div>
  <?php elseif ($step === 1): ?>
    <form method="post" class="bg-white rounded-xl shadow p-6 space-y-3">
      <input type="hidden" name="action" value="db">
      <h2 class="font-semibold text-lg">Step 1 — Database</h2>
      <label class="block text-sm">Host
        <input name="db_host" value="localhost" class="w-full border rounded px-3 py-2"></label>
      <label class="block text-sm">Port
        <input name="db_port" value="3306" class="w-full border rounded px-3 py-2"></label>
      <label class="block text-sm">Nama Database
        <input name="db_name" value="undangandigital" class="w-full border rounded px-3 py-2" required></label>
      <label class="block text-sm">User
        <input name="db_user" value="root" class="w-full border rounded px-3 py-2"></label>
      <label class="block text-sm">Password
        <input type="password" name="db_pass" class="w-full border rounded px-3 py-2"></label>
      <button class="w-full bg-rose-600 text-white py-2 rounded font-medium">Lanjut</button>
    </form>
  <?php else: // step 2 ?>
    <form method="post" class="bg-white rounded-xl shadow p-6 space-y-3">
      <input type="hidden" name="action" value="admin">
      <h2 class="font-semibold text-lg">Step 2 — Akun Admin & Site</h2>
      <label class="block text-sm">Nama Situs
        <input name="site_name" value="Undangan Digital" class="w-full border rounded px-3 py-2"></label>
      <label class="block text-sm">Nama Admin
        <input name="nama" class="w-full border rounded px-3 py-2" required></label>
      <label class="block text-sm">Username
        <input name="username" class="w-full border rounded px-3 py-2" required></label>
      <label class="block text-sm">Password (min 6)
        <input type="password" name="password" class="w-full border rounded px-3 py-2" required></label>
      <button class="w-full bg-rose-600 text-white py-2 rounded font-medium">Selesaikan Instalasi</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
