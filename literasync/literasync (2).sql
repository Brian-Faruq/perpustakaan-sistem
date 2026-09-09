-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Sep 2026 pada 06.56
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `literasync`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `judul_buku` varchar(255) DEFAULT NULL,
  `penulis_buku` varchar(255) DEFAULT NULL,
  `review_buku` text DEFAULT NULL,
  `tgl_selesai` timestamp NOT NULL DEFAULT current_timestamp(),
  `cover_buku` varchar(255) DEFAULT NULL,
  `link_buku` varchar(255) DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `tokoh_utama` text DEFAULT NULL,
  `halaman_suka` text DEFAULT NULL,
  `adegan_ingat` text DEFAULT NULL,
  `perasaan_setelah_baca` text DEFAULT NULL,
  `bab_konflik` text DEFAULT NULL,
  `tokoh_antagonis` text DEFAULT NULL,
  `tokoh_protagonis` text DEFAULT NULL,
  `pelajaran_buku` text DEFAULT NULL,
  `lima_tokoh` text DEFAULT NULL,
  `tgl_input` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `user_id`, `judul_buku`, `penulis_buku`, `review_buku`, `tgl_selesai`, `cover_buku`, `link_buku`, `rating`, `tokoh_utama`, `halaman_suka`, `adegan_ingat`, `perasaan_setelah_baca`, `bab_konflik`, `tokoh_antagonis`, `tokoh_protagonis`, `pelajaran_buku`, `lima_tokoh`, `tgl_input`) VALUES
