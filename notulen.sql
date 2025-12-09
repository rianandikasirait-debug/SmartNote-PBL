CREATE DATABASE IF NOT EXISTS `notulen`;
USE `notulen`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `tambah_notulen` (
  `id` int NOT NULL,
  `judul_rapat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_rapat` date NOT NULL,
  `isi_rapat` text COLLATE utf8mb4_general_ci NOT NULL,
  `Lampiran` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `peserta` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tambah_pengguna` (
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','peserta') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'peserta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int NOT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'user.jpg',
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nik` int NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','peserta') COLLATE utf8mb4_general_ci DEFAULT 'peserta',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `foto`, `nama`, `email`, `nik`, `password`, `role`, `created_at`) VALUES
(1, '1763529296_foto profile.jpg', 'Febry', 'admin@gmail.com', 123456, '$2y$10$hOd/rJAASQBcZdrzURsCZODO4XvM7jXq61E2RT3VX9EIj73kjvJl2', 'admin', '2025-11-17 08:23:59'),
(15, 'user.jpg', 'yohana', 'yohana@gmail.com', 213456, '$2y$10$SoNQ7cQX1MdohIjWs3f6nOZPGja9Ew7bixQ6HhvNC/bgRqHDUSbC6', 'peserta', '2025-11-17 08:27:36'),
(17, 'user.jpg', 'rian', 'rian@gmail.com', 908765, '$2y$10$XV5rqbg2wbZtx4empI3PQ..DzPCjdN264396USI320l4lAPuNh8h.', 'peserta', '2025-11-19 04:17:50'),
(18, 'user.jpg', 'febry', 'febry@gmail.com', 909087, '$2y$10$4R1.xDv9X/W8miVIMcWijepSDoOnux0ki5h.sct3L8fAgDnoOkLoq', 'peserta', '2025-11-19 04:54:01');

ALTER TABLE `tambah_notulen`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`nik`);

ALTER TABLE `tambah_notulen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

COMMIT;
