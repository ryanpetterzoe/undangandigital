<?php
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

$totalInv  = (int)$pdo->query('SELECT COUNT(*) FROM invitations')->fetchColumn();
$totalCli  = (int)$pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
$totalRsvp = (int)$pdo->query('SELECT COUNT(*) FROM rsvp')->fetchColumn();
$totalUc   = (int)$pdo->query('SELECT COUNT(*) FROM ucapan')->fetchColumn();
$recent    = $pdo->query('SELECT id, slug, judul, tema, tanggal_acara FROM invitations ORDER BY id DESC LIMIT 8')->fetchAll();
?>
<div class="page-header">
  <h1 class="page-title">Dashboard</h1>
  <span class="spacer"></span>
  <a href="<?= h(base_url('admin/mempelai.php')) ?>" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    <span>Tambah Undangan</span>
  </a>
</div>

<div class="stat-grid">
  <div class="stat rose">
    <div class="stat-label">Undangan</div>
    <div class="stat-value"><?= $totalInv ?></div>
    <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
  </div>
  <div class="stat amber">
    <div class="stat-label">Klien</div>
    <div class="stat-value"><?= $totalCli ?></div>
    <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
  </div>
  <div class="stat emerald">
    <div class="stat-label">RSVP</div>
    <div class="stat-value"><?= $totalRsvp ?></div>
    <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg></div>
  </div>
  <div class="stat sky">
    <div class="stat-label">Ucapan</div>
    <div class="stat-value"><?= $totalUc ?></div>
    <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></div>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <h2>Undangan Terbaru</h2>
    <span class="spacer"></span>
    <a href="<?= h(base_url('admin/mempelai.php')) ?>" class="btn btn-ghost btn-sm">Lihat semua &rarr;</a>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Judul</th><th>Slug</th><th>Tema</th><th>Tanggal</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><strong><?= h($r['judul']) ?></strong></td>
          <td><a class="row-link" target="_blank" href="<?= h(base_url($r['slug'])) ?>">/<?= h($r['slug']) ?></a></td>
          <td><span class="badge slate"><?= h($r['tema']) ?></span></td>
          <td class="muted"><?= h(format_tanggal($r['tanggal_acara'] ?? '')) ?></td>
          <td class="text-right"><a class="btn btn-soft btn-sm" href="<?= h(base_url('admin/edit.php?id='.$r['id'])) ?>">Edit</a></td>
        </tr>
      <?php endforeach; if (!$recent): ?>
        <tr><td colspan="5">
          <div class="empty-state">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <p>Belum ada undangan.<br><a class="btn btn-primary btn-sm mt-3" href="<?= h(base_url('admin/mempelai.php')) ?>">+ Tambah Undangan Pertama</a></p>
          </div>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
