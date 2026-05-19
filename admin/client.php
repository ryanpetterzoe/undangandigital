<?php
$pageTitle = 'Klien';
$activeNav = 'client';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'create') {
        $token = rand_token(16);
        $pdo->prepare('INSERT INTO clients (nama,email,no_hp,token) VALUES (?,?,?,?)')->execute([
            trim($_POST['nama']),
            trim($_POST['email'] ?? '') ?: null,
            trim($_POST['no_hp'] ?? '') ?: null,
            $token
        ]);
    }
    if ($a === 'delete') {
        $pdo->prepare('DELETE FROM clients WHERE id=?')->execute([(int)$_POST['id']]);
    }
    if ($a === 'regen') {
        $pdo->prepare('UPDATE clients SET token=? WHERE id=?')->execute([rand_token(16), (int)$_POST['id']]);
    }
    redirect(base_url('admin/client.php'));
}

$rows = $pdo->query('SELECT * FROM clients ORDER BY id DESC')->fetchAll();
?>
<div class="page-header">
  <h1 class="page-title">Klien</h1>
  <span class="spacer"></span>
  <button onclick="uOpenModal('mAdd')" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    <span>Tambah</span>
  </button>
</div>
<p class="muted mb-4">Klien dapat membuka panel mereka tanpa login menggunakan link akses (token unik).</p>

<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Nama</th><th>Kontak</th><th>Link Panel</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $url = base_url('client/?t=' . $r['token']); ?>
        <tr>
          <td><strong><?= h($r['nama']) ?></strong></td>
          <td>
            <div class="muted"><?= h($r['email'] ?: '—') ?></div>
            <div class="muted"><?= h($r['no_hp'] ?: '') ?></div>
          </td>
          <td><a class="row-link break-all" target="_blank" href="<?= h($url) ?>"><?= h($url) ?></a></td>
          <td class="text-right">
            <div class="row-end">
              <form method="post">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="regen">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-outline btn-sm" type="submit">Regenerate</button>
              </form>
              <form method="post" onsubmit="return confirm('Hapus klien?')">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="4">
          <div class="empty-state">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <p>Belum ada klien.</p>
          </div>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="mAdd" class="modal" onclick="uCloseModal('mAdd')">
  <form method="post" class="modal-card" onclick="event.stopPropagation()">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="create">
    <h3>Tambah Klien</h3>
    <div class="form-row">
      <div><label class="label">Nama</label><input class="input" name="nama" required></div>
      <div><label class="label">Email</label><input class="input" type="email" name="email"></div>
      <div><label class="label">No. HP</label><input class="input" type="tel" name="no_hp" placeholder="628xxxxxxxxxx"></div>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-outline btn-block" onclick="uCloseModal('mAdd')">Batal</button>
      <button class="btn btn-primary btn-block">Simpan</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
