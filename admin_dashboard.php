<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$msg = '';
$msg_type = 'success';

// Aksi 1: Registrasi Siswa Baru
if (isset($_POST['tambah_siswa'])) {
    $nomor_kartu = mysqli_real_escape_string($koneksi, trim($_POST['nomor_kartu']));
    $nama        = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $kelas       = mysqli_real_escape_string($koneksi, trim($_POST['kelas']));
    $pass_hash   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek_kartu = mysqli_query($koneksi, "SELECT id FROM siswa WHERE nomor_kartu='$nomor_kartu'");
    if (mysqli_num_rows($cek_kartu) > 0) {
        $msg = "Gagal! Nomor Kartu ($nomor_kartu) sudah terdaftar.";
        $msg_type = 'error';
    } else {
        $sql = "INSERT INTO siswa (nomor_kartu, nama, kelas, password) VALUES ('$nomor_kartu', '$nama', '$kelas', '$pass_hash')";
        if (mysqli_query($koneksi, $sql)) {
            $msg = "Siswa berhasil terdaftar!";
        } else {
            $msg = "Terjadi kesalahan saat menyimpan data.";
            $msg_type = 'error';
        }
    }
}

// Aksi 2: Edit Siswa
if (isset($_POST['edit_siswa'])) {
    $id_siswa    = intval($_POST['id_siswa']);
    $nomor_kartu = mysqli_real_escape_string($koneksi, trim($_POST['nomor_kartu']));
    $nama        = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $kelas       = mysqli_real_escape_string($koneksi, trim($_POST['kelas']));

    if (!empty($_POST['password'])) {
        $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE siswa SET nomor_kartu='$nomor_kartu', nama='$nama', kelas='$kelas', password='$pass_hash' WHERE id=$id_siswa";
    } else {
        $sql = "UPDATE siswa SET nomor_kartu='$nomor_kartu', nama='$nama', kelas='$kelas' WHERE id=$id_siswa";
    }

    if (mysqli_query($koneksi, $sql)) {
        $msg = "Data siswa berhasil diperbarui!";
    } else {
        $msg = "Gagal memperbarui data siswa.";
        $msg_type = 'error';
    }
}

// Aksi 3: Hapus Siswa
if (isset($_POST['hapus_siswa'])) {
    $id_siswa = intval($_POST['id_siswa']);
    if (mysqli_query($koneksi, "DELETE FROM siswa WHERE id = $id_siswa")) {
        $msg = "Data siswa berhasil dihapus!";
    } else {
        $msg = "Gagal menghapus siswa. Pastikan siswa tidak memiliki riwayat transaksi aktif.";
        $msg_type = 'error';
    }
}

// Aksi 4: Tambah Koleksi Buku
if (isset($_POST['tambah_buku'])) {
    $judul    = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $penulis  = mysqli_real_escape_string($koneksi, trim($_POST['penulis']));
    $sinopsis = mysqli_real_escape_string($koneksi, trim($_POST['sinopsis']));
    $nama_cover = 'default_cover.jpg';

    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $nama_cover = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['cover']['tmp_name'], 'uploads/' . $nama_cover);
    }

    $sql = "INSERT INTO buku (judul, penulis, sinopsis, cover) VALUES ('$judul', '$penulis', '$sinopsis', '$nama_cover')";
    if (mysqli_query($koneksi, $sql)) {
        $msg = "Buku baru berhasil ditambahkan!";
    }
}

// Aksi 5: Edit Buku
if (isset($_POST['edit_buku'])) {
    $id_buku  = intval($_POST['id_buku']);
    $judul    = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $penulis  = mysqli_real_escape_string($koneksi, trim($_POST['penulis']));
    $sinopsis = mysqli_real_escape_string($koneksi, trim($_POST['sinopsis']));

    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $nama_cover = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['cover']['tmp_name'], 'uploads/' . $nama_cover);
        $sql = "UPDATE buku SET judul='$judul', penulis='$penulis', sinopsis='$sinopsis', cover='$nama_cover' WHERE id=$id_buku";
    } else {
        $sql = "UPDATE buku SET judul='$judul', penulis='$penulis', sinopsis='$sinopsis' WHERE id=$id_buku";
    }

    if (mysqli_query($koneksi, $sql)) {
        $msg = "Data buku berhasil diperbarui!";
    } else {
        $msg = "Gagal memperbarui data buku.";
        $msg_type = 'error';
    }
}

