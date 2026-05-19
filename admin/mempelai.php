<?php
$pageTitle = 'Daftar Undangan';
$activeNav = 'mempelai';
require_once __DIR__ . '/../includes/admin_header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $judul     = trim($_POST['judul'] ?? '');
        $slugIn    = trim($_POST['slug'] ?? '');
        $tanggal   = $_POST['tanggal_acara'] ?? null;
        $clientId  = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
        $slug      = slugify($slugIn !== '' ? $slugIn : $judul);
        $i = 1; $base = $slug;
        while (true) {
            $st = $pdo->prepare('SELECT id FROM invitations WHERE slug=?');
            $st->execute([$slug]);
            if (!$st->fetch()) break;
            $slug = $base . '-' . (++$i);
        }
        $st = $pdo->prepare('INSERT INTO invitations (client_id,slug,judul,tanggal_acara,tema) VALUES (?,?,?,?,?)');
        $st->execute([$clientId, $slug, $judul, $tanggal ?: null, 'theme01']);
        $newId = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO mempelai (invitation_id,peran,nama) VALUES (?,?,?), (?,?,?)')
            ->execute([$newId,'pria','', $newId,'wanita','']);
        $stE = $pdo->prepare('INSERT INTO events (invitation_id,jenis,tanggal_mulai,tanggal_selesai,urutan) VALUES (?,?,?,?,?)');
        $base_dt = $tanggal ? $tanggal . ' 08:00:00' : date('Y-m-d 08:00:00');
        $end_dt  = $tanggal ? $tanggal . ' 10:00:00' : date('Y-m-d 10:00:00');
        $stE->execute([$newId,'Akad Nikah',$base_dt,$end_dt,1]);
        $stE->execute([$newId,'Resepsi',$tanggal ? $tanggal.' 11:00:00' : date('Y-m-d 11:00:00'), $tanggal ? $tanggal.' 14:00:00' : date('Y-m-d 14:00:00'),2]);
        redirect(base_url('admin/edit.php?id='.$newId));
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM invitations WHERE id=?')->execute([$id]);
        $msg = 'Undangan dihapus.';
    }
}

$rows    = $pdo->query('SELECT i.*, c.nama AS client_nama FROM invitations i LEFT JOIN clients c ON c.id=i.client_id ORDER BY i.id DESC')->fetchAll();
$clients = $pdo->query('SELECT id,nama FROM clients ORDER BY nama')->fetchAll();
?>
<div class="page-header">
  <h1 class="page-title">Daftar Undangan</h1>
  <span class="spacer"></span>
  <button onclick="uOpenModal('mAdd')" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    <span>Tambah</span>
  </button>
</div>
<?php if ($msg): ?><div class="alert success"><?= h($msg) ?></div><?php endif; ?>

<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Judul</th><th>Slug / Link</th><th>Klien</th><th>Tema</th><th>Tanggal</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= h($r['judul']) ?></strong></td>
          <td><a class="row-link break-all" target="_blank" href="<?= h(base_url($r['slug'])) ?>">/<?= h($r['slug']) ?></a></td>
          <td class="muted"><?= h($r['client_nama'] ?? '-') ?></td>
          <td><span class="badge slate"><?= h($r['tema']) ?></span></td>
          <td class="muted"><?= h(format_tanggal($r['tanggal_acara'] ?? '')) ?></td>
          <td class="text-right">
            <div class="row-end">
              <a class="btn btn-soft btn-sm" href="<?= h(base_url('admin/edit.php?id='.$r['id'])) ?>">Edit</a>
              <form method="post" onsubmit="return confirm('Hapus undangan ini?')">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="6">
          <div class="empty-state">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <p>Belum ada undangan.<br><button class="btn btn-primary btn-sm mt-3" onclick="uOpenModal('mAdd')">+ Buat Undangan Pertama</button></p>
          </div>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div id="mAdd" class="modal" onclick="uCloseModal('mAdd')">
  <form method="post" class="modal-card" onclick="event.stopPropagation()">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="create">
    <h3>Tambah Undangan Baru</h3>
    <div class="form-row">
      <div>
        <label class="label">Judul <span class="muted">(mis. "Rudi & Diana")</span></label>
        <input class="input" name="judul" required>
      </div>
      <div>
        <label class="label">Slug</label>
        <input class="input" name="slug" placeholder="rudidandiana">
        <p class="field-help">Kosongkan untuk auto. Akan menjadi /<em>slug</em></p>
      </div>
      <div>
        <label class="label">Tanggal Acara</label>
        <input class="input" type="date" name="tanggal_acara">
      </div>
      <div>
        <label class="label">Klien (opsional)</label>
        <select class="select" name="client_id">
          <option value="">— tanpa klien —</option>
          <?php foreach ($clients as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= h($c['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-outline btn-block" onclick="uCloseModal('mAdd')">Batal</button>
      <button class="btn btn-primary btn-block">Simpan & Edit</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
