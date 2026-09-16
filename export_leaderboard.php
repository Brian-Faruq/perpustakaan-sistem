<?php
session_start();
include 'koneksi.php';

// Proteksi akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Header HTTP agar browser membaca response sebagai file Excel (CSV)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Leaderboard_Siswa_' . date('Y-m-d') . '.csv');

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis Header Kolom di Excel
fputcsv($output, ['Rank', 'Nama Siswa', 'Kelas', 'Total Peminjaman Buku', 'Total Ulasan', 'Total Poin']);

// Query data leaderboard
$q_leaderboard = mysqli_query($koneksi, "
    SELECT 
        s.nama, 
        s.kelas, 
        COUNT(DISTINCT p.id) AS total_pinjam,
        COUNT(DISTINCT r.id) AS total_review,
        ((COUNT(DISTINCT p.id) * 10) + (COUNT(DISTINCT r.id) * 20)) AS total_poin
    FROM siswa s
    LEFT JOIN peminjaman p ON s.id = p.siswa_id
    LEFT JOIN review_buku r ON s.id = r.siswa_id
    GROUP BY s.id, s.nomor_kartu, s.nama, s.kelas
    ORDER BY total_poin DESC, s.nama ASC
");

$rank = 1;
while ($row = mysqli_fetch_assoc($q_leaderboard)) {
    fputcsv($output, [
        $rank++,
        $row['nama'],
        $row['kelas'],
        $row['total_pinjam'],
        $row['total_review'],
        $row['total_poin']
    ]);
}

fclose($output);
exit;