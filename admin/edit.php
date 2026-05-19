<?php
$pageTitle = 'Edit Undangan';
require_once __DIR__ . '/../includes/admin_header.php';

$id = (int)($_GET['id'] ?? 0);
$inv = $pdo->prepare('SELECT * FROM invitations WHERE id=?');
$inv->execute([$id]);
$invitation = $inv->fetch();
if (!$invitation) { echo '<p>Undangan tidak ditemukan.</p>'; require_once __DIR__.'/../includes/admin_footer.php'; exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';

    if ($a === 'invitation') {
        // Background upload
        $bg = $invitation['background'];
        $newBg = upload_file('background', 'bg');
        if ($newBg) $bg = $newBg;
        // QRIS upload
        $qris = $invitation['qris'];
        $newQ = upload_file('qris_file', 'qris');
        if ($newQ) $qris = $newQ;

        $st = $pdo->prepare('UPDATE invitations SET judul=?,tanggal_acara=?,tema=?,background=?,qris=?,quote_arab=?,quote_arti=?,doa_restu=?,mohon_konfirmasi=?,bank_info=?,livestream_url=?,livestream_text=?,show_livestream=?,show_kisah=?,show_galeri=?,show_kado=?,is_active=? WHERE id=?');
        $st->execute([
            trim($_POST['judul'] ?? ''),
            $_POST['tanggal_acara'] ?: null,
            $_POST['tema'] ?? 'theme01',
            $bg, $qris,
            $_POST['quote_arab'] ?? '',
            $_POST['quote_arti'] ?? '',
            $_POST['doa_restu'] ?? '',
            $_POST['mohon_konfirmasi'] ?? '',
            $_POST['bank_info'] ?? '',
            $_POST['livestream_url'] ?? '',
            $_POST['livestream_text'] ?? '',
            isset($_POST['show_livestream']) ? 1 : 0,
            isset($_POST['show_kisah']) ? 1 : 0,
            isset($_POST['show_galeri']) ? 1 : 0,
            isset($_POST['show_kado']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);
        $msg = 'Detail undangan disimpan.';
    }

    if ($a === 'mempelai') {
        foreach (($_POST['mp'] ?? []) as $mid => $row) {
            $foto = null;
            if (!empty($_FILES['mp_foto']['name'][$mid])) {
                $_FILES['__tmp'] = [
                    'name' => $_FILES['mp_foto']['name'][$mid],
                    'type' => $_FILES['mp_foto']['type'][$mid],
                    'tmp_name' => $_FILES['mp_foto']['tmp_name'][$mid],
                    'error' => $_FILES['mp_foto']['error'][$mid],
                    'size' => $_FILES['mp_foto']['size'][$mid],
                ];
                $foto = upload_file('__tmp', 'foto');
            }
            $sql = 'UPDATE mempelai SET nama=?, nama_panggilan=?, ayah=?, ibu=?, deskripsi=?, instagram=?' . ($foto ? ', foto=?' : '') . ' WHERE id=? AND invitation_id=?';
            $params = [$row['nama'] ?? '', $row['nama_panggilan'] ?? '', $row['ayah'] ?? '', $row['ibu'] ?? '', $row['deskripsi'] ?? '', $row['instagram'] ?? ''];
            if ($foto) $params[] = $foto;
            $params[] = (int)$mid;
            $params[] = $id;
            $pdo->prepare($sql)->execute($params);
        }
        $msg = 'Mempelai disimpan.';
    }

    if ($a === 'event_save') {
        $eid = (int)($_POST['eid'] ?? 0);
        $data = [
            $_POST['jenis'] ?? '',
            $_POST['tanggal_mulai'] ?? null,
            $_POST['tanggal_selesai'] ?: null,
            $_POST['tempat'] ?? '',
            $_POST['alamat'] ?? '',
            $_POST['maps_url'] ?? '',
            (int)($_POST['urutan'] ?? 0),
        ];
        if ($eid) {
            $data[] = $eid; $data[] = $id;
            $pdo->prepare('UPDATE events SET jenis=?,tanggal_mulai=?,tanggal_selesai=?,tempat=?,alamat=?,maps_url=?,urutan=? WHERE id=? AND invitation_id=?')->execute($data);
        } else {
            array_unshift($data, $id);
            $pdo->prepare('INSERT INTO events (invitation_id,jenis,tanggal_mulai,tanggal_selesai,tempat,alamat,maps_url,urutan) VALUES (?,?,?,?,?,?,?,?)')->execute($data);
        }
        $msg = 'Acara disimpan.';
    }
    if ($a === 'event_delete') {
        $pdo->prepare('DELETE FROM events WHERE id=? AND invitation_id=?')->execute([(int)$_POST['eid'], $id]);
        $msg = 'Acara dihapus.';
    }

    if ($a === 'kisah_save') {
        $kid = (int)($_POST['kid'] ?? 0);
        $foto = upload_file('kisah_foto', 'kisah');
        if ($kid) {
            $row = $pdo->prepare('SELECT foto FROM kisah_cinta WHERE id=? AND invitation_id=?');
            $row->execute([$kid, $id]);
            $cur = $row->fetch();
            $fotoFinal = $foto ?: ($cur['foto'] ?? null);
            $pdo->prepare('UPDATE kisah_cinta SET judul=?,tanggal=?,deskripsi=?,foto=?,urutan=? WHERE id=? AND invitation_id=?')
                ->execute([$_POST['judul'] ?? '', $_POST['tanggal'] ?? '', $_POST['deskripsi'] ?? '', $fotoFinal, (int)($_POST['urutan'] ?? 0), $kid, $id]);
        } else {
            $pdo->prepare('INSERT INTO kisah_cinta (invitation_id,judul,tanggal,deskripsi,foto,urutan) VALUES (?,?,?,?,?,?)')
                ->execute([$id, $_POST['judul'] ?? '', $_POST['tanggal'] ?? '', $_POST['deskripsi'] ?? '', $foto, (int)($_POST['urutan'] ?? 0)]);
        }
        $msg = 'Kisah disimpan.';
    }
    if ($a === 'kisah_delete') {
        $pdo->prepare('DELETE FROM kisah_cinta WHERE id=? AND invitation_id=?')->execute([(int)$_POST['kid'], $id]);
        $msg = 'Kisah dihapus.';
    }

    if ($a === 'galeri_add' && !empty($_FILES['galeri_files'])) {
        $files = $_FILES['galeri_files'];
        $count = is_array($files['name']) ? count($files['name']) : 0;
        for ($i=0; $i<$count; $i++) {
            if (empty($files['tmp_name'][$i])) continue;
            $_FILES['__g'] = [
                'name' => $files['name'][$i], 'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i],
            ];
            $p = upload_file('__g', 'galeri');
            if ($p) $pdo->prepare('INSERT INTO galeri (invitation_id,foto,urutan) VALUES (?,?,?)')->execute([$id,$p,$i]);
        }
        $msg = 'Foto ditambahkan ke galeri.';
    }
    if ($a === 'galeri_delete') {
        $pdo->prepare('DELETE FROM galeri WHERE id=? AND invitation_id=?')->execute([(int)$_POST['gid'], $id]);
        $msg = 'Foto dihapus.';
    }

    // Reload
    $inv->execute([$id]);
    $invitation = $inv->fetch();
}

$mempelai = $pdo->prepare('SELECT * FROM mempelai WHERE invitation_id=? ORDER BY peran DESC');
$mempelai->execute([$id]); $mempelai = $mempelai->fetchAll();
$events   = $pdo->prepare('SELECT * FROM events WHERE invitation_id=? ORDER BY urutan');
$events->execute([$id]); $events = $events->fetchAll();
$kisah    = $pdo->prepare('SELECT * FROM kisah_cinta WHERE invitation_id=? ORDER BY urutan,id');
$kisah->execute([$id]); $kisah = $kisah->fetchAll();
$galeri   = $pdo->prepare('SELECT * FROM galeri WHERE invitation_id=? ORDER BY urutan,id');
$galeri->execute([$id]); $galeri = $galeri->fetchAll();

$themes = list_themes();
?>
<div class="flex items-center mb-3">
  <h1 class="text-2xl font-semibold">Edit: <?= h($invitation['judul']) ?></h1>
  <a target="_blank" href="<?= h(base_url($invitation['slug'])) ?>" class="ml-auto text-sm text-rose-600">Pratinjau /<?= h($invitation['slug']) ?> &rarr;</a>
</div>
<?php if ($msg): ?><div class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-2 rounded mb-3 text-sm"><?= h($msg) ?></div><?php endif; ?>

<div class="grid md:grid-cols-2 gap-4">
  <!-- DETAIL UTAMA -->
  <form method="post" enctype="multipart/form-data" class="bg-white border rounded-xl p-5 space-y-3">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="invitation">
    <h2 class="font-semibold">Detail Utama</h2>
    <label class="block text-sm">Judul <input name="judul" value="<?= h($invitation['judul']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
    <label class="block text-sm">Tanggal Acara <input type="date" name="tanggal_acara" value="<?= h($invitation['tanggal_acara']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
    <label class="block text-sm">Tema
      <select name="tema" class="w-full border rounded px-3 py-2 mt-1">
        <?php foreach ($themes as $t): ?>
          <option value="<?= h($t) ?>" <?= $invitation['tema']===$t?'selected':'' ?>><?= h($t) ?></option>
        <?php endforeach; ?>
      </select></label>
    <label class="block text-sm">Background (gambar transparan/ornamen)
      <input type="file" name="background" accept="image/*" class="w-full border rounded px-3 py-2 mt-1">
      <?php if ($invitation['background']): ?><img src="<?= h(upload_url($invitation['background'])) ?>" class="h-20 mt-2 rounded"><?php endif; ?>
    </label>
    <label class="block text-sm">Quote Arab <textarea name="quote_arab" rows="2" class="w-full border rounded px-3 py-2 mt-1" dir="rtl"><?= h($invitation['quote_arab']) ?></textarea></label>
    <label class="block text-sm">Arti / Terjemahan <textarea name="quote_arti" rows="2" class="w-full border rounded px-3 py-2 mt-1"><?= h($invitation['quote_arti']) ?></textarea></label>
    <label class="block text-sm">Kalimat Mohon Doa Restu <textarea name="doa_restu" rows="2" class="w-full border rounded px-3 py-2 mt-1"><?= h($invitation['doa_restu']) ?></textarea></label>
    <label class="block text-sm">Live Streaming - Teks <textarea name="livestream_text" rows="2" class="w-full border rounded px-3 py-2 mt-1"><?= h($invitation['livestream_text']) ?></textarea></label>
    <label class="block text-sm">Live Streaming - URL <input name="livestream_url" value="<?= h($invitation['livestream_url']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
    <label class="block text-sm">Bank/Rekening (multi-baris) <textarea name="bank_info" rows="3" class="w-full border rounded px-3 py-2 mt-1" placeholder="BCA 1234567890 a.n. Rudi&#10;BRI 0987654321 a.n. Diana"><?= h($invitation['bank_info']) ?></textarea></label>
    <label class="block text-sm">QRIS (gambar)
      <input type="file" name="qris_file" accept="image/*" class="w-full border rounded px-3 py-2 mt-1">
      <?php if ($invitation['qris']): ?><img src="<?= h(upload_url($invitation['qris'])) ?>" class="h-20 mt-2 rounded"><?php endif; ?>
    </label>
    <label class="block text-sm">No. Konfirmasi (WhatsApp) <input name="mohon_konfirmasi" value="<?= h($invitation['mohon_konfirmasi']) ?>" class="w-full border rounded px-3 py-2 mt-1" placeholder="628xxxxxxxxxx"></label>
    <div class="grid grid-cols-2 gap-2 text-sm">
      <label class="flex items-center gap-2"><input type="checkbox" name="show_livestream" <?= $invitation['show_livestream']?'checked':'' ?>> Live Streaming</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="show_kisah" <?= $invitation['show_kisah']?'checked':'' ?>> Kisah Cinta</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="show_galeri" <?= $invitation['show_galeri']?'checked':'' ?>> Galeri</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="show_kado" <?= $invitation['show_kado']?'checked':'' ?>> Kado/RSVP</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="is_active" <?= $invitation['is_active']?'checked':'' ?>> Aktif</label>
    </div>
    <button class="bg-rose-600 text-white px-4 py-2 rounded">Simpan Detail</button>
  </form>

  <!-- MEMPELAI -->
  <form method="post" enctype="multipart/form-data" class="bg-white border rounded-xl p-5 space-y-4">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="mempelai">
    <h2 class="font-semibold">Mempelai</h2>
    <?php foreach ($mempelai as $mp): ?>
      <fieldset class="border rounded-lg p-3">
        <legend class="px-1 text-xs uppercase text-slate-500"><?= h($mp['peran']) ?></legend>
        <label class="block text-sm">Nama Lengkap <input name="mp[<?= (int)$mp['id'] ?>][nama]" value="<?= h($mp['nama']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
        <label class="block text-sm">Nama Panggilan <input name="mp[<?= (int)$mp['id'] ?>][nama_panggilan]" value="<?= h($mp['nama_panggilan']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
        <label class="block text-sm">Ayah <input name="mp[<?= (int)$mp['id'] ?>][ayah]" value="<?= h($mp['ayah']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
        <label class="block text-sm">Ibu <input name="mp[<?= (int)$mp['id'] ?>][ibu]" value="<?= h($mp['ibu']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
        <label class="block text-sm">Instagram <input name="mp[<?= (int)$mp['id'] ?>][instagram]" value="<?= h($mp['instagram']) ?>" class="w-full border rounded px-3 py-2 mt-1"></label>
        <label class="block text-sm">Deskripsi <textarea name="mp[<?= (int)$mp['id'] ?>][deskripsi]" rows="2" class="w-full border rounded px-3 py-2 mt-1"><?= h($mp['deskripsi']) ?></textarea></label>
        <label class="block text-sm">Foto <input type="file" name="mp_foto[<?= (int)$mp['id'] ?>]" accept="image/*" class="w-full border rounded px-3 py-2 mt-1">
          <?php if ($mp['foto']): ?><img src="<?= h(upload_url($mp['foto'])) ?>" class="h-20 mt-2 rounded-full"><?php endif; ?>
        </label>
      </fieldset>
    <?php endforeach; ?>
    <button class="bg-rose-600 text-white px-4 py-2 rounded">Simpan Mempelai</button>
  </form>
</div>

<!-- ACARA -->
<div class="bg-white border rounded-xl p-5 mt-4">
  <h2 class="font-semibold mb-3">Acara</h2>
  <table class="w-full text-sm mb-4">
    <thead class="bg-slate-50"><tr>
      <th class="text-left p-2">Jenis</th><th class="text-left p-2">Mulai</th><th class="text-left p-2">Selesai</th><th class="text-left p-2">Tempat</th><th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($events as $e): ?>
      <tr class="border-t align-top">
        <td class="p-2">
          <form method="post" class="space-y-1">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="event_save">
            <input type="hidden" name="eid" value="<?= (int)$e['id'] ?>">
            <input name="jenis" value="<?= h($e['jenis']) ?>" class="border rounded px-2 py-1 w-full">
        </td>
        <td class="p-2"><input type="datetime-local" name="tanggal_mulai" value="<?= h(str_replace(' ','T',substr($e['tanggal_mulai'],0,16))) ?>" class="border rounded px-2 py-1 w-full"></td>
        <td class="p-2"><input type="datetime-local" name="tanggal_selesai" value="<?= h(str_replace(' ','T',substr((string)$e['tanggal_selesai'],0,16))) ?>" class="border rounded px-2 py-1 w-full"></td>
        <td class="p-2">
          <input name="tempat" value="<?= h($e['tempat']) ?>" placeholder="Nama tempat" class="border rounded px-2 py-1 w-full mb-1">
          <input name="alamat" value="<?= h($e['alamat']) ?>" placeholder="Alamat singkat" class="border rounded px-2 py-1 w-full mb-1">
          <input name="maps_url" value="<?= h($e['maps_url']) ?>" placeholder="Google Maps URL" class="border rounded px-2 py-1 w-full">
        </td>
        <td class="p-2 text-right whitespace-nowrap">
          <button class="text-emerald-700">Simpan</button>
          </form>
          <form method="post" class="inline" onsubmit="return confirm('Hapus acara?')">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="event_delete">
            <input type="hidden" name="eid" value="<?= (int)$e['id'] ?>">
            <button class="text-red-600 ml-2">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <!-- Tambah baru -->
    <tr class="border-t bg-amber-50/30">
      <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="event_save">
      <td class="p-2"><input name="jenis" placeholder="Akad / Resepsi / dll" class="border rounded px-2 py-1 w-full"></td>
      <td class="p-2"><input type="datetime-local" name="tanggal_mulai" class="border rounded px-2 py-1 w-full"></td>
      <td class="p-2"><input type="datetime-local" name="tanggal_selesai" class="border rounded px-2 py-1 w-full"></td>
      <td class="p-2">
        <input name="tempat" placeholder="Tempat" class="border rounded px-2 py-1 w-full mb-1">
        <input name="alamat" placeholder="Alamat singkat" class="border rounded px-2 py-1 w-full mb-1">
        <input name="maps_url" placeholder="Google Maps URL" class="border rounded px-2 py-1 w-full">
      </td>
      <td class="p-2 text-right"><button class="text-rose-600">+ Tambah</button></td>
      </form>
    </tr>
    </tbody>
  </table>
</div>

<!-- KISAH -->
<div class="bg-white border rounded-xl p-5 mt-4">
  <h2 class="font-semibold mb-3">Kisah Cinta</h2>
  <div class="space-y-3">
    <?php foreach ($kisah as $k): ?>
      <form method="post" enctype="multipart/form-data" class="border rounded-lg p-3 grid md:grid-cols-4 gap-2 items-start">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="kisah_save">
        <input type="hidden" name="kid" value="<?= (int)$k['id'] ?>">
        <input name="judul" value="<?= h($k['judul']) ?>" class="border rounded px-2 py-1" placeholder="Judul">
        <input name="tanggal" value="<?= h($k['tanggal']) ?>" class="border rounded px-2 py-1" placeholder="2020 / Mei 2021">
        <textarea name="deskripsi" rows="2" class="border rounded px-2 py-1 md:col-span-2" placeholder="Deskripsi"><?= h($k['deskripsi']) ?></textarea>
        <input type="file" name="kisah_foto" accept="image/*" class="border rounded px-2 py-1">
        <input type="number" name="urutan" value="<?= (int)$k['urutan'] ?>" class="border rounded px-2 py-1 w-24" placeholder="Urutan">
        <?php if ($k['foto']): ?><img src="<?= h(upload_url($k['foto'])) ?>" class="h-16 rounded"><?php else: ?><span></span><?php endif; ?>
        <div class="text-right">
          <button class="text-emerald-700">Simpan</button>
        </div>
      </form>
    <?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="border-2 border-dashed rounded-lg p-3 grid md:grid-cols-4 gap-2 bg-amber-50/30">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="kisah_save">
      <input name="judul" class="border rounded px-2 py-1" placeholder="Judul (mis. Pertemuan Pertama)">
      <input name="tanggal" class="border rounded px-2 py-1" placeholder="Tanggal/periode">
      <textarea name="deskripsi" rows="2" class="border rounded px-2 py-1 md:col-span-2" placeholder="Deskripsi"></textarea>
      <input type="file" name="kisah_foto" accept="image/*" class="border rounded px-2 py-1">
      <input type="number" name="urutan" value="0" class="border rounded px-2 py-1 w-24" placeholder="Urutan">
      <span></span>
      <div class="text-right"><button class="text-rose-600">+ Tambah Kisah</button></div>
    </form>
    <?php if (!$kisah): ?>
      <form method="post" class="hidden"></form>
    <?php endif; ?>
    <?php // delete forms ?>
    <?php foreach ($kisah as $k): ?>
      <form method="post" class="text-right -mt-3" onsubmit="return confirm('Hapus kisah?')">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="kisah_delete">
        <input type="hidden" name="kid" value="<?= (int)$k['id'] ?>">
        <button class="text-xs text-red-600">hapus #<?= (int)$k['id'] ?></button>
      </form>
    <?php endforeach; ?>
  </div>
</div>

<!-- GALERI -->
<div class="bg-white border rounded-xl p-5 mt-4">
  <h2 class="font-semibold mb-3">Galeri Foto</h2>
  <form method="post" enctype="multipart/form-data" class="mb-4 flex items-center gap-2">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="galeri_add">
    <input type="file" name="galeri_files[]" multiple accept="image/*" class="border rounded px-3 py-2">
    <button class="bg-rose-600 text-white px-4 py-2 rounded">Upload</button>
  </form>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
    <?php foreach ($galeri as $g): ?>
      <div class="relative group">
        <img src="<?= h(upload_url($g['foto'])) ?>" class="h-32 w-full object-cover rounded-lg">
        <form method="post" class="absolute top-1 right-1" onsubmit="return confirm('Hapus foto?')">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="galeri_delete">
          <input type="hidden" name="gid" value="<?= (int)$g['id'] ?>">
          <button class="bg-red-600/90 text-white w-6 h-6 rounded-full text-xs">x</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
