-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Sep 2026 pada 08.50
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_perpustakaan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama`) VALUES
(3, 'admin', '$2y$10$.TB3oqn16M9YmmwXusI5SOkH.zlh.IMbGe.gfJMji5zcRghUNQzH2', 'Petugas Perpustakaan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `sinopsis` text DEFAULT NULL,
  `cover` varchar(255) DEFAULT 'default_cover.jpg',
  `status` enum('tersedia','dipinjam') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `judul`, `penulis`, `sinopsis`, `cover`, `status`) VALUES
(35, 'Laskar Belitung', 'Andrea Hirata', 'Kisah perjuangan anak sekolah di Belitung', 'laskar.jpg', 'tersedia'),
(36, 'Bumi Pertiwi', 'Tere Liye', 'Petualangan di dunia paralel', 'bumi.jpg', 'tersedia'),
(37, 'Negeri 5 Menara', 'Ahmad Fuadi', 'Kisah kehidupan santri', 'menara.jpg', 'dipinjam'),
(41, 'Pulang', 'Tere Liye', 'itulah intinya', 'pulang.jpg', 'tersedia'),
(42, 'Pergi', 'Tere Liye', 'pp', 'pergi.jpg', 'tersedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `siswa_id`, `pesan`, `is_read`, `created_at`) VALUES
(1, 8, 'Waktu peminjaman buku \"Hujan\" habis, kembalikan sekarang!', 0, '2026-08-24 07:40:06'),
(2, 8, 'Waktu peminjaman buku \"Hujan\" habis, kembalikan sekarang!', 0, '2026-08-24 07:42:53'),
(6, 11, 'Waktu peminjaman buku \"Negeri 5 Menara\" habis, kembalikan sekarang!', 0, '2026-09-16 03:53:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) DEFAULT NULL,
  `buku_id` int(11) DEFAULT NULL,
  `tanggal_pinjam` date NOT NULL,
  `durasi_hari` int(11) DEFAULT 3,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status_transaksi` enum('berjalan','selesai') DEFAULT 'berjalan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `siswa_id`, `buku_id`, `tanggal_pinjam`, `durasi_hari`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status_transaksi`) VALUES
(46, 9, 37, '2026-09-10', 5, '2026-09-15', '2026-09-13', 'selesai'),
(47, 15, 42, '2026-09-10', 3, '2026-09-13', '2026-09-13', 'selesai'),
(48, 15, 37, '2026-09-14', 6, '2026-09-20', '2026-09-14', 'selesai'),
(49, 7, 35, '2026-09-14', 2, '2026-09-16', '2026-09-15', 'selesai'),
(50, 6, 42, '2026-09-14', 2, '2026-09-16', '2026-09-15', 'selesai'),
(51, 9, 41, '2026-09-14', 7, '2026-09-21', '2026-09-15', 'selesai'),
(52, 11, 37, '2026-09-16', 1, '2026-09-17', NULL, 'berjalan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `review_buku`
--

CREATE TABLE `review_buku` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL DEFAULT 5,
  `ulasan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `review_buku`
--

INSERT INTO `review_buku` (`id`, `siswa_id`, `buku_id`, `rating`, `ulasan`, `created_at`) VALUES
(1, 15, 42, 5, 'hemmmh, kelas sih bukunya asik jugak', '2026-09-13 13:58:17'),
(2, 9, 37, 5, 'asikkkk, kelas banget bukunya king, kayak gitulah intinya king, ngetikan lu king apa yang gw maksud?, yah jadi gitulah, ada orang masuk pesantren terus jadi gila, habis itu mati, terus ibunya nangis, terus ikut mati jugak,, ya begitulah', '2026-09-13 14:00:10'),
(3, 15, 37, 3, 'keren king, kelas king, keren king kelas king, keren king, kelas king, keren king kelas king', '2026-09-14 14:03:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nomor_kartu` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id`, `nomor_kartu`, `nama`, `kelas`, `password`) VALUES
(6, '0010180679', 'Hafidzan', '1 SMA', '$2y$10$RB09FUqQeFWsBWNFVI1AGuqg6Lf1SFjdBEnBEPCl.boa4jKMVgee2'),
(7, '0011047348', 'Putria', '1 SMA', '$2y$10$VN9fiV2XUTCpdcIru2GViuywyZS9MAyaF0PSj9e/PLbsp/zHSYCku'),
(9, '0010173422', 'Nasywa', '1 SMA', '$2y$10$4NzHe7m/qoIYV0XPdEXBdO1GDzKlMhzCBjL28rMcgEv.p5Ctz7XPe'),
(10, '0011039170', 'Raihanah', '1 SMA', '$2y$10$Pn.0yZCDifLER737vAWrHeJBkCnuVwqFclQzwCt43Y5H0nfcxPaWS'),
(11, '0010142008', 'Yumna', '1 SMA', '$2y$10$G7J625orryRdqHVgMkesyuIv/DFTw/eYsahNbAuIobzEkx266dyme'),
(12, '0010986384', 'Ayla', '1 SMA', '$2y$10$KDlItG3Xx2ukBruFOUMGiuzm0uZUS3ETeIUBBx5scm.0NW4JL3PXC'),
(13, '0010184930', 'Karimah', '1 SMA', '$2y$10$biCmCsEGUss.aYMQ440A5u.W8wxfmfvkbQ0K8Z582Mu6blhmSiu1O'),
(14, '0010992711', 'Husna', '1 SMA', '$2y$10$VfgqDqcXEIYuMfWSFNtCI.LP1USIMFkJPFdAIGdPWPUxET2W4rafe'),
(15, '0010159319', 'Kinzi', '1 SMA', '$2y$10$DGsTo/BdMkHaqbQL52mwXOtayPIiE7LMr7G0TiVO1lN2uLyn5Iiz2');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `review_buku`
--
ALTER TABLE `review_buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_kartu` (`nomor_kartu`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `review_buku`
--
ALTER TABLE `review_buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `review_buku`
--
ALTER TABLE `review_buku`
  ADD CONSTRAINT `fk_review_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
