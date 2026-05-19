<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$client = client_check($pdo);
$site   = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
$msg    = '';

// Get invitations milik klien
$st = $pdo->prepare('SELECT id, slug, judul, tanggal_acara FROM invitations WHERE client_id = ? ORDER BY id DESC');
$st->execute([$client['id']]);
$invs = $st->fetchAll();

$activeId = (int)($_GET['inv'] ?? ($invs[0]['id'] ?? 0));
$active   = null;
foreach ($invs as $i) if ((int)$i['id'] === $activeId) { $active = $i; break; }

// Handle generator add/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $active) {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'guest_add') {
        $nama = trim($_POST['nama'] ?? '');
        $hp   = trim($_POST['no_hp'] ?? '');
        if ($nama !== '') {
            $token = strtoupper(rand_token(6));
            $pdo->prepare('INSERT INTO guests (invitation_id,nama,no_hp,token) VALUES (?,?,?,?)')
                ->execute([$active['id'], $nama, $hp ?: null, $token]);
            $msg = 'Tamu ditambahkan & link siap dibagikan.';
        }
    }
    if ($a === 'guest_delete') {
        $pdo->prepare('DELETE FROM guests WHERE id=? AND invitation_id=?')->execute([(int)$_POST['gid'], $active['id']]);
        $msg = 'Tamu dihapus.';
    }
}

// Data per tab
$rsvpRows = []; $ucapanRows = []; $guests = [];
if ($active) {
    $r = $pdo->prepare('SELECT * FROM rsvp WHERE invitation_id=? ORDER BY id DESC');
    $r->execute([$active['id']]); $rsvpRows = $r->fetchAll();

    $u = $pdo->prepare('SELECT * FROM ucapan WHERE invitation_id=? ORDER BY id DESC');
    $u->execute([$active['id']]); $ucapanRows = $u->fetchAll();

    $g = $pdo->prepare('SELECT * FROM guests WHERE invitation_id=? ORDER BY id DESC');
    $g->execute([$active['id']]); $guests = $g->fetchAll();
}

