-- Database Informasi OSIS (15 tabel: 11 inti + 4 tugas)
-- Import file ini melalui phpMyAdmin, atau jalankan dari tab SQL. Aman diimpor ulang untuk tabel/seed yang sudah ada (tidak menghapus data).
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','pengurus','anggota') NOT NULL DEFAULT 'anggota',
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anggota (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL UNIQUE,
    nama VARCHAR(120) NOT NULL,
    nis VARCHAR(40) NULL,
    kelas VARCHAR(60) NULL,
    no_hp VARCHAR(30) NULL,
    email VARCHAR(120) NULL,
    alamat TEXT NULL,
    status ENUM('aktif','alumni') NOT NULL DEFAULT 'aktif',
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_anggota_nama (nama),
    CONSTRAINT fk_anggota_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pengurus (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    anggota_id BIGINT UNSIGNED NULL,
    nama VARCHAR(120) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    periode VARCHAR(40) NULL,
    deskripsi TEXT NULL,
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pengurus_anggota (anggota_id),
    CONSTRAINT fk_pengurus_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kegiatan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(180) NOT NULL,
    deskripsi TEXT NOT NULL,
    tanggal DATE NOT NULL,
    lokasi VARCHAR(150) NULL,
    status ENUM('rencana','berlangsung','selesai') NOT NULL DEFAULT 'rencana',
    gambar VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_kegiatan_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kategori_laporan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(80) NOT NULL UNIQUE,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS laporan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    anggota_id BIGINT UNSIGNED NOT NULL,
    kategori_id BIGINT UNSIGNED NULL,
    judul VARCHAR(160) NOT NULL,
    isi TEXT NOT NULL,
    file VARCHAR(255) NULL,
    status ENUM('baru','diproses','selesai','ditolak') NOT NULL DEFAULT 'baru',
    balasan TEXT NULL,
    ditangani_oleh BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_laporan_anggota (anggota_id),
    INDEX idx_laporan_status (status),
    CONSTRAINT fk_laporan_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_laporan_kategori FOREIGN KEY (kategori_id) REFERENCES kategori_laporan(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_laporan_admin FOREIGN KEY (ditangani_oleh) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pengumuman (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(180) NOT NULL,
    isi TEXT NOT NULL,
    penting ENUM('ya','tidak') NOT NULL DEFAULT 'tidak',
    dibuat_oleh BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pengumuman_penting (penting),
    INDEX idx_pengumuman_created (created_at),
    CONSTRAINT fk_pengumuman_admin FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS galeri_album (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(180) NOT NULL,
    deskripsi TEXT NULL,
    tanggal DATE NOT NULL,
    kegiatan_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_album_tanggal (tanggal),
    CONSTRAINT fk_album_kegiatan FOREIGN KEY (kegiatan_id) REFERENCES kegiatan(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS galeri_foto (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    album_id BIGINT UNSIGNED NOT NULL,
    file VARCHAR(255) NOT NULL,
    keterangan VARCHAR(220) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_foto_album (album_id),
    CONSTRAINT fk_foto_album FOREIGN KEY (album_id) REFERENCES galeri_album(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS absensi_sesi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(180) NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT NULL,
    dibuat_oleh BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_absensi_sesi_tanggal (tanggal),
    CONSTRAINT fk_sesi_admin FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS absensi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sesi_id BIGINT UNSIGNED NOT NULL,
    anggota_id BIGINT UNSIGNED NOT NULL,
    status ENUM('hadir','izin','sakit','alpa') NOT NULL DEFAULT 'alpa',
    keterangan VARCHAR(220) NULL,
    dicatat_oleh BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_absensi_sesi_anggota (sesi_id, anggota_id),
    INDEX idx_absensi_anggota (anggota_id),
    CONSTRAINT fk_absensi_sesi FOREIGN KEY (sesi_id) REFERENCES absensi_sesi(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_absensi_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_absensi_pencatat FOREIGN KEY (dicatat_oleh) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS tugas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(180) NOT NULL,
    deskripsi TEXT NOT NULL,
    deadline DATE NULL,
    prioritas ENUM('rendah','normal','tinggi') NOT NULL DEFAULT 'normal',
    status ENUM('aktif','selesai') NOT NULL DEFAULT 'aktif',
    dibuat_oleh BIGINT UNSIGNED NOT NULL,
    diselesaikan_oleh BIGINT UNSIGNED NULL,
    diselesaikan_pada DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tugas_status (status),
    INDEX idx_tugas_deadline (deadline),
    CONSTRAINT fk_tugas_pembuat FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_tugas_penyelesai FOREIGN KEY (diselesaikan_oleh) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tugas_penerima (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tugas_id BIGINT UNSIGNED NOT NULL,
    anggota_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ditugaskan','terkirim') NOT NULL DEFAULT 'ditugaskan',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tugas_penerima (tugas_id, anggota_id),
    INDEX idx_tugas_penerima_anggota (anggota_id),
    CONSTRAINT fk_tugas_penerima_tugas FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tugas_penerima_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tugas_pengumpulan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    penugasan_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    laporan TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tugas_pengumpulan_assignment (penugasan_id),
    CONSTRAINT fk_tugas_pengumpulan_assignment FOREIGN KEY (penugasan_id) REFERENCES tugas_penerima(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tugas_pengumpulan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tugas_dokumentasi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengumpulan_id BIGINT UNSIGNED NOT NULL,
    file VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tugas_dokumentasi_pengumpulan (pengumpulan_id),
    CONSTRAINT fk_tugas_dokumentasi_pengumpulan FOREIGN KEY (pengumpulan_id) REFERENCES tugas_pengumpulan(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tugas_riwayat (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tugas_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    aksi ENUM('dibuat','pengumpulan','selesai') NOT NULL,
    keterangan VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tugas_riwayat_task_created (tugas_id, created_at),
    CONSTRAINT fk_tugas_riwayat_task FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tugas_riwayat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO kategori_laporan (nama) VALUES ('Aspirasi'), ('Kegiatan'), ('Fasilitas sekolah'), ('Lainnya');

SET FOREIGN_KEY_CHECKS = 1;
