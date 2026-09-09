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

// Ambil Statistik Singkat
$count_buku = 0;
$count_siswa = 0;
$q_b = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
if ($q_b) $count_buku = mysqli_fetch_assoc($q_b)['total'] ?? 0;

$q_s = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa");
if ($q_s) $count_siswa = mysqli_fetch_assoc($q_s)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login E-Perpustakaan - Sekolah Impian</title>
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
        body.swal2-shown .swal2-container {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: 100vw !important; height: 100vh !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            margin: 0 !important; padding: 1rem !important; z-index: 99999 !important;
        }
        .swal2-popup { margin: auto !important; box-sizing: border-box !important; }
        .swal2-icon { transform: scale(0.75) !important; margin: 0.5rem auto -0.5rem auto !important; }
        .swal2-title { font-size: 1.1rem !important; padding-top: 0.5rem !important; }
        .swal2-html-container { font-size: 0.825rem !important; margin: 0.5rem 0 0 0 !important; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 antialiased relative overflow-x-hidden">

    <!-- Ambient Glow Background -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-brand-orange/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-brand-teal/20 rounded-full blur-3xl pointer-events-none"></div>

    <!-- MAIN CARD CONTAINER (2 COLUMNS LAYOUT) -->
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 border border-slate-100 z-10 my-auto">

        <!-- KIRI: BRANDING & HERO CONTENT (Hidden on small screens) -->
        <div class="hidden md:flex md:col-span-6 bg-gradient-to-br from-slate-900 via-brand-navy to-slate-900 p-8 flex-col justify-between relative overflow-hidden text-white">
            <!-- Decorative Accent Circle -->
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-brand-orange/10 rounded-full blur-2xl"></div>

            <!-- Top Header Logo -->
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-orange to-brand-amber flex items-center justify-center text-white shadow-lg shadow-brand-orange/30 font-bold text-lg">
                    📚
                </div>
                <div>
                    <h1 class="font-extrabold text-sm tracking-wide text-white">SEKOLAH IMPIAN</h1>
                    <p class="text-[10px] text-slate-400 tracking-wider uppercase">Portal Perpustakaan Digital</p>
                </div>
            </div>

            <!-- Hero Text Section -->
            <div class="my-auto py-8 relative z-10 space-y-4">
                <span class="inline-block px-3 py-1 bg-brand-orange/20 border border-brand-orange/30 text-brand-amber font-semibold text-[10px] rounded-full uppercase tracking-wider">
                    Smart Library System
                </span>
                <h2 class="text-2xl font-black text-white leading-tight">
                    Jelajahi Ribuan Buku & Literasi Tanpa Batas
                </h2>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Akses katalog perpustakaan, pantau peminjaman, dan tap kartu RFID Anda secara langsung untuk transaksi cepat.
                </p>

                <!-- Feature List -->
                <div class="space-y-2 pt-2 text-xs">
                    <div class="flex items-center gap-2 text-slate-200">
                        <span class="w-5 h-5 rounded-full bg-brand-teal/20 text-brand-teal flex items-center justify-center font-bold text-[10px]">✓</span>
                        <span>Scan Kartu RFID Otomatis</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-200">
                        <span class="w-5 h-5 rounded-full bg-brand-teal/20 text-brand-teal flex items-center justify-center font-bold text-[10px]">✓</span>
                        <span>Katalog Digital & Riwayat Peminjaman</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-200">
                        <span class="w-5 h-5 rounded-full bg-brand-teal/20 text-brand-teal flex items-center justify-center font-bold text-[10px]">✓</span>
                        <span>Notifikasi & Peringatan Pengembalian</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Stats Section -->
            <div class="pt-6 border-t border-slate-800 grid grid-cols-2 gap-4 relative z-10">
                <div>
                    <p class="text-xl font-extrabold text-brand-amber"><?= number_format($count_buku); ?></p>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold">Koleksi Buku</p>
                </div>
                <div>
                    <p class="text-xl font-extrabold text-brand-teal"><?= number_format($count_siswa); ?></p>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold">Siswa Terdaftar</p>
                </div>
            </div>
        </div>

        <!-- KANAN: FORM LOGIN -->
        <div class="md:col-span-6 p-6 sm:p-10 flex flex-col justify-between bg-white">
            
            <!-- Header Mobile Only -->
            <div class="text-center mb-6 md:hidden">
                <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-tr from-brand-orange to-brand-amber rounded-2xl flex items-center justify-center text-white shadow-md text-xl">
                    📚
                </div>
                <h2 class="text-xl font-black bg-gradient-to-r from-brand-orange via-brand-amber to-brand-teal bg-clip-text text-transparent">
                    PERPUSTAKAAN
                </h2>
                <p class="text-[10px] font-bold text-slate-500 tracking-widest uppercase">Sekolah Impian</p>
            </div>

            <div class="my-auto">
                <div class="hidden md:block mb-6">
                    <h3 class="text-xl font-black text-slate-800">Selamat Datang 👋</h3>
                    <p class="text-xs text-slate-400 mt-1">Silakan masuk dengan akun Anda atau scan kartu RFID.</p>
                </div>

                <!-- Form Login -->
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Username / Nomor Kartu RFID</label>
                        <div class="relative">
                            <input type="text" name="user_input" value="<?= htmlspecialchars($remember_user); ?>" required autofocus placeholder="Scan Kartu RFID / Username" class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-teal focus:border-brand-teal focus:bg-white focus:outline-none text-slate-800 placeholder-slate-400 transition text-xs font-medium">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" name="password" required placeholder="••••••••" class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-teal focus:border-brand-teal focus:bg-white focus:outline-none text-slate-800 placeholder-slate-400 transition text-xs font-medium">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600 hover:text-slate-900 transition font-medium">
                            <input type="checkbox" name="remember" <?= $remember_user ? 'checked' : ''; ?> class="w-3.5 h-3.5 rounded border-slate-300 text-brand-orange focus:ring-brand-orange">
                            <span>Ingat Saya</span>
                        </label>
                    </div>

                    <button type="submit" name="login" class="w-full bg-gradient-to-r from-brand-orange to-brand-amber hover:opacity-95 text-white font-bold py-3 rounded-xl transition duration-300 shadow-md shadow-brand-orange/25 active:scale-[0.98] text-xs mt-2">
                        Masuk ke Sistem
                    </button>
                </form>
            </div>

            <!-- Footer Card -->
            <div class="pt-6 mt-6 border-t border-slate-100 text-center text-[10px] text-slate-400">
                &copy; <?= date('Y'); ?> E-Perpustakaan Sekolah Impian
            </div>
        </div>

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