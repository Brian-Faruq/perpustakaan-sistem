<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            orange: '#F3811E', // Warna oranye utama logo
                            red: '#E84524',    // Warna merah/oranye tua logo ("SEKOLAH")
                            teal: '#1DB996',   // Warna hijau toska logo ("MPIAN")
                            blue: '#1F3C88',   // Warna biru navy logo
                            lightTeal: '#E6F8F4',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

    <!-- NAVBAR (Menggunakan Gradient Oranye ke Merah sesuai pita logo) -->
    <nav class="bg-gradient-to-r from-brand-orange to-brand-red text-white px-6 py-4 flex justify-between items-center shadow-md sticky top-0 z-40">
        <h1 class="font-bold text-xl tracking-wide flex items-center gap-2">
            <span>📚</span> Katalog Perpustakaan
        </h1>
        <div class="flex items-center space-x-3">
            
            <!-- TOMBOL RIWAYAT PEMINJAMAN -->
            <button onclick="openModal('modal-riwayat')" class="bg-black/20 hover:bg-black/30 text-white text-xs px-3.5 py-2 rounded-xl font-semibold transition border border-white/20 flex items-center gap-1.5 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat
            </button>

            <!-- IKON LONCENG NOTIFIKASI -->
            <button onclick="openModal('modal-notifikasi')" class="relative p-2 bg-black/20 hover:bg-black/30 rounded-xl border border-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                
                <!-- TITIK MERAH (MUNCIK JIKA ADA UNREAD NOTIF) -->
                <?php if ($unread_count > 0): ?>
                    <span class="absolute -top-1 -right-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-teal opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-brand-blue text-[10px] font-bold text-white items-center justify-center">
                            <?= $unread_count; ?>
                        </span>
                    </span>
                <?php endif; ?>
            </button>

            <span class="text-sm hidden sm:inline pl-2 border-l border-white/30">Halo, <b><?= htmlspecialchars($nama_user); ?></b></span>
            <a href="login.php" class="bg-brand-blue hover:bg-slate-900 text-xs px-3.5 py-2 rounded-xl font-semibold transition shadow-sm">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-6">
        <!-- SEARCH BAR & BADGE -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Daftar Buku Koleksi</h2>
                <span class="bg-brand-lightTeal text-brand-teal font-bold text-xs px-3 py-1.5 rounded-full border border-brand-teal/20">
                    Total: <?= $total_buku; ?>
                </span>
            </div>

            <form id="formSearch" action="" method="GET" class="w-full md:w-80">
                <input type="text" id="inputSearch" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari judul atau penulis..." class="w-full p-2.5 px-4 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-teal focus:border-brand-teal transition" autocomplete="off">
            </form>
        </div>

        <!-- KATALOG BUKU -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
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
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between border border-slate-200 group">
                    <div>
                        <div class="relative h-52 bg-slate-100 overflow-hidden flex items-center justify-center">
                            <img src="<?= $gambar_cover; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-3 right-3">
                                <?php if ($b['status'] == 'tersedia'): ?>
                                    <span class="bg-brand-teal text-white text-[11px] font-bold px-3 py-1 rounded-full shadow-sm">Tersedia</span>
                                <?php else: ?>
                                    <span class="bg-brand-red text-white text-[11px] font-bold px-3 py-1 rounded-full shadow-sm">Dipinjam</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-5">
                            <h3 class="font-bold text-base text-slate-800 leading-snug line-clamp-1 group-hover:text-brand-orange transition-colors"><?= htmlspecialchars($b['judul']); ?></h3>
                            <p class="text-xs text-slate-500 mt-1">Penulis: <span class="font-medium text-slate-700"><?= htmlspecialchars($b['penulis']); ?></span></p>
                        </div>
                    </div>

                    <div class="p-5 pt-0">
                        <button onclick="openModal('modal-<?= $b['id']; ?>')" class="w-full bg-brand-teal hover:bg-emerald-600 text-white text-xs py-2.5 rounded-xl font-semibold transition shadow-sm">
                            Lihat Detail
                        </button>
                    </div>
                </div>

                <!-- MODAL DETAIL BUKU -->
                <div id="modal-<?= $b['id']; ?>" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
                    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl relative border border-slate-100">
                        <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

                        <div class="flex flex-col sm:flex-row gap-5 items-start pt-2">
                            <img src="<?= $gambar_cover; ?>" class="w-32 h-44 object-cover rounded-xl shadow-md border border-slate-100 flex-shrink-0 self-center sm:self-start">
                            <div class="space-y-2 flex-1">
                                <h3 class="font-bold text-xl text-slate-800 leading-tight"><?= htmlspecialchars($b['judul']); ?></h3>
                                <p class="text-xs text-slate-500">Penulis: <span class="font-semibold text-slate-700"><?= htmlspecialchars($b['penulis']); ?></span></p>
                                <div class="pt-2">
                                    <h4 class="font-bold text-[11px] text-brand-teal uppercase tracking-wider mb-1">Sinopsis Buku</h4>
                                    <p class="text-xs text-slate-600 leading-relaxed max-h-36 overflow-y-auto pr-1"><?= nl2br(htmlspecialchars($b['sinopsis'] ?? '')); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="text-right border-t border-slate-100 pt-3">
                            <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-4 py-2 rounded-xl font-semibold transition">Tutup</button>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="col-span-full bg-white p-12 rounded-2xl text-center text-slate-400 border border-slate-200 shadow-sm">
                    <p class="text-sm">Buku tidak ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL NOTIFIKASI PERINGATAN -->
    <div id="modal-notifikasi" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl relative border border-slate-100">
            <button onclick="closeModal('modal-notifikasi')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="text-xl">🔔</span>
                <h3 class="font-bold text-lg text-slate-800">Notifikasi Peringatan</h3>
            </div>

            <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
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
                    <p class="text-center text-xs text-slate-400 py-6">Tidak ada notifikasi peringatan saat ini.</p>
                <?php endif; ?>
            </div>

            <div class="text-right border-t border-slate-100 pt-3">
                <button onclick="closeModal('modal-notifikasi')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-4 py-2 rounded-xl font-semibold transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL RIWAYAT PEMINJAMAN -->
    <div id="modal-riwayat" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 space-y-4 shadow-xl relative border border-slate-100">
            <button onclick="closeModal('modal-riwayat')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pr-8">
                <h3 class="font-bold text-xl text-slate-800">Riwayat Peminjaman Saya</h3>
                <input type="text" id="searchRiwayat" onkeyup="filterRiwayat()" placeholder="Cari judul buku..." class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs w-full sm:w-56 focus:outline-none focus:ring-2 focus:ring-brand-teal transition">
            </div>

            <div class="overflow-x-auto max-h-80 overflow-y-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider sticky top-0 bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="p-3">Judul Buku</th>
                            <th class="p-3">Penulis</th>
                            <th class="p-3">Tanggal Pinjam</th>
                            <th class="p-3">Tanggal Kembali</th>
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
                                $tgl_kembali = !empty($r['tanggal_kembali']) ? date('d-m-Y', strtotime($r['tanggal_kembali'])) : '<span class="text-amber-700 font-semibold bg-amber-50 px-2.5 py-1 rounded-md text-[11px] border border-amber-200/80">Belum Kembali</span>';
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

            <div class="text-right border-t border-slate-100 pt-3">
                <button onclick="closeModal('modal-riwayat')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-4 py-2 rounded-xl font-semibold transition">Tutup</button>
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

        // Otomatis Buka Modal Notifikasi jika ada notifikasi belum dibaca saat login
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($unread_count > 0): ?>
                openModal('modal-notifikasi');
            <?php endif; ?>
        });

        // Auto-search saat ngetik (dengan delay 400ms)
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

            // Menjaga kursor tetap di akhir teks setelah page reload
            const val = inputSearch.value;
            inputSearch.value = '';
            inputSearch.focus();
            inputSearch.value = val;
        }
    </script>
</body>
</html>