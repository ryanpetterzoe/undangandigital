-- Digital Wedding Invitation Schema
-- Engine: InnoDB, Charset: utf8mb4

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `nama` VARCHAR(120) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(160) NOT NULL,
  `email` VARCHAR(160) DEFAULT NULL,
  `no_hp` VARCHAR(40) DEFAULT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `invitations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED DEFAULT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `judul` VARCHAR(200) NOT NULL,
  `tanggal_acara` DATE DEFAULT NULL,
  `tema` VARCHAR(40) NOT NULL DEFAULT 'theme01',
  `background` VARCHAR(255) DEFAULT NULL,
  `musik` VARCHAR(255) DEFAULT NULL,
  `quote_arab` TEXT DEFAULT NULL,
  `quote_arti` TEXT DEFAULT NULL,
  `doa_restu` TEXT DEFAULT NULL,
  `mohon_konfirmasi` VARCHAR(80) DEFAULT NULL,
  `bank_info` TEXT DEFAULT NULL,
  `qris` VARCHAR(255) DEFAULT NULL,
  `livestream_url` VARCHAR(255) DEFAULT NULL,
  `livestream_text` TEXT DEFAULT NULL,
  `show_livestream` TINYINT(1) NOT NULL DEFAULT 1,
  `show_kisah` TINYINT(1) NOT NULL DEFAULT 1,
  `show_galeri` TINYINT(1) NOT NULL DEFAULT 1,
  `show_kado` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `slug_idx` (`slug`),
  KEY `client_idx` (`client_id`),
  CONSTRAINT `fk_inv_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mempelai` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` INT UNSIGNED NOT NULL,
  `peran` ENUM('pria','wanita') NOT NULL,
  `nama` VARCHAR(160) NOT NULL,
  `nama_panggilan` VARCHAR(80) DEFAULT NULL,
  `ayah` VARCHAR(160) DEFAULT NULL,
  `ibu` VARCHAR(160) DEFAULT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `instagram` VARCHAR(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inv_idx` (`invitation_id`),
  CONSTRAINT `fk_mp_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` INT UNSIGNED NOT NULL,
  `jenis` VARCHAR(60) NOT NULL,
  `tanggal_mulai` DATETIME NOT NULL,
  `tanggal_selesai` DATETIME DEFAULT NULL,
  `tempat` VARCHAR(200) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `maps_url` VARCHAR(500) DEFAULT NULL,
  `calendar_url` VARCHAR(500) DEFAULT NULL,
  `urutan` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `inv_idx` (`invitation_id`),
  CONSTRAINT `fk_ev_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kisah_cinta` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` INT UNSIGNED NOT NULL,
  `judul` VARCHAR(160) NOT NULL,
  `tanggal` VARCHAR(80) DEFAULT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `urutan` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `inv_idx` (`invitation_id`),
  CONSTRAINT `fk_kc_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `galeri` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` INT UNSIGNED NOT NULL,
  `foto` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `urutan` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `inv_idx` (`invitation_id`),
  CONSTRAINT `fk_gl_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `rsvp` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` INT UNSIGNED NOT NULL,
  `guest_id` INT UNSIGNED DEFAULT NULL,
  `nama` VARCHAR(160) NOT NULL,
  `hadir` ENUM('hadir','tidak','ragu') NOT NULL DEFAULT 'hadir',
  `jumlah_tamu` INT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inv_idx` (`invitation_id`),
  CONSTRAINT `fk_rsvp_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ucapan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` INT UNSIGNED NOT NULL,
  `guest_id` INT UNSIGNED DEFAULT NULL,
  `nama` VARCHAR(160) NOT NULL,
  `pesan` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inv_idx` (`invitation_id`),
  CONSTRAINT `fk_uc_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `guests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` INT UNSIGNED NOT NULL,
  `nama` VARCHAR(160) NOT NULL,
  `no_hp` VARCHAR(40) DEFAULT NULL,
  `token` VARCHAR(40) NOT NULL UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inv_idx` (`invitation_id`),
  CONSTRAINT `fk_g_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
