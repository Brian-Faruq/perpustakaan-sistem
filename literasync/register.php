<?php 
include 'koneksi.php'; // Panggil kabel penghubung tadi

if (isset($_POST['daftar'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $query = "INSERT INTO users (username, password) VALUES ('$user', '$pass')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Berhasil Daftar!');</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<h2>Daftar Akun Literasync</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username baru" required><br><br>
    <input type="password" name="password" placeholder="Password baru" required><br><br>
    <button type="submit" name="daftar">Klik untuk Daftar</button>
</form>