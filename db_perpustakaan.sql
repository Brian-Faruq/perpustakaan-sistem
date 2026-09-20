-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Sep 2026 pada 08.50
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
(46, 'Sisi Tergelap Surga', 'Brian Khrisna', 'Jakarta kerap menjadi pelabuhan bagi mereka yang datang membawa sekoper harapan. Mereka yang siap bertaruh dengan nasibnya sendiri-sendiri. Namun, kota ini selalu mampu melumat habis harapan dan menukarnya dengan keputusasaan.\n\nPemulung, pengamen, pramuria yang menjajakan tubuh agar anaknya bisa makan, pemimpin-pemimpin kecil yang culas, lelaki tua di balik kostum badut ayam, pencuri motor yang ingin membeli obat untuk ibunya, remaja yang melumuri tubuh dengan cat perak, hingga mereka yang bergelut di terminal setelah terpaksa merelakan impiannya habis digerus kejinya ibu kota.\n\nDi Jakarta, semua orang dipaksa bergelut dan bertempur demi bisa hidup dari hari ke hari. Dan di kampung inilah semua itu dimulai. Sebuah cerita tentang kehidupan orang-orang yang hidup di sisi tergelap surga kota bernama Jakarta...', 'sisitergelapsurga.jpg', 'tersedia'),
(47, 'Pergi', 'Tere Liye', 'Sebuah kisah tentang menemukan tujuan, ke mana hendak pergi, melalui kenangan demi kenangan masa lalu, pertarungan demi pertarungan, untuk memeluk erat-erat kesedihan dan rasa sakit.\n\nSetelah mengetahui definisi \"pulang\" dan berdamai dengan masa lalunya, Bujang kini memimpin Keluarga Tong sebagai salah satu penguasa dunia shadow economy. Namun, ketenangan tak pernah bertahan lama. Ketika sebuah proyek teknologi berharga dicuri oleh jaringan El Pacho di Meksiko, Bujang terseret ke dalam konflik yang membawanya berhadapan dengan sosok misterius yang mengetahui nama aslinya, Agam.\n\nDi tengah persaingan antar-keluarga penguasa dunia bawah tanah dan ancaman besar dari Master Dragon, Bujang harus menelusuri kembali jejak masa lalu ayahnya, Samad. Dalam perjalanan yang penuh desingan peluru dan pertarungan di berbagai belahan dunia, Bujang tidak hanya berjuang mempertahankan Keluarga Tong, tetapi juga dipaksa menjawab pertanyaan terpenting dalam hidupnya: Ke mana ia akan pergi setelah tahu jalan untuk pulang?', 'pergi.jpg', 'tersedia'),
(48, 'Bumi', 'Tere Liye', 'Namaku Raib, usiaku 15 tahun, kelas sepuluh. Aku anak remaja seperti kalian, dua orang tuaku lembut dan menyenangkan, aku punya dua ekor kucing yang lucu, teman-temanku baik, dan guru-guru di sekolahku hebat.\n\nTidak ada yang aneh dari diriku, sama persis seperti remaja SMA pada umumnya.\n\nKecuali satu hal. Mengenai diriku yang bisa menghilang.\n\nSssttt... Jangan beri tahu siapa-siapa, ya.', 'bumi.jpg', 'tersedia'),
(49, 'Bandung Menjelang Pagi', 'Brian Khrisna', 'Menjelang pagi, Bandung berubah menjadi kota yang tak lagi sama. Malam terasa sangat panjang dan lebih mencekam dari kelam. Para bandit, pemadat, tukang judi, bocah geng motor, begundal grafiti, semuanya berkeliaran bak tikus-tikus ketika air got meluap.   \n\nDipha adalah pemuda serabutan yang sudah mengenal betul sisi kelam kota ini. Apa pun ia lakukan untuk bertahan hidup. Kemampuannya untuk mengerjakan apa saja membawanya bertemu dengan Vinda, seorang gadis misterius yang ngotot minta dicarikan tempat tinggal dengan segala syarat yang tak masuk akal.\n\nJalan Asia Afrika, Braga, Dago, Kalipah Apo, Astana Anyar, Banceuy, Jalan ABC, dan seluruh jalan-jalan tikus di Kota Bandung menjadi saksi tumbuhnya perasaan di antara keduanya. Namun, sayangnya mereka berdua kerap lupa, bahwa sejatinya, oleh-oleh paling khas dari Kota Bandung adalah: patah hati.', 'bandungmenjelangpagi.jpg', 'tersedia'),
(50, 'Matahari', 'Tere Liye', 'Namaku Ali, 15 tahun, kelas X. Jika saja orangtuaku mengizinkan, seharusnya aku sudah duduk di tingkat akhir fakultas fisika program doktor di universitas terbaik. Aku tidak menyukai sekolah, atau lebih tepatnya, aku bosan dengan pelajaran yang itu-itu saja.\n\nTapi sejak Raib dan Seli membawaku ke petualangan yang luar biasa, aku tahu, ada banyak hal misterius di dunia ini yang belum terpecahkan. Dan aku adalah tipe orang yang tidak akan berhenti sebelum menemukan jawabannya.\n\nKali ini, aku yang membawa mereka menjelajahi dunia paralel. Menuju Klan Matahari.\n\nSssttt... Jangan beri tahu siapa-siapa, ya.', 'matahari.jpg', 'tersedia');

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
(2, 8, 'Waktu peminjaman buku \"Hujan\" habis, kembalikan sekarang!', 0, '2026-08-24 07:42:53');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT untuk tabel `review_buku`
--
ALTER TABLE `review_buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
