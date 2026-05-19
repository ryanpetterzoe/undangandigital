# Undangan Digital Pernikahan

Sistem undangan pernikahan digital berbasis PHP + MySQL. Cocok dijalankan di
**XAMPP + Virtual Host + Cloudflare**. Mendukung domain dinamis, multi-tenant
(banyak undangan dalam satu instalasi via slug URL), dan instalasi ala
WordPress.

## Fitur

### Halaman Undangan (publik)
- **Cover** dengan animasi transisi & tombol *Buka Undangan*
- **Card Utama**: judul, tanggal, **countdown akad**, nama tamu (otomatis)
- **Card Quote Arab** (font Amiri / Scheherazade, RTL)
- **Card Mohon Doa Restu** + foto kedua mempelai & data orang tua
- **Card Acara** (Akad / Resepsi) dengan tombol *Google Calendar* & *Maps*,
  termasuk **Live Streaming** (bisa di-on/off)
- **Card Kisah Cinta** (timeline, on/off)
- **Card Galeri** (on/off)
- **Card RSVP & Kado** (on/off): konfirmasi hadir, info bank, QRIS,
  konfirmasi via WhatsApp
- **Card Ucapan & Doa**: ditambahkan otomatis ala kolom komentar
- **10 Tema** transparan (background bisa diupload di admin)
- Animasi gulir (AOS), responsif

### Panel Admin
- Login admin
- CRUD undangan & otomatis generate slug (`/rudidandiana`)
- Edit semua konten: detail, quote, doa restu, mempelai, acara, kisah,
  galeri, livestream, bank, QRIS, tema, background, toggle visibility setiap
  card

### Panel Klien (tanpa login, akses via token)
- Daftar **RSVP**
- Daftar **Ucapan**
- **Link Generator**: input nama tamu (+nomor HP opsional) → generate link
  personal `slug?to=TOKEN` + tombol kirim ke WhatsApp dengan template pesan

## Struktur

```
/install.php          installer DB & admin
/index.php            router slug -> theme/layout
/.htaccess            URL rewrite (vhost-friendly)
/config.php           digenerate installer (jangan commit)
/schema.sql           skema database
/includes/            db, auth, functions, header/footer admin
/admin/               panel admin
/client/              panel klien (token URL ?t=...)
/invitation/api.php   AJAX endpoints (RSVP, ucapan)
/themes/              10 tema + _shared (base.php, sections.php)
/assets/              CSS bawaan
/uploads/             foto, background, QRIS (PHP diblok di sini)
```

## Instalasi (XAMPP + Virtual Host)

1. Salin folder ini ke `htdocs`, mis. `C:\xampp\htdocs\undangan`.
2. Pastikan modul Apache `mod_rewrite` aktif dan `AllowOverride All`.
3. Opsional vhost (`httpd-vhosts.conf`):
   ```apache
   <VirtualHost *:80>
     ServerName undangan.test
     DocumentRoot "C:/xampp/htdocs/undangan"
     <Directory "C:/xampp/htdocs/undangan">
       AllowOverride All
       Require all granted
     </Directory>
   </VirtualHost>
   ```
4. Buat database (atau biarkan installer membuatnya).
5. Buka `http://undangan.test/install.php`, ikuti wizard 2 langkah.
6. Setelah selesai, **hapus `install.php`** dan login di
   `http://undangan.test/admin/`.

## Cloudflare

- Aktifkan *Always Use HTTPS*. Aplikasi sudah memeriksa header
  `X-Forwarded-Proto`, sehingga URL yang dihasilkan otomatis HTTPS.
- Jika menggunakan domain dinamis, kosongkan `app.base_url` pada
  `config.php` (default) — host akan di-deteksi dari `HTTP_HOST`.

## Alur Pakai

1. **Admin** → Tambah Klien → salin link panel klien.
2. **Admin** → Tambah Undangan, pilih klien, isi tema, upload background.
3. **Admin** → Edit Undangan, lengkapi mempelai, acara, kisah, galeri, dll.
4. **Klien** buka panel (token), isi link generator, kirim WA.
5. **Tamu** membuka link, mengisi RSVP & ucapan.

## Keamanan

- CSRF token di setiap form admin/klien
- Folder `uploads/` memblokir eksekusi PHP
- File `config.php` & `schema.sql` ditolak akses langsung via `.htaccess`
- Password admin di-hash dengan `password_hash` (bcrypt)
