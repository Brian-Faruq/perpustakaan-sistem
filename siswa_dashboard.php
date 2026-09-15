<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

// Aksi: Simpan Review / Ulasan Buku oleh Siswa
if (isset($_POST['simpan_review'])) {
    $siswa_id_input = intval($_POST['siswa_id']);
    $buku_id_input  = intval($_POST['buku_id']);
    $rating_input   = intval($_POST['rating']);
    $ulasan_input   = mysqli_real_escape_string($koneksi, trim($_POST['ulasan']));

    // Verifikasi agar siswa hanya mengulas buku miliknya yang sudah selesai dipinjam
    $q_cek_pinjam = mysqli_query($koneksi, "
        SELECT id FROM peminjaman 
        WHERE siswa_id = '$siswa_id_input' AND buku_id = '$buku_id_input' AND status_transaksi = 'selesai'
    ");

    if (mysqli_num_rows($q_cek_pinjam) > 0) {
        // Cek apakah sudah pernah diulas
        $q_cek_review = mysqli_query($koneksi, "
            SELECT id FROM review_buku 
            WHERE siswa_id = '$siswa_id_input' AND buku_id = '$buku_id_input'
        ");

        if (mysqli_num_rows($q_cek_review) == 0) {
            $sql_ins_review = "INSERT INTO review_buku (siswa_id, buku_id, rating, ulasan) 
                               VALUES ('$siswa_id_input', '$buku_id_input', '$rating_input', '$ulasan_input')";
            if (mysqli_query($koneksi, $sql_ins_review)) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire('Berhasil!', 'Ulasan berhasil disimpan. Kamu mendapatkan +20 Poin!', 'success')
                        .then(() => { window.location.href='siswa_dashboard.php'; });
                    });
                </script>";
            }
        }
    }
}

