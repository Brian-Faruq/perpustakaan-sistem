<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku Siswa</title>
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
        
        <!-- HEADER & SEARCH BAR -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-2xl font-bold text-slate-800">Daftar Buku Koleksi</h2>

            <form action="" method="GET" class="w-full md:w-80">
                <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari judul buku atau penulis..." class="w-full p-2.5 px-4 border rounded-xl text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </form>
        </div>

        <!-- KATALOG GRID -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $query_str = "SELECT * FROM buku";
            if (!empty($search)) {
                $query_str .= " WHERE judul LIKE '%$search%' OR penulis LIKE '%$search%'";
            }
            $query_str .= " ORDER BY id DESC";

            $query_buku = mysqli_query($koneksi, $query_str);

            if (mysqli_num_rows($query_buku) > 0):
                while ($b = mysqli_fetch_assoc($query_buku)):
            ?>
                <div class="bg-white p-5 rounded-2xl shadow hover:shadow-lg transition flex flex-col justify-between border border-slate-200">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg text-slate-800 leading-snug"><?= $b['judul']; ?></h3>
                            <?php if ($b['status'] == 'tersedia'): ?>
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-full">Tersedia</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-1 rounded-full">Dipinjam</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">Penulis: <span class="font-semibold text-slate-700"><?= $b['penulis']; ?></span></p>
                    </div>

                    <button onclick="openModal('modal-<?= $b['id']; ?>')" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm py-2 rounded-xl font-medium transition">
                        Lihat Sinopsis
                    </button>
                </div>

                <!-- MODAL POP-UP SINOPSIS -->
                <div id="modal-<?= $b['id']; ?>" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 z-50">
                    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
                        <div class="flex justify-between items-center border-b pb-3">
                            <h3 class="font-bold text-xl text-slate-800"><?= $b['judul']; ?></h3>
                            <button onclick="closeModal('modal-<?= $b['id']; ?>')" class="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-3">Penulis: <span class="font-semibold text-slate-700"><?= $b['penulis']; ?></span></p>
                            <h4 class="font-semibold text-sm text-slate-800 mb-1">Sinopsis Buku:</h4>
                            <p class="text-sm text-slate-600 leading-relaxed max-h-60 overflow-y-auto"><?= nl2br($b['sinopsis']); ?></p>
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
                <div class="col-span-3 text-center py-12 text-slate-500">
                    Buku yang kamu cari tidak ditemukan.
                </div>
            <?php endif; ?>
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