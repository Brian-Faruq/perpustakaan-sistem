<?php
include 'koneksi.php';

$username = 'admin';
$password_plain = 'admin123';
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Hapus akun admin lama
mysqli_query($koneksi, "DELETE FROM admin WHERE username='$username'");

// Insert akun admin baru dengan hash pas
$sql = "INSERT INTO admin (username, password, nama) VALUES ('$username', '$password_hash', 'Petugas Perpustakaan')";

if (mysqli_query($koneksi, $sql)) {
    echo "<h2 style='color:green;'>Berhasil Reset Admin!</h2>";
    echo "<p>Username: <b>admin</b></p>";
    echo "<p>Password: <b>admin123</b></p>";
    echo "<a href='login.php'>Klik di sini untuk Login</a>";
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>