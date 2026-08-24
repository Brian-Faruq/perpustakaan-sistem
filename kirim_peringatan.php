<?php
session_start();
include 'koneksi.php';

if (isset($_GET['peminjaman_id'])) {
    $peminjaman_id = mysqli_real_escape_string($koneksi, $_GET['peminjaman_id']);

    // Ambil detail siswa dan buku dari tabel peminjaman
    $query = mysqli_query($koneksi, "
        SELECT p.siswa_id, b.judul 
        FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.id = '$peminjaman_id'
    ");

    if ($data = mysqli_fetch_assoc($query)) {
        $siswa_id = $data['siswa_id'];
        $judul_buku = $data['judul'];
        $pesan = "Waktu peminjaman buku '" . $judul_buku . "' habis, kembalikan sekarang!";

        // Simpan notifikasi ke database
        mysqli_query($koneksi, "INSERT INTO notifikasi (siswa_id, pesan) VALUES ('$siswa_id', '$pesan')");
    }
}

// Redirect kembali ke halaman peminjaman admin
header("Location: admin_peminjaman.php");
exit;