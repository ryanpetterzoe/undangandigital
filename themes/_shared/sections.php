<?php
/**
 * Shared invitation sections.
 * Variables expected (set by index.php):
 *   $inv, $mempelai, $events, $kisah, $galeri, $ucapan, $pria, $wanita, $guestName, $invitation
 *
 * Themes can include this file inside their layout.php after rendering header,
 * or render their own custom version. Style controlled via $sectionTone (rose/amber/emerald/etc).
 */
$tone = $sectionTone ?? 'rose';
$apiUrl = base_url('invitation/api.php');
$slugVal = h($invitation['slug']);
$tgt = !empty($_GET['to']) ? h((string)$_GET['to']) : '';

// Countdown to akad (first event of jenis Akad, fallback first event)
$countdownTo = null;
foreach ($events as $e) { if (stripos($e['jenis'],'akad') !== false) { $countdownTo = $e['tanggal_mulai']; break; } }
if (!$countdownTo && !empty($events)) $countdownTo = $events[0]['tanggal_mulai'];
?>

<!-- COVER -->
<section id="cover" class="cover-section relative min-h-screen flex items-center justify-center text-center p-6 overflow-hidden">
  <div class="cover-inner relative z-10 max-w-md mx-auto">
    <p class="cover-eyebrow tracking-[0.4em] text-xs uppercase mb-4 opacity-80">The Wedding Of</p>
    <h1 class="cover-title font-serif text-5xl md:text-6xl mb-3"><?= h($pria['nama_panggilan'] ?? $pria['nama'] ?? 'Mempelai Pria') ?> <span class="opacity-60">&amp;</span> <?= h($wanita['nama_panggilan'] ?? $wanita['nama'] ?? 'Mempelai Wanita') ?></h1>
    <p class="cover-date text-lg mb-6"><?= h(format_tanggal($invitation['tanggal_acara'] ?? '', 'l, d F Y')) ?></p>
    <?php if ($guestName): ?>
      <div class="cover-guest mb-6">
        <p class="text-xs uppercase tracking-widest opacity-70 mb-1">Kepada Yth.</p>
        <p class="font-medium"><?= h($guestName) ?></p>
      </div>
    <?php else: ?>
      <p class="text-xs uppercase tracking-widest opacity-70 mb-6">Kepada Bapak / Ibu / Saudara/i</p>
    <?php endif; ?>
    <button type="button" id="btnOpen" class="cover-btn px-7 py-3 rounded-full font-medium shadow-lg">Buka Undangan</button>
  </div>
</section>

