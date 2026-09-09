<?php
session_start();
include 'koneksi.php';

$user_nama = $_SESSION['username'] ?? $_SESSION['user_aktif'] ?? 'Iffah';

if (isset($_POST['simpan'])) {
    $user_nama = $_SESSION['user_aktif'];
    $judul = $_POST['judul'];
    $review = $_POST['review'];
    $link = $_POST['link_buku'];
    $rating = $_POST['rating']; // <-- Menangkap data rating

    // Ambil ID User
    $get_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$user_nama'");
    $data_user = mysqli_fetch_assoc($get_user);
    $user_id = $data_user['id'];

    // Proses Upload Foto
    $nama_foto = $_FILES['cover']['name'];
    $tmp_name = $_FILES['cover']['tmp_name'];
    $ekstensi = pathinfo($nama_foto, PATHINFO_EXTENSION);
    $nama_foto_baru = time() . "_" . $user_id . "." . $ekstensi;
    $folder_tujuan = "uploads/" . $nama_foto_baru;

    if (move_uploaded_file($tmp_name, $folder_tujuan)) {
        // Query INSERT yang sudah mendukung rating
        $query = "INSERT INTO books (user_id, judul_buku, review_buku, cover_buku, link_buku, rating) 
                  VALUES ('$user_id', '$judul', '$review', '$nama_foto_baru', '$link', '$rating')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Buku & Rating Berhasil Disimpan!'); window.location='dashboard.php';</script>";
        }
    } else {
        echo "Gagal upload foto! Pastikan folder 'uploads' sudah ada.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lapor Buku - Literasync</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .form-container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #2e7d32; color: white; padding: 10px; border: none; border-radius: 5px; width: 100%; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Lapor Buku Baru 📖</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Judul Buku:</label>
        <input type="text" name="judul" required>

        <label>Rating Buku:</label>
<div class="star-rating">
    <input type="radio" name="rating" value="10" id="rate-10"><label for="rate-10">★</label>
    <input type="radio" name="rating" value="8" id="rate-8"><label for="rate-8">★</label>
    <input type="radio" name="rating" value="6" id="rate-6"><label for="rate-6">★</label>
    <input type="radio" name="rating" value="4" id="rate-4"><label for="rate-4">★</label>
    <input type="radio" name="rating" value="2" id="rate-2"><label for="rate-2">★</label>
</div>

<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse; /* Biar logika CSS-nya gampang */
        justify-content: flex-end;
    }
    .star-rating input { display: none; } /* Sembunyikan radio button asli */
    .star-rating label {
        font-size: 40px;
        color: #ccc;
        cursor: pointer;
        transition: 0.2s;
    }
    /* Efek saat bintang dihover atau diklik */
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #ffca08; /* Warna kuning emas ala Google */
    }
</style>

        <label>Upload Cover Buku:</label>
        <input type="file" name="cover" accept="image/*" required>

        <label>Link Digital Book (Opsional):</label>
        <input type="url" name="link_buku" placeholder="https://...">

        <label>Review Singkat:</label>
        <textarea name="review" required></textarea>

        <button type="submit" name="simpan" class="btn">Simpan Progres</button>
    </form>
    <br>
    <center><a href="dashboard.php" style="color: #666; text-decoration: none;">Kembali ke Dashboard</a></center>
</div>

</body>
</html>