// Aksi 6: Hapus Buku
if (isset($_POST['hapus_buku'])) {
    $id_buku = intval($_POST['id_buku']);
    if (mysqli_query($koneksi, "DELETE FROM buku WHERE id = $id_buku")) {
        $msg = "Buku berhasil dihapus dari sistem!";
    } else {
        $msg = "Gagal menghapus buku. Pastikan buku tidak sedang dipinjam.";
        $msg_type = 'error';
    }
}

// Aksi 7: Transaksi Pinjam Buku
if (isset($_POST['pinjam_buku'])) {
    $nomor_kartu = mysqli_real_escape_string($koneksi, trim($_POST['nomor_kartu_pinjam']));
    $buku_id     = intval($_POST['buku_id']);
    $tgl_pinjam  = date('Y-m-d');

    $q_siswa = mysqli_query($koneksi, "SELECT id, nama FROM siswa WHERE nomor_kartu='$nomor_kartu'");
    if (mysqli_num_rows($q_siswa) > 0) {
        $d_siswa  = mysqli_fetch_assoc($q_siswa);
        $siswa_id = $d_siswa['id'];

        $q_cek = mysqli_query($koneksi, "SELECT id FROM peminjaman WHERE siswa_id='$siswa_id' AND status_transaksi='berjalan'");
        if (mysqli_num_rows($q_cek) > 0) {
            $msg = "Gagal! Siswa " . $d_siswa['nama'] . " masih meminjam buku lain yang belum dikembalikan.";
            $msg_type = 'error';
        } else {
            mysqli_query($koneksi, "INSERT INTO peminjaman (siswa_id, buku_id, tanggal_pinjam) VALUES ('$siswa_id', '$buku_id', '$tgl_pinjam')");
            mysqli_query($koneksi, "UPDATE buku SET status='dipinjam' WHERE id='$buku_id'");
            $msg = "Transaksi Peminjaman untuk " . $d_siswa['nama'] . " Berhasil!";
        }
    } else {
        $msg = "Kartu siswa tidak terdaftar di sistem!";
        $msg_type = 'error';
    }
}

// Aksi 8: Pengembalian Buku
if (isset($_POST['kembalikan_buku'])) {
    $id_pinjam = intval($_POST['id_peminjaman']);
    $q_get = mysqli_query($koneksi, "SELECT buku_id FROM peminjaman WHERE id = $id_pinjam");
    if ($data = mysqli_fetch_assoc($q_get)) {
        $buku_id = $data['buku_id'];
        $tgl_sekarang = date('Y-m-d');

        mysqli_query($koneksi, "UPDATE peminjaman SET status_transaksi = 'selesai', tanggal_kembali = '$tgl_sekarang' WHERE id = $id_pinjam");
        mysqli_query($koneksi, "UPDATE buku SET status = 'tersedia' WHERE id = $buku_id");

        $msg = "Buku berhasil dikembalikan!";
    }
}