<!-- INVITATION CONTENT -->
<div id="content" class="invitation-content hidden">

  <!-- Card Utama -->
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant">
      <p class="eyebrow">Undangan Pernikahan</p>
      <h2 class="couple-title font-serif text-4xl md:text-5xl my-4"><?= h($pria['nama_panggilan'] ?? '') ?> &amp; <?= h($wanita['nama_panggilan'] ?? '') ?></h2>
      <p class="couple-date mb-6"><?= h(format_tanggal($invitation['tanggal_acara'] ?? '', 'l, d F Y')) ?></p>
      <div id="countdown" class="countdown-grid grid grid-cols-4 gap-2 max-w-md mx-auto">
        <div class="cd"><span data-cd="d">0</span><small>Hari</small></div>
        <div class="cd"><span data-cd="h">0</span><small>Jam</small></div>
        <div class="cd"><span data-cd="m">0</span><small>Menit</small></div>
        <div class="cd"><span data-cd="s">0</span><small>Detik</small></div>
      </div>
      <?php if ($guestName): ?>
        <p class="mt-6 text-sm opacity-80">Kepada Yth. <strong><?= h($guestName) ?></strong></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Quote Arab -->
  <?php if (!empty($invitation['quote_arab']) || !empty($invitation['quote_arti'])): ?>
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant text-center">
      <?php if (!empty($invitation['quote_arab'])): ?>
        <p class="quote-arab text-2xl md:text-3xl leading-loose mb-4" dir="rtl"><?= h($invitation['quote_arab']) ?></p>
      <?php endif; ?>
      <?php if (!empty($invitation['quote_arti'])): ?>
        <p class="italic opacity-80"><?= nl2br(h($invitation['quote_arti'])) ?></p>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Mohon Doa Restu -->
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant text-center">
      <p class="eyebrow">Bismillahirrahmanirrahim</p>
      <p class="mb-6 leading-relaxed"><?= nl2br(h($invitation['doa_restu'] ?: 'Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan acara pernikahan putra-putri kami:')) ?></p>
      <div class="grid md:grid-cols-2 gap-6">
        <?php foreach ([['data'=>$wanita,'label'=>'Mempelai Wanita'], ['data'=>$pria,'label'=>'Mempelai Pria']] as $m): $d = $m['data']; ?>
          <div class="mempelai-card" data-aos="zoom-in">
            <?php if (!empty($d['foto'])): ?>
              <img src="<?= h(upload_url($d['foto'])) ?>" alt="" class="mx-auto h-40 w-40 rounded-full object-cover ring-4 ring-white/60 mb-4">
            <?php endif; ?>
            <p class="text-xs uppercase tracking-widest opacity-70"><?= h($m['label']) ?></p>
            <h3 class="font-serif text-2xl my-1"><?= h($d['nama'] ?? '-') ?></h3>
            <?php if (!empty($d['deskripsi'])): ?><p class="text-sm opacity-80 mb-2"><?= h($d['deskripsi']) ?></p><?php endif; ?>
            <p class="text-sm">Putra/Putri dari</p>
            <p class="text-sm">Bapak <strong><?= h($d['ayah'] ?? '-') ?></strong></p>
            <p class="text-sm">&amp; Ibu <strong><?= h($d['ibu'] ?? '-') ?></strong></p>
            <?php if (!empty($d['instagram'])): ?>
              <a href="https://instagram.com/<?= h(ltrim($d['instagram'],'@')) ?>" target="_blank" class="inline-block mt-3 text-xs underline">@<?= h(ltrim($d['instagram'],'@')) ?></a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Acara -->
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant text-center">
      <p class="eyebrow">Save The Date</p>
      <h3 class="font-serif text-3xl mb-2">Detail Acara</h3>
      <p class="opacity-80 mb-6">Kami bermaksud mengundang Bapak/Ibu/Saudara/i pada acara pernikahan kami:</p>
      <div class="grid md:grid-cols-2 gap-4">
        <?php foreach ($events as $e): ?>
          <div class="event-card" data-aos="fade-up">
            <p class="text-xs uppercase tracking-widest opacity-70"><?= h($e['jenis']) ?></p>
            <h4 class="font-serif text-2xl my-1"><?= h(format_tanggal($e['tanggal_mulai'], 'l, d F Y')) ?></h4>
            <p class="text-sm mb-2">
              <?= h(date('H:i', strtotime($e['tanggal_mulai']))) ?>
              <?= !empty($e['tanggal_selesai']) ? ' - ' . h(date('H:i', strtotime($e['tanggal_selesai']))) : '' ?> WIB
            </p>
            <?php if (!empty($e['tempat'])): ?><p class="font-medium"><?= h($e['tempat']) ?></p><?php endif; ?>
            <?php if (!empty($e['alamat'])): ?><p class="text-sm opacity-80 mb-3"><?= h($e['alamat']) ?></p><?php endif; ?>
            <div class="flex flex-wrap gap-2 justify-center mt-3">
              <a target="_blank" href="<?= h(gcal_url($e, ($invitation['judul'] ?? 'Pernikahan') . ' - ' . $e['jenis'])) ?>" class="btn-outline">+ Google Calendar</a>
              <?php if (!empty($e['maps_url'])): ?>
                <a target="_blank" href="<?= h($e['maps_url']) ?>" class="btn-outline">Lihat Maps</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($invitation['show_livestream'] && !empty($invitation['livestream_url'])): ?>
        <div class="livestream mt-8" data-aos="fade-up">
          <h4 class="font-serif text-xl mb-1">Live Streaming</h4>
          <p class="text-sm opacity-80 mb-3"><?= nl2br(h($invitation['livestream_text'] ?: 'Acara ini akan disiarkan langsung melalui media internet. Silakan klik tombol di bawah ini.')) ?></p>
          <a target="_blank" href="<?= h($invitation['livestream_url']) ?>" class="btn-primary">Buka Live Streaming</a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Kisah Cinta -->
  <?php if ($invitation['show_kisah'] && !empty($kisah)): ?>
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant">
      <p class="eyebrow text-center">Our Love Story</p>
      <h3 class="font-serif text-3xl text-center mb-6">Kisah Cinta Kami</h3>
      <div class="timeline space-y-6">
        <?php foreach ($kisah as $k): ?>
          <div class="timeline-item md:grid md:grid-cols-5 md:gap-6 items-center" data-aos="fade-up">
            <?php if (!empty($k['foto'])): ?>
              <img src="<?= h(upload_url($k['foto'])) ?>" class="md:col-span-2 w-full h-44 object-cover rounded-lg mb-3 md:mb-0">
            <?php else: ?>
              <div class="md:col-span-2"></div>
            <?php endif; ?>
            <div class="md:col-span-3">
              <h4 class="font-serif text-2xl"><?= h($k['judul']) ?></h4>
              <?php if (!empty($k['tanggal'])): ?><p class="text-xs uppercase tracking-widest opacity-70 mb-1"><?= h($k['tanggal']) ?></p><?php endif; ?>
              <p class="opacity-90"><?= nl2br(h($k['deskripsi'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Galeri -->
  <?php if ($invitation['show_galeri'] && !empty($galeri)): ?>
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant">
      <p class="eyebrow text-center">Moments</p>
      <h3 class="font-serif text-3xl text-center mb-6">Galeri</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <?php foreach ($galeri as $g): ?>
          <a href="<?= h(upload_url($g['foto'])) ?>" target="_blank" data-aos="zoom-in">
            <img src="<?= h(upload_url($g['foto'])) ?>" class="h-40 md:h-48 w-full object-cover rounded-lg hover:opacity-90 transition">
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- RSVP & Kado -->
  <?php if ($invitation['show_kado']): ?>
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant">
      <p class="eyebrow text-center">RSVP</p>
      <h3 class="font-serif text-3xl text-center mb-2">Kehadiran Anda Sangat Berarti</h3>
      <p class="text-center opacity-80 mb-5">Mohon konfirmasi kehadiran Anda</p>

      <form id="formRsvp" class="grid md:grid-cols-2 gap-3 max-w-xl mx-auto">
        <input name="nama" placeholder="Nama Anda" value="<?= h($guestName) ?>" required class="input">
        <input type="number" name="jumlah_tamu" min="1" max="10" value="1" class="input">
        <select name="hadir" class="input md:col-span-2">
          <option value="hadir">Hadir</option>
          <option value="ragu">Mungkin Hadir</option>
          <option value="tidak">Tidak Bisa Hadir</option>
        </select>
        <button class="btn-primary md:col-span-2">Kirim Konfirmasi</button>
      </form>
      <p id="rsvpMsg" class="text-center mt-3 text-sm"></p>

      <hr class="my-8 opacity-30">

      <h3 class="font-serif text-2xl text-center mb-2">Kirim Kado / Amplop</h3>
      <p class="text-center opacity-80 mb-5 max-w-lg mx-auto">Doa restu Anda merupakan karunia yang sangat berarti bagi kedua mempelai. Namun jika ingin memberi tanda kasih, Anda dapat menggunakan fitur berikut:</p>

      <?php if (!empty($invitation['bank_info'])): ?>
        <div class="space-y-2 max-w-md mx-auto">
          <?php foreach (preg_split("/\r?\n/", trim($invitation['bank_info'])) as $line): if (trim($line)===''): continue; endif; ?>
            <div class="bank-row flex items-center justify-between gap-2 px-3 py-2 rounded-lg bg-white/40 border border-white/40">
              <span><?= h($line) ?></span>
              <button type="button" class="btn-outline text-xs" onclick="navigator.clipboard.writeText(<?= json_encode(preg_replace('/.*\b(\d{6,})\b.*/', '$1', $line)) ?>);this.textContent='Tersalin'">Salin No.</button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($invitation['qris'])): ?>
        <div class="text-center mt-6">
          <p class="text-xs uppercase tracking-widest opacity-70 mb-2">QRIS</p>
          <img src="<?= h(upload_url($invitation['qris'])) ?>" class="mx-auto max-h-72 rounded-lg shadow">
        </div>
      <?php endif; ?>

      <?php if (!empty($invitation['mohon_konfirmasi'])): ?>
        <p class="text-center mt-6 text-sm">Mohon konfirmasi ke
          <a href="https://wa.me/<?= h(preg_replace('/\D/','',$invitation['mohon_konfirmasi'])) ?>" target="_blank" class="underline font-medium"><?= h($invitation['mohon_konfirmasi']) ?></a>
        </p>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Ucapan & Doa -->
  <section class="section-block" data-aos="fade-up">
    <div class="card-elegant">
      <p class="eyebrow text-center">Wishes</p>
      <h3 class="font-serif text-3xl text-center mb-6">Ucapan &amp; Doa</h3>
      <form id="formUcapan" class="grid gap-3 max-w-xl mx-auto mb-6">
        <input name="nama" placeholder="Nama" value="<?= h($guestName) ?>" required class="input">
        <textarea name="pesan" rows="3" placeholder="Tulis ucapan & doa..." required class="input"></textarea>
        <button class="btn-primary">Kirim Ucapan</button>
      </form>
      <div id="ucapanList" class="space-y-3 max-w-xl mx-auto">
        <?php foreach ($ucapan as $u): ?>
          <div class="ucapan-item">
            <div class="flex items-center gap-2 mb-1">
              <div class="avatar"><?= h(mb_strtoupper(mb_substr($u['nama'],0,1))) ?></div>
              <strong><?= h($u['nama']) ?></strong>
              <span class="text-xs opacity-60 ml-auto"><?= h(format_tanggal($u['created_at'], 'd M Y')) ?></span>
            </div>
            <p class="text-sm opacity-90"><?= nl2br(h($u['pesan'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Penutup -->
  <section class="section-block text-center" data-aos="fade-up">
    <div class="card-elegant text-center">
      <p class="opacity-80 mb-2">Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu kepada putra-putri kami.</p>
      <p class="font-serif text-2xl mt-4">Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>
      <p class="font-serif text-3xl mt-3"><?= h($pria['nama_panggilan'] ?? '') ?> &amp; <?= h($wanita['nama_panggilan'] ?? '') ?></p>
    </div>
  </section>
</div>

<script>
(function(){
  // Cover open
  var btn = document.getElementById('btnOpen');
  var cover = document.getElementById('cover');
  var content = document.getElementById('content');
  if (btn) btn.addEventListener('click', function(){
    cover.classList.add('cover-open');
    content.classList.remove('hidden');
    // Scroll setelah transisi
    setTimeout(function(){ content.scrollIntoView({behavior:'smooth'}); }, 350);
    // Music autoplay (optional)
    var aud = document.getElementById('bgMusic'); if (aud) { aud.play().catch(()=>{}); }
  });

  // Countdown
  var target = <?= $countdownTo ? json_encode(date('c', strtotime($countdownTo))) : 'null' ?>;
  if (target) {
    var t = new Date(target).getTime();
    function tick(){
      var now = Date.now(), d = Math.max(0, t - now);
      var s = Math.floor(d/1000);
      var days = Math.floor(s/86400); s %= 86400;
      var hrs  = Math.floor(s/3600);  s %= 3600;
      var mins = Math.floor(s/60);    s %= 60;
      document.querySelectorAll('[data-cd="d"]').forEach(e=>e.textContent=days);
      document.querySelectorAll('[data-cd="h"]').forEach(e=>e.textContent=hrs);
      document.querySelectorAll('[data-cd="m"]').forEach(e=>e.textContent=mins);
      document.querySelectorAll('[data-cd="s"]').forEach(e=>e.textContent=s);
    }
    tick(); setInterval(tick, 1000);
  }

  // Init AOS
  if (window.AOS) AOS.init({ duration: 800, once: true, offset: 60 });

  // RSVP
  var rsvp = document.getElementById('formRsvp');
  if (rsvp) rsvp.addEventListener('submit', function(ev){
    ev.preventDefault();
    var fd = new FormData(rsvp);
    fd.append('action','rsvp'); fd.append('slug','<?= $slugVal ?>');
    <?php if ($tgt): ?>fd.append('guest_token','<?= $tgt ?>');<?php endif; ?>
    fetch('<?= h($apiUrl) ?>',{method:'POST',body:fd}).then(r=>r.json()).then(j=>{
      var m = document.getElementById('rsvpMsg');
      m.textContent = j.msg || (j.ok?'Terima kasih.':'Terjadi kesalahan');
      m.className = 'text-center mt-3 text-sm ' + (j.ok ? 'text-emerald-600' : 'text-red-600');
      if (j.ok) rsvp.reset();
    });
  });

  // Ucapan
  var uc = document.getElementById('formUcapan');
  var list = document.getElementById('ucapanList');
  if (uc) uc.addEventListener('submit', function(ev){
    ev.preventDefault();
    var fd = new FormData(uc);
    fd.append('action','ucapan'); fd.append('slug','<?= $slugVal ?>');
    <?php if ($tgt): ?>fd.append('guest_token','<?= $tgt ?>');<?php endif; ?>
    fetch('<?= h($apiUrl) ?>',{method:'POST',body:fd}).then(r=>r.json()).then(j=>{
      if (j.ok && j.item) {
        var div = document.createElement('div');
        div.className = 'ucapan-item';
        var nama = j.item.nama.replace(/[<>&]/g,'');
        var pesan = j.item.pesan.replace(/[<>&]/g,'').replace(/\n/g,'<br>');
        div.innerHTML = '<div class="flex items-center gap-2 mb-1"><div class="avatar">'+nama.charAt(0).toUpperCase()+'</div><strong>'+nama+'</strong><span class="text-xs opacity-60 ml-auto">Baru saja</span></div><p class="text-sm opacity-90">'+pesan+'</p>';
        list.prepend(div);
        uc.reset();
      } else {
        alert(j.msg || 'Gagal mengirim');
      }
    });
  });
})();
</script>
