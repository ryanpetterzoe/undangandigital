<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$client = client_check($pdo);
$site   = $CONFIG['app']['site_name'] ?? 'Undangan Digital';
$msg    = '';

$st = $pdo->prepare('SELECT id, slug, judul, tanggal_acara FROM invitations WHERE client_id = ? ORDER BY id DESC');
$st->execute([$client['id']]);
$invs = $st->fetchAll();

$activeId = (int)($_GET['inv'] ?? ($invs[0]['id'] ?? 0));
$active   = null;
foreach ($invs as $i) if ((int)$i['id'] === $activeId) { $active = $i; break; }

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

$LOGO_SVG = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.5-9.5-9C1.2 9 3 5 7 5c2 0 3.5 1 5 3 1.5-2 3-3 5-3 4 0 5.8 4 4.5 7-2 4.5-9.5 9-9.5 9z"/></svg>';
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Panel Klien · <?= h($site) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Philosopher:wght@400;700&display=swap" rel="stylesheet">
<link href="<?= h(asset('css/admin.css')) ?>" rel="stylesheet">
<style>
  /* override sidebar untuk client (no sidebar; just topbar) */
  .app-shell { display: block; }
  @media (min-width: 768px) { .app-shell { display: block; grid-template-columns: none; } }
  .app-topbar { display: flex !important; }
  .app-sidebar { display: none !important; }
  .app-main { padding-top: 18px; }
