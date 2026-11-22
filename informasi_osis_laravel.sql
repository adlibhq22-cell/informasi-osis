-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2026 at 03:43 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `informasi_osis_laravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sesi_id` bigint(20) UNSIGNED NOT NULL,
  `anggota_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('hadir','izin','sakit','alpa') NOT NULL DEFAULT 'alpa',
  `keterangan` varchar(220) DEFAULT NULL,
  `dicatat_oleh` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `absensi_sesi`
--

CREATE TABLE `absensi_sesi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(180) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuat_oleh` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(120) NOT NULL,
  `nis` varchar(40) DEFAULT NULL,
  `kelas` varchar(60) DEFAULT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','alumni') NOT NULL DEFAULT 'aktif',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id`, `user_id`, `nama`, `nis`, `kelas`, `no_hp`, `email`, `alamat`, `status`, `foto`, `created_at`, `updated_at`) VALUES
(5, 4, 'test', '0809', 'test', '081234567890', 'test@gmail.com', 'test', 'aktif', 'drive/1NYq-PlFtwgOl5M1xNOTHyvN_g4JdwWxz.jpg', '2026-11-14 07:05:38', '2026-11-14 07:05:38'),
(7, 10, 'david', '0809', 'XII RPL', '085749648237', 'david@gmail.com', 'dada', 'aktif', 'drive/1ocnW8Z-GkVa8ffTQH6dOpFD5p7YpzMi-.jpg', '2026-11-14 08:04:21', '2026-11-14 08:04:36');

-- --------------------------------------------------------

--
-- Table structure for table `galeri_album`
--

CREATE TABLE `galeri_album` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(180) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `kegiatan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galeri_album`
--

INSERT INTO `galeri_album` (`id`, `judul`, `deskripsi`, `tanggal`, `kegiatan_id`, `created_at`) VALUES
(3, 'yok', 'fse', '2026-11-10', 4, '2026-11-10 12:45:14');

-- --------------------------------------------------------

--
-- Table structure for table `galeri_foto`
--

CREATE TABLE `galeri_foto` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `album_id` bigint(20) UNSIGNED NOT NULL,
  `file` varchar(255) NOT NULL,
  `keterangan` varchar(220) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galeri_foto`
--

INSERT INTO `galeri_foto` (`id`, `album_id`, `file`, `keterangan`, `created_at`) VALUES
(2, 3, 'galeri/2c88b262b2777ee906b28edea0b78765.jpg', NULL, '2026-11-11 13:22:39'),
(3, 3, 'drive/1TnOx-kjp0aNDFWNs9Ka7cQpdHZ6Jj1St.jpg', NULL, '2026-11-14 06:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_laporan`
--

CREATE TABLE `kategori_laporan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(80) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_laporan`
--

INSERT INTO `kategori_laporan` (`id`, `nama`, `aktif`, `created_at`) VALUES
(1, 'Aspirasi', 1, '2026-11-04 01:53:59'),
(2, 'Kegiatan', 1, '2026-11-04 01:53:59'),
(3, 'Fasilitas sekolah', 1, '2026-11-04 01:53:59'),
(4, 'Lainnya', 1, '2026-11-04 01:53:59');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(180) NOT NULL,
  `deskripsi` text NOT NULL,
  `tanggal` date NOT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `status` enum('rencana','berlangsung','selesai') NOT NULL DEFAULT 'rencana',
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `judul`, `deskripsi`, `tanggal`, `lokasi`, `status`, `gambar`, `created_at`) VALUES
(4, 'yok', 'sfdfwe', '2026-11-10', NULL, 'rencana', NULL, '2026-11-10 12:45:02');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `anggota_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_id` bigint(20) UNSIGNED DEFAULT NULL,
  `judul` varchar(160) NOT NULL,
  `isi` text NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `status` enum('baru','diproses','selesai','ditolak') NOT NULL DEFAULT 'baru',
  `balasan` text DEFAULT NULL,
  `ditangani_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(180) NOT NULL,
  `isi` text NOT NULL,
  `penting` enum('ya','tidak') NOT NULL DEFAULT 'tidak',
  `dibuat_oleh` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `penting`, `dibuat_oleh`, `created_at`) VALUES
(4, 'yok', 'afdssgsd', 'ya', 2, '2026-11-10 12:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `pengurus`
--

CREATE TABLE `pengurus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `anggota_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(120) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `periode` varchar(40) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengurus`
--

