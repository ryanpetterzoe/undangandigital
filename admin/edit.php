<?php
$pageTitle = 'Edit Undangan';
$activeNav = 'mempelai';
require_once __DIR__ . '/../includes/admin_header.php';

$id = (int)($_GET['id'] ?? 0);
$inv = $pdo->prepare('SELECT * FROM invitations WHERE id=?');
$inv->execute([$id]);
$invitation = $inv->fetch();
if (!$invitation) {
    echo '<div class="alert error">Undangan tidak ditemukan.</div>';
    require_once __DIR__.'/../includes/admin_footer.php'; exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';

    if ($a === 'invitation') {
        $bg = $invitation['background'];
        $newBg = upload_file('background', 'bg');
        if ($newBg) $bg = $newBg;
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
            $_POST['tanggal_mulai'] ?: null,
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
$tab = $_GET['tab'] ?? 'detail';
$tabs = [
    'detail' => 'Detail',
    'mempelai' => 'Mempelai',
    'acara' => 'Acara',
    'kisah' => 'Kisah Cinta',
    'galeri' => 'Galeri',
];
?>
<div class="page-header">
  <a href="<?= h(base_url('admin/mempelai.php')) ?>" class="btn btn-icon btn-outline" aria-label="Kembali">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12,19 5,12 12,5"/></svg>
  </a>
  <h1 class="page-title" style="margin:0"><?= h($invitation['judul']) ?></h1>
  <span class="spacer"></span>
  <a target="_blank" href="<?= h(base_url($invitation['slug'])) ?>" class="btn btn-soft btn-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15,3 21,3 21,9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
    <span>Pratinjau</span>
  </a>
</div>

<?php if ($msg): ?><div class="alert success"><?= h($msg) ?></div><?php endif; ?>

<div class="card mb-4">
  <nav class="tabs">
    <?php foreach ($tabs as $key=>$lbl): ?>
      <a class="tab <?= $tab===$key?'active':'' ?>" href="?id=<?= $id ?>&tab=<?= $key ?>"><?= h($lbl) ?></a>
    <?php endforeach; ?>
  </nav>

<?php if ($tab === 'detail'): ?>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" class="form-row">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="invitation">

      <div class="grid-2">
        <div>
          <label class="label">Judul</label>
          <input class="input" name="judul" value="<?= h($invitation['judul']) ?>">
        </div>
        <div>
          <label class="label">Tanggal Acara</label>
          <input class="input" type="date" name="tanggal_acara" value="<?= h($invitation['tanggal_acara']) ?>">
        </div>
      </div>

      <div class="grid-2">
        <div>
          <label class="label">Tema</label>
          <select class="select" name="tema">
            <?php foreach ($themes as $t): ?>
              <option value="<?= h($t) ?>" <?= $invitation['tema']===$t?'selected':'' ?>><?= h($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Background (gambar)</label>
          <input class="input" type="file" name="background" accept="image/*">
          <?php if ($invitation['background']): ?><img src="<?= h(upload_url($invitation['background'])) ?>" class="mt-2" style="max-height:80px;border-radius:8px"><?php endif; ?>
        </div>
      </div>

      <div>
        <label class="label">Quote Arab</label>
        <textarea class="textarea" name="quote_arab" dir="rtl" rows="2"><?= h($invitation['quote_arab']) ?></textarea>
      </div>
      <div>
        <label class="label">Arti / Terjemahan</label>
        <textarea class="textarea" name="quote_arti" rows="2"><?= h($invitation['quote_arti']) ?></textarea>
      </div>
      <div>
        <label class="label">Kalimat Mohon Doa Restu</label>
        <textarea class="textarea" name="doa_restu" rows="2"><?= h($invitation['doa_restu']) ?></textarea>
      </div>

      <div class="grid-2">
        <div>
          <label class="label">Live Streaming - Teks</label>
          <textarea class="textarea" name="livestream_text" rows="2"><?= h($invitation['livestream_text']) ?></textarea>
        </div>
        <div>
          <label class="label">Live Streaming - URL</label>
          <input class="input" name="livestream_url" value="<?= h($invitation['livestream_url']) ?>" placeholder="https://youtube.com/...">
        </div>
      </div>

      <div>
        <label class="label">Bank/Rekening (multi-baris)</label>
        <textarea class="textarea" name="bank_info" rows="3" placeholder="BCA 1234567890 a.n. Rudi&#10;BRI 0987654321 a.n. Diana"><?= h($invitation['bank_info']) ?></textarea>
      </div>

      <div class="grid-2">
        <div>
          <label class="label">QRIS (gambar)</label>
          <input class="input" type="file" name="qris_file" accept="image/*">
          <?php if ($invitation['qris']): ?><img src="<?= h(upload_url($invitation['qris'])) ?>" class="mt-2" style="max-height:80px;border-radius:8px"><?php endif; ?>
        </div>
        <div>
          <label class="label">No. Konfirmasi (WhatsApp)</label>
          <input class="input" name="mohon_konfirmasi" value="<?= h($invitation['mohon_konfirmasi']) ?>" placeholder="628xxxxxxxxxx">
        </div>
      </div>

      <div class="card" style="background:var(--bg);box-shadow:none">
        <div class="card-body">
          <p class="muted mb-3"><strong>Visibility</strong> — atur card mana yang ditampilkan</p>
          <div class="grid-2">
            <label class="checkbox"><input type="checkbox" name="show_livestream" <?= $invitation['show_livestream']?'checked':'' ?>> Live Streaming</label>
            <label class="checkbox"><input type="checkbox" name="show_kisah" <?= $invitation['show_kisah']?'checked':'' ?>> Kisah Cinta</label>
            <label class="checkbox"><input type="checkbox" name="show_galeri" <?= $invitation['show_galeri']?'checked':'' ?>> Galeri</label>
            <label class="checkbox"><input type="checkbox" name="show_kado" <?= $invitation['show_kado']?'checked':'' ?>> RSVP &amp; Kado</label>
            <label class="checkbox"><input type="checkbox" name="is_active" <?= $invitation['is_active']?'checked':'' ?>> Undangan Aktif</label>
          </div>
        </div>
      </div>

      <button class="btn btn-primary">Simpan Detail</button>
    </form>
  </div>

<?php elseif ($tab === 'mempelai'): ?>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" class="form-row">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="mempelai">
      <div class="grid-2">
        <?php foreach ($mempelai as $mp): ?>
          <fieldset class="card" style="box-shadow:none">
            <div class="card-head">
              <h2 style="text-transform:capitalize">Mempelai <?= h($mp['peran']) ?></h2>
            </div>
            <div class="card-body form-row">
              <div><label class="label">Nama Lengkap</label><input class="input" name="mp[<?= (int)$mp['id'] ?>][nama]" value="<?= h($mp['nama']) ?>"></div>
              <div><label class="label">Nama Panggilan</label><input class="input" name="mp[<?= (int)$mp['id'] ?>][nama_panggilan]" value="<?= h($mp['nama_panggilan']) ?>"></div>
              <div class="grid-2">
                <div><label class="label">Ayah</label><input class="input" name="mp[<?= (int)$mp['id'] ?>][ayah]" value="<?= h($mp['ayah']) ?>"></div>
                <div><label class="label">Ibu</label><input class="input" name="mp[<?= (int)$mp['id'] ?>][ibu]" value="<?= h($mp['ibu']) ?>"></div>
              </div>
              <div><label class="label">Instagram</label><input class="input" name="mp[<?= (int)$mp['id'] ?>][instagram]" value="<?= h($mp['instagram']) ?>" placeholder="@username"></div>
              <div><label class="label">Deskripsi</label><textarea class="textarea" name="mp[<?= (int)$mp['id'] ?>][deskripsi]" rows="2"><?= h($mp['deskripsi']) ?></textarea></div>
              <div>
                <label class="label">Foto</label>
                <input class="input" type="file" name="mp_foto[<?= (int)$mp['id'] ?>]" accept="image/*">
                <?php if ($mp['foto']): ?><img src="<?= h(upload_url($mp['foto'])) ?>" class="mt-2" style="height:80px;width:80px;border-radius:50%;object-fit:cover"><?php endif; ?>
              </div>
            </div>
          </fieldset>
        <?php endforeach; ?>
      </div>
      <button class="btn btn-primary">Simpan Mempelai</button>
    </form>
  </div>

<?php elseif ($tab === 'acara'): ?>
  <div class="card-body">
    <p class="muted mb-3">Tambah, edit, atau hapus acara (Akad, Resepsi, dll.)</p>
    <div class="form-row">
      <?php foreach ($events as $e): ?>
        <form method="post" class="card" style="box-shadow:none">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="event_save">
          <input type="hidden" name="eid" value="<?= (int)$e['id'] ?>">
          <div class="card-body form-row">
            <div class="grid-2">
              <div><label class="label">Jenis</label><input class="input" name="jenis" value="<?= h($e['jenis']) ?>"></div>
              <div><label class="label">Urutan</label><input class="input" type="number" name="urutan" value="<?= (int)$e['urutan'] ?>"></div>
            </div>
            <div class="grid-2">
              <div><label class="label">Mulai</label><input class="input" type="datetime-local" name="tanggal_mulai" value="<?= h(str_replace(' ','T',substr($e['tanggal_mulai'],0,16))) ?>"></div>
              <div><label class="label">Selesai</label><input class="input" type="datetime-local" name="tanggal_selesai" value="<?= h(str_replace(' ','T',substr((string)$e['tanggal_selesai'],0,16))) ?>"></div>
            </div>
            <div><label class="label">Tempat</label><input class="input" name="tempat" value="<?= h($e['tempat']) ?>"></div>
            <div><label class="label">Alamat</label><input class="input" name="alamat" value="<?= h($e['alamat']) ?>"></div>
            <div><label class="label">Google Maps URL</label><input class="input" name="maps_url" value="<?= h($e['maps_url']) ?>"></div>
            <div class="row">
              <button class="btn btn-primary btn-sm">Simpan</button>
            </div>
          </div>
        </form>
        <form method="post" onsubmit="return confirm('Hapus acara?')" style="text-align:right;margin-top:-8px">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="event_delete">
          <input type="hidden" name="eid" value="<?= (int)$e['id'] ?>">
          <button class="btn btn-danger btn-sm">Hapus acara ini</button>
        </form>
      <?php endforeach; ?>

      <form method="post" class="card" style="box-shadow:none;border-style:dashed;background:var(--bg)">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="event_save">
        <div class="card-head"><h2>+ Tambah Acara Baru</h2></div>
        <div class="card-body form-row">
          <div class="grid-2">
            <div><label class="label">Jenis</label><input class="input" name="jenis" placeholder="Akad / Resepsi"></div>
            <div><label class="label">Urutan</label><input class="input" type="number" name="urutan" value="0"></div>
          </div>
          <div class="grid-2">
            <div><label class="label">Mulai</label><input class="input" type="datetime-local" name="tanggal_mulai"></div>
            <div><label class="label">Selesai</label><input class="input" type="datetime-local" name="tanggal_selesai"></div>
          </div>
          <div><label class="label">Tempat</label><input class="input" name="tempat"></div>
          <div><label class="label">Alamat</label><input class="input" name="alamat"></div>
          <div><label class="label">Google Maps URL</label><input class="input" name="maps_url"></div>
          <button class="btn btn-primary">Tambah Acara</button>
        </div>
      </form>
    </div>
  </div>

<?php elseif ($tab === 'kisah'): ?>
  <div class="card-body">
    <p class="muted mb-3">Tambah cerita perjalanan kalian — pertemuan pertama, lamaran, dll.</p>
    <div class="form-row">
      <?php foreach ($kisah as $k): ?>
        <form method="post" enctype="multipart/form-data" class="card" style="box-shadow:none">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="kisah_save">
          <input type="hidden" name="kid" value="<?= (int)$k['id'] ?>">
          <div class="card-body form-row">
            <div class="grid-2">
              <div><label class="label">Judul</label><input class="input" name="judul" value="<?= h($k['judul']) ?>"></div>
              <div><label class="label">Tanggal/Periode</label><input class="input" name="tanggal" value="<?= h($k['tanggal']) ?>" placeholder="Mei 2021"></div>
            </div>
            <div><label class="label">Deskripsi</label><textarea class="textarea" name="deskripsi" rows="2"><?= h($k['deskripsi']) ?></textarea></div>
            <div class="grid-2">
              <div>
                <label class="label">Foto (ganti)</label>
                <input class="input" type="file" name="kisah_foto" accept="image/*">
                <?php if ($k['foto']): ?><img src="<?= h(upload_url($k['foto'])) ?>" class="mt-2" style="max-height:80px;border-radius:8px"><?php endif; ?>
              </div>
              <div><label class="label">Urutan</label><input class="input" type="number" name="urutan" value="<?= (int)$k['urutan'] ?>"></div>
            </div>
            <div class="row-end">
              <button class="btn btn-primary btn-sm">Simpan</button>
            </div>
          </div>
        </form>
        <form method="post" onsubmit="return confirm('Hapus kisah?')" style="text-align:right;margin-top:-8px">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="kisah_delete">
          <input type="hidden" name="kid" value="<?= (int)$k['id'] ?>">
          <button class="btn btn-danger btn-sm">Hapus kisah ini</button>
        </form>
      <?php endforeach; ?>

      <form method="post" enctype="multipart/form-data" class="card" style="box-shadow:none;border-style:dashed;background:var(--bg)">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="kisah_save">
        <div class="card-head"><h2>+ Tambah Kisah</h2></div>
        <div class="card-body form-row">
          <div class="grid-2">
            <div><label class="label">Judul</label><input class="input" name="judul" placeholder="Pertemuan Pertama"></div>
            <div><label class="label">Tanggal/Periode</label><input class="input" name="tanggal"></div>
          </div>
          <div><label class="label">Deskripsi</label><textarea class="textarea" name="deskripsi" rows="2"></textarea></div>
          <div class="grid-2">
            <div><label class="label">Foto</label><input class="input" type="file" name="kisah_foto" accept="image/*"></div>
            <div><label class="label">Urutan</label><input class="input" type="number" name="urutan" value="0"></div>
          </div>
          <button class="btn btn-primary">Tambah Kisah</button>
        </div>
      </form>
    </div>
  </div>

<?php else: // galeri ?>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" class="row mb-4">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="galeri_add">
      <input class="input" type="file" name="galeri_files[]" multiple accept="image/*" style="flex:1;min-width:200px">
      <button class="btn btn-primary">Upload</button>
    </form>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px">
      <?php foreach ($galeri as $g): ?>
        <div style="position:relative;border-radius:12px;overflow:hidden">
          <img src="<?= h(upload_url($g['foto'])) ?>" style="width:100%;height:140px;object-fit:cover">
          <form method="post" style="position:absolute;top:6px;right:6px" onsubmit="return confirm('Hapus foto?')">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="galeri_delete">
            <input type="hidden" name="gid" value="<?= (int)$g['id'] ?>">
            <button class="btn btn-icon" style="background:rgba(220,38,38,.95);color:#fff;width:28px;height:28px;border-radius:999px;padding:0">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </form>
        </div>
      <?php endforeach; if (!$galeri): ?>
        <div class="empty-state" style="grid-column:1/-1">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
          <p>Belum ada foto. Upload foto-foto kalian.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
</div>

<style>
@media (min-width: 768px) {
  #galeri-grid, .galeri-grid { grid-template-columns: repeat(4, 1fr) !important; }
}
</style>
<script>
// Make galeri grid 4-col on tablet+
(function(){var g=document.querySelectorAll('[style*="grid-template-columns:repeat(2,1fr)"]'); for(var i=0;i<g.length;i++){g[i].classList.add('galeri-grid');}})();
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
