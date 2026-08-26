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
    <title>Login Perpustakaan - Sekolah Impian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                orange: '#F37021', // Oranye Logo
                amber: '#F9A01B',  // Yellow-Orange Logo
                teal: '#2BB69D',   // Tosca Logo
                navy: '#1B365D',   // Navy Logo
                lightBg: '#F8FAFC' // Soft Gray Background
              }
            }
          }
        }
      }
    </script>
</head>
<body class="bg-brand-lightBg text-slate-800 flex items-center justify-center min-h-screen relative overflow-hidden">

    <!-- Glowing Accent Circles Soft -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-brand-orange/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-brand-teal/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-xl w-full max-w-md border border-slate-100 relative z-10">
        
        <!-- Header & Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-black bg-gradient-to-r from-brand-orange via-brand-amber to-brand-teal bg-clip-text text-transparent">
                PERPUSTAKAAN
            </h2>
            <p class="text-xs font-bold text-brand-navy tracking-widest uppercase mt-1">Sekolah Impian</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 p-3 rounded-xl mb-6 text-sm text-center font-medium">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Username / Nomor Kartu (RFID)</label>
                <input type="text" name="user_input" value="<?= htmlspecialchars($remember_user); ?>" required autofocus placeholder="Tap Kartu atau ketik Username" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-teal focus:border-brand-teal focus:bg-white focus:outline-none text-slate-800 placeholder-slate-400 transition font-medium">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-teal focus:border-brand-teal focus:bg-white focus:outline-none text-slate-800 placeholder-slate-400 transition font-medium">
            </div>

            <!-- Fitur Ingat Saya -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-600 hover:text-slate-900 transition font-medium">
                    <input type="checkbox" name="remember" <?= $remember_user ? 'checked' : ''; ?> class="w-4 h-4 rounded border-slate-300 text-brand-orange focus:ring-brand-orange">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" name="login" class="w-full bg-gradient-to-r from-brand-orange to-brand-amber hover:opacity-95 text-white font-bold py-3.5 rounded-xl transition duration-300 shadow-lg shadow-brand-orange/25 active:scale-[0.98]">
                Masuk ke Sistem
            </button>
        </form>
    </div>

</body>
</html>