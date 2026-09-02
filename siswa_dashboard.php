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

// Hitung Notifikasi Belum Dibaca (is_read = 0)
$q_unread = mysqli_query($koneksi, "SELECT COUNT(*) as unread FROM notifikasi WHERE siswa_id = '$siswa_id_escaped' AND is_read = 0");
$d_unread = mysqli_fetch_assoc($q_unread);
$unread_count = $d_unread['unread'] ?? 0;

// Ambil Daftar Notifikasi Siswa
$q_notif = mysqli_query($koneksi, "SELECT * FROM notifikasi WHERE siswa_id = '$siswa_id_escaped' ORDER BY id DESC");

// Sanitasi Pencarian Buku
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_escaped = mysqli_real_escape_string($koneksi, $search);

// Hitung Total Buku
$q_total_buku = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku");
$d_total_buku = mysqli_fetch_assoc($q_total_buku);
$total_buku = $d_total_buku['total'] ?? 0;

// Handler Hapus Notifikasi Siswa
if (isset($_POST['hapus_notif'])) {
    $notif_id = intval($_POST['notif_id']);
    mysqli_query($koneksi, "DELETE FROM notifikasi WHERE id = $notif_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Perpustakaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 CDN -->
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
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col justify-between">

    <div>
        <!-- NAVBAR RESPONSIF -->
        <nav class="bg-gradient-to-r from-brand-orange to-brand-red text-white px-4 sm:px-6 py-3.5 flex justify-between items-center shadow-md sticky top-0 z-40">
            <h1 class="font-bold text-lg sm:text-xl tracking-wide flex items-center gap-2">
                <span>📚</span> <span class="hidden xs:inline">Katalog</span> Perpustakaan
            </h1>
            <div class="flex items-center space-x-2 sm:space-x-3">

                <!-- TOMBOL RIWAYAT PEMINJAMAN -->
                <button onclick="openModal('modal-riwayat')" class="bg-black/20 hover:bg-black/30 text-white text-xs px-2.5 sm:px-3.5 py-2 rounded-xl font-semibold transition border border-white/20 flex items-center gap-1.5 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="hidden sm:inline">Riwayat</span>
                </button>

                <!-- IKON LONCENG NOTIFIKASI -->
                <button onclick="openModal('modal-notifikasi')" class="relative p-2 bg-black/20 hover:bg-black/30 rounded-xl border border-white/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 sm:h-5 w-4 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>

                    <?php if ($unread_count > 0): ?>
                        <span class="absolute -top-1 -right-1 flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-teal opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-brand-blue text-[10px] font-bold text-white items-center justify-center">
                                <?= $unread_count; ?>
                            </span>
                        </span>
                    <?php endif; ?>
                </button>

                <span class="text-xs sm:text-sm hidden md:inline pl-2 border-l border-white/30">Halo, <b><?= htmlspecialchars($nama_user); ?></b></span>
                
                <!-- TOMBOL LOGOUT -->
                <button onclick="confirmLogout()" class="bg-brand-blue hover:bg-slate-900 text-xs px-3 sm:px-3.5 py-2 rounded-xl font-semibold transition shadow-sm">
                    Logout
                </button>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
            <!-- SEARCH BAR & BADGE RESPONSIF -->
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center mb-6 gap-3 sm:gap-4">
                <div class="flex items-center justify-between md:justify-start gap-3">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Koleksi Buku</h2>
                    <span class="bg-brand-lightTeal text-brand-teal font-bold text-xs px-3 py-1 rounded-full border border-brand-teal/20">
                        Total: <?= $total_buku; ?>
                    </span>
                </div>

                <form id="formSearch" action="" method="GET" class="w-full md:w-80">
                    <input type="text" id="inputSearch" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari judul atau penulis..." class="w-full p-2.5 px-4 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-teal focus:border-brand-teal transition" autocomplete="off">
                </form>
            </div>

            <!-- KATALOG BUKU GRID MOBILE-FRIENDLY -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-6">
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
                    <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col border border-slate-200 group">
                        <!-- COVER ASPECT RATIO 3:4 -->
                        <div class="relative w-full aspect-[3/4] bg-slate-100 overflow-hidden flex items-center justify-center">
                            <img src="<?= $gambar_cover; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-2 right-2 sm:top-2.5 sm:right-2.5">
                                <?php if ($b['status'] == 'tersedia'): ?>
                                    <span class="bg-brand-teal/90 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full shadow-sm">Tersedia</span>
                                <?php else: ?>
                                    <span class="bg-brand-red/90 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full shadow-sm">Dipinjam</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- INFORMASI JUDUL & PENULIS -->
                        <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-xs sm:text-sm text-slate-800 leading-snug line-clamp-2 group-hover:text-brand-orange transition-colors"><?= htmlspecialchars($b['judul']); ?></h3>
                                <p class="text-[11px] sm:text-xs text-slate-500 mt-1 line-clamp-1">Penulis: <span class="font-medium text-slate-700"><?= htmlspecialchars($b['penulis']); ?></span></p>
                            </div>
                            
                            <div class="pt-3">
                                <button onclick="openModal('modal-<?= $b['id']; ?>')" class="w-full bg-brand-teal hover:bg-emerald-600 text-white text-xs py-1.5 sm:py-2 rounded-lg sm:rounded-xl font-semibold transition shadow-sm">
                                    Detail
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL DETAIL BUKU RESPONSIF -->
                    <div id="modal-<?= $b['id']; ?>" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
                        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-xl relative border border-slate-100 max-h-[90vh] flex flex-col justify-between">
                            <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="absolute top-3 right-4 text-slate-400 hover:text-slate-600 font-bold text-xl z-10">✕</button>

                            <div class="overflow-y-auto pr-1 flex-1 space-y-4">
                                <div class="flex flex-col sm:flex-row gap-4 sm:gap-5 items-center sm:items-start pt-2">
                                    <div class="w-28 sm:w-32 aspect-[3/4] bg-slate-100 rounded-xl overflow-hidden shadow-md border border-slate-100 flex-shrink-0">
                                        <img src="<?= $gambar_cover; ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div class="space-y-1.5 text-center sm:text-left flex-1">
                                        <h3 class="font-bold text-lg sm:text-xl text-slate-800 leading-tight"><?= htmlspecialchars($b['judul']); ?></h3>
                                        <p class="text-xs text-slate-500">Penulis: <span class="font-semibold text-slate-700"><?= htmlspecialchars($b['penulis']); ?></span></p>
                                        
                                        <div class="pt-2 text-left">
                                            <h4 class="font-bold text-[11px] text-brand-teal uppercase tracking-wider mb-1">Sinopsis Buku</h4>
                                            <p class="text-xs text-slate-600 leading-relaxed max-h-40 overflow-y-auto bg-slate-50 p-3 rounded-xl border border-slate-100"><?= nl2br(htmlspecialchars($b['sinopsis'] ?? 'Sinopsis belum tersedia.')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right border-t border-slate-100 pt-3 flex-shrink-0">
                                <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-4 py-2 rounded-xl font-semibold transition">Tutup</button>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="col-span-full bg-white p-8 sm:p-12 rounded-2xl text-center text-slate-400 border border-slate-200 shadow-sm">
                        <p class="text-xs sm:text-sm">Buku tidak ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL NOTIFIKASI RESPONSIF -->
    <div id="modal-notifikasi" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 space-y-4 shadow-xl relative border border-slate-100 max-h-[85vh] flex flex-col">
            <button onclick="closeModal('modal-notifikasi')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 flex-shrink-0">
                <span class="text-lg">🔔</span>
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
                    <p class="text-center text-xs text-slate-400 py-8">Tidak ada notifikasi peringatan saat ini.</p>
                <?php endif; ?>
            </div>

            <div class="text-right border-t border-slate-100 pt-3 flex-shrink-0">
                <button onclick="closeModal('modal-notifikasi')" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-4 py-2 rounded-xl font-semibold transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL RIWAYAT RESPONSIF DAN SCROLLABLE TABEL -->
    <div id="modal-riwayat" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-5 sm:p-6 space-y-4 shadow-xl relative border border-slate-100 max-h-[90vh] flex flex-col">
            <button onclick="closeModal('modal-riwayat')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pr-6 flex-shrink-0">
                <h3 class="font-bold text-lg sm:text-xl text-slate-800">Riwayat Peminjaman</h3>
                <input type="text" id="searchRiwayat" onkeyup="filterRiwayat()" placeholder="Cari judul..." class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs w-full sm:w-56 focus:outline-none focus:ring-2 focus:ring-brand-teal transition">
            </div>

            <div class="overflow-x-auto overflow-y-auto border border-slate-200 rounded-xl flex-1 min-h-[200px]">
                <table class="w-full text-left text-xs whitespace-nowrap sm:whitespace-normal">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider sticky top-0 bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="p-3">Judul Buku</th>
                            <th class="p-3">Penulis</th>
                            <th class="p-3">Pinjam</th>
                            <th class="p-3">Kembali</th>
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
                                $tgl_kembali = !empty($r['tanggal_kembali']) ? date('d-m-Y', strtotime($r['tanggal_kembali'])) : '<span class="text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded text-[10px] border border-amber-200/80">Belum Kembali</span>';
                        ?>
                            <tr class="row-riwayat hover:bg-slate-50 transition">
                                <td class="p-3 font-semibold text-slate-800 cell-judul"><?= htmlspecialchars($r['judul']); ?></td>
                                <td class="p-3 text-slate-600"><?= htmlspecialchars($r['penulis']); ?></td>
                                <td class="p-3 text-slate-500"><?= date('d-m-Y', strtotime($r['tanggal_pinjam'])); ?></td>
                                <td class="p-3 text-slate-600"><?= $tgl_kembali; ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400 text-xs">Belum ada riwayat peminjaman.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-right border-t border-slate-100 pt-3 flex-shrink-0">
                <button onclick="closeModal('modal-riwayat')" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-4 py-2 rounded-xl font-semibold transition">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.getElementById(id).classList.add('flex');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.getElementById(id).classList.remove('flex');
        }

        function filterRiwayat() {
            let input = document.getElementById('searchRiwayat').value.toLowerCase();
            let rows = document.querySelectorAll('.row-riwayat');
            rows.forEach(row => {
                let judul = row.querySelector('.cell-judul').textContent.toLowerCase();
                row.style.display = judul.includes(input) ? "" : "none";
            });
        }

        // FUNGSI KONFIRMASI LOGOUT
        function confirmLogout() {
            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: 'Anda akan diarahkan kembali ke halaman login.',
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

        // Buka Modal Notifikasi otomatis jika ada pesan unread
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($unread_count > 0): ?>
                openModal('modal-notifikasi');
            <?php endif; ?>
        });

        // Auto-search saat diketik
        let searchTimer;
        const inputSearch = document.getElementById('inputSearch');
        const formSearch = document.getElementById('formSearch');

        if (inputSearch && formSearch) {
            inputSearch.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    formSearch.submit();
                }, 400);
            });

            // Menjaga kursor tetap di akhir teks
            const val = inputSearch.value;
            inputSearch.value = '';
            inputSearch.focus();
            inputSearch.value = val;
        }
    </script>
</body>
</html>