-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 04, 2026 at 11:17 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `platform_karir_kampus`
--

-- --------------------------------------------------------

--
-- Table structure for table `dokumen`
--

CREATE TABLE `dokumen` (
  `id` int NOT NULL,
  `lamaran_id` int NOT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dokumen`
--

INSERT INTO `dokumen` (`id`, `lamaran_id`, `jenis`, `file_path`, `uploaded_at`) VALUES
(1, 8, 'Dokumen 1', 'doc_8_69ce82813e2a5.pdf', '2026-04-02 14:51:45'),
(3, 10, 'Dokumen 1', 'doc_10_69cff3403f492.pdf', '2026-04-03 17:05:04'),
(4, 11, 'Dokumen 1', 'doc_11_69d0ecd3493c3.pdf', '2026-04-04 10:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `dpa`
--

CREATE TABLE `dpa` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dpa`
--

INSERT INTO `dpa` (`id`, `user_id`, `nama`) VALUES
(2, 13, 'Muchammad Abdurrohim');

-- --------------------------------------------------------

--
-- Table structure for table `lamaran`
--

CREATE TABLE `lamaran` (
  `id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `peluang_id` int NOT NULL,
  `is_recommended` tinyint(1) DEFAULT '0',
  `tanggal_apply` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `result_published` tinyint(1) DEFAULT '0' COMMENT 'Status: 0=not published, 1=published to mahasiswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lamaran`
--

INSERT INTO `lamaran` (`id`, `mahasiswa_id`, `peluang_id`, `is_recommended`, `tanggal_apply`, `status`, `result_published`) VALUES
(8, 3, 3, 1, '2026-04-02 14:51:45', 'accepted', 1),
(10, 3, 4, 0, '2026-04-03 17:05:04', 'rejected', 1),
(11, 3, 1, 0, '2026-04-04 10:49:55', 'accepted', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `angkatan` year DEFAULT NULL,
  `semester` int DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `dpa_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `user_id`, `nim`, `nama`, `fakultas`, `prodi`, `angkatan`, `semester`, `ipk`, `dpa_id`) VALUES
(3, 6, '321314', 'John Doe', 'MIPA', 'Kimia', '2024', 4, 3.67, 2),
(8, 14, '4243250091', 'Jane Doe', 'MIPA', 'Biologi', '2025', 2, 3.90, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mitra`
--

CREATE TABLE `mitra` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nama_organisasi` varchar(150) DEFAULT NULL,
  `deskripsi` text,
  `kontak` varchar(100) DEFAULT NULL,
  `status_verifikasi` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mitra`
--

INSERT INTO `mitra` (`id`, `user_id`, `nama_organisasi`, `deskripsi`, `kontak`, `status_verifikasi`) VALUES
(3, 9, 'Buy', 'buycorp', '0812', 'approved'),
(4, 12, NULL, NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `pesan` text,
  `pesan_custom` text,
  `tipe_notifikasi` enum('standard','recommendation','result') DEFAULT 'standard',
  `related_id` int DEFAULT NULL,
  `pengirim_id` int DEFAULT NULL,
  `status_baca` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `pesan`, `pesan_custom`, `tipe_notifikasi`, `related_id`, `pengirim_id`, `status_baca`, `created_at`) VALUES
(1, 6, 'Lamaran Anda diterima!', NULL, 'standard', NULL, NULL, 1, '2026-04-02 15:33:02'),
(2, 6, 'Lamaran Anda ditolak.', NULL, 'standard', NULL, NULL, 1, '2026-04-02 15:33:27'),
(3, 6, 'Lamaran Anda diterima!', NULL, 'standard', NULL, NULL, 1, '2026-04-02 15:33:36'),
(4, 6, 'Lamaran Anda ditolak.', NULL, 'standard', NULL, NULL, 1, '2026-04-02 15:33:41'),
(5, 6, 'Lamaran Anda diterima!', NULL, 'standard', NULL, NULL, 1, '2026-04-02 15:33:47'),
(6, 6, 'Lamaran Anda ditolak.', NULL, 'standard', NULL, NULL, 1, '2026-04-02 15:59:10'),
(7, 6, 'Lamaran Anda diterima!', NULL, 'standard', NULL, NULL, 1, '2026-04-03 07:29:02'),
(10, 6, 'DPA merekomendasikan peluang \"Pro\" untuk Anda. Silakan periksa peluang ini.', NULL, 'standard', NULL, NULL, 1, '2026-04-03 16:50:54'),
(11, 6, 'Lamaran Anda ditolak.', NULL, 'standard', NULL, NULL, 1, '2026-04-03 16:55:18'),
(12, 6, 'Lamaran Anda diterima!', NULL, 'standard', NULL, NULL, 1, '2026-04-03 16:55:30'),
(13, 6, 'Lamaran Anda ditolak.', NULL, 'standard', NULL, NULL, 1, '2026-04-04 06:20:29'),
(14, 6, 'Lamaran Anda diterima!', NULL, 'standard', NULL, NULL, 0, '2026-04-04 10:51:43'),
(15, 6, 'DPA merekomendasikan peluang \"dsad\" untuk Anda. Silakan periksa peluang ini.', 'Bababoeey', 'recommendation', 4, 13, 0, '2026-04-04 11:11:32');

-- --------------------------------------------------------

--
-- Table structure for table `peluang`
--

CREATE TABLE `peluang` (
  `id` int NOT NULL,
  `mitra_id` int NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `tipe` enum('magang','kursus') NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `kuota` int DEFAULT '0',
  `min_ipk` decimal(3,2) DEFAULT '0.00',
  `min_semester` int DEFAULT '1',
  `fakultas` varchar(100) DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peluang`
--

INSERT INTO `peluang` (`id`, `mitra_id`, `judul`, `deskripsi`, `tipe`, `lokasi`, `kuota`, `min_ipk`, `min_semester`, `fakultas`, `deadline`, `status`, `created_at`, `closed_at`) VALUES
(1, 9, 'Pro', 'Prokok', 'magang', 'Medan', 12, 2.00, 21, '213', '2027-02-18 10:20:00', 'approved', '2026-03-22 14:23:28', '2026-04-04 10:51:43'),
(2, 9, 'Bujas', 'Mantapo', 'magang', 'Medan', 2, 0.00, 2, '', '2028-02-04 10:20:00', 'pending', '2026-03-22 14:37:03', NULL),
(3, 9, 'dasda', 'dasdad', 'magang', 'dasdad', 1, 2.00, 2, 'dwqqw', '2028-05-01 10:50:00', 'approved', '2026-03-22 15:25:56', NULL),
(4, 9, '31', 'ewdqeq', 'magang', 'eqwewq', 1, 4.00, 23, 'MIPA', '2026-04-04 00:04:00', 'approved', '2026-04-03 17:04:47', NULL),
(5, 9, 'dsad', 'dsadas', 'magang', 'wqdqws', 1, 2.00, 3, '', '2030-03-02 12:22:00', 'approved', '2026-04-04 11:11:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pesan_hasil`
--

CREATE TABLE `pesan_hasil` (
  `id` int NOT NULL,
  `lamaran_id` int NOT NULL,
  `tipe_hasil` enum('accepted','rejected') NOT NULL,
  `pesan_mitra` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rekomendasi`
--

CREATE TABLE `rekomendasi` (
  `id` int NOT NULL,
  `dpa_id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `peluang_id` int NOT NULL,
  `pesan_dosen` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rekomendasi`
--

INSERT INTO `rekomendasi` (`id`, `dpa_id`, `mahasiswa_id`, `peluang_id`, `pesan_dosen`, `created_at`) VALUES
(1, 2, 3, 3, NULL, '2026-04-03 16:41:35'),
(3, 2, 3, 1, NULL, '2026-04-03 16:50:54'),
(4, 2, 3, 5, 'Bababoeey', '2026-04-04 11:11:32');

-- --------------------------------------------------------

--
-- Table structure for table `sertifikat`
--

CREATE TABLE `sertifikat` (
  `id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `peluang_id` int NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `tanggal_terbit` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','mitra','admin','dpa') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','pending','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `created_at`, `status`) VALUES
(6, '1@gmail.com', '$2y$10$9uQNPfDfMLt9awRP05D0DusyK5omCggp8CvK6eIvEm1uQ.HUXTScC', 'mahasiswa', '2026-03-22 10:47:49', 'active'),
(9, '2@gmail.com', '$2y$10$cl1LSHWgGmXDj6aziO6EVetPMJrlxEA9BRom5ghJy3FByCwdEXSwK', 'mitra', '2026-03-22 12:24:43', 'active'),
(11, 'Admin1@gmail.com', '$2y$10$PsLjYrsDQRaGyBet4ZZ/F.Jf3cfgmkGLhmNyde1UcUUaWx5l4tWxS', 'admin', '2026-04-03 08:43:47', 'active'),
(12, 'Mitra@gmail.com', '$2y$10$ngpQWoFRItcmNGPFjwXQUOUux2kMC.S9PrSIjDwO/WZpOGucfWtZS', 'mitra', '2026-04-03 10:09:46', 'active'),
(13, 'DPA1@gmail.com', '$2y$10$NxV2XyDG4DIfU1fRBWP5M./OeW9oYM/szFs3FMnUeF6FHe0Q.dNme', 'dpa', '2026-04-03 10:18:05', 'active'),
(14, 'Jane@gmail.com', '$2y$10$sbgrNXeGHX8uZ3z3jqG4SejWehOjZ4/2I6Ao24K1jOKyVjJlwmpWa', 'mahasiswa', '2026-04-03 11:52:34', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dokumen`
--
ALTER TABLE `dokumen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lamaran_id` (`lamaran_id`);

--
-- Indexes for table `dpa`
--
ALTER TABLE `dpa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lamaran_mahasiswa` (`mahasiswa_id`),
  ADD KEY `fk_lamaran_peluang` (`peluang_id`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mitra`
--
ALTER TABLE `mitra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_tipe_notifikasi` (`tipe_notifikasi`),
  ADD KEY `idx_related_id` (`related_id`),
  ADD KEY `idx_pengirim_id` (`pengirim_id`);

--
-- Indexes for table `peluang`
--
ALTER TABLE `peluang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_peluang_mitra` (`mitra_id`);

--
-- Indexes for table `pesan_hasil`
--
ALTER TABLE `pesan_hasil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pesan_hasil_lamaran` (`lamaran_id`);

--
-- Indexes for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dpa_id` (`dpa_id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `peluang_id` (`peluang_id`);

--
-- Indexes for table `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `peluang_id` (`peluang_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dokumen`
--
ALTER TABLE `dokumen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dpa`
--
ALTER TABLE `dpa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lamaran`
--
ALTER TABLE `lamaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mitra`
--
ALTER TABLE `mitra`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `peluang`
--
ALTER TABLE `peluang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pesan_hasil`
--
ALTER TABLE `pesan_hasil`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sertifikat`
--
ALTER TABLE `sertifikat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokumen`
--
ALTER TABLE `dokumen`
  ADD CONSTRAINT `dokumen_ibfk_1` FOREIGN KEY (`lamaran_id`) REFERENCES `lamaran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dpa`
--
ALTER TABLE `dpa`
  ADD CONSTRAINT `dpa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD CONSTRAINT `fk_lamaran_peluang` FOREIGN KEY (`peluang_id`) REFERENCES `peluang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lamaran_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lamaran_ibfk_2` FOREIGN KEY (`peluang_id`) REFERENCES `peluang` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mitra`
--
ALTER TABLE `mitra`
  ADD CONSTRAINT `mitra_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_pengirim` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `peluang`
--
ALTER TABLE `peluang`
  ADD CONSTRAINT `fk_peluang_mitra` FOREIGN KEY (`mitra_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesan_hasil`
--
ALTER TABLE `pesan_hasil`
  ADD CONSTRAINT `fk_pesan_hasil_lamaran` FOREIGN KEY (`lamaran_id`) REFERENCES `lamaran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  ADD CONSTRAINT `rekomendasi_ibfk_1` FOREIGN KEY (`dpa_id`) REFERENCES `dpa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rekomendasi_ibfk_2` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rekomendasi_ibfk_3` FOREIGN KEY (`peluang_id`) REFERENCES `peluang` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD CONSTRAINT `sertifikat_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sertifikat_ibfk_2` FOREIGN KEY (`peluang_id`) REFERENCES `peluang` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
