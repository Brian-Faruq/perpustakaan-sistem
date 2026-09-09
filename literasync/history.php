<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_aktif'])) {
    header("Location: login.php");
    exit();
}

$user_nama = $_SESSION['user_aktif'];
$get_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$user_nama'");
$data_user = mysqli_fetch_assoc($get_user);
$user_id = $data_user['id'];

// Ambil semua daftar buku yang pernah dibaca user ini
$query_history = mysqli_query($conn, "SELECT * FROM books WHERE user_id='$user_id' ORDER BY tgl_selesai DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Membaca - Literasync</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 10px; }
        .buku-item { display: flex; gap: 20px; border-bottom: 1px solid #eee; padding: 15px 0; }
        .cover { width: 100px; height: 140px; object-fit: cover; border-radius: 5px; background: #ddd; }
        .info h3 { margin: 0 0 10px 0; color: #2e7d32; }
        .btn-link { color: #2e7d32; text-decoration: none; font-weight: bold; }
        .date { font-size: 12px; color: #888; }
    </style>
</head>
<body>

<div class="container">
    <h2>📚 Jurnal Membaca Kamu</h2>
    <a href="dashboard.php">← Kembali ke Dashboard</a>
    <hr>

    <?php if(mysqli_num_rows($query_history) == 0): ?>
        <p>Kamu belum membaca buku apa pun. Yuk, mulai baca!</p>
    <?php endif; ?>

    <?php while($row = mysqli_fetch_assoc($query_history)): ?>
        <div class="buku-item">
            <img src="uploads/<?php echo $row['cover_buku']; ?>" class="cover" alt="Cover">
            
            <div class="info">
                <p class="date">Selesai pada: <?php echo date('d M Y', strtotime($row['tgl_selesai'])); ?></p>
                <h3><?php echo $row['judul_buku']; ?></h3>
                <p><i>"<?php echo $row['review_buku']; ?>"</i></p>
                <p style="margin: 5px 0;">
     <p style="margin: 5px 0;">
    <?php 
        // Nilai dibagi 2 dan dibulatkan ke atas (misal 7 jadi 4 bintang)
        $bintang = ceil($row['rating'] / 2); 
        echo str_repeat("⭐", $bintang); 
    ?>
    <span style="color: #666; font-size: 13px;"> Skor: <b><?php echo $row['rating']; ?></b>/10</span>
</p>
    <span style="color: #888; font-size: 13px;"> (<?php echo $row['rating']; ?>/10)</span>
</p>
                <?php if(!empty($row['link_buku'])): ?>
                    <a href="<?php echo $row['link_buku']; ?>" target="_blank" class="btn-link">🔗 Baca Digital Book</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>