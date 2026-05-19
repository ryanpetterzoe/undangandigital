<?php
$pageTitle = 'Daftar Undangan';
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
        // Pastikan unik
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
        // Buat 2 mempelai default
        $pdo->prepare('INSERT INTO mempelai (invitation_id,peran,nama) VALUES (?,?,?), (?,?,?)')
            ->execute([$newId,'pria','', $newId,'wanita','']);
        // Buat 2 event default (akad & resepsi)
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
<div class="flex items-center mb-4">
  <h1 class="text-2xl font-semibold">Daftar Undangan</h1>
  <button onclick="document.getElementById('m').classList.remove('hidden')" class="ml-auto bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm">+ Tambah Undangan</button>
</div>
<?php if ($msg): ?><div class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-2 rounded mb-3 text-sm"><?= h($msg) ?></div><?php endif; ?>

<div class="bg-white rounded-xl border overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-600">
      <tr><th class="text-left p-3">Judul</th><th class="text-left p-3">Slug / Link</th><th class="text-left p-3">Klien</th><th class="text-left p-3">Tema</th><th class="text-left p-3">Tanggal</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr class="border-t">
        <td class="p-3 font-medium"><?= h($r['judul']) ?></td>
        <td class="p-3"><a class="text-rose-600" target="_blank" href="<?= h(base_url($r['slug'])) ?>">/<?= h($r['slug']) ?></a></td>
        <td class="p-3"><?= h($r['client_nama'] ?? '-') ?></td>
        <td class="p-3"><?= h($r['tema']) ?></td>
        <td class="p-3"><?= h(format_tanggal($r['tanggal_acara'] ?? '')) ?></td>
        <td class="p-3 text-right whitespace-nowrap">
          <a class="text-sky-600 mr-2" href="<?= h(base_url('admin/edit.php?id='.$r['id'])) ?>">Edit</a>
          <form method="post" class="inline" onsubmit="return confirm('Hapus undangan?')">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="text-red-600">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; if (!$rows): ?>
      <tr><td colspan="6" class="p-6 text-center text-slate-500">Belum ada data.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div id="m" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4">
  <form method="post" class="bg-white rounded-xl p-6 w-full max-w-md space-y-3">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="create">
    <h3 class="font-semibold text-lg">Tambah Undangan</h3>
    <label class="block text-sm">Judul (mis. "Rudi & Diana")
      <input name="judul" class="w-full border rounded px-3 py-2 mt-1" required></label>
    <label class="block text-sm">Slug (kosongkan untuk auto)
      <input name="slug" class="w-full border rounded px-3 py-2 mt-1" placeholder="rudidandiana"></label>
    <label class="block text-sm">Tanggal Acara
      <input type="date" name="tanggal_acara" class="w-full border rounded px-3 py-2 mt-1"></label>
    <label class="block text-sm">Klien (opsional)
      <select name="client_id" class="w-full border rounded px-3 py-2 mt-1">
        <option value="">-- tanpa klien --</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= h($c['nama']) ?></option>
        <?php endforeach; ?>
      </select></label>
    <div class="flex gap-2 pt-2">
      <button type="button" onclick="document.getElementById('m').classList.add('hidden')" class="px-3 py-2 rounded border">Batal</button>
      <button class="ml-auto bg-rose-600 text-white px-4 py-2 rounded">Simpan & Edit</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
