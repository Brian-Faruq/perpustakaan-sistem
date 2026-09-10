<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

$siswa_id = $_SESSION['id'] ?? $_SESSION['siswa_id'] ?? $_SESSION['user_id'] ?? 0;
$nama_user = $_SESSION['nama'] ?? 'Siswa';
$siswa_id_escaped = mysqli_real_escape_string($koneksi, $siswa_id);

// Hitung Notifikasi Belum Dibaca
$q_unread = mysqli_query($koneksi, "SELECT COUNT(*) as unread FROM notifikasi WHERE siswa_id = '$siswa_id_escaped' AND is_read = 0");
$d_unread = mysqli_fetch_assoc($q_unread);
$unread_count = $d_unread['unread'] ?? 0;

// Ambil Daftar Notifikasi
$q_notif = mysqli_query($koneksi, "SELECT * FROM notifikasi WHERE siswa_id = '$siswa_id_escaped' ORDER BY id DESC");

// Sanitasi Pencarian Buku
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_escaped = mysqli_real_escape_string($koneksi, $search);

// Hitung Total Buku
$q_total_buku = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku");
$d_total_buku = mysqli_fetch_assoc($q_total_buku);
$total_buku = $d_total_buku['total'] ?? 0;

// Query Leaderboard (Tambahkan s.kelas pada SELECT)
$q_leaderboard = mysqli_query($koneksi, "
    SELECT s.id, s.nama, s.kelas, COUNT(p.id) AS total_pinjam 
    FROM siswa s 
    LEFT JOIN peminjaman p ON s.id = p.siswa_id 
    GROUP BY s.id, s.nama, s.kelas 
    ORDER BY total_pinjam DESC, s.nama ASC 
    LIMIT 10
");

// FIX: Inisialisasi array $leaderboard_data
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

            <button onclick="openModal('modal-notifikasi')" class="w-full flex items-center justify-between px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 font-medium text-xs rounded-xl transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span>Notifikasi</span>
                </div>
                <?php if ($unread_count > 0): ?>
                    <span class="bg-brand-red text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?= $unread_count; ?></span>
                <?php endif; ?>
            </button>
        </nav>

        <!-- Tombol Keluar -->
        <div class="p-4 border-t border-slate-800 flex-shrink-0">
            <button onclick="confirmLogout()" class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-red-600/20 text-slate-300 hover:text-red-400 text-xs py-3 rounded-xl font-bold transition border border-slate-700 hover:border-red-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Keluar Aplikasi</span>
            </button>
        </div>
    </aside>

    <!-- ================= MAIN CONTENT AREA ================= -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        
        <!-- TOPBAR HEADER -->
        <header class="bg-white border-b border-slate-200 px-4 sm:px-8 py-4 flex items-center justify-between sticky top-0 z-20 shadow-sm">
            <div class="flex items-center gap-3 md:hidden">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-orange to-brand-red flex items-center justify-center text-white text-base">📚</div>
                <h1 class="font-bold text-slate-800 text-sm">Perpustakaan</h1>
            </div>

            <div class="hidden md:block">
                <h2 id="page-title" class="text-lg font-bold text-slate-800">Katalog Digital</h2>
                <p id="page-subtitle" class="text-xs text-slate-400">Eksplorasi koleksi buku yang tersedia</p>
            </div>

            <div id="wrapper-search-katalog" class="w-48 sm:w-80">
                <form id="formSearch" action="" method="GET">
                    <div class="relative">
                        <input type="text" id="inputSearch" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari buku..." class="w-full pl-9 pr-4 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs shadow-inner focus:outline-none focus:ring-2 focus:ring-brand-teal focus:bg-white transition" autocomplete="off">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>
            </div>

            <div id="wrapper-search-riwayat" class="w-48 sm:w-80 hidden">
                <div class="relative">
                    <input type="text" id="searchRiwayat" onkeyup="filterRiwayat()" placeholder="Cari di riwayat..." class="w-full pl-9 pr-4 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs shadow-inner focus:outline-none focus:ring-2 focus:ring-brand-teal focus:bg-white transition" autocomplete="off">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </header>

        <div class="p-4 sm:p-8 space-y-6">

            <!-- ================= TAB 1: KATALOG BUKU ================= -->
            <div id="tab-katalog" class="space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg sm:text-xl font-bold text-slate-800">Daftar Buku</h2>
                        <span class="bg-brand-lightTeal text-brand-teal font-bold text-xs px-2.5 py-1 rounded-lg border border-brand-teal/20">
                            <?= $total_buku; ?> Buku
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                    <?php
                    $query_str = "SELECT * FROM buku";
                    if (!empty($search_escaped)) {
                        $query_str .= " WHERE judul LIKE '%$search_escaped%' OR penulis LIKE '%$search_escaped%'";
                    }
                    $query_str .= " ORDER BY id DESC";

                    $query_buku = mysqli_query($koneksi, $query_str);

                    if (mysqli_num_rows($query_buku) > 0):
                        while ($b = mysqli_fetch_assoc($query_buku)):
                            $gambar_cover = !empty($b['cover']) && file_exists('uploads/' . $b['cover']) ? 'uploads/' . $b['cover'] : 'https://via.placeholder.com/300x400?text=No+Cover';
                    ?>
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col border border-slate-200 group hover:-translate-y-1">
                            <div class="relative w-full aspect-[3/4] bg-slate-100 overflow-hidden">
                                <img src="<?= $gambar_cover; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute top-2 right-2">
                                    <?php if ($b['status'] == 'tersedia'): ?>
                                        <span class="bg-emerald-500/90 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">Tersedia</span>
                                    <?php else: ?>
                                        <span class="bg-rose-500/90 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">Dipinjam</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <h3 class="font-bold text-xs sm:text-sm text-slate-800 leading-snug line-clamp-2 group-hover:text-brand-orange transition-colors"><?= htmlspecialchars($b['judul']); ?></h3>
                                    <p class="text-[11px] text-slate-400 mt-1 line-clamp-1">Oleh: <span class="text-slate-600 font-medium"><?= htmlspecialchars($b['penulis']); ?></span></p>
                                </div>
                                
                                <button onclick="openModal('modal-<?= $b['id']; ?>')" class="w-full bg-slate-900 hover:bg-brand-orange text-white text-xs py-2 rounded-xl font-semibold transition shadow-sm">
                                    Detail
                                </button>
                            </div>
                        </div>

                        <!-- MODAL DETAIL BUKU -->
                        <div id="modal-<?= $b['id']; ?>" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
                            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative border border-slate-100">
                                <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

                                <div class="flex flex-col sm:flex-row gap-5 items-center sm:items-start pt-2">
                                    <div class="w-28 sm:w-36 aspect-[3/4] bg-slate-100 rounded-xl overflow-hidden shadow-md flex-shrink-0 border border-slate-200">
                                        <img src="<?= $gambar_cover; ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div class="space-y-2 text-center sm:text-left flex-1">
                                        <h3 class="font-bold text-base sm:text-lg text-slate-800 leading-tight"><?= htmlspecialchars($b['judul']); ?></h3>
                                        <p class="text-xs text-slate-500">Penulis: <span class="font-semibold text-slate-700"><?= htmlspecialchars($b['penulis']); ?></span></p>
                                        
                                        <div class="pt-2 text-left">
                                            <h4 class="font-bold text-[11px] text-brand-orange uppercase tracking-wider mb-1">Sinopsis</h4>
                                            <p class="text-xs text-slate-600 leading-relaxed max-h-36 overflow-y-auto bg-slate-50 p-3 rounded-xl border border-slate-200/60"><?= nl2br(htmlspecialchars($b['sinopsis'] ?? 'Sinopsis belum tersedia.')); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right border-t border-slate-100 pt-3">
                                    <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-5 py-2.5 rounded-xl font-semibold transition">Tutup</button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="col-span-full bg-white p-12 rounded-2xl text-center text-slate-400 border border-slate-200">
                            <p class="text-sm">Buku tidak ditemukan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ================= TAB 2: RIWAYAT PEMINJAMAN ================= -->
            <div id="tab-riwayat" class="hidden">
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="p-4">Judul Buku</th>
                                    <th class="p-4">Penulis</th>
                                    <th class="p-4">Tanggal Pinjam</th>
                                    <th class="p-4">Tanggal Kembali</th>
                                    <th class="p-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php
                                $q_riwayat = mysqli_query($koneksi, "
                                    SELECT b.judul, b.penulis, p.tanggal_pinjam, p.tanggal_kembali 
                                    FROM peminjaman p
                                    JOIN buku b ON p.buku_id = b.id
                                    WHERE p.siswa_id = '$siswa_id_escaped'
                                    ORDER BY p.id DESC
                                ");

                                if (mysqli_num_rows($q_riwayat) > 0):
                                    while ($r = mysqli_fetch_assoc($q_riwayat)):
                                        $sudah_kembali = !empty($r['tanggal_kembali']);
                                        $tgl_kembali = $sudah_kembali ? date('d-m-Y', strtotime($r['tanggal_kembali'])) : '-';
                                ?>
                                    <tr class="row-riwayat hover:bg-slate-50/80 transition">
                                        <td class="p-4 font-bold text-slate-800 cell-judul"><?= htmlspecialchars($r['judul']); ?></td>
                                        <td class="p-4 text-slate-600 cell-penulis"><?= htmlspecialchars($r['penulis']); ?></td>
                                        <td class="p-4 text-slate-500"><?= date('d-m-Y', strtotime($r['tanggal_pinjam'])); ?></td>
                                        <td class="p-4 text-slate-500"><?= $tgl_kembali; ?></td>
                                        <td class="p-4">
                                            <?php if ($sudah_kembali): ?>
                                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold px-2.5 py-1 rounded-full text-[10px]">Dikembalikan</span>
                                            <?php else: ?>
                                                <span class="bg-amber-50 text-amber-600 border border-amber-200 font-bold px-2.5 py-1 rounded-full text-[10px]">Dipinjam</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="5" class="p-12 text-center text-slate-400 text-xs">Belum ada riwayat peminjaman buku saat ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB LEADERBOARD -->
            <div id="tab-leaderboard" class="tab-content hidden space-y-6">
                
                <div class="bg-slate-900 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-xl border border-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                        <div>
                            <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-400 text-[10px] font-extrabold uppercase tracking-widest rounded-full mb-3 border border-amber-500/30">
                                PERINGKAT LITERASI
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight flex items-center gap-2">
                                🏆 Leaderboard Peminjam Terbanyak
                            </h2>
                            <p class="text-slate-400 text-xs sm:text-sm mt-1">Siswa teraktif meminjam buku di perpustakaan</p>
                        </div>
                        
                        <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-4 text-center min-w-[140px]">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">TOTAL SISWA ACTIVE</p>
                            <p class="text-2xl font-black text-amber-500 mt-0.5"><?= count($leaderboard_data); ?></p>
                        </div>
                    </div>
                </div>

                <!-- PODIUM TOP 3 -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end pt-4">
                    
                    <?php if (isset($leaderboard_data[1])): $rank2 = $leaderboard_data[1]; ?>
                    <div class="order-2 md:order-1 bg-white rounded-3xl p-6 border border-slate-100 shadow-md flex flex-col items-center text-center relative">
                        <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-700 font-extrabold flex items-center justify-center text-lg shadow-inner mb-2 border-2 border-slate-300">
                            2
                        </div>
                        <span class="text-xl mb-1">🥈</span>
                        <h3 class="font-bold text-slate-800 text-base line-clamp-1"><?= htmlspecialchars($rank2['nama']); ?></h3>
                        <p class="text-xs text-slate-400 font-medium mb-3"><?= htmlspecialchars($rank2['kelas'] ?? 'Siswa'); ?></p>
                        <span class="px-4 py-1.5 bg-slate-100 text-slate-600 font-bold text-xs rounded-xl border border-slate-200">
                            <?= $rank2['total_pinjam']; ?> Buku
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($leaderboard_data[0])): $rank1 = $leaderboard_data[0]; ?>
                    <div class="order-1 md:order-2 bg-gradient-to-b from-amber-50/50 to-white rounded-3xl p-7 border-2 border-amber-400 shadow-xl flex flex-col items-center text-center relative -translate-y-2">
                        <span class="absolute -top-3 px-3 py-0.5 bg-amber-500 text-white font-extrabold text-[10px] rounded-full uppercase tracking-wider shadow">
                            TOP READER
                        </span>
                        <div class="w-14 h-14 rounded-full bg-amber-500 text-white font-black flex items-center justify-center text-xl shadow-lg shadow-amber-500/30 mb-2 mt-1">
                            1
                        </div>
                        <span class="text-2xl mb-1">👑</span>
                        <h3 class="font-extrabold text-slate-900 text-lg line-clamp-1"><?= htmlspecialchars($rank1['nama']); ?></h3>
                        <p class="text-xs text-slate-400 font-medium mb-4"><?= htmlspecialchars($rank1['kelas'] ?? 'Siswa'); ?></p>
                        <span class="px-5 py-2 bg-amber-500 text-white font-bold text-xs rounded-xl shadow-md shadow-amber-500/20">
                            <?= $rank1['total_pinjam']; ?> Buku
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($leaderboard_data[2])): $rank3 = $leaderboard_data[2]; ?>
                    <div class="order-3 bg-white rounded-3xl p-6 border border-slate-100 shadow-md flex flex-col items-center text-center relative">
                        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-800 font-extrabold flex items-center justify-center text-lg shadow-inner mb-2 border-2 border-amber-200">
                            3
                        </div>
                        <span class="text-xl mb-1">🥉</span>
                        <h3 class="font-bold text-slate-800 text-base line-clamp-1"><?= htmlspecialchars($rank3['nama']); ?></h3>
                        <p class="text-xs text-slate-400 font-medium mb-3"><?= htmlspecialchars($rank3['kelas'] ?? 'Siswa'); ?></p>
                        <span class="px-4 py-1.5 bg-amber-50/80 text-amber-700 font-bold text-xs rounded-xl border border-amber-200/60">
                            <?= $rank3['total_pinjam']; ?> Buku
                        </span>
                    </div>
                    <?php endif; ?>

                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <h3 class="font-extrabold text-slate-800 text-base">Daftar Seluruh Peringkat Siswa</h3>
                        <div class="relative min-w-[240px]">
                            <input type="text" id="search-leaderboard" placeholder="Cari nama siswa..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange outline-none transition">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[11px] font-bold text-slate-400 uppercase border-b border-slate-100">
                                    <th class="py-3 px-4">RANK</th>
                                    <th class="py-3 px-4">NAMA SISWA</th>
                                    <th class="py-3 px-4">KELAS</th>
                                    <th class="py-3 px-4">QTY PEMINJAMAN</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                                <?php foreach ($leaderboard_data as $index => $row): 
                                    $rank = $index + 1;
                                    $is_current_user = ($row['nama'] == $nama_user);
                                ?>
                                <tr class="hover:bg-slate-50/80 transition <?= $is_current_user ? 'bg-amber-50/50 font-bold' : ''; ?>">
                                    <td class="py-3.5 px-4">
                                        <?php if($rank == 1): ?>
                                            <span class="w-7 h-7 rounded-full bg-amber-500 text-white font-extrabold inline-flex items-center justify-center text-xs shadow">1</span>
                                        <?php elseif($rank == 2): ?>
                                            <span class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 font-extrabold inline-flex items-center justify-center text-xs">2</span>
                                        <?php elseif($rank == 3): ?>
                                            <span class="w-7 h-7 rounded-full bg-amber-200 text-amber-800 font-extrabold inline-flex items-center justify-center text-xs">3</span>
                                        <?php else: ?>
                                            <span class="text-slate-400 font-bold px-2">#<?= $rank; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                                        <?= htmlspecialchars($row['nama']); ?>
                                        <?php if ($is_current_user): ?>
                                            <span class="px-2 py-0.5 bg-brand-orange text-white text-[10px] font-bold rounded-full">Saya</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- KOLOM KELAS TAMBAHAN -->
                                    <td class="py-3.5 px-4 text-slate-500 font-medium">
                                        <?= htmlspecialchars($row['kelas'] ?? '-'); ?>
                                    </td>

                                    <td class="py-3.5 px-4 font-extrabold text-brand-teal">
                                        <?= $row['total_pinjam']; ?> Buku
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- ================= BOTTOM NAV MOBILE ================= -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-slate-200 px-6 py-2 flex justify-around items-center z-40 shadow-lg">
        <button id="mb-btn-katalog" onclick="switchTab('katalog')" class="flex flex-col items-center gap-1 text-brand-orange">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <span class="text-[10px] font-bold">Katalog</span>
        </button>

        <button id="mb-btn-riwayat" onclick="switchTab('riwayat')" class="flex flex-col items-center gap-1 text-slate-500 hover:text-slate-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-[10px] font-medium">Riwayat</span>
        </button>

        <button id="mb-btn-leaderboard" onclick="switchTab('leaderboard')" class="flex flex-col items-center gap-1 text-slate-500 hover:text-slate-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span class="text-[10px] font-medium">Top Siswa</span>
        </button>

        <button onclick="openModal('modal-notifikasi')" class="flex flex-col items-center gap-1 text-slate-500 hover:text-slate-800 relative">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span class="text-[10px] font-medium">Notif</span>
            <?php if ($unread_count > 0): ?>
                <span class="absolute top-0 right-1 w-2 h-2 rounded-full bg-brand-red"></span>
            <?php endif; ?>
        </button>

        <button onclick="confirmLogout()" class="flex flex-col items-center gap-1 text-slate-500 hover:text-red-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span class="text-[10px] font-medium">Keluar</span>
        </button>
    </div>

    <!-- MODAL NOTIFIKASI -->
    <div id="modal-notifikasi" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-100 max-h-[85vh] flex flex-col">
            <button onclick="closeModal('modal-notifikasi')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 flex-shrink-0">
                <span class="text-xl">🔔</span>
                <h3 class="font-bold text-base sm:text-lg text-slate-800">Notifikasi Peringatan</h3>
            </div>

            <div class="space-y-3 overflow-y-auto pr-1 flex-1">
                <?php if (mysqli_num_rows($q_notif) > 0): ?>
                    <?php while ($n = mysqli_fetch_assoc($q_notif)): ?>
                        <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/70 text-amber-900 text-xs space-y-1 shadow-sm">
                            <div class="flex items-center justify-between font-bold text-amber-700">
                                <span>⚠️ PERINGATAN</span>
                                <span class="text-[10px] text-amber-600 font-normal"><?= date('d/m/Y H:i', strtotime($n['created_at'])); ?></span>
                            </div>
                            <p class="leading-relaxed text-amber-800"><?= htmlspecialchars($n['pesan']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center text-xs text-slate-400 py-8">Tidak ada notifikasi saat ini.</p>
                <?php endif; ?>
            </div>

            <div class="text-right border-t border-slate-100 pt-3 flex-shrink-0">
                <button onclick="closeModal('modal-notifikasi')" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-4 py-2 rounded-xl font-semibold transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- SCRIPT TABS INTERACTION -->
    <script>
        function switchTab(tabName) {
            const tabKatalog = document.getElementById('tab-katalog');
            const tabRiwayat = document.getElementById('tab-riwayat');
            const tabLeaderboard = document.getElementById('tab-leaderboard');

            const btnKatalog = document.getElementById('btn-tab-katalog');
            const btnRiwayat = document.getElementById('btn-tab-riwayat');
            const btnLeaderboard = document.getElementById('btn-tab-leaderboard');

            const mbBtnKatalog = document.getElementById('mb-btn-katalog');
            const mbBtnRiwayat = document.getElementById('mb-btn-riwayat');
            const mbBtnLeaderboard = document.getElementById('mb-btn-leaderboard');

            const title = document.getElementById('page-title');
            const subtitle = document.getElementById('page-subtitle');
            
            const searchKatalog = document.getElementById('wrapper-search-katalog');
            const searchRiwayat = document.getElementById('wrapper-search-riwayat');

            [btnKatalog, btnRiwayat, btnLeaderboard].forEach(b => {
                if(b) b.className = "w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 font-medium text-xs rounded-xl transition";
            });
            [mbBtnKatalog, mbBtnRiwayat, mbBtnLeaderboard].forEach(b => {
                if(b) b.className = "flex flex-col items-center gap-1 text-slate-500 hover:text-slate-800 font-medium";
            });

            tabKatalog.classList.add('hidden');
            tabRiwayat.classList.add('hidden');
            tabLeaderboard.classList.add('hidden');
            searchKatalog.classList.add('hidden');
            searchRiwayat.classList.add('hidden');

            if (tabName === 'katalog') {
                tabKatalog.classList.remove('hidden');
                searchKatalog.classList.remove('hidden');
                title.innerText = "Katalog Digital";
                subtitle.innerText = "Eksplorasi koleksi buku yang tersedia";

                btnKatalog.className = "w-full flex items-center gap-3 px-4 py-3 font-bold text-xs rounded-xl transition bg-brand-orange/10 text-brand-orange border border-brand-orange/20";
                mbBtnKatalog.className = "flex flex-col items-center gap-1 text-brand-orange font-bold";
            } else if (tabName === 'riwayat') {
                tabRiwayat.classList.remove('hidden');
                searchRiwayat.classList.remove('hidden');
                title.innerText = "Riwayat Peminjaman";
                subtitle.innerText = "Daftar seluruh buku yang sedang & pernah kamu pinjam";

                btnRiwayat.className = "w-full flex items-center gap-3 px-4 py-3 font-bold text-xs rounded-xl transition bg-brand-orange/10 text-brand-orange border border-brand-orange/20";
                mbBtnRiwayat.className = "flex flex-col items-center gap-1 text-brand-orange font-bold";
            } else if (tabName === 'leaderboard') {
                tabLeaderboard.classList.remove('hidden');
                title.innerText = "Leaderboard Siswa";
                subtitle.innerText = "Peringkat siswa dengan aktivitas peminjaman buku terbanyak";

                btnLeaderboard.className = "w-full flex items-center gap-3 px-4 py-3 font-bold text-xs rounded-xl transition bg-brand-orange/10 text-brand-orange border border-brand-orange/20";
                mbBtnLeaderboard.className = "flex flex-col items-center gap-1 text-brand-orange font-bold";
            }
        }

        function filterRiwayat() {
            let input = document.getElementById('searchRiwayat').value.toLowerCase();
            let rows = document.querySelectorAll('.row-riwayat');
            rows.forEach(row => {
                let judul = row.querySelector('.cell-judul').textContent.toLowerCase();
                let penulis = row.querySelector('.cell-penulis').textContent.toLowerCase();
                row.style.display = (judul.includes(input) || penulis.includes(input)) ? "" : "none";
            });
        }

        // Live Search Leaderboard JS
        document.getElementById('search-leaderboard')?.addEventListener('keyup', function() {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tab-leaderboard tbody tr');
            
            rows.forEach(row => {
                let nama = row.children[1].textContent.toLowerCase();
                row.style.display = nama.includes(value) ? '' : 'none';
            });
        });

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.getElementById(id).classList.add('flex');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.getElementById(id).classList.remove('flex');
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Keluar dari aplikasi?',
                text: 'Kamu harus login kembali untuk mengakses katalog.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E84524',
                cancelButtonColor: '#1F3C88',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php';
                }
            });
        }
    </script>
</body>
</html>