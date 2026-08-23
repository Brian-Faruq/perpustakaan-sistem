<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';

// Hitung Total Buku
$q_total_buku = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku");
$d_total_buku = mysqli_fetch_assoc($q_total_buku);
$total_buku = $d_total_buku['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Perpustakaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">

    <nav class="bg-emerald-700 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="font-bold text-xl">Katalog Perpustakaan</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm">Selamat Datang, <b><?= $_SESSION['nama']; ?></b></span>
            <a href="login.php" class="bg-red-600 hover:bg-red-700 text-xs px-3 py-2 rounded-lg font-semibold transition">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-6">
        
        <!-- HEADER, SEARCH BAR & TOTAL BUKU BADGE -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <!-- JUDUL & BADGE TOTAL BUKU -->
            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
                <h2 class="text-2xl font-bold text-slate-800">Daftar Buku Koleksi</h2>
                <span class="bg-emerald-100 text-emerald-800 text-xs font-extrabold px-3 py-2 rounded-lg border border-emerald-200 whitespace-nowrap">
                    Total Buku: <?= $total_buku; ?>
                </span>
            </div>

            <form action="" method="GET" class="w-full md:w-80">
                <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari judul buku atau penulis..." class="w-full p-2.5 px-4 border border-slate-200 rounded-xl text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </form>
        </div>

        <!-- KATALOG GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php
            $query_str = "SELECT * FROM buku";
            if (!empty($search)) {
                $query_str .= " WHERE judul LIKE '%$search%' OR penulis LIKE '%$search%'";
            }
            $query_str .= " ORDER BY id DESC";

            $query_buku = mysqli_query($koneksi, $query_str);

            if (mysqli_num_rows($query_buku) > 0):
                while ($b = mysqli_fetch_assoc($query_buku)):
                    $gambar_cover = !empty($b['cover']) && file_exists('uploads/' . $b['cover']) ? 'uploads/' . $b['cover'] : 'https://via.placeholder.com/300x400?text=No+Cover';
            ?>
                <div class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden flex flex-col justify-between border border-slate-200">
                    <div>
                        <!-- Cover Buku di Paling Atas Card -->
                        <div class="relative h-52 bg-slate-100 overflow-hidden">
                            <img src="<?= $gambar_cover; ?>" alt="<?= $b['judul']; ?>" class="w-full h-full object-cover">
                            <div class="absolute top-3 right-3">
                                <?php if ($b['status'] == 'tersedia'): ?>
                                    <span class="bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow">Tersedia</span>
                                <?php else: ?>
                                    <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow">Dipinjam</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-lg text-slate-800 leading-snug line-clamp-1"><?= $b['judul']; ?></h3>
                            <p class="text-xs text-slate-500 mt-1">Penulis: <span class="font-semibold text-slate-700"><?= $b['penulis']; ?></span></p>
                        </div>
                    </div>

                    <div class="p-4 pt-0">
                        <button onclick="openModal('modal-<?= $b['id']; ?>')" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm py-2.5 rounded-xl font-medium transition">
                            Lihat Detail
                        </button>
                    </div>
                </div>

                <!-- MODAL OVERLAY -->
                <div id="modal-<?= $b['id']; ?>" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 z-50">
                    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative">
                        <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>

                        <div class="flex flex-col sm:flex-row gap-4 items-start pt-2">
                            <img src="<?= $gambar_cover; ?>" alt="<?= $b['judul']; ?>" class="w-28 h-40 object-cover rounded-xl shadow-md border flex-shrink-0 self-center sm:self-start">
                            
                            <div class="space-y-2 flex-1">
                                <h3 class="font-bold text-xl text-slate-800 leading-tight"><?= $b['judul']; ?></h3>
                                <p class="text-xs text-slate-500">Penulis: <span class="font-semibold text-slate-700"><?= $b['penulis']; ?></span></p>
                                
                                <div>
                                    <h4 class="font-semibold text-xs text-slate-400 uppercase tracking-wider mb-1">Sinopsis Buku</h4>
                                    <p class="text-xs text-slate-600 leading-relaxed max-h-36 overflow-y-auto pr-1"><?= nl2br($b['sinopsis']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="text-right border-t pt-3">
                            <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm px-4 py-2 rounded-xl font-medium">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="col-span-full bg-white p-8 rounded-2xl text-center text-slate-400 border border-slate-200">
                    Buku tidak ditemukan.
                </div>
            <?php
            endif; 
            ?>
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
    </script>

</body>
</html>