</style>
</head>
<body>
<div class="app-shell">
  <header class="app-topbar">
    <span class="app-brand"><span class="logo"><?= $LOGO_SVG ?></span><span>Panel Klien</span></span>
    <span class="spacer"></span>
    <span class="app-user-mobile"><?= h($client['nama']) ?></span>
  </header>
  <main class="app-main">

  <?php if (!$invs): ?>
    <div class="card">
      <div class="empty-state">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <p>Belum ada undangan ditugaskan untuk Anda.<br>Silakan hubungi admin.</p>
      </div>
    </div>
  <?php else: ?>

  <div class="row mb-4">
    <label class="muted">Undangan:</label>
    <select class="select" style="width:auto;flex:1;min-width:160px" onchange="location.href='?t=<?= h($client['token']) ?>&inv='+this.value">
      <?php foreach ($invs as $i): ?>
        <option value="<?= (int)$i['id'] ?>" <?= $i['id']==$active['id']?'selected':'' ?>><?= h($i['judul']) ?></option>
      <?php endforeach; ?>
    </select>
    <a target="_blank" href="<?= h(base_url($active['slug'])) ?>" class="btn btn-soft btn-sm">Buka /<?= h($active['slug']) ?> &rarr;</a>
  </div>

  <?php if ($msg): ?><div class="alert success"><?= h($msg) ?></div><?php endif; ?>

  <div class="card">
    <nav class="tabs">
      <?php
        $tabsList = ['rsvp'=>'RSVP', 'ucapan'=>'Ucapan', 'generator'=>'Link Generator'];
        foreach ($tabsList as $key=>$lbl):
          $url = '?t='.h($client['token']).'&inv='.(int)$active['id'].'&tab='.$key;
      ?>
        <a href="<?= $url ?>" class="tab <?= $tab===$key?'active':'' ?>"><?= h($lbl) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="card-body">
    <?php if ($tab === 'rsvp'): ?>
      <div class="row mb-3">
        <h2 style="margin:0;font-size:1rem">Daftar RSVP</h2>
        <span class="badge slate"><?= count($rsvpRows) ?></span>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Nama</th><th>Status</th><th>Jumlah</th><th>Waktu</th></tr></thead>
          <tbody>
          <?php foreach ($rsvpRows as $r): ?>
            <tr>
              <td><strong><?= h($r['nama']) ?></strong></td>
              <td>
                <?php $cl = ['hadir'=>'green','tidak'=>'red','ragu'=>'amber'][$r['hadir']] ?? 'slate'; ?>
                <span class="badge <?= $cl ?>"><?= h($r['hadir']) ?></span>
              </td>
              <td><?= (int)$r['jumlah_tamu'] ?></td>
              <td class="muted"><?= h($r['created_at']) ?></td>
            </tr>
          <?php endforeach; if (!$rsvpRows): ?>
            <tr><td colspan="4"><div class="empty-state">Belum ada konfirmasi.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($tab === 'ucapan'): ?>
      <div class="row mb-3">
        <h2 style="margin:0;font-size:1rem">Daftar Ucapan</h2>
        <span class="badge slate"><?= count($ucapanRows) ?></span>
      </div>
      <div class="form-row">
        <?php foreach ($ucapanRows as $u): ?>
          <div class="card" style="box-shadow:none">
            <div class="card-body">
              <div class="row mb-2">
                <strong><?= h($u['nama']) ?></strong>
                <span class="spacer"></span>
                <small class="muted"><?= h($u['created_at']) ?></small>
              </div>
              <p class="muted" style="margin:0;color:var(--text)"><?= nl2br(h($u['pesan'])) ?></p>
            </div>
          </div>
        <?php endforeach; if (!$ucapanRows): ?>
          <div class="empty-state">Belum ada ucapan.</div>
        <?php endif; ?>
      </div>

    <?php else: // generator ?>
      <h2 style="margin:0 0 4px;font-size:1rem">Link Generator</h2>
      <p class="muted mb-4">Buat tautan personal untuk setiap tamu. Tamu akan melihat namanya di undangan.</p>

      <form method="post" class="form-row mb-4">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="guest_add">
        <div class="grid-2">
          <div><label class="label">Nama Penerima</label><input class="input" name="nama" required></div>
          <div><label class="label">No. WhatsApp (opsional)</label><input class="input" type="tel" name="no_hp" placeholder="628xxxxxxxxxx"></div>
        </div>
        <button class="btn btn-primary">+ Generate Link</button>
      </form>

      <div class="form-row">
        <?php foreach ($guests as $g): $link = base_url($active['slug'].'?to='.$g['token']); ?>
          <div class="card" style="box-shadow:none">
            <div class="card-body form-row">
              <div class="row">
                <strong><?= h($g['nama']) ?></strong>
                <span class="spacer"></span>
                <span class="muted"><?= h($g['no_hp'] ?: '—') ?></span>
              </div>
              <input class="input" readonly value="<?= h($link) ?>" onclick="this.select()" style="font-size:.82rem;font-family:ui-monospace,monospace">
              <div class="row-end">
                <button type="button" class="btn btn-outline btn-sm" data-copy="<?= h($link) ?>">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                  <span>Salin</span>
                </button>
                <?php
                  $waText = "Bismillahirrahmanirrahim\n\nKepada Yth. " . $g['nama'] . "\n\nDengan memohon rahmat Allah SWT, kami mengundang Anda pada acara pernikahan kami. Berikut tautan undangan digital:\n" . $link . "\n\nKehadiran & doa restu Anda merupakan kehormatan bagi kami.";
                  $waUrl  = $g['no_hp']
                    ? 'https://wa.me/' . preg_replace('/\D/','', $g['no_hp']) . '?text=' . rawurlencode($waText)
                    : 'https://wa.me/?text=' . rawurlencode($waText);
                ?>
                <a target="_blank" href="<?= h($waUrl) ?>" class="btn btn-sm" style="background:#25d366;color:#fff">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.32A8 8 0 0 0 12.04 4a8 8 0 0 0-6.97 11.97L4 20l4.13-1.07A8 8 0 0 0 12.04 20h.01a8 8 0 0 0 7.95-8c0-2.13-.83-4.14-2.4-5.68zm-5.56 12.3h-.01a6.6 6.6 0 0 1-3.36-.92l-.24-.14-2.45.64.65-2.39-.16-.25a6.6 6.6 0 1 1 12.23-3.55 6.6 6.6 0 0 1-6.66 6.6zm3.6-4.94c-.2-.1-1.16-.57-1.34-.64-.18-.07-.31-.1-.44.1s-.5.64-.62.78c-.11.13-.23.15-.43.05-.2-.1-.83-.3-1.58-.97-.59-.52-.98-1.16-1.1-1.36-.11-.2-.01-.31.09-.41.09-.09.2-.23.3-.35.1-.11.13-.2.2-.33.07-.13.04-.25-.02-.35-.05-.1-.44-1.06-.6-1.45-.16-.38-.32-.33-.44-.34-.11 0-.24-.01-.37-.01s-.34.05-.52.25c-.18.2-.69.67-.69 1.64 0 .97.7 1.9.8 2.04.1.13 1.4 2.13 3.4 2.99.47.2.85.32 1.14.42.48.15.92.13 1.27.08.39-.06 1.16-.47 1.32-.93.16-.46.16-.85.11-.93-.05-.08-.18-.13-.38-.23z"/></svg>
                  <span>WA</span>
                </a>
                <form method="post" onsubmit="return confirm('Hapus tamu?')">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="guest_delete">
                  <input type="hidden" name="gid" value="<?= (int)$g['id'] ?>">
                  <button class="btn btn-danger btn-sm">Hapus</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; if (!$guests): ?>
          <div class="empty-state">Belum ada tamu di-generate.</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>

  <footer class="app-footer">&copy; <?= date('Y') ?> <?= h($site) ?> &middot; Panel Klien</footer>
  </main>
</div>
<script>
document.addEventListener('click', function(ev){
  var b = ev.target.closest('[data-copy]'); if (!b) return;
  var txt = b.getAttribute('data-copy') || ''; if (!txt) return;
  navigator.clipboard.writeText(txt).then(function(){
    var orig = b.innerHTML; b.innerHTML = '<span>Tersalin</span>';
    setTimeout(function(){ b.innerHTML = orig; }, 1500);
  });
});
</script>
</body>
</html>