$tab = $_GET['tab'] ?? 'rsvp';
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel Klien · <?= h($site) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">
<header class="bg-white border-b">
  <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-4">
    <span class="font-semibold text-rose-700">Panel Klien</span>
    <span class="text-sm text-slate-500"><?= h($client['nama']) ?></span>
    <a href="<?= h(base_url()) ?>" class="ml-auto text-sm text-slate-500 hover:text-rose-600">Beranda</a>
  </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-6">

  <?php if (!$invs): ?>
    <div class="bg-white border rounded-xl p-8 text-center">
      <p class="text-slate-600">Belum ada undangan ditugaskan untuk Anda. Silakan hubungi admin.</p>
    </div>
  <?php else: ?>

  <div class="flex flex-wrap items-center gap-2 mb-4">
    <label class="text-sm">Undangan:</label>
    <select onchange="location.href='?t=<?= h($client['token']) ?>&inv='+this.value" class="border rounded-lg px-3 py-2 text-sm">
      <?php foreach ($invs as $i): ?>
        <option value="<?= (int)$i['id'] ?>" <?= $i['id']==$active['id']?'selected':'' ?>><?= h($i['judul']) ?></option>
      <?php endforeach; ?>
    </select>
    <a target="_blank" href="<?= h(base_url($active['slug'])) ?>" class="ml-auto text-sm text-rose-600">Buka Undangan /<?= h($active['slug']) ?> &rarr;</a>
  </div>

  <?php if ($msg): ?><div class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-2 rounded mb-3 text-sm"><?= h($msg) ?></div><?php endif; ?>

  <div class="bg-white border rounded-xl">
    <nav class="flex border-b text-sm">
      <?php
        $tabs = ['rsvp'=>'RSVP', 'ucapan'=>'Ucapan', 'generator'=>'Link Generator'];
        foreach ($tabs as $key=>$lbl):
          $url = '?t='.h($client['token']).'&inv='.(int)$active['id'].'&tab='.$key;
      ?>
        <a href="<?= $url ?>" class="px-4 py-3 <?= $tab===$key?'border-b-2 border-rose-600 text-rose-700 font-medium':'text-slate-600 hover:text-rose-700' ?>"><?= h($lbl) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="p-5">
    <?php if ($tab === 'rsvp'): ?>
      <h2 class="font-semibold mb-3">Daftar RSVP (<?= count($rsvpRows) ?>)</h2>
      <table class="w-full text-sm">
        <thead class="bg-slate-50"><tr>
          <th class="text-left p-2">Nama</th><th class="text-left p-2">Status</th><th class="text-left p-2">Jumlah</th><th class="text-left p-2">Waktu</th>
        </tr></thead>
        <tbody>
        <?php foreach ($rsvpRows as $r): ?>
          <tr class="border-t">
            <td class="p-2"><?= h($r['nama']) ?></td>
            <td class="p-2">
              <?php
                $color = ['hadir'=>'emerald','tidak'=>'red','ragu'=>'amber'][$r['hadir']] ?? 'slate';
              ?>
              <span class="px-2 py-0.5 rounded-full text-xs bg-<?= $color ?>-100 text-<?= $color ?>-700"><?= h($r['hadir']) ?></span>
            </td>
            <td class="p-2"><?= (int)$r['jumlah_tamu'] ?></td>
            <td class="p-2 text-slate-500"><?= h($r['created_at']) ?></td>
          </tr>
        <?php endforeach; if (!$rsvpRows): ?>
          <tr><td colspan="4" class="p-6 text-center text-slate-500">Belum ada konfirmasi.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

    <?php elseif ($tab === 'ucapan'): ?>
      <h2 class="font-semibold mb-3">Daftar Ucapan (<?= count($ucapanRows) ?>)</h2>
      <div class="space-y-3">
        <?php foreach ($ucapanRows as $u): ?>
          <div class="border rounded-lg p-3">
            <div class="flex items-center gap-2 mb-1">
              <strong><?= h($u['nama']) ?></strong>
              <span class="text-xs text-slate-500 ml-auto"><?= h($u['created_at']) ?></span>
            </div>
            <p class="text-sm text-slate-700"><?= nl2br(h($u['pesan'])) ?></p>
          </div>
        <?php endforeach; if (!$ucapanRows): ?>
          <p class="text-center text-slate-500 py-6">Belum ada ucapan.</p>
        <?php endif; ?>
      </div>

    <?php else: // generator ?>
      <h2 class="font-semibold mb-1">Link Generator</h2>
      <p class="text-sm text-slate-600 mb-4">Generate tautan personal untuk setiap tamu. Tamu akan melihat namanya pada undangan.</p>

      <form method="post" class="grid md:grid-cols-3 gap-2 mb-5 items-end">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="guest_add">
        <label class="text-sm">Nama Penerima
          <input name="nama" class="w-full border rounded px-3 py-2 mt-1" required></label>
        <label class="text-sm">No. WhatsApp (opsional)
          <input name="no_hp" class="w-full border rounded px-3 py-2 mt-1" placeholder="628xxxxxxxxxx"></label>
        <button class="bg-rose-600 text-white px-4 py-2 rounded">+ Generate Link</button>
      </form>

      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50"><tr>
            <th class="text-left p-2">Nama</th>
            <th class="text-left p-2">No. HP</th>
            <th class="text-left p-2">Link Personal</th>
            <th class="text-left p-2">Aksi</th>
          </tr></thead>
          <tbody>
          <?php foreach ($guests as $g): $link = base_url($active['slug'].'?to='.$g['token']); ?>
            <tr class="border-t align-top">
              <td class="p-2"><?= h($g['nama']) ?></td>
              <td class="p-2"><?= h($g['no_hp'] ?: '-') ?></td>
              <td class="p-2">
                <input value="<?= h($link) ?>" readonly class="w-full border rounded px-2 py-1 text-xs bg-slate-50" onclick="this.select()">
              </td>
              <td class="p-2 whitespace-nowrap">
                <button type="button" class="text-sky-600 mr-2" onclick="navigator.clipboard.writeText('<?= h($link) ?>');this.textContent='Tersalin'">Salin</button>
                <?php
                  $waText = "Bismillahirrahmanirrahim\n\nKepada Yth. " . $g['nama'] . "\n\nDengan memohon rahmat Allah SWT, kami mengundang Anda pada acara pernikahan kami. Berikut tautan undangan digital:\n" . $link . "\n\nKehadiran & doa restu Anda merupakan kehormatan bagi kami.";
                  $waUrl  = $g['no_hp']
                    ? 'https://wa.me/' . preg_replace('/\D/','', $g['no_hp']) . '?text=' . rawurlencode($waText)
                    : 'https://wa.me/?text=' . rawurlencode($waText);
                ?>
                <a target="_blank" href="<?= h($waUrl) ?>" class="text-emerald-600 mr-2">Kirim WA</a>
                <form method="post" class="inline" onsubmit="return confirm('Hapus tamu ini?')">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="guest_delete">
                  <input type="hidden" name="gid" value="<?= (int)$g['id'] ?>">
                  <button class="text-red-600">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; if (!$guests): ?>
            <tr><td colspan="4" class="p-6 text-center text-slate-500">Belum ada tamu di-generate.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>
</main>
</body>
</html>
