<?php
session_start();
include 'koneksi.php';

$login_status = '';
$login_message = '';
$redirect_url = '';

// Cek apakah ada cookie 'remember_user'
$remember_user = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';

if (isset($_POST['login'])) {
    $user_input = mysqli_real_escape_string($koneksi, trim($_POST['user_input']));
    $password   = $_POST['password'];
    $remember   = isset($_POST['remember']); 

    // 1. Cek Akun Admin
    $query_admin = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$user_input'");
    if (mysqli_num_rows($query_admin) > 0) {
        $data_admin = mysqli_fetch_assoc($query_admin);
        if (password_verify($password, $data_admin['password'])) {
            $_SESSION['user_id'] = $data_admin['id'];
            $_SESSION['nama']    = $data_admin['nama'];
            $_SESSION['role']    = 'admin';

            if ($remember) {
                setcookie('remember_user', $user_input, time() + (7 * 24 * 60 * 60), "/");
            } else {
                setcookie('remember_user', '', time() - 3600, "/");
            }

            $login_status  = 'success';
            $login_message = 'Selamat datang kembali, ' . htmlspecialchars($data_admin['nama']) . '!';
            $redirect_url  = 'admin_dashboard.php';
        }
    }

    // 2. Cek Akun Siswa
    if (empty($login_status)) {
        $query_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nomor_kartu='$user_input' OR nama='$user_input'");
        if (mysqli_num_rows($query_siswa) > 0) {
            $data_siswa = mysqli_fetch_assoc($query_siswa);
            if (password_verify($password, $data_siswa['password'])) {
                $_SESSION['user_id'] = $data_siswa['id'];
                $_SESSION['nama']    = $data_siswa['nama'];
                $_SESSION['role']    = 'siswa';

                if ($remember) {
                    setcookie('remember_user', $user_input, time() + (7 * 24 * 60 * 60), "/");
                } else {
                    setcookie('remember_user', '', time() - 3600, "/");
                }

                $login_status  = 'success';
                $login_message = 'Selamat datang, ' . htmlspecialchars($data_siswa['nama']) . '!';
                $redirect_url  = 'siswa_dashboard.php';
            }
        }
    }

    // 3. Login Gagal
    if (empty($login_status)) {
        $login_status  = 'error';
        $login_message = 'Username / Nomor Kartu atau Password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Perpustakaan - Sekolah Impian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                orange: '#F37021',
                amber: '#F9A01B',
                teal: '#2BB69D',
                navy: '#1B365D',
                lightBg: '#F8FAFC'
              }
            }
          }
        }
      }
    </script>
    <style>
        /* SweetAlert Presisi Center Mod */
        body.swal2-shown .swal2-container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 1rem !important;
            z-index: 99999 !important;
        }

        .swal2-popup {
            margin: auto !important;
            box-sizing: border-box !important;
        }

        .swal2-icon {
            transform: scale(0.75) !important;
            margin: 0.5rem auto -0.5rem auto !important;
        }
        .swal2-title {
            font-size: 1.1rem !important;
            padding-top: 0.5rem !important;
        }
        .swal2-html-container {
            font-size: 0.825rem !important;
            margin: 0.5rem 0 0 0 !important;
        }
    </style>
</head>
<!-- h-screen & overflow-hidden mengunci layar agar TIDAK BISA DI-SCROLL sama sekali -->
<body class="bg-brand-lightBg text-slate-800 antialiased h-screen overflow-hidden relative flex flex-col justify-between">

    <!-- Background Accents -->
    <div class="absolute -top-20 -left-20 w-80 h-80 bg-brand-orange/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-brand-teal/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- MAIN CONTAINER (Centered Vertical & Horizontal) -->
    <div class="flex-grow flex items-center justify-center p-4 z-10 my-auto">
        <div class="bg-white p-5 sm:p-7 rounded-3xl shadow-xl shadow-slate-200/60 w-full max-w-sm border border-slate-100">
            
            <!-- Header & Branding -->
            <div class="text-center mb-5">
                <div class="w-11 h-11 mx-auto mb-2 bg-gradient-to-tr from-brand-orange to-brand-amber rounded-2xl flex items-center justify-center shadow-md shadow-brand-orange/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h2 class="text-xl sm:text-2xl font-black bg-gradient-to-r from-brand-orange via-brand-amber to-brand-teal bg-clip-text text-transparent tracking-tight">
                    PERPUSTAKAAN
                </h2>
                <p class="text-[9px] font-bold text-brand-navy tracking-widest uppercase mt-0.5">Sekolah Impian</p>
                <p class="text-[11px] text-slate-400 mt-1.5 leading-tight">Silakan masuk menggunakan akun atau scan kartu RFID Anda.</p>
            </div>

            <!-- Form -->
            <form action="" method="POST" class="space-y-3.5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Username / Nomor Kartu</label>
                    <div class="relative">
                        <input type="text" name="user_input" value="<?= htmlspecialchars($remember_user); ?>" required autofocus placeholder="Tap Kartu / Username" class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-teal focus:border-brand-teal focus:bg-white focus:outline-none text-slate-800 placeholder-slate-400 transition text-xs font-medium">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-teal focus:border-brand-teal focus:bg-white focus:outline-none text-slate-800 placeholder-slate-400 transition text-xs font-medium">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600 hover:text-slate-900 transition font-medium">
                        <input type="checkbox" name="remember" <?= $remember_user ? 'checked' : ''; ?> class="w-3.5 h-3.5 rounded border-slate-300 text-brand-orange focus:ring-brand-orange">
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" name="login" class="w-full bg-gradient-to-r from-brand-orange to-brand-amber hover:opacity-95 text-white font-bold py-2.5 rounded-xl transition duration-300 shadow-md shadow-brand-orange/25 active:scale-[0.98] text-xs mt-1">
                    Masuk ke Sistem
                </button>
            </form>
        </div>
    </div>

    <!-- Footer Mini Decorative -->
    <div class="py-3 text-center text-[10px] text-slate-400 z-10 shrink-0">
        &copy; <?= date('Y'); ?> E-Perpustakaan Sekolah Impian
    </div>

    <!-- Script SweetAlert Absolute Center -->
    <script>
    <?php if ($login_status === 'success'): ?>
        Swal.fire({
            title: 'Login Berhasil!',
            text: '<?= $login_message; ?>',
            icon: 'success',
            width: '280px',
            padding: '1.25rem',
            timer: 1500,
            showConfirmButton: false,
            heightAuto: false,
            target: 'body'
        }).then(function() {
            window.location.href = '<?= $redirect_url; ?>';
        });
    <?php elseif ($login_status === 'error'): ?>
        Swal.fire({
            title: 'Login Gagal!',
            text: '<?= $login_message; ?>',
            icon: 'error',
            width: '280px',
            padding: '1.25rem',
            confirmButtonText: 'Coba Lagi',
            confirmButtonColor: '#F37021',
            heightAuto: false,
            target: 'body'
        });
    <?php endif; ?>
    </script>

</body>
</html>