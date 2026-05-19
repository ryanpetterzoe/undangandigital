<?php
$pageTitle = 'Klien';
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
<div class="flex items-center mb-3">
  <h1 class="text-2xl font-semibold">Klien</h1>
  <button onclick="document.getElementById('m').classList.remove('hidden')" class="ml-auto bg-rose-600 text-white px-4 py-2 rounded">+ Tambah Klien</button>
</div>
<p class="text-sm text-slate-600 mb-3">Klien dapat membuka panel mereka tanpa login menggunakan link akses (token unik).</p>

<div class="bg-white border rounded-xl overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50"><tr>
      <th class="text-left p-3">Nama</th><th class="text-left p-3">Kontak</th><th class="text-left p-3">Link Panel</th><th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $url = base_url('client/?t=' . $r['token']); ?>
      <tr class="border-t">
        <td class="p-3 font-medium"><?= h($r['nama']) ?></td>
        <td class="p-3"><?= h($r['email'] ?: '-') ?><br><span class="text-xs text-slate-500"><?= h($r['no_hp'] ?: '') ?></span></td>
        <td class="p-3"><a class="text-rose-600 break-all" target="_blank" href="<?= h($url) ?>"><?= h($url) ?></a></td>
        <td class="p-3 text-right whitespace-nowrap">
          <form method="post" class="inline">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="regen">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="text-amber-600">Regenerate</button>
          </form>
          <form method="post" class="inline ml-2" onsubmit="return confirm('Hapus klien?')">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="text-red-600">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="4" class="p-6 text-center text-slate-500">Belum ada klien.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div id="m" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4">
  <form method="post" class="bg-white rounded-xl p-6 w-full max-w-md space-y-3">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="create">
    <h3 class="font-semibold text-lg">Tambah Klien</h3>
    <label class="block text-sm">Nama <input name="nama" class="w-full border rounded px-3 py-2 mt-1" required></label>
    <label class="block text-sm">Email <input name="email" class="w-full border rounded px-3 py-2 mt-1"></label>
    <label class="block text-sm">No. HP <input name="no_hp" class="w-full border rounded px-3 py-2 mt-1"></label>
    <div class="flex gap-2 pt-2">
      <button type="button" onclick="document.getElementById('m').classList.add('hidden')" class="px-3 py-2 rounded border">Batal</button>
      <button class="ml-auto bg-rose-600 text-white px-4 py-2 rounded">Simpan</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