INSERT INTO `pengurus` (`id`, `anggota_id`, `nama`, `jabatan`, `periode`, `deskripsi`, `foto`, `created_at`) VALUES
(4, 5, 'test', 'test', 'test', 'test', NULL, '2026-11-14 07:06:56');

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(180) NOT NULL,
  `deskripsi` text NOT NULL,
  `deadline` date DEFAULT NULL,
  `prioritas` enum('rendah','normal','tinggi') NOT NULL DEFAULT 'normal',
  `status` enum('aktif','selesai') NOT NULL DEFAULT 'aktif',
  `dibuat_oleh` bigint(20) UNSIGNED NOT NULL,
  `diselesaikan_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `diselesaikan_pada` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`id`, `judul`, `deskripsi`, `deadline`, `prioritas`, `status`, `dibuat_oleh`, `diselesaikan_oleh`, `diselesaikan_pada`, `created_at`, `updated_at`) VALUES
(1, 'yok', 'ayam', '2026-11-11', 'normal', 'selesai', 2, 2, '2026-11-11 07:30:48', '2026-11-11 00:29:19', '2026-11-11 00:30:48'),
(2, 'yok', 'hhhhhhhhhhhhhhhhhhhh', '2026-11-11', 'normal', 'aktif', 2, NULL, NULL, '2026-11-11 00:46:29', '2026-11-11 00:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `tugas_dokumentasi`
--

CREATE TABLE `tugas_dokumentasi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pengumpulan_id` bigint(20) UNSIGNED NOT NULL,
  `file` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas_penerima`
--

CREATE TABLE `tugas_penerima` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tugas_id` bigint(20) UNSIGNED NOT NULL,
  `anggota_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('ditugaskan','terkirim') NOT NULL DEFAULT 'ditugaskan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas_pengumpulan`
--

CREATE TABLE `tugas_pengumpulan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penugasan_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `laporan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas_riwayat`
--

CREATE TABLE `tugas_riwayat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tugas_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `aksi` enum('dibuat','pengumpulan','selesai') NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tugas_riwayat`
--

INSERT INTO `tugas_riwayat` (`id`, `tugas_id`, `user_id`, `aksi`, `keterangan`, `created_at`) VALUES
(1, 1, 2, 'dibuat', 'Tugas dibuat dan dibagikan kepada 1 penerima.', '2026-11-11 00:29:19'),
(2, 1, NULL, 'pengumpulan', 'Mengirim laporan dengan 1 dokumentasi.', '2026-11-11 00:30:12'),
(3, 1, 2, 'selesai', 'Tugas ditandai selesai setelah seluruh penerima mengirim laporan dan dokumentasi.', '2026-11-11 00:30:48'),
(4, 2, 2, 'dibuat', 'Tugas dibuat dan dibagikan kepada 1 penerima.', '2026-11-11 00:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(120) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','pengurus','anggota') NOT NULL DEFAULT 'anggota',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `email`, `password_hash`, `role`, `aktif`, `created_at`) VALUES
(2, 'Administrator OSIS', 'admin', NULL, '$2y$12$WExbNNjVVhyVqD4f4yDoBegpUn5dVMaL3xuMXm3sYA6T1k5TeYVhK', 'admin', 1, '2026-11-04 03:41:09'),
(4, 'test', 'test', 'test@gmail.com', '$2y$12$b7FZcoRb4eV6WHByHwPeKOiqBCpQmsQp3rcSGSQqo3WjARByX10vW', 'pengurus', 1, '2026-11-14 07:05:38'),
(10, 'david', 'david', 'david@gmail.com', '$2y$12$F2lQfXNGycMHLmLA3jwgoO20d977d585Cwi3evHgv8/Y1WKdIf1fe', 'anggota', 1, '2026-11-14 08:04:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_absensi_sesi_anggota` (`sesi_id`,`anggota_id`),
  ADD KEY `idx_absensi_anggota` (`anggota_id`),
  ADD KEY `fk_absensi_pencatat` (`dicatat_oleh`);

--
-- Indexes for table `absensi_sesi`
--
ALTER TABLE `absensi_sesi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_absensi_sesi_tanggal` (`tanggal`),
  ADD KEY `fk_sesi_admin` (`dibuat_oleh`);

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_anggota_nama` (`nama`);

--
-- Indexes for table `galeri_album`
--
ALTER TABLE `galeri_album`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_album_tanggal` (`tanggal`),
  ADD KEY `fk_album_kegiatan` (`kegiatan_id`);

