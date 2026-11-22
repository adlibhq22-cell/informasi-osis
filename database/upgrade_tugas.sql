-- Upgrade fitur login pengurus dan penugasan (5 tabel tugas) pada database yang sudah berjalan.
-- Cadangkan database terlebih dahulu, lalu impor file ini dari phpMyAdmin.
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE users MODIFY role ENUM('admin','pengurus','anggota') NOT NULL DEFAULT 'anggota';

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

-- Akun lama yang sudah ditautkan ke profil pengurus langsung mendapat role pengurus.
UPDATE users u
JOIN anggota a ON a.user_id = u.id
JOIN pengurus p ON p.anggota_id = a.id
SET u.role = 'pengurus'
WHERE u.role = 'anggota';

SET FOREIGN_KEY_CHECKS = 1;
