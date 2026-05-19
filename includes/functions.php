<?php
declare(strict_types=1);

function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string {
    global $CONFIG;
    $base = $CONFIG['app']['base_url'] ?? '';
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        // Cloudflare may set this header
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Detect script subdir (untuk install di subfolder XAMPP, mis. /undangan)
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        // Bila script ada di /admin atau /invitation, naik ke parent
        if (preg_match('#/(admin|client|invitation|themes|assets|uploads)$#', $dir)) {
            $dir = dirname($dir);
        }
        $base = $scheme . '://' . $host . ($dir === '/' ? '' : $dir);
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return base_url('assets/' . ltrim($path, '/'));
}

function upload_url(string $path): string {
    if ($path === '' ) return '';
    if (preg_match('#^https?://#', $path)) return $path;
    return base_url('uploads/' . ltrim($path, '/'));
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    $t = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)$t)) {
        http_response_code(419);
        exit('CSRF token mismatch');
    }
}

function slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string)$s, '-');
}

function rand_token(int $len = 16): string {
    return bin2hex(random_bytes($len));
}

function get_invitation_by_slug(PDO $pdo, string $slug): ?array {
    $st = $pdo->prepare('SELECT * FROM invitations WHERE slug = ? AND is_active = 1 LIMIT 1');
    $st->execute([$slug]);
    $row = $st->fetch();
    return $row ?: null;
}

function get_invitation_full(PDO $pdo, int $id): array {
    $inv = $pdo->prepare('SELECT * FROM invitations WHERE id=?');
    $inv->execute([$id]);
    $invitation = $inv->fetch();
    if (!$invitation) return [];

    $mp = $pdo->prepare('SELECT * FROM mempelai WHERE invitation_id=? ORDER BY peran DESC');
    $mp->execute([$id]);

    $ev = $pdo->prepare('SELECT * FROM events WHERE invitation_id=? ORDER BY urutan, tanggal_mulai');
    $ev->execute([$id]);

    $kc = $pdo->prepare('SELECT * FROM kisah_cinta WHERE invitation_id=? ORDER BY urutan, id');
    $kc->execute([$id]);

    $gl = $pdo->prepare('SELECT * FROM galeri WHERE invitation_id=? ORDER BY urutan, id');
    $gl->execute([$id]);

    $uc = $pdo->prepare('SELECT * FROM ucapan WHERE invitation_id=? ORDER BY id DESC LIMIT 100');
    $uc->execute([$id]);

    return [
        'invitation' => $invitation,
        'mempelai'   => $mp->fetchAll(),
        'events'     => $ev->fetchAll(),
        'kisah'      => $kc->fetchAll(),
        'galeri'     => $gl->fetchAll(),
        'ucapan'     => $uc->fetchAll(),
    ];
}

function upload_file(string $field, string $subdir = 'foto', array $allowed = ['jpg','jpeg','png','webp','gif']): ?string {
    if (empty($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'] ?? '')) return null;
    $f   = $_FILES[$field];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) return null;
    $dir = __DIR__ . '/../uploads/' . trim($subdir, '/');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) return null;
    return trim($subdir, '/') . '/' . $name;
}

function format_tanggal(string $dt, string $fmt = 'd F Y'): string {
    if ($dt === '' || $dt === null) return '';
    $ts = strtotime($dt);
    if (!$ts) return $dt;
    static $bln = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    static $hari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    $out = date($fmt, $ts);
    $out = strtr($out, $hari);
    $out = preg_replace_callback('/\bF\b/', fn() => $bln[(int)date('n',$ts)], $out);
    // also replace English month names produced by date()
    $out = strtr($out, [
        'January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
        'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September',
        'October'=>'Oktober','November'=>'November','December'=>'Desember',
    ]);
    return $out;
}

function gcal_url(array $ev, string $title): string {
    $start = date('Ymd\THis', strtotime($ev['tanggal_mulai']));
    $end   = !empty($ev['tanggal_selesai']) ? date('Ymd\THis', strtotime($ev['tanggal_selesai'])) : date('Ymd\THis', strtotime($ev['tanggal_mulai'].' +2 hours'));
    $params = http_build_query([
        'action' => 'TEMPLATE',
        'text'   => $title,
        'dates'  => $start.'/'.$end,
        'details'=> ($ev['tempat'] ?? '') . "\n" . ($ev['alamat'] ?? ''),
        'location'=> trim(($ev['tempat'] ?? '') . ' ' . ($ev['alamat'] ?? '')),
    ]);
    return 'https://calendar.google.com/calendar/render?' . $params;
}

function list_themes(): array {
    $dir = __DIR__ . '/../themes';
    $out = [];
    if (!is_dir($dir)) return $out;
    foreach (scandir($dir) as $d) {
        if ($d === '.' || $d === '..') continue;
        if (is_dir($dir.'/'.$d) && file_exists($dir.'/'.$d.'/layout.php')) {
            $out[] = $d;
        }
    }
    sort($out);
    return $out;
}
