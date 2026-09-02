-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Agu 2026 pada 09.25
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
(3, 'Pulang', 'Tere Liye', 'gitu', 'default_cover.jpg', 'tersedia'),
(4, 'Pergi', 'Tere Liye', 'ya gitu deh', '1787131051_6a8574aba567e.png', 'tersedia'),
(5, 'Si Anak Badai', 'Tere Liye', 'ada anak badai', '1787131315_6a8575b3bb137.png', 'tersedia'),
(9, 'Matahari', 'Tere Liye', 'yang gitu', 'default_cover.jpg', 'tersedia'),
(11, 'Seporsi Mie Ayam Sebelum Mati', 'Brian Khrisna', 'mati', 'default_cover.jpg', 'tersedia'),
(15, 'Teruslah Bodoh Jangan Pintar', 'Tere Liye', 'bodoh', 'default_cover.jpg', 'tersedia'),
(16, 'Hujan', 'Tere Liye', 'haha', 'default_cover.jpg', 'tersedia'),
(17, 'Bandung Menjelang Pagi', 'Brian Khrisna', 'apa aajlah', 'default_cover.jpg', 'dipinjam'),
(18, 'Bumi', 'Tere Liye', 'itu', 'default_cover.jpg', 'tersedia'),
(19, 'Bulan', 'Tere Liye', 'heeh', 'default_cover.jpg', 'tersedia'),
(20, 'Selena', 'Tere Liye', 'gitu dah mager gw', 'default_cover.jpg', 'tersedia'),
(21, 'Dompet Ayah Sepatu Ibu', 'JS.Khairen', 'ya gitu lah king, ada zenna, sama asrul ya ing , asik lah bukunhya', 'default_cover.jpg', 'tersedia'),
(22, 'Ily', 'Tere Liye', 'gendut', '1788160391_6a9529870c5a3.JPG', 'tersedia');

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
(3, 11, 'Waktu peminjaman buku \"Bandung Menjelang Pagi\" habis, kembalikan sekarang!', 0, '2026-08-25 03:41:55');

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
(25, 7, 9, '2026-08-23', 3, NULL, '2026-08-23', 'selesai'),
(27, 9, 16, '2026-08-23', 3, NULL, '2026-08-23', 'selesai'),
(29, 7, 4, '2026-08-23', 3, NULL, '2026-08-23', 'selesai'),
(30, 9, 16, '2026-08-23', 3, NULL, '2026-08-23', 'selesai'),
(31, 10, 18, '2026-08-24', 3, NULL, '2026-08-25', 'selesai'),
(32, 6, 17, '2026-08-24', 3, NULL, '2026-08-25', 'selesai'),
(33, 9, 11, '2026-08-24', 3, NULL, '2026-08-25', 'selesai'),
(34, 7, 19, '2026-08-24', 3, NULL, '2026-08-25', 'selesai'),
(36, 11, 17, '2026-08-25', 3, NULL, '2026-08-25', 'selesai'),
(38, 7, 19, '2026-08-25', 3, NULL, '2026-08-25', 'selesai'),
(41, 9, 18, '2026-08-25', 7, '2026-09-01', '2026-08-26', 'selesai'),
(42, 15, 19, '2026-08-26', 2, '2026-08-28', '2026-08-26', 'selesai'),
(43, 15, 17, '2026-08-31', 3, '2026-09-03', NULL, 'berjalan');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
