<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

$totalInv  = (int)$pdo->query('SELECT COUNT(*) FROM invitations')->fetchColumn();
$totalCli  = (int)$pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
$totalRsvp = (int)$pdo->query('SELECT COUNT(*) FROM rsvp')->fetchColumn();
$totalUc   = (int)$pdo->query('SELECT COUNT(*) FROM ucapan')->fetchColumn();
$recent    = $pdo->query('SELECT id, slug, judul, tema, tanggal_acara FROM invitations ORDER BY id DESC LIMIT 8')->fetchAll();
?>
<h1 class="text-2xl font-semibold mb-4">Dashboard</h1>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
  <?php foreach ([
    ['Undangan', $totalInv, 'rose'],
    ['Klien', $totalCli, 'amber'],
    ['RSVP', $totalRsvp, 'emerald'],
    ['Ucapan', $totalUc, 'sky'],
  ] as [$lbl, $val, $col]): ?>
    <div class="bg-white rounded-xl border p-4">
      <div class="text-xs text-slate-500 uppercase"><?= h($lbl) ?></div>
      <div class="text-2xl font-bold text-<?= $col ?>-600"><?= $val ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="bg-white rounded-xl border">
  <div class="px-4 py-3 border-b flex items-center">
    <h2 class="font-medium">Undangan Terbaru</h2>
    <a href="<?= h(base_url('admin/mempelai.php')) ?>" class="ml-auto text-sm text-rose-600">Lihat semua</a>
  </div>
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-600">
      <tr><th class="text-left p-3">Judul</th><th class="text-left p-3">Slug</th><th class="text-left p-3">Tema</th><th class="text-left p-3">Tanggal</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($recent as $r): ?>
      <tr class="border-t">
        <td class="p-3"><?= h($r['judul']) ?></td>
        <td class="p-3"><a class="text-rose-600" target="_blank" href="<?= h(base_url($r['slug'])) ?>">/<?= h($r['slug']) ?></a></td>
        <td class="p-3"><?= h($r['tema']) ?></td>
        <td class="p-3"><?= h(format_tanggal($r['tanggal_acara'] ?? '')) ?></td>
        <td class="p-3 text-right"><a class="text-sky-600" href="<?= h(base_url('admin/edit.php?id='.$r['id'])) ?>">Edit</a></td>
      </tr>
    <?php endforeach; if (!$recent): ?>
      <tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada undangan. <a href="<?= h(base_url('admin/mempelai.php')) ?>" class="text-rose-600">Tambah sekarang</a></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