// Aksi 9: Hapus Riwayat
if (isset($_POST['hapus_riwayat'])) {
    $id_riwayat = intval($_POST['id_riwayat']);
    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id = $id_riwayat");
    $msg = "Data riwayat berhasil dihapus!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Perpustakaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">

    <nav class="bg-slate-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="font-bold text-xl text-blue-400">Dashboard Admin Perpustakaan</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm">Petugas: <b><?= $_SESSION['nama']; ?></b></span>
            <a href="login.php" class="bg-red-600 hover:bg-red-700 text-xs px-3 py-2 rounded-lg font-semibold transition">Logout</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-6 space-y-8">

        <?php if ($msg): ?>
            <div class="<?= $msg_type == 'success' ? 'bg-emerald-100 text-emerald-800 border-emerald-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border p-4 rounded-xl shadow-sm font-medium">
                <?= $msg; ?>
            </div>
        <?php endif; ?>

        <!-- GRID CONTROL PANEL -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- 1. Tambah Siswa -->
            <div class="bg-white p-5 rounded-xl shadow border border-slate-200">
                <h3 class="font-bold text-lg mb-4 text-slate-800 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">1</span>
                    Registrasi Siswa (Tap Kartu)
                </h3>
                <form action="" method="POST" class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Nomor Kartu (Tap di sini):</label>
                        <input type="text" name="nomor_kartu" required placeholder="Tap kartu siswa..." class="w-full p-2 border rounded-lg text-sm bg-yellow-50 focus:bg-white border-yellow-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Nama Siswa:</label>
                        <input type="text" name="nama" required class="w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Kelas:</label>
                        <input type="text" name="kelas" required class="w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Password Akun Siswa:</label>
                        <input type="password" name="password" required class="w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" name="tambah_siswa" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg text-sm font-bold transition">Simpan Data Siswa</button>
                </form>
            </div>

            <!-- 2. Tambah Buku -->
            <div class="bg-white p-5 rounded-xl shadow border border-slate-200">
                <h3 class="font-bold text-lg mb-4 text-slate-800 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">2</span>
                    Tambah Koleksi Buku
                </h3>
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Judul Buku:</label>
                        <input type="text" name="judul" required class="w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Penulis:</label>
                        <input type="text" name="penulis" required class="w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Sinopsis Singkat:</label>
                        <textarea name="sinopsis" rows="3" required class="w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Cover Buku (JPG/PNG):</label>
                        <input type="file" name="cover" accept="image/*" class="w-full p-1.5 border rounded-lg text-sm bg-slate-50 focus:outline-none">
                    </div>
                    <button type="submit" name="tambah_buku" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-bold transition">Tambah Buku</button>
                </form>
            </div>

            <!-- 3. Transaksi Pinjam Buku -->
            <div class="bg-white p-5 rounded-xl shadow border border-slate-200">
                <h3 class="font-bold text-lg mb-4 text-slate-800 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">3</span>
                    Transaksi Pinjam Buku
                </h3>
                <form action="" method="POST" class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Tap Kartu Peminjam:</label>
                        <input type="text" name="nomor_kartu_pinjam" required placeholder="Tap kartu siswa..." class="w-full p-2 border rounded-lg text-sm bg-yellow-50 focus:bg-white border-yellow-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Pilih Buku (Tersedia):</label>
                        <select name="buku_id" required class="w-full p-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Buku --</option>
                            <?php
                            $q_buku_ada = mysqli_query($koneksi, "SELECT * FROM buku WHERE status='tersedia' ORDER BY judul ASC");
                            while ($b = mysqli_fetch_assoc($q_buku_ada)):
                            ?>
                                <option value="<?= $b['id']; ?>"><?= $b['judul']; ?> — (<?= $b['penulis']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="pinjam_buku" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-bold transition">Proses Peminjaman</button>
                </form>
            </div>

        </div>

        <!-- NAVBAR / MENU TAB TOMBOL KONTROL TABEL -->
        <div class="flex flex-wrap gap-3 border-b border-slate-200 pb-4">
            <button onclick="openTab('pinjam-tab', this)" class="tab-btn bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow transition">
                📋 Peminjaman Aktif
            </button>
            <button onclick="openTab('riwayat-tab', this)" class="tab-btn bg-white hover:bg-slate-200 text-slate-700 border border-slate-200 px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition">
                📜 Riwayat Peminjaman
            </button>
            <button onclick="openTab('siswa-tab', this)" class="tab-btn bg-white hover:bg-slate-200 text-slate-700 border border-slate-200 px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition">
                👨‍🎓 Daftar Siswa
            </button>
            <button onclick="openTab('buku-tab', this)" class="tab-btn bg-white hover:bg-slate-200 text-slate-700 border border-slate-200 px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition">
                📚 Daftar Buku
            </button>
        </div>

        <!-- TABEL DATA PEMINJAMAN AKTIF -->
        <div id="pinjam-tab" class="tab-content bg-white rounded-2xl shadow p-6 border border-slate-200">
            
            <?php
            $q_peminjaman = mysqli_query($koneksi, "
                SELECT p.id AS id_pinjam, s.nama, s.kelas, b.judul, p.tanggal_pinjam 
                FROM peminjaman p
                JOIN siswa s ON p.siswa_id = s.id
                JOIN buku b ON p.buku_id = b.id
                WHERE p.status_transaksi = 'berjalan'
                ORDER BY p.tanggal_pinjam DESC
            ");
            $total_pinjam = mysqli_num_rows($q_peminjaman);
            ?>

            <!-- HEADER: JUDUL & BADGE TOTAL -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-slate-800">Daftar Peminjaman Aktif</h2>
                <span class="bg-indigo-100 text-indigo-800 text-xs font-extrabold px-3 py-2 rounded-lg border border-indigo-200 whitespace-nowrap">
                    Total: <?= $total_pinjam; ?>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700 font-semibold uppercase text-xs">
                        <tr>
                            <th class="p-3 rounded-l-lg">Nama Siswa</th>
                            <th class="p-3">Kelas</th>
                            <th class="p-3">Judul Buku</th>
                            <th class="p-3">Tanggal Pinjam</th>
                            <th class="p-3 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        if ($total_pinjam > 0):
                            while ($p = mysqli_fetch_assoc($q_peminjaman)):
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3 font-medium text-slate-800"><?= $p['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $p['kelas']; ?></td>
                                <td class="p-3 text-slate-700 font-medium"><?= $p['judul']; ?></td>
                                <td class="p-3 text-slate-600"><?= date('d-m-Y', strtotime($p['tanggal_pinjam'])); ?></td>
                                <td class="p-3 text-center">
                                    <form action="" method="POST" onsubmit="return confirm('Yakin buku ini sudah dikembalikan?');">
                                        <input type="hidden" name="id_peminjaman" value="<?= $p['id_pinjam']; ?>">
                                        <button type="submit" name="kembalikan_buku" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                                            Kembalikan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-400 text-sm">Tidak ada peminjaman aktif saat ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL RIWAYAT PEMINJAMAN (+ SEARCH BAR NAMA SISWA) -->
        <div id="riwayat-tab" class="tab-content hidden bg-white rounded-2xl shadow p-6 border border-slate-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <h2 class="text-lg font-bold text-slate-800">Daftar Riwayat Peminjaman</h2>
                
                <!-- SEARCH BAR NAMA SISWA -->
                <div class="w-full sm:w-auto">
                    <input type="text" id="searchRiwayat" onkeyup="filterRiwayat()" placeholder="Cari nama siswa..." class="w-full sm:w-64 p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="tableRiwayat">
                    <thead class="bg-slate-100 text-slate-700 font-semibold uppercase text-xs">
                        <tr>
                            <th class="p-3 rounded-l-lg">Nama Siswa</th>
                            <th class="p-3">Kelas</th>
                            <th class="p-3">Judul Buku</th>
                            <th class="p-3">Tanggal Pinjam</th>
                            <th class="p-3">Tanggal Kembali</th>
                            <th class="p-3 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $q_riwayat = mysqli_query($koneksi, "
                            SELECT p.id AS id_riwayat, s.nama, s.kelas, b.judul, p.tanggal_pinjam, p.tanggal_kembali 
                            FROM peminjaman p
                            JOIN siswa s ON p.siswa_id = s.id
                            JOIN buku b ON p.buku_id = b.id
                            WHERE p.status_transaksi = 'selesai'
                            ORDER BY p.tanggal_kembali DESC
                        ");

                        if (mysqli_num_rows($q_riwayat) > 0):
                            while ($r = mysqli_fetch_assoc($q_riwayat)):
                        ?>
                            <tr class="row-riwayat hover:bg-slate-50 transition">
                                <td class="p-3 font-medium text-slate-800 cell-nama-riwayat"><?= $r['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $r['kelas']; ?></td>
                                <td class="p-3 text-slate-700 font-medium"><?= $r['judul']; ?></td>
                                <td class="p-3 text-slate-600"><?= date('d-m-Y', strtotime($r['tanggal_pinjam'])); ?></td>
                                <td class="p-3 text-emerald-600 font-medium"><?= date('d-m-Y', strtotime($r['tanggal_kembali'])); ?></td>
                                <td class="p-3 text-center">
                                    <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus riwayat ini?');">
                                        <input type="hidden" name="id_riwayat" value="<?= $r['id_riwayat']; ?>">
                                        <button type="submit" name="hapus_riwayat" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr id="emptyRiwayatRow">
                                <td colspan="6" class="p-6 text-center text-slate-400 text-sm">Belum ada riwayat peminjaman.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL DAFTAR SISWA (ADA AKSI EDIT & HAPUS + SEARCH BAR) -->
        <div id="siswa-tab" class="tab-content hidden bg-white rounded-2xl shadow p-6 border border-slate-200">
            <?php
            $q_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama ASC");
            $total_siswa = mysqli_num_rows($q_siswa);
            ?>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <h2 class="text-lg font-bold text-slate-800">Daftar Siswa Terdaftar</h2>
                
                <!-- SEARCH BAR & TOTAL BADGE -->
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <input type="text" id="searchSiswa" onkeyup="filterSiswa()" placeholder="Cari nama siswa..." class="w-full sm:w-64 p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="bg-blue-100 text-blue-800 text-xs font-extrabold px-3 py-2 rounded-lg border border-blue-200 whitespace-nowrap">
                        Total: <?= $total_siswa; ?>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="tableSiswa">
                    <thead class="bg-slate-100 text-slate-700 font-semibold uppercase text-xs">
                        <tr>
                            <th class="p-3 rounded-l-lg">Nomor Kartu</th>
                            <th class="p-3">Nama Siswa</th>
                            <th class="p-3">Kelas</th>
                            <th class="p-3 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        if ($total_siswa > 0):
                            while ($s = mysqli_fetch_assoc($q_siswa)):
                        ?>
                            <tr class="row-siswa hover:bg-slate-50 transition">
                                <td class="p-3 font-mono font-medium text-blue-600"><?= $s['nomor_kartu']; ?></td>
                                <td class="p-3 font-medium text-slate-800 cell-nama"><?= $s['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $s['kelas']; ?></td>
                                <td class="p-3 text-center flex justify-center gap-2">
                                    <button onclick='openEditSiswaModal(<?= json_encode($s); ?>)' class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                                        Edit
                                    </button>
                                    <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus siswa ini?');">
                                        <input type="hidden" name="id_siswa" value="<?= $s['id']; ?>">
                                        <button type="submit" name="hapus_siswa" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr id="emptySiswaRow">
                                <td colspan="4" class="p-6 text-center text-slate-400 text-sm">Belum ada siswa yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL DAFTAR BUKU KOLEKSI (ADA AKSI EDIT & HAPUS + SEARCH BAR) -->
        <div id="buku-tab" class="tab-content hidden bg-white rounded-2xl shadow p-6 border border-slate-200">
            <?php
            $q_buku_all = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY judul ASC");
            $total_buku = mysqli_num_rows($q_buku_all);
            ?>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <h2 class="text-lg font-bold text-slate-800">Daftar Buku Koleksi</h2>
                
                <!-- SEARCH BAR & TOTAL BADGE -->
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <input type="text" id="searchBuku" onkeyup="filterBuku()" placeholder="Cari judul buku..." class="w-full sm:w-64 p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-extrabold px-3 py-2 rounded-lg border border-indigo-200 whitespace-nowrap">
                        Total: <?= $total_buku; ?>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="tableBuku">
                    <thead class="bg-slate-100 text-slate-700 font-semibold uppercase text-xs">
                        <tr>
                            <th class="p-3 rounded-l-lg">Judul Buku</th>
                            <th class="p-3">Penulis</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        if ($total_buku > 0):
                            while ($b = mysqli_fetch_assoc($q_buku_all)):
                        ?>
                            <tr class="row-buku hover:bg-slate-50 transition">
                                <td class="p-3 font-medium text-slate-800 cell-judul"><?= $b['judul']; ?></td>
                                <td class="p-3 text-slate-600"><?= $b['penulis']; ?></td>
                                <td class="p-3 text-center">
                                    <?php if ($b['status'] === 'tersedia'): ?>
                                        <span class="bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold">Tersedia</span>
                                    <?php else: ?>
                                        <span class="bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-center flex justify-center gap-2">
                                    <button onclick='openEditBukuModal(<?= json_encode($b); ?>)' class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                                        Edit
                                    </button>
                                    <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus buku ini?');">
                                        <input type="hidden" name="id_buku" value="<?= $b['id']; ?>">
                                        <button type="submit" name="hapus_buku" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr id="emptyBukuRow">
                                <td colspan="4" class="p-6 text-center text-slate-400 text-sm">Belum ada buku yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL EDIT SISWA -->
    <div id="modalSiswa" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-bold text-lg text-slate-800 border-b pb-2">Edit Data Siswa</h3>
            <form action="" method="POST" class="space-y-3">
                <input type="hidden" name="id_siswa" id="edit_siswa_id">
                <div>
                    <label class="text-xs font-semibold text-slate-600">Nomor Kartu:</label>
                    <input type="text" name="nomor_kartu" id="edit_siswa_kartu" required class="w-full p-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Nama Siswa:</label>
                    <input type="text" name="nama" id="edit_siswa_nama" required class="w-full p-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Kelas:</label>
                    <input type="text" name="kelas" id="edit_siswa_kelas" required class="w-full p-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Password (Kosongkan jika tak diubah):</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full p-2 border rounded-lg text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalSiswa')" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-300">Batal</button>
                    <button type="submit" name="edit_siswa" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT BUKU -->
    <div id="modalBuku" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-bold text-lg text-slate-800 border-b pb-2">Edit Data Buku</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="id_buku" id="edit_buku_id">
                <div>
                    <label class="text-xs font-semibold text-slate-600">Judul Buku:</label>
                    <input type="text" name="judul" id="edit_buku_judul" required class="w-full p-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Penulis:</label>
                    <input type="text" name="penulis" id="edit_buku_penulis" required class="w-full p-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Sinopsis Singkat:</label>
                    <textarea name="sinopsis" id="edit_buku_sinopsis" rows="3" required class="w-full p-2 border rounded-lg text-sm"></textarea>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Ganti Cover (Kosongkan jika tidak diubah):</label>
                    <input type="file" name="cover" accept="image/*" class="w-full p-1.5 border rounded-lg text-sm bg-slate-50">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalBuku')" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-300">Batal</button>
                    <button type="submit" name="edit_buku" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT TAB NAVIGATION & MODAL -->
    <script>
        function openTab(tabName, btnElement) {
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.add('hidden'));

            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.className = "tab-btn bg-white hover:bg-slate-200 text-slate-700 border border-slate-200 px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition";
            });

            document.getElementById(tabName).classList.remove('hidden');
            btnElement.className = "tab-btn bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow transition";
        }

        function openEditSiswaModal(siswa) {
            document.getElementById('edit_siswa_id').value = siswa.id;
            document.getElementById('edit_siswa_kartu').value = siswa.nomor_kartu;
            document.getElementById('edit_siswa_nama').value = siswa.nama;
            document.getElementById('edit_siswa_kelas').value = siswa.kelas;
            document.getElementById('modalSiswa').classList.remove('hidden');
        }

        function openEditBukuModal(buku) {
            document.getElementById('edit_buku_id').value = buku.id;
            document.getElementById('edit_buku_judul').value = buku.judul;
            document.getElementById('edit_buku_penulis').value = buku.penulis;
            document.getElementById('edit_buku_sinopsis').value = buku.sinopsis;
            document.getElementById('modalBuku').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Filter Live Nama Siswa
        function filterSiswa() {
            let input = document.getElementById('searchSiswa').value.toLowerCase();
            let rows = document.querySelectorAll('.row-siswa');

            rows.forEach(row => {
                let nama = row.querySelector('.cell-nama').textContent.toLowerCase();
                if (nama.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Filter Live Judul Buku
        function filterBuku() {
            let input = document.getElementById('searchBuku').value.toLowerCase();
            let rows = document.querySelectorAll('.row-buku');

            rows.forEach(row => {
                let judul = row.querySelector('.cell-judul').textContent.toLowerCase();
                if (judul.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Filter Live Nama Siswa pada Riwayat
        function filterRiwayat() {
            let input = document.getElementById('searchRiwayat').value.toLowerCase();
            let rows = document.querySelectorAll('.row-riwayat');

            rows.forEach(row => {
                let nama = row.querySelector('.cell-nama-riwayat').textContent.toLowerCase();
                if (nama.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>

</body>
</html>