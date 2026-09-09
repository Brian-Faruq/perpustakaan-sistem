<?php
session_start(); // Ini untuk "mengingat" siapa yang login
include 'koneksi.php';

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Cek ke database apakah user dan pass ini ada
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    $hitung = mysqli_num_rows($cek);

    if ($hitung > 0) {
        $_SESSION['user_aktif'] = $user; // Simpan nama user di memori server
        header("Location: dashboard.php"); // Lempar ke halaman dashboard
    } else {
        echo "<script>alert('Waduh, Username atau Password salah!');</script>";
    }
}
?>

<h2>Masuk ke Literasync</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username kamu" required><br><br>
    <input type="password" name="password" placeholder="Password kamu" required><br><br>
    <button type="submit" name="login">Masuk Sekarang</button>
</form>
<p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>