(1, 1, 'Laut bercerita', NULL, 'bagus bangetttt', '2026-04-19 09:56:30', NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(2, 1, 'hujan', NULL, 'lucu banget buku nyaaaaaaaaaaa,suka parah sumpahhh wajib baca sih ini mah cinta banget asliiii', '2026-04-19 09:57:52', NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(3, 1, 'kemarin', NULL, 'keren bangetttt\r\n', '2026-04-19 10:17:34', '1776593854_1.png', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(4, 5, 'Laut bercerita', NULL, 'keren banget gilak', '2026-04-20 04:07:31', '1776658051_5.png', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(5, 5, 'teka tki rumah aneh', NULL, 'takut banget hiiiiiii', '2026-04-20 04:08:28', '1776658108_5.png', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(6, 1, 'ITMI technology', NULL, 'kren', '2026-04-21 15:20:16', '1776784816_1.png', '', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(7, 1, 'laut bercerita', NULL, 'wow', '2026-04-22 07:49:43', '1776844183_Screenshot (1).png', '', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(8, 8, 'White Wedding', 'Ziggy Zezsyazeoviennazabrizkie', 'LUCU BANGET IMOET BANGET?????', '2026-04-22 14:53:45', '1776869625_Screenshot 2026-04-22 215242.png', '', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(9, 11, 'uhuyyy', 'gue', 'bagus banget euyy intinya mahh', '2026-04-24 09:27:30', '1777022850_logo literasync.png', '', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(10, 12, 'laut bercerita', 'leila s chudori', 'tentang politik, masih proses membaca. tapi menarik dan bagus.', '2026-04-25 07:53:24', '1777103604_Screenshot 2026-04-25 145138.png', '', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(11, 8, 'Semua Ikan Di Langit', 'Ziggy Zezsyazeoviennazabrizkie', 'KEREN IMUT LUCU MENGGEMASKAN MENGENASKAN MENYENANGKAN ', '2026-04-25 07:58:29', '1777103909_Screenshot 2026-04-25 145727.png', '', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(12, 8, 'Tiga Dalam Kayu', 'Ziggy Zezsyazeoviennazabrizkie', 'Mengerikan', '2026-04-26 13:58:11', '1777211891_Screenshot 2026-04-26 205723.png', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17'),
(13, 8, 'Kita Pergi Hari Ini', 'Ziggy Zezsyazeoviennazabrizkie', 'GATAU TERSERAH', '2026-04-27 03:57:54', '1777262274_Screenshot 2026-04-27 103425.png', '', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 04:30:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karya`
--

CREATE TABLE `karya` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `judul_karya` varchar(255) DEFAULT NULL,
  `penulis` varchar(255) DEFAULT NULL,
  `tipe_konten` enum('tulisan','file') NOT NULL,
  `isi_tulisan` text DEFAULT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `tgl_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `karya`
--

INSERT INTO `karya` (`id`, `user_id`, `judul_karya`, `penulis`, `tipe_konten`, `isi_tulisan`, `nama_file`, `tgl_upload`) VALUES
(1, 1, 'Kala', 'What_Iff', 'tulisan', '-KALA-\r\n\r\nBanyak barang terjaja di pasar loak Kebayoran Lama. \r\nJam dinding, jam tangan, buku, sendal, sepatu, piring,\r\ndan Saya.\r\n\r\nTidak ada yang pandang tangan kepada siapa mereka tergapai,\r\nkecuali Saya.\r\n\r\nSaya mencari dan terus mencari diantara langkah kaki, \r\nSiapa, dan kemanakah kala yang dulu menuai sirup kasih telah pergi.\r\nBelum ada titik setelah koma, dan itu yang membuat Saya ...\r\n\r\nTidak juga beranjak dari kumpulan barang loak yang tersusun apik\r\nSaya mencari dan menunggu, berjalan berputar tanpa henti,\r\nhingga Saya mabuk oleh ceri dekat warung pangkal serong kaki lima\r\n\r\nKala yang sedia, sudikah kamu berdiri didepan gerai \r\ndan menjadi tangan yang terulur untuk menggapai Saya?', '', '2026-04-24 09:23:52'),
(2, 11, 'hdndsgsyjnxbdg', 'zzh', 'tulisan', 'intinya gitu', '', '2026-04-24 09:27:54'),
(3, 8, 'KALA', 'What.Iff', 'tulisan', '-KALA-\r\n\r\nBanyak barang terjaja di pasar loak Kebayoran Lama. \r\nJam dinding, jam tangan, buku, sendal, sepatu, piring,\r\ndan Saya.\r\n\r\nTidak ada yang pandang tangan kepada siapa mereka tergapai,\r\nkecuali Saya.\r\n\r\nSaya mencari dan terus mencari diantara langkah kaki, \r\nSiapa, dan kemanakah kala yang dulu menuai sirup kasih telah pergi.\r\nBelum ada titik setelah koma, dan itu yang membuat Saya ...\r\n\r\nTidak juga beranjak dari kumpulan barang loak yang tersusun apik\r\nSaya mencari dan menunggu, berjalan berputar tanpa henti,\r\nhingga Saya mabuk oleh ceri dekat warung pangkal serong kaki lima\r\n\r\nKala yang sedia, sudikah kamu berdiri didepan gerai \r\ndan menjadi tangan yang terulur untuk menggapai Saya?', '', '2026-04-25 07:55:03'),
(4, 15, 'pulang', 'Rani', 'tulisan', 'pulang aja yuk ke rumah', '', '2026-04-28 03:45:17'),
(5, 8, 'pulang', 'aku', 'tulisan', 'yuk pulang', '', '2026-05-02 07:10:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_pinjam` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `judul_buku` varchar(255) NOT NULL,
  `tgl_pinjam` datetime DEFAULT current_timestamp(),
  `status` enum('Dipinjam','Dikembalikan') DEFAULT 'Dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id_pinjam`, `user_id`, `judul_buku`, `tgl_pinjam`, `status`) VALUES
(1, 8, 'Judul Buku Teman', '2026-04-29 11:34:12', 'Dikembalikan'),
(2, 8, 'Judul Buku Teman', '2026-04-29 11:34:31', 'Dikembalikan'),
(3, 8, 'Judul Buku Teman', '2026-04-29 11:40:22', 'Dikembalikan'),
(4, 8, 'Judul Buku Teman', '2026-04-29 15:05:22', 'Dikembalikan'),
(5, 10, 'Judul Buku Teman', '2026-04-29 16:07:18', 'Dipinjam'),
(6, 8, 'Judul Buku Teman', '2026-04-30 13:13:56', 'Dikembalikan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pinjam`
--

CREATE TABLE `riwayat_pinjam` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `judul_buku` varchar(255) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `status` varchar(50) DEFAULT 'Dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_pinjam`
--

INSERT INTO `riwayat_pinjam` (`id`, `user_id`, `buku_id`, `judul_buku`, `tgl_pinjam`, `status`) VALUES
(1, 8, 8, 'White Wedding', '2026-04-30', 'Dikembalikan'),
(2, 8, 8, 'White Wedding', '2026-04-30', 'Dikembalikan'),
(3, 8, 8, 'White Wedding', '2026-04-30', 'Dikembalikan'),
(4, 8, 1, 'Laut bercerita', '2026-05-02', 'Dikembalikan'),
(5, 8, 1, 'Laut bercerita', '2026-05-02', 'Dikembalikan'),
(6, 8, 8, 'White Wedding', '2026-05-09', 'Dikembalikan'),
(7, 8, 8, 'White Wedding', '2026-05-10', 'Dikembalikan'),
(8, 8, 8, 'White Wedding', '2026-05-17', 'Dikembalikan'),
(9, 27, 8, 'White Wedding', '2026-09-02', 'Dipinjam');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'member',
  `total_poin` int(11) DEFAULT 0,
  `bio` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `foto_profile` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `total_poin`, `bio`, `foto`, `foto_profile`) VALUES
(1, 'hana', '1234', 'member', 40, NULL, NULL, NULL),
(2, 'hana', '1234', 'member', 0, NULL, NULL, NULL),
(3, 'hana', '1234', 'member', 0, NULL, NULL, NULL),
(4, 'Seema', 'qwerty', 'member', 0, NULL, NULL, NULL),
(5, 'syayyidqs', 'qwerty', 'member', 0, NULL, NULL, NULL),
(6, 'syayyidqs', 'qwerty', 'member', 0, NULL, NULL, NULL),
(7, 'rani!', 'rawr', 'member', 0, NULL, NULL, NULL),
(8, 'Iffah', 'qwerty', 'member', 110, 'Bagai jatuh tertimpa tangga, lantainya yang kesakitan', 'user_1777537346.jpeg', '1777103769_8.jpg'),
(9, 'Ifffah', 'POIUYT', 'member', 0, NULL, NULL, NULL),
(10, 'iffah', 'qwerty', 'member', 0, NULL, NULL, NULL),
(11, 'zizahhhh', 'azzhnrli698', 'member', 40, 'nyenyenye', NULL, ''),
(12, 'Rani AP', '123435', 'member', 15, NULL, NULL, NULL),
(13, 'putri aliyya', '25012010', 'member', 0, NULL, NULL, NULL),
(14, 'Iffahh', 'qwerty', 'member', 0, NULL, NULL, NULL),
(15, 'irna', '123456', 'member', 25, 'baca buku yuk', NULL, ''),
(16, 'irna', '12345', 'member', 0, NULL, NULL, NULL),
(17, 'halo', '09876', 'member', 0, NULL, NULL, NULL),
(18, 'Admin Literasync', 'literasync', 'admin', 0, NULL, NULL, NULL),
(19, 'aliyya', '25012010', 'member', 0, '', 'user_1777695640.png', NULL),
(20, 'keysha', '190912', 'member', 0, '', 'user_1777695927.jpeg', NULL),
(21, 'salma', 'apasi', 'member', 0, '', 'user_1777696145.jpeg', NULL),
(22, 'rayanun', '250411', 'member', 0, '', 'user_1777696337.jpeg', NULL),
(23, 'Ust pur', 'SI123', 'member', 0, NULL, NULL, NULL),
(24, 'keyyy', '12345', 'member', 0, NULL, NULL, NULL),
(25, 'keong', 'asdf', 'member', 0, NULL, NULL, NULL),
(26, 'lianaa', '130409', 'member', 0, 'literasi mengubah dunia', 'user_1778318339.jpg', NULL),
(27, 'hanafa', 'qazxswedc', 'member', 0, 'menulis', '', NULL),
(28, 'ADMINN', 'qazxswedc', 'member', 0, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `karya`
--
ALTER TABLE `karya`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_pinjam`);

--
-- Indeks untuk tabel `riwayat_pinjam`
--
ALTER TABLE `riwayat_pinjam`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `karya`
--
ALTER TABLE `karya`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_pinjam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pinjam`
--
ALTER TABLE `riwayat_pinjam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `karya`
--
ALTER TABLE `karya`
  ADD CONSTRAINT `karya_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