// Query Leaderboard (Total Pinjam * 10 + Total Review * 20)
$q_leaderboard = mysqli_query($koneksi, "
    SELECT 
        s.id,
        s.nama,
        s.kelas,
        COUNT(DISTINCT p.id) AS total_pinjam,
        COUNT(DISTINCT r.id) AS total_review,
        ((COUNT(DISTINCT p.id) * 10) + (COUNT(DISTINCT r.id) * 20)) AS total_poin
    FROM siswa s
    LEFT JOIN peminjaman p ON s.id = p.siswa_id
    LEFT JOIN review_buku r ON s.id = r.siswa_id
    GROUP BY s.id, s.nama, s.kelas
    ORDER BY total_poin DESC, s.nama ASC
    LIMIT 10
");

$siswa_id = $_SESSION['id'] ?? $_SESSION['siswa_id'] ?? $_SESSION['user_id'] ?? 0;
$nama_user = $_SESSION['nama'] ?? 'Siswa';
$siswa_id_escaped = mysqli_real_escape_string($koneksi, $siswa_id);

// Hitung Notifikasi Belum Dibaca
$q_unread = mysqli_query($koneksi, "SELECT COUNT(*) as unread FROM notifikasi WHERE siswa_id = '$siswa_id_escaped' AND is_read = 0");
$d_unread = mysqli_fetch_assoc($q_unread);
$unread_count = $d_unread['unread'] ?? 0;

// Ambil Daftar Notifikasi
$q_notif = mysqli_query($koneksi, "SELECT * FROM notifikasi WHERE siswa_id = '$siswa_id_escaped' ORDER BY id DESC");

// Query mengambil seluruh katalog buku
$query_katalog_buku = "
    SELECT 
        b.*, 
        COALESCE(AVG(r.rating), 0) AS rating_rata, 
        COUNT(r.id) AS total_review 
    FROM buku b 
    LEFT JOIN review_buku r ON b.id = r.buku_id 
    GROUP BY b.id 
    ORDER BY b.id DESC
";
$q_buku = mysqli_query($koneksi, $query_katalog_buku);

// Hitung Total Buku
$total_buku = mysqli_num_rows($q_buku);

// Query mengambil buku yang SUDAH dikembalikan tapi BELUM di-review oleh siswa ini
$q_buku_review = mysqli_query($koneksi, "
    SELECT DISTINCT b.id, b.judul, b.penulis, b.cover 
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    WHERE p.siswa_id = '$siswa_id_escaped' 
      AND p.status_transaksi = 'selesai'
      AND b.id NOT IN (
          SELECT buku_id FROM review_buku WHERE siswa_id = '$siswa_id_escaped'
      )
");

// Inisialisasi array $leaderboard_data
$leaderboard_data = [];
if ($q_leaderboard) {
    while ($row = mysqli_fetch_assoc($q_leaderboard)) {
        $leaderboard_data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Perpustakaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            orange: '#F3811E',
                            red: '#E84524',
                            teal: '#1DB996',
                            blue: '#1F3C88',
                            lightTeal: '#E6F8F4',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col md:flex-row pb-20 md:pb-0">

<!-- ================= SIDEBAR DESKTOP ================= -->
    <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-slate-300 h-screen sticky top-0 border-r border-slate-800 flex-shrink-0 z-40 relative">
        <!-- Header Logo -->
        <div class="p-6 border-b border-slate-800 flex items-center gap-3 flex-shrink-0">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-orange to-brand-red flex items-center justify-center text-white text-xl shadow-lg">
                📚
            </div>
            <div>
                <h1 class="font-extrabold text-white tracking-wide text-base">Perpustakaan</h1>
                <p class="text-[11px] text-slate-400">Portal Siswa</p>
            </div>
        </div>

        <!-- Profil Siswa -->
        <div class="p-4 mx-4 mt-4 bg-slate-800/80 rounded-2xl border border-slate-700/50 flex items-center gap-3 flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-brand-teal text-white flex items-center justify-center font-bold text-sm shadow">
                <?= strtoupper(substr($nama_user, 0, 1)); ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-xs text-slate-400">Selamat datang,</p>
                <h2 class="text-sm font-bold text-white truncate"><?= htmlspecialchars($nama_user); ?></h2>
            </div>
        </div>

        <!-- Menu Navigation Tabs -->
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <button id="btn-tab-katalog" onclick="switchTab('katalog')" class="w-full flex items-center gap-3 px-4 py-3 font-bold text-xs rounded-xl transition bg-brand-orange/10 text-brand-orange border border-brand-orange/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span>Koleksi Buku</span>
            </button>

            <button id="btn-tab-riwayat" onclick="switchTab('riwayat')" class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 font-medium text-xs rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Riwayat Pinjam</span>
            </button>

            <button id="btn-tab-leaderboard" onclick="switchTab('leaderboard')" class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 font-medium text-xs rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Leaderboard</span>
            </button>

            <!-- Button Tab Review Buku -->
            <button id="btn-tab-review" onclick="switchTab('review')" class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 font-medium text-xs rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span>Ulas Buku</span>
            </button>
        </nav>

        <!-- Logout Button -->
        <div class="p-4 border-t border-slate-800 shrink-0">
            <a href="javascript:void(0);" onclick="konfirmasiLogout()" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-xl font-bold text-xs transition border border-rose-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Keluar / Logout</span>
            </a>
        </div>
    </aside>

    <!-- ================= MAIN CONTENT AREA ================= -->
    <main class="flex-1 p-4 sm:p-6 md:p-8 max-w-7xl mx-auto w-full">
        
        <!-- HEADER TOP BAR -->
        <header class="flex items-center justify-between mb-6 pb-4 border-b border-slate-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-800">Portal Siswa</h1>
                <p class="text-xs text-slate-500">Temukan buku favorit dan tingkatkan peringkat literasimu!</p>
            </div>

            <!-- NOTIFIKASI POP-UP BUTTON -->
            <div class="relative">
                <button onclick="toggleModal('modal-notif')" class="relative p-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
                    <span class="text-lg">🔔</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center animate-pulse">
                            <?= $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>
        </header>

        <!-- --------------------------------------- -->
        <!-- TAB 1: KATALOG BUKU                     -->
        <!-- --------------------------------------- -->
        <section id="tab-katalog" class="space-y-6">
            <!-- Search Live Filter Bar -->
            <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="w-full sm:w-96 relative">
                    <input type="text" id="live-search-input" onkeyup="liveSearch()" placeholder="Ketik judul atau penulis buku..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal bg-slate-50">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div class="text-xs text-slate-500 font-medium self-end sm:self-center">
                    Total Buku: <span id="total-buku-count" class="font-bold text-slate-800"><?= $total_buku; ?></span>
                </div>
            </div>

            <div id="katalog-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <?php if (mysqli_num_rows($q_buku) > 0): ?>
                    <?php while ($b = mysqli_fetch_assoc($q_buku)): ?>
                        <div class="buku-item bg-white rounded-2xl border border-slate-200/80 p-3 flex flex-col justify-between shadow-sm hover:shadow-md transition" 
                             data-judul="<?= htmlspecialchars(strtolower($b['judul'])); ?>" 
                             data-penulis="<?= htmlspecialchars(strtolower($b['penulis'])); ?>">
                            <div>
                                <div class="w-full h-44 rounded-xl bg-slate-100 overflow-hidden mb-3 relative">
                                    <img src="uploads/<?= !empty($b['cover']) ? htmlspecialchars($b['cover']) : 'default_cover.jpg'; ?>" alt="<?= htmlspecialchars($b['judul']); ?>" class="w-full h-full object-cover">
                                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-md text-[10px] font-bold <?= $b['status'] === 'tersedia' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'; ?>">
                                        <?= ucfirst($b['status']); ?>
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-800 text-xs sm:text-sm line-clamp-2 leading-snug"><?= htmlspecialchars($b['judul']); ?></h3>
                                <p class="text-[11px] text-slate-400 font-medium mt-1 truncate">✍️ <?= htmlspecialchars($b['penulis']); ?></p>
                            </div>
                            
                            <!-- Rating & Review Dinamis -->
                            <div class="flex items-center gap-1.5 my-2">
                                <div class="flex text-amber-400">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 5.00L3.82 19z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-slate-700">
                                    <?= $b['rating_rata'] > 0 ? number_format($b['rating_rata'], 1) : '0'; ?>
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    (<?= $b['total_review']; ?> ulasan)
                                </span>
                            </div>

                            <button onclick="openSinopsisModal('<?= htmlspecialchars(addslashes($b['judul'])); ?>', '<?= htmlspecialchars(addslashes($b['sinopsis'])); ?>')" class="mt-3 w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-1.5 rounded-xl text-[11px] transition">
                                📖 Baca Sinopsis
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <!-- Tampilan jika hasil pencarian kosong -->
                <div id="no-search-result" class="hidden col-span-full py-12 text-center text-slate-400 text-xs sm:text-sm">
                    Buku tidak ditemukan.
                </div>
            </div>
        </section>

        <!-- --------------------------------------- -->
        <!-- TAB 2: RIWAYAT PEMINJAMAN              -->
        <!-- --------------------------------------- -->
        <section id="tab-riwayat" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                
                <!-- Header & Live Search Bar Riwayat -->
                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                    <h2 class="font-bold text-slate-800 text-base">Riwayat Peminjaman Buku</h2>
                    
                    <div class="w-full sm:w-72 relative">
                        <input type="text" id="search-riwayat-input" onkeyup="liveSearchRiwayat()" placeholder="Cari judul buku atau status..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal bg-slate-50">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-3 rounded-l-xl">Judul Buku</th>
                                <th class="p-3">Tanggal Pinjam</th>
                                <th class="p-3">Jatuh Tempo</th>
                                <th class="p-3">Tanggal Kembali</th>
                                <th class="p-3 rounded-r-xl">Status</th>
                            </tr>
                        </thead>
                        <tbody id="riwayat-table-body" class="divide-y divide-slate-100">
                            <?php
                            $q_my_history = mysqli_query($koneksi, "
                                SELECT p.*, b.judul 
                                FROM peminjaman p
                                JOIN buku b ON p.buku_id = b.id
                                WHERE p.siswa_id = '$siswa_id_escaped'
                                ORDER BY p.id DESC
                            ");

                            if (mysqli_num_rows($q_my_history) > 0):
                                while ($h = mysqli_fetch_assoc($q_my_history)):
                                    $status_text = ($h['status_transaksi'] === 'berjalan') ? 'dipinjam' : 'selesai';
                            ?>
                                <tr class="riwayat-item" data-judul="<?= htmlspecialchars(strtolower($h['judul'])); ?>" data-status="<?= $status_text; ?>">
                                    <td class="p-3 font-semibold text-slate-800"><?= htmlspecialchars($h['judul']); ?></td>
                                    <td class="p-3 text-slate-500"><?= date('d-m-Y', strtotime($h['tanggal_pinjam'])); ?></td>
                                    <td class="p-3 text-slate-500"><?= date('d-m-Y', strtotime($h['tanggal_jatuh_tempo'])); ?></td>
                                    <td class="p-3 text-slate-500"><?= !empty($h['tanggal_kembali']) ? date('d-m-Y', strtotime($h['tanggal_kembali'])) : '-'; ?></td>
                                    <td class="p-3">
                                        <?php if ($h['status_transaksi'] === 'berjalan'): ?>
                                            <span class="bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-[10px] font-bold">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full text-[10px] font-bold">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr id="no-riwayat-data">
                                    <td colspan="5" class="p-6 text-center text-slate-400 text-xs">Belum ada riwayat peminjaman.</td>
                                </tr>
                            <?php endif; ?>

                            <!-- Row Tampilan jika hasil pencarian kosong -->
                            <tr id="no-riwayat-search-result" class="hidden">
                                <td colspan="5" class="p-6 text-center text-slate-400 text-xs">Peminjaman tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- --------------------------------------- -->
        <!-- TAB 3: LEADERBOARD                     -->
        <!-- --------------------------------------- -->
        <section id="tab-leaderboard" class="hidden space-y-6">
            <!-- Podium Top 3 -->
            <?php if (count($leaderboard_data) >= 1): ?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end pt-4">
                    
                    <!-- JUARA 2 -->
                    <?php if (isset($leaderboard_data[1])): ?>
                    <div class="order-2 sm:order-1 bg-white p-5 rounded-3xl shadow-lg border border-slate-100 text-center flex flex-col items-center relative overflow-hidden">
                        <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-black text-lg border-2 border-slate-300 shadow-md mb-2">
                            2
                        </div>
                        <span class="text-xl mb-1">🥈</span>
                        <p class="font-bold text-slate-800 text-sm truncate w-full"><?= htmlspecialchars($leaderboard_data[1]['nama']); ?></p>
                        <p class="text-[11px] text-slate-400 font-medium"><?= htmlspecialchars($leaderboard_data[1]['kelas']); ?></p>
                        <span class="mt-3 bg-slate-100 text-slate-700 font-extrabold text-xs px-3 py-1.5 rounded-xl border border-slate-200">
                            <?= $leaderboard_data[1]['total_poin']; ?> Poin
                        </span>
                    </div>
                    <?php endif; ?>

                    <!-- JUARA 1 -->
                    <?php if (isset($leaderboard_data[0])): ?>
                    <div class="order-1 sm:order-2 bg-gradient-to-b from-amber-500/10 to-white p-6 rounded-3xl shadow-xl border-2 border-brand-orange text-center flex flex-col items-center relative overflow-hidden transform sm:-translate-y-2">
                        <div class="absolute top-0 right-0 bg-brand-orange text-white text-[9px] font-black uppercase px-3 py-1 rounded-bl-xl shadow">
                            Top Reader
                        </div>
                        <div class="w-14 h-14 rounded-full bg-brand-orange text-white flex items-center justify-center font-black text-xl shadow-lg shadow-brand-orange/40 border-2 border-white mb-2">
                            1
                        </div>
                        <span class="text-2xl mb-1">👑</span>
                        <p class="font-black text-slate-900 text-base truncate w-full"><?= htmlspecialchars($leaderboard_data[0]['nama']); ?></p>
                        <p class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($leaderboard_data[0]['kelas']); ?></p>
                        <span class="mt-3 bg-brand-orange text-white font-black text-xs px-4 py-1.5 rounded-xl shadow-md shadow-brand-orange/30">
                            <?= $leaderboard_data[0]['total_poin']; ?> Poin
                        </span>
                    </div>
                    <?php endif; ?>

                    <!-- JUARA 3 -->
                    <?php if (isset($leaderboard_data[2])): ?>
                    <div class="order-3 bg-white p-5 rounded-3xl shadow-lg border border-slate-100 text-center flex flex-col items-center relative overflow-hidden">
                        <div class="w-12 h-12 rounded-full bg-amber-700/20 text-amber-800 flex items-center justify-center font-black text-lg border-2 border-amber-600/30 shadow-md mb-2">
                            3
                        </div>
                        <span class="text-xl mb-1">🥉</span>
                        <p class="font-bold text-slate-800 text-sm truncate w-full"><?= htmlspecialchars($leaderboard_data[2]['nama']); ?></p>
                        <p class="text-[11px] text-slate-400 font-medium"><?= htmlspecialchars($leaderboard_data[2]['kelas']); ?></p>
                        <span class="mt-3 bg-amber-50 text-amber-800 font-extrabold text-xs px-3 py-1.5 rounded-xl border border-amber-200">
                            <?= $leaderboard_data[2]['total_poin']; ?> Poin
                        </span>
                    </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

            <!-- Tabel Peringkat Lengkap -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <h2 class="font-bold text-slate-800 text-base mb-4">Peringkat Teratas Siswa</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-3 text-center rounded-l-xl w-16">Rank</th>
                                <th class="p-3">Nama Siswa</th>
                                <th class="p-3">Kelas</th>
                                <th class="p-3 text-center">Data Aktivitas</th>
                                <th class="p-3 text-center rounded-r-xl">QTY POIN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (count($leaderboard_data) > 0): 
                                $rank = 1;
                                foreach ($leaderboard_data as $ld):
                            ?>
                                <tr>
                                    <td class="p-3 text-center font-bold">#<?= $rank; ?></td>
                                    <td class="p-3 font-semibold text-slate-800"><?= htmlspecialchars($ld['nama']); ?></td>
                                    <td class="p-3 text-slate-500"><?= htmlspecialchars($ld['kelas']); ?></td>
                                    <td class="p-3 text-center text-xs text-slate-500">
                                        <span class="font-medium text-slate-700"><?= $ld['total_pinjam']; ?> buku</span> • 
                                        <span class="font-medium text-slate-700"><?= $ld['total_review']; ?> review</span>
                                    </td>
                                    <td class="p-3 text-center font-bold text-brand-orange">
                                        <?= $ld['total_poin']; ?> POIN
                                    </td>
                                </tr>
                            <?php 
                                $rank++;
                                endforeach; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400 text-xs">Belum ada data peringkat.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- --------------------------------------- -->
        <!-- TAB 4: REVIEW / ULAS BUKU              -->
        <!-- --------------------------------------- -->
        <section id="tab-review" class="hidden space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm max-w-2xl mx-auto">
                <h2 class="font-bold text-slate-800 text-base mb-1">Berikan Ulasan Buku</h2>
                <p class="text-xs text-slate-400 mb-4">Dapatkan +20 Poin untuk setiap ulasan buku yang sudah kamu kembalikan!</p>

                <?php if (mysqli_num_rows($q_buku_review) > 0): ?>
                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="siswa_id" value="<?= $siswa_id; ?>">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Buku yang Pernah Dipinjam:</label>
                            <select name="buku_id" required class="w-full p-2.5 border border-slate-200 rounded-xl text-xs sm:text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-teal">
                                <option value="">-- Pilih Buku --</option>
                                <?php while ($br = mysqli_fetch_assoc($q_buku_review)): ?>
                                    <option value="<?= $br['id']; ?>"><?= htmlspecialchars($br['judul']); ?> (✍️ <?= htmlspecialchars($br['penulis']); ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Berikan Rating:</label>
                            <select name="rating" required class="w-full p-2.5 border border-slate-200 rounded-xl text-xs sm:text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-teal">
                                <option value="5">⭐⭐⭐⭐⭐ (5/5) - Sangat Bagus</option>
                                <option value="4">⭐⭐⭐⭐ (4/5) - Bagus</option>
                                <option value="3">⭐⭐⭐ (3/5) - Cukup</option>
                                <option value="2">⭐⭐ (2/5) - Kurang</option>
                                <option value="1">⭐ (1/5) - Buruk</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Ulasan Kamu:</label>
                            <textarea name="ulasan" rows="4" required placeholder="Tulis pendapatmu tentang buku ini..." class="w-full p-2.5 border border-slate-200 rounded-xl text-xs sm:text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-teal"></textarea>
                        </div>

                        <button type="submit" name="simpan_review" class="w-full bg-brand-teal hover:bg-teal-600 text-white font-bold py-2.5 rounded-xl text-xs sm:text-sm transition shadow-md shadow-brand-teal/20">
                            Kirim Ulasan & Dapatkan +20 Poin
                        </button>
                    </form>
                <?php else: ?>
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Tidak ada buku yang perlu diulas saat ini. Pinjam dan kembalikan buku terlebih dahulu!
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- MODAL POPUP NOTIFIKASI -->
    <div id="modal-notif" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-100">
            <button onclick="toggleModal('modal-notif')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-800">Notifikasi Peringatan</h3>
                <p class="text-xs text-slate-400">Pesan dari petugas perpustakaan</p>
            </div>

            <div class="space-y-2 max-h-60 overflow-y-auto">
                <?php if (mysqli_num_rows($q_notif) > 0): while ($n = mysqli_fetch_assoc($q_notif)): ?>
                    <div class="p-3 bg-amber-50 border border-amber-200/60 rounded-xl text-xs text-amber-900">
                        <p class="font-bold mb-0.5">⚠️ Perhatian!</p>
                        <p><?= htmlspecialchars($n['pesan']); ?></p>
                        <span class="text-[9px] text-amber-700/70 mt-1 block"><?= date('d-m-Y H:i', strtotime($n['created_at'])); ?></span>
                    </div>
                <?php endwhile; else: ?>
                    <p class="text-center text-slate-400 text-xs py-4">Tidak ada notifikasi baru.</p>
                <?php endif; ?>
            </div>

            <div class="flex justify-end pt-2">
                <button onclick="toggleModal('modal-notif')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-4 py-2 rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP SINOPSIS BUKU -->
    <div id="modal-sinopsis" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-100">
            <button onclick="toggleModal('modal-sinopsis')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

            <div class="border-b border-slate-100 pb-3">
                <h3 id="sinopsis-judul" class="font-bold text-base text-slate-800">Judul Buku</h3>
                <p class="text-xs text-slate-400">Sinopsis Singkat</p>
            </div>

            <div id="sinopsis-isi" class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-2xl max-h-60 overflow-y-auto border border-slate-100">
                Isi sinopsis...
            </div>

            <div class="flex justify-end pt-2">
                <button onclick="toggleModal('modal-sinopsis')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-4 py-2 rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function liveSearch() {
            const keyword = document.getElementById('live-search-input').value.toLowerCase().trim();
            const items = document.querySelectorAll('.buku-item');
            const emptyMessage = document.getElementById('no-search-result');
            let visibleCount = 0;

            items.forEach(item => {
                const judul = item.getAttribute('data-judul');
                const penulis = item.getAttribute('data-penulis');

                if (judul.includes(keyword) || penulis.includes(keyword)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            document.getElementById('total-buku-count').innerText = visibleCount;

            if (visibleCount === 0) {
                emptyMessage.classList.remove('hidden');
            } else {
                emptyMessage.classList.add('hidden');
            }
        }

        function switchTab(tabName) {
            const tabs = ['katalog', 'riwayat', 'leaderboard', 'review'];
            tabs.forEach(t => {
                document.getElementById('tab-' + t).classList.add('hidden');
                const btn = document.getElementById('btn-tab-' + t);
                if (btn) {
                    btn.className = "w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 font-medium text-xs rounded-xl transition";
                }
            });

            document.getElementById('tab-' + tabName).classList.remove('hidden');
            const activeBtn = document.getElementById('btn-tab-' + tabName);
            if (activeBtn) {
                activeBtn.className = "w-full flex items-center gap-3 px-4 py-3 font-bold text-xs rounded-xl transition bg-brand-orange/10 text-brand-orange border border-brand-orange/20";
            }
        }

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                if (modal.classList.contains('hidden')) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                } else {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }
        }

        function openSinopsisModal(judul, sinopsis) {
            document.getElementById('sinopsis-judul').innerText = judul;
            document.getElementById('sinopsis-isi').innerText = sinopsis;
            toggleModal('modal-sinopsis');
        }

        function konfirmasiLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'yakin ingin keluar',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php';
                }
            });
        }

        function liveSearchRiwayat() {
            const keyword = document.getElementById('search-riwayat-input').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.riwayat-item');
            const emptyMessage = document.getElementById('no-riwayat-search-result');
            let visibleCount = 0;

            rows.forEach(row => {
                const judul = row.getAttribute('data-judul') || '';
                const status = row.getAttribute('data-status') || '';

                if (judul.includes(keyword) || status.includes(keyword)) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (emptyMessage) {
                if (visibleCount === 0 && rows.length > 0) {
                    emptyMessage.classList.remove('hidden');
                } else {
                    emptyMessage.classList.add('hidden');
                }
            }
        }
    </script>
</body>
</html>