--
-- Indexes for table `galeri_foto`
--
ALTER TABLE `galeri_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_foto_album` (`album_id`);

--
-- Indexes for table `kategori_laporan`
--
ALTER TABLE `kategori_laporan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kegiatan_tanggal` (`tanggal`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_laporan_anggota` (`anggota_id`),
  ADD KEY `idx_laporan_status` (`status`),
  ADD KEY `fk_laporan_kategori` (`kategori_id`),
  ADD KEY `fk_laporan_admin` (`ditangani_oleh`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pengumuman_penting` (`penting`),
  ADD KEY `idx_pengumuman_created` (`created_at`),
  ADD KEY `fk_pengumuman_admin` (`dibuat_oleh`);

--
-- Indexes for table `pengurus`
--
ALTER TABLE `pengurus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pengurus_anggota` (`anggota_id`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tugas_status` (`status`),
  ADD KEY `idx_tugas_deadline` (`deadline`),
  ADD KEY `fk_tugas_pembuat` (`dibuat_oleh`),
  ADD KEY `fk_tugas_penyelesai` (`diselesaikan_oleh`);

--
-- Indexes for table `tugas_dokumentasi`
--
ALTER TABLE `tugas_dokumentasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tugas_dokumentasi_pengumpulan` (`pengumpulan_id`);

--
-- Indexes for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tugas_penerima` (`tugas_id`,`anggota_id`),
  ADD KEY `idx_tugas_penerima_anggota` (`anggota_id`);

--
-- Indexes for table `tugas_pengumpulan`
--
ALTER TABLE `tugas_pengumpulan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tugas_pengumpulan_assignment` (`penugasan_id`),
  ADD KEY `fk_tugas_pengumpulan_user` (`user_id`);

--
-- Indexes for table `tugas_riwayat`
--
ALTER TABLE `tugas_riwayat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tugas_riwayat_task_created` (`tugas_id`,`created_at`),
  ADD KEY `fk_tugas_riwayat_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `absensi_sesi`
--
ALTER TABLE `absensi_sesi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `galeri_album`
--
ALTER TABLE `galeri_album`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `galeri_foto`
--
ALTER TABLE `galeri_foto`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori_laporan`
--
ALTER TABLE `kategori_laporan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengurus`
--
ALTER TABLE `pengurus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tugas_dokumentasi`
--
ALTER TABLE `tugas_dokumentasi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tugas_pengumpulan`
--
ALTER TABLE `tugas_pengumpulan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tugas_riwayat`
--
ALTER TABLE `tugas_riwayat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absensi_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_absensi_pencatat` FOREIGN KEY (`dicatat_oleh`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_absensi_sesi` FOREIGN KEY (`sesi_id`) REFERENCES `absensi_sesi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `absensi_sesi`
--
ALTER TABLE `absensi_sesi`
  ADD CONSTRAINT `fk_sesi_admin` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `anggota`
--
ALTER TABLE `anggota`
  ADD CONSTRAINT `fk_anggota_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `galeri_album`
--
ALTER TABLE `galeri_album`
  ADD CONSTRAINT `fk_album_kegiatan` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `galeri_foto`
--
ALTER TABLE `galeri_foto`
  ADD CONSTRAINT `fk_foto_album` FOREIGN KEY (`album_id`) REFERENCES `galeri_album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `fk_laporan_admin` FOREIGN KEY (`ditangani_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laporan_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laporan_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_laporan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `fk_pengumuman_admin` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `pengurus`
--
ALTER TABLE `pengurus`
  ADD CONSTRAINT `fk_pengurus_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `fk_tugas_pembuat` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tugas_penyelesai` FOREIGN KEY (`diselesaikan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tugas_dokumentasi`
--
ALTER TABLE `tugas_dokumentasi`
  ADD CONSTRAINT `fk_tugas_dokumentasi_pengumpulan` FOREIGN KEY (`pengumpulan_id`) REFERENCES `tugas_pengumpulan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  ADD CONSTRAINT `fk_tugas_penerima_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tugas_penerima_tugas` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tugas_pengumpulan`
--
ALTER TABLE `tugas_pengumpulan`
  ADD CONSTRAINT `fk_tugas_pengumpulan_assignment` FOREIGN KEY (`penugasan_id`) REFERENCES `tugas_penerima` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tugas_pengumpulan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `tugas_riwayat`
--
ALTER TABLE `tugas_riwayat`
  ADD CONSTRAINT `fk_tugas_riwayat_task` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tugas_riwayat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
