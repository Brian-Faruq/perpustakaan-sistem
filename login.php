<?php
session_start();
include 'koneksi.php';

$error = '';

// Cek apakah ada cookie 'remember_user'
$remember_user = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';

if (isset($_POST['login'])) {
    $user_input = mysqli_real_escape_string($koneksi, trim($_POST['user_input']));
    $password   = $_POST['password'];
    $remember   = isset($_POST['remember']); // Cek apakah checkbox dicentang

    // 1. Cek Akun Admin (via Username)
    $query_admin = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$user_input'");
    if (mysqli_num_rows($query_admin) > 0) {
        $data_admin = mysqli_fetch_assoc($query_admin);
        if (password_verify($password, $data_admin['password'])) {
            $_SESSION['user_id'] = $data_admin['id'];
            $_SESSION['nama']    = $data_admin['nama'];
            $_SESSION['role']    = 'admin';

            // Kelola Cookie Remember Me
            if ($remember) {
                setcookie('remember_user', $user_input, time() + (7 * 24 * 60 * 60), "/"); // Simpan 7 hari
            } else {
                setcookie('remember_user', '', time() - 3600, "/"); // Hapus cookie jika tidak dicentang
            }

            header("Location: admin_dashboard.php");
            exit;
        }
    }

    // 2. Cek Akun Siswa (Bisa via Nama ATAU Nomor Kartu)
    $query_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nomor_kartu='$user_input' OR nama='$user_input'");
    if (mysqli_num_rows($query_siswa) > 0) {
        $data_siswa = mysqli_fetch_assoc($query_siswa);
        if (password_verify($password, $data_siswa['password'])) {
            $_SESSION['user_id'] = $data_siswa['id'];
            $_SESSION['nama']    = $data_siswa['nama'];
            $_SESSION['role']    = 'siswa';

            // Kelola Cookie Remember Me
            if ($remember) {
                setcookie('remember_user', $user_input, time() + (7 * 24 * 60 * 60), "/"); // Simpan 7 hari
            } else {
                setcookie('remember_user', '', time() - 3600, "/"); // Hapus cookie jika tidak dicentang
            }

            header("Location: siswa_dashboard.php");
            exit;
        }
    }

    $error = "Username / Nomor Kartu atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Perpustakaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-slate-800 p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-700">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-extrabold text-blue-400">Sistem Perpustakaan</h2>
            <p class="text-xs text-slate-400 mt-1">Silakan login sebagai Admin atau Siswa</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded-lg mb-4 text-sm text-center">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Username / Nomor Kartu (RFID)</label>
                <input type="text" name="user_input" value="<?= htmlspecialchars($remember_user); ?>" required autofocus placeholder="Tap Kartu atau ketik Username" class="w-full p-3 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-200 placeholder-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full p-3 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-200 placeholder-slate-500">
            </div>

            <!-- Fitur Ingat Saya -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-400 hover:text-slate-200 transition">
                    <input type="checkbox" name="remember" <?= $remember_user ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-800">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg transition duration-200">
                Masuk ke Sistem
            </button>
        </form>
    </div>

</body>
</html>