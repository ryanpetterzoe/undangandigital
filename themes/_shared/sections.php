<?php
/**
 * Invitation sections — Mildness-inspired, mobile-first.
 * Variables: $invitation, $mempelai, $events, $kisah, $galeri, $ucapan, $pria, $wanita, $guestName
 */
$apiUrl  = base_url('invitation/api.php');
$slugVal = h($invitation['slug']);
$tgt     = !empty($_GET['to']) ? h((string)$_GET['to']) : '';

$countdownTo = null;
foreach ($events as $e) { if (stripos($e['jenis'],'akad') !== false) { $countdownTo = $e['tanggal_mulai']; break; } }
if (!$countdownTo && !empty($events)) $countdownTo = $events[0]['tanggal_mulai'];

/* Helper: pick first non-empty value (handles empty strings unlike ??). */
$pick = static function(...$vals) {
    foreach ($vals as $v) { if ($v !== null && $v !== '') return $v; }
    return '';
};
$priaShort   = $pick($pria['nama_panggilan']   ?? null, $pria['nama']   ?? null, 'Pria');
$wanitaShort = $pick($wanita['nama_panggilan'] ?? null, $wanita['nama'] ?? null, 'Wanita');
?>

<!-- ============= COVER ============= -->
<section id="cover" class="cover-section">
  <div class="cover-inner">
    <p class="cover-eyebrow">The Wedding Of</p>
    <h1 class="cover-title"><?= h($priaShort) ?> &amp; <?= h($wanitaShort) ?></h1>
    <div class="cover-divider"><span class="line"></span><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.5-9.5-9C1.2 9 3 5 7 5c2 0 3.5 1 5 3 1.5-2 3-3 5-3 4 0 5.8 4 4.5 7-2 4.5-9.5 9-9.5 9z"/></svg><span class="line"></span></div>
    <p class="cover-date"><?= h(format_tanggal($invitation['tanggal_acara'] ?? '', 'l, d F Y')) ?></p>

    <div class="cover-guest">
      <p class="cover-guest-label">Kepada Yth.</p>
      <p class="cover-guest-name"><?= $guestName ? h($guestName) : 'Bapak / Ibu / Saudara/i' ?></p>
    </div>

    <button type="button" id="btnOpen" class="cover-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      <span>Buka Undangan</span>
    </button>
  </div>
</section>

<!-- ============= INVITATION CONTENT ============= -->
<div id="content" class="invitation-content" hidden>

  <!-- Card Utama -->
  <section class="section-block">
    <article class="card text-center" data-aos="fade-up">
      <p class="eyebrow">The Wedding Of</p>
      <h2 class="couple-title"><?= h($priaShort) ?> &amp; <?= h($wanitaShort) ?></h2>
      <div class="cover-divider"><span class="line"></span><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.5-9.5-9C1.2 9 3 5 7 5c2 0 3.5 1 5 3 1.5-2 3-3 5-3 4 0 5.8 4 4.5 7-2 4.5-9.5 9-9.5 9z"/></svg><span class="line"></span></div>
      <p class="couple-date"><?= h(format_tanggal($invitation['tanggal_acara'] ?? '', 'l, d F Y')) ?></p>
      <div class="countdown" id="countdown">
        <div class="cd-cell"><span class="cd-num" data-cd="d">0</span><span class="cd-label">Hari</span></div>
        <div class="cd-cell"><span class="cd-num" data-cd="h">0</span><span class="cd-label">Jam</span></div>
        <div class="cd-cell"><span class="cd-num" data-cd="m">0</span><span class="cd-label">Menit</span></div>
        <div class="cd-cell"><span class="cd-num" data-cd="s">0</span><span class="cd-label">Detik</span></div>
      </div>
      <?php if ($guestName): ?>
        <div class="guest-pill"><small>Kepada Yth.</small><?= h($guestName) ?></div>
      <?php endif; ?>
    </article>
  </section>

  <!-- Quote Arab -->
  <?php if (!empty($invitation['quote_arab']) || !empty($invitation['quote_arti'])): ?>
  <section class="section-block">
    <article class="card text-center" data-aos="fade-up">
      <?php if (!empty($invitation['quote_arab'])): ?>
        <p class="quote-arab" dir="rtl"><?= h($invitation['quote_arab']) ?></p>
      <?php endif; ?>
      <?php if (!empty($invitation['quote_arti'])): ?>
        <p class="quote-arti"><?= nl2br(h($invitation['quote_arti'])) ?></p>
      <?php endif; ?>
    </article>
  </section>
  <?php endif; ?>

  <!-- Mohon Doa Restu -->
  <section class="section-block">
    <article class="card text-center" data-aos="fade-up">
      <p class="eyebrow">Bismillahirrahmanirrahim</p>
      <h3 class="section-title">Mohon Doa Restu</h3>
      <p class="prose"><?= nl2br(h($invitation['doa_restu'] ?: 'Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan acara pernikahan putra-putri kami:')) ?></p>

      <div class="mempelai-grid">
        <?php $first = $wanita; $second = $pria; ?>
        <?php foreach ([$first, $second] as $idx => $d): if (!$d) continue; ?>
          <div class="mempelai" data-aos="fade-up">
            <?php if (!empty($d['foto'])): ?>
              <img src="<?= h(upload_url($d['foto'])) ?>" alt="" class="mempelai-photo">
            <?php else: ?>
              <div class="mempelai-photo placeholder" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
            <?php endif; ?>
            <h4 class="mempelai-name"><?= h(!empty($d['nama']) ? $d['nama'] : '—') ?></h4>
            <?php if (!empty($d['deskripsi'])): ?><p class="mempelai-desc"><?= h($d['deskripsi']) ?></p><?php endif; ?>
            <?php $hasOrtu = !empty($d['ayah']) || !empty($d['ibu']); ?>
            <?php if ($hasOrtu): ?>
              <p class="mempelai-meta">Putra/Putri dari</p>
              <?php if (!empty($d['ayah'])): ?><p class="mempelai-meta">Bapak <strong><?= h($d['ayah']) ?></strong></p><?php endif; ?>
              <?php if (!empty($d['ibu'])):  ?><p class="mempelai-meta"><?= !empty($d['ayah']) ? '&amp; ' : '' ?>Ibu <strong><?= h($d['ibu']) ?></strong></p><?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($d['instagram'])): ?>
              <a href="https://instagram.com/<?= h(ltrim($d['instagram'],'@')) ?>" target="_blank" class="ig-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.4A4 4 0 1 1 12.6 8 4 4 0 0 1 16 11.4z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                <span>@<?= h(ltrim($d['instagram'],'@')) ?></span>
              </a>
            <?php endif; ?>
          </div>
          <?php if ($idx === 0): ?><div class="mempelai-amp" aria-hidden="true">&amp;</div><?php endif; ?>
        <?php endforeach; ?>
      </div>
    </article>
  </section>

  <!-- Acara -->
  <section class="section-block">
    <article class="card text-center" data-aos="fade-up">
      <p class="eyebrow">Save The Date</p>
      <h3 class="section-title">Detail Acara</h3>
      <p class="prose">Kami bermaksud mengundang Bapak/Ibu/Saudara/i pada acara pernikahan kami:</p>
      <div class="events">
        <?php foreach ($events as $e): ?>
          <div class="event" data-aos="fade-up">
            <p class="event-type"><?= h($e['jenis']) ?></p>
            <p class="event-date"><?= h(format_tanggal($e['tanggal_mulai'], 'l, d F Y')) ?></p>
            <p class="event-time">
              <?= h(date('H:i', strtotime($e['tanggal_mulai']))) ?>
              <?= !empty($e['tanggal_selesai']) ? ' &ndash; ' . h(date('H:i', strtotime($e['tanggal_selesai']))) : '' ?> WIB
            </p>
            <?php if (!empty($e['tempat'])): ?><p class="event-venue"><?= h($e['tempat']) ?></p><?php endif; ?>
            <?php if (!empty($e['alamat'])): ?><p class="event-addr"><?= h($e['alamat']) ?></p><?php endif; ?>
            <div class="event-actions">
              <a target="_blank" href="<?= h(gcal_url($e, ($invitation['judul'] ?? 'Pernikahan') . ' - ' . $e['jenis'])) ?>" class="btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Calendar</span>
              </a>
              <?php if (!empty($e['maps_url'])): ?>
                <a target="_blank" href="<?= h($e['maps_url']) ?>" class="btn-outline">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span>Maps</span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($invitation['show_livestream'] && !empty($invitation['livestream_url'])): ?>
        <div class="livestream" data-aos="fade-up">
          <div class="livestream-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
          </div>
          <h4 class="livestream-title">Live Streaming</h4>
          <p class="livestream-text"><?= nl2br(h($invitation['livestream_text'] ?: 'Acara ini akan disiarkan langsung melalui media internet.')) ?></p>
          <a target="_blank" href="<?= h($invitation['livestream_url']) ?>" class="btn-primary">Buka Live Streaming</a>
        </div>
      <?php endif; ?>
    </article>
  </section>

  <!-- Kisah Cinta -->
  <?php if ($invitation['show_kisah'] && !empty($kisah)): ?>
  <section class="section-block">
    <article class="card" data-aos="fade-up">
      <div class="text-center">
        <p class="eyebrow">Our Love Story</p>
        <h3 class="section-title">Kisah Cinta Kami</h3>
      </div>
      <div class="timeline">
        <?php foreach ($kisah as $k): ?>
          <div class="timeline-item" data-aos="fade-up">
            <div class="timeline-marker" aria-hidden="true"></div>
            <?php if (!empty($k['foto'])): ?>
              <img src="<?= h(upload_url($k['foto'])) ?>" class="timeline-photo" alt="">
            <?php endif; ?>
            <div class="timeline-text">
              <h4 class="timeline-title"><?= h($k['judul']) ?></h4>
              <?php if (!empty($k['tanggal'])): ?><p class="timeline-date"><?= h($k['tanggal']) ?></p><?php endif; ?>
              <p class="timeline-desc"><?= nl2br(h($k['deskripsi'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </section>
  <?php endif; ?>

  <!-- Galeri -->
  <?php if ($invitation['show_galeri'] && !empty($galeri)): ?>
  <section class="section-block">
    <article class="card" data-aos="fade-up">
      <div class="text-center">
        <p class="eyebrow">Moments</p>
        <h3 class="section-title">Galeri Kami</h3>
      </div>
      <div class="gallery">
        <?php foreach ($galeri as $g): ?>
          <a href="<?= h(upload_url($g['foto'])) ?>" target="_blank" class="gallery-item" data-aos="zoom-in">
            <img src="<?= h(upload_url($g['foto'])) ?>" alt="" loading="lazy">
          </a>
        <?php endforeach; ?>
      </div>
    </article>
  </section>
  <?php endif; ?>

  <!-- RSVP & Kado -->
  <?php if ($invitation['show_kado']): ?>
  <section class="section-block">
    <article class="card" data-aos="fade-up">
      <div class="text-center">
        <p class="eyebrow">RSVP</p>
        <h3 class="section-title">Konfirmasi Kehadiran</h3>
        <p class="prose">Kehadiran Anda merupakan kebahagiaan bagi kami. Mohon konfirmasi dengan mengisi formulir di bawah ini.</p>
      </div>

      <form id="formRsvp" class="form">
        <label>
          <span class="field-label">Nama</span>
          <input class="input" name="nama" placeholder="Nama Anda" value="<?= h($guestName) ?>" required>
        </label>
        <label>
          <span class="field-label">Jumlah Tamu</span>
          <input class="input" type="number" name="jumlah_tamu" min="1" max="10" value="1">
        </label>
        <div>
          <span class="field-label">Status Kehadiran</span>
          <div class="radio-group">
            <label class="radio-pill"><input type="radio" name="hadir" value="hadir" checked><span>Hadir</span></label>
            <label class="radio-pill"><input type="radio" name="hadir" value="ragu"><span>Ragu</span></label>
            <label class="radio-pill"><input type="radio" name="hadir" value="tidak"><span>Tidak Hadir</span></label>
          </div>
        </div>
        <button class="btn-primary btn-block">Kirim Konfirmasi</button>
      </form>
      <p id="rsvpMsg" class="form-msg"></p>

      <div class="divider"></div>

      <div class="text-center">
        <p class="eyebrow">Wedding Gift</p>
        <h3 class="section-title">Kirim Hadiah</h3>
        <p class="prose">Doa restu Anda adalah karunia terindah. Namun bila Anda berkenan memberikan tanda kasih, dapat melalui:</p>
      </div>

      <?php if (!empty($invitation['bank_info'])): ?>
        <div class="bank-list">
          <?php foreach (preg_split("/\r?\n/", trim($invitation['bank_info'])) as $line): if (trim($line)===''): continue; endif;
              $num = preg_match('/(\d{6,})/', $line, $m) ? $m[1] : '';
          ?>
            <div class="bank-row">
              <div class="bank-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
              </div>
              <div class="bank-info"><?= h($line) ?></div>
              <button type="button" class="btn-outline" data-copy="<?= h($num) ?>">Salin</button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($invitation['qris'])): ?>
        <div class="qris">
          <p class="qris-label">QRIS</p>
          <img src="<?= h(upload_url($invitation['qris'])) ?>" alt="QRIS">
        </div>
      <?php endif; ?>

      <?php if (!empty($invitation['mohon_konfirmasi'])): ?>
        <p class="confirm-line">Mohon konfirmasi ke
          <a href="https://wa.me/<?= h(preg_replace('/\D/','',$invitation['mohon_konfirmasi'])) ?>" target="_blank"><?= h($invitation['mohon_konfirmasi']) ?></a>
        </p>
      <?php endif; ?>
    </article>
  </section>
  <?php endif; ?>

  <!-- Ucapan & Doa -->
  <section class="section-block">
    <article class="card" data-aos="fade-up">
      <div class="text-center">
        <p class="eyebrow">Wishes</p>
        <h3 class="section-title">Ucapan &amp; Doa</h3>
      </div>
      <form id="formUcapan" class="form">
        <label>
          <span class="field-label">Nama</span>
          <input class="input" name="nama" placeholder="Nama Anda" value="<?= h($guestName) ?>" required>
        </label>
        <label>
          <span class="field-label">Pesan</span>
          <textarea class="input" name="pesan" rows="3" placeholder="Tuliskan ucapan & doa..." required></textarea>
        </label>
        <button class="btn-primary btn-block">Kirim Ucapan</button>
      </form>
      <div id="ucapanList" class="ucapan-list">
        <?php foreach ($ucapan as $u): ?>
          <div class="ucapan-item">
            <div class="ucapan-head">
              <div class="avatar"><?= h(mb_strtoupper(mb_substr($u['nama'],0,1))) ?></div>
              <span class="ucapan-name"><?= h($u['nama']) ?></span>
              <span class="ucapan-time"><?= h(format_tanggal($u['created_at'], 'd M Y')) ?></span>
            </div>
            <p class="ucapan-text"><?= nl2br(h($u['pesan'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </section>

  <!-- Penutup -->
  <section class="section-block">
    <article class="card text-center" data-aos="fade-up">
      <p class="closing-prose">Merupakan suatu kehormatan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu kepada putra-putri kami.</p>
      <p class="closing-salam">Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>
      <p class="closing-couple"><?= h($priaShort) ?> &amp; <?= h($wanitaShort) ?></p>
    </article>
  </section>

  <footer class="invitation-footer">
    Made with <span class="heart" aria-hidden="true">&#9825;</span> &middot; <?= h($CONFIG['app']['site_name'] ?? 'Undangan Digital') ?>
  </footer>
</div>

<script>
(function(){
  'use strict';

  var btn     = document.getElementById('btnOpen');
  var cover   = document.getElementById('cover');
  var content = document.getElementById('content');
  function openInvitation() {
    if (!cover || !content) return;
    cover.classList.add('cover-open');
    content.removeAttribute('hidden');
    setTimeout(function(){
      content.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (window.AOS && AOS.refresh) AOS.refresh();
    }, 350);
    var aud = document.getElementById('bgMusic');
    if (aud) { aud.play().catch(function(){}); var mt = document.getElementById('musicToggle'); if (mt) mt.classList.add('playing'); }
  }
  if (btn) btn.addEventListener('click', openInvitation);

  function initAOS() {
    if (window.AOS) AOS.init({ duration: 800, once: true, offset: 40, easing: 'ease-out-cubic' });
  }
  if (document.readyState === 'complete' || document.readyState === 'interactive') initAOS();
  else document.addEventListener('DOMContentLoaded', initAOS);
  window.addEventListener('load', function(){ if (window.AOS && AOS.refresh) AOS.refresh(); });

  var target = <?= $countdownTo ? json_encode(date('c', strtotime($countdownTo))) : 'null' ?>;
  if (target) {
    var t = new Date(target).getTime();
    function tick(){
      var d = Math.max(0, t - Date.now()); var s = Math.floor(d/1000);
      var days = Math.floor(s/86400); s %= 86400;
      var hrs  = Math.floor(s/3600);  s %= 3600;
      var mins = Math.floor(s/60);    s %= 60;
      function set(sel, v){ var els=document.querySelectorAll(sel); for(var i=0;i<els.length;i++) els[i].textContent=v; }
      set('[data-cd="d"]', days); set('[data-cd="h"]', hrs); set('[data-cd="m"]', mins); set('[data-cd="s"]', s);
    }
    tick(); setInterval(tick, 1000);
  }

  document.addEventListener('click', function(ev){
    var b = ev.target.closest('[data-copy]'); if (!b) return;
    var txt = b.getAttribute('data-copy') || ''; if (!txt) return;
    navigator.clipboard.writeText(txt).then(function(){
      var orig = b.textContent; b.textContent = 'Tersalin';
      setTimeout(function(){ b.textContent = orig; }, 1500);
    });
  });

  var mt = document.getElementById('musicToggle');
  var aud = document.getElementById('bgMusic');
  if (mt && aud) {
    mt.addEventListener('click', function(){
      if (aud.paused) { aud.play(); mt.classList.add('playing'); }
      else { aud.pause(); mt.classList.remove('playing'); }
    });
  }

  function postForm(form, action, onSuccess) {
    var fd = new FormData(form);
    fd.append('action', action); fd.append('slug', '<?= $slugVal ?>');
    <?php if ($tgt): ?>fd.append('guest_token', '<?= $tgt ?>');<?php endif; ?>
    return fetch('<?= h($apiUrl) ?>', { method: 'POST', body: fd })
      .then(function(r){ return r.json(); }).then(onSuccess);
  }

  var rsvp = document.getElementById('formRsvp');
  if (rsvp) rsvp.addEventListener('submit', function(ev){
    ev.preventDefault();
    postForm(rsvp, 'rsvp', function(j){
      var m = document.getElementById('rsvpMsg');
      m.textContent = j.msg || (j.ok?'Terima kasih.':'Terjadi kesalahan');
      m.className = 'form-msg ' + (j.ok ? 'success' : 'error');
      if (j.ok) rsvp.reset();
    });
  });

  var uc = document.getElementById('formUcapan');
  var list = document.getElementById('ucapanList');
  if (uc) uc.addEventListener('submit', function(ev){
    ev.preventDefault();
    postForm(uc, 'ucapan', function(j){
      if (j.ok && j.item) {
        var div = document.createElement('div');
        div.className = 'ucapan-item ucapan-new';
        var esc = function(s){ return String(s).replace(/[<>&]/g, function(c){return ({'<':'&lt;','>':'&gt;','&':'&amp;'})[c];}); };
        var nama = esc(j.item.nama);
        var pesan = esc(j.item.pesan).replace(/\n/g,'<br>');
        div.innerHTML =
          '<div class="ucapan-head"><div class="avatar">' + (nama.charAt(0).toUpperCase()) + '</div>' +
          '<span class="ucapan-name">' + nama + '</span>' +
          '<span class="ucapan-time">Baru saja</span></div>' +
          '<p class="ucapan-text">' + pesan + '</p>';
        list.prepend(div);
        uc.reset();
      } else { alert(j.msg || 'Gagal mengirim'); }
    });
  });
})();
</script>
