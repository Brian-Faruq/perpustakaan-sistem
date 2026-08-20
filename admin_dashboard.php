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

    // 1. Cek dulu apakah nomor kartu sudah terdaftar
    $cek_kartu = mysqli_query($koneksi, "SELECT id FROM siswa WHERE nomor_kartu='$nomor_kartu'");
    
    if (mysqli_num_rows($cek_kartu) > 0) {
        // Jika nomor kartu sudah ada di database
        $msg = "Gagal! Nomor Kartu ($nomor_kartu) sudah terdaftar untuk siswa lain.";
        $msg_type = 'error';
    } else {
        // Jika belum ada, masukkan data siswa baru
        $sql = "INSERT INTO siswa (nomor_kartu, nama, kelas, password) VALUES ('$nomor_kartu', '$nama', '$kelas', '$pass_hash')";
        if (mysqli_query($koneksi, $sql)) {
            $msg = "Siswa berhasil terdaftar!";
            $msg_type = 'success';
        } else {
            $msg = "Terjadi kesalahan saat menyimpan data.";
            $msg_type = 'error';
        }
    }
}

// Aksi 2: Tambah Koleksi Buku
if (isset($_POST['tambah_buku'])) {
    $judul    = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $penulis  = mysqli_real_escape_string($koneksi, trim($_POST['penulis']));
    $sinopsis = mysqli_real_escape_string($koneksi, trim($_POST['sinopsis']));
    
    // Default cover jika tidak upload gambar
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

// Aksi 3: Transaksi Pinjam Buku via Tap Kartu
if (isset($_POST['pinjam_buku'])) {
    $nomor_kartu = mysqli_real_escape_string($koneksi, trim($_POST['nomor_kartu_pinjam']));
    $buku_id     = intval($_POST['buku_id']);
    $tgl_pinjam  = date('Y-m-d');

    // Cek keberadaan siswa
    $q_siswa = mysqli_query($koneksi, "SELECT id, nama FROM siswa WHERE nomor_kartu='$nomor_kartu'");
    if (mysqli_num_rows($q_siswa) > 0) {
        $d_siswa  = mysqli_fetch_assoc($q_siswa);
        $siswa_id = $d_siswa['id'];

        // Validasi: Cek apakah siswa masih punya pinjaman aktif
        $q_cek = mysqli_query($koneksi, "SELECT id FROM peminjaman WHERE siswa_id='$siswa_id' AND status_transaksi='berjalan'");
        if (mysqli_num_rows($q_cek) > 0) {
            $msg = "Gagal! Siswa " . $d_siswa['nama'] . " masih meminjam buku lain yang belum dikembalikan.";
            $msg_type = 'error';
        } else {
            // Catat peminjaman & update status buku
            mysqli_query($koneksi, "INSERT INTO peminjaman (siswa_id, buku_id, tanggal_pinjam) VALUES ('$siswa_id', '$buku_id', '$tgl_pinjam')");
            mysqli_query($koneksi, "UPDATE buku SET status='dipinjam' WHERE id='$buku_id'");
            $msg = "Transaksi Peminjaman untuk " . $d_siswa['nama'] . " Berhasil!";
        }
    } else {
        $msg = "Kartu siswa tidak terdaftar di sistem!";
        $msg_type = 'error';
    }
}

// Aksi 4: Pengembalian Buku
if (isset($_GET['kembali_id'])) {
    $pem_id  = intval($_GET['kembali_id']);
    $buku_id = intval($_GET['buku_id']);
    $tgl_kmb = date('Y-m-d');

    mysqli_query($koneksi, "UPDATE peminjaman SET tanggal_kembali='$tgl_kmb', status_transaksi='selesai' WHERE id='$pem_id'");
    mysqli_query($koneksi, "UPDATE buku SET status='tersedia' WHERE id='$buku_id'");

    header("Location: admin_dashboard.php");
    exit;
}

// Aksi 5: Pengembalian Buku via Tombol
if (isset($_POST['kembalikan_buku'])) {
    $id_pinjam = intval($_POST['id_peminjaman']);

    // Ambil buku_id terlebih dahulu
    $q_get = mysqli_query($koneksi, "SELECT buku_id FROM peminjaman WHERE id = $id_pinjam");
    if ($data = mysqli_fetch_assoc($q_get)) {
        $buku_id = $data['buku_id'];

        // Update status peminjaman menjadi selesai & tanggal kembali
        $tgl_sekarang = date('Y-m-d');
        mysqli_query($koneksi, "UPDATE peminjaman SET status_transaksi = 'selesai', tanggal_kembali = '$tgl_sekarang' WHERE id = $id_pinjam");

        // Kembalikan status buku menjadi tersedia
        mysqli_query($koneksi, "UPDATE buku SET status = 'tersedia' WHERE id = $buku_id");

        $msg = "Buku berhasil dikembalikan!";
        $msg_type = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        <!-- TABEL DATA PEMINJAMAN AKTIF & HITUNG DENDA -->
        <div class="bg-white rounded-2xl shadow p-6 border border-slate-200">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Daftar Peminjaman Aktif</h2>
            
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
                        // Query mengambil peminjaman yang statusnya masih dipinjam / aktif
                        $q_peminjaman = mysqli_query($koneksi, "
                            SELECT p.id AS id_pinjam, s.nama, s.kelas, b.judul, p.tanggal_pinjam 
                            FROM peminjaman p
                            JOIN siswa s ON p.siswa_id = s.id
                            JOIN buku b ON p.buku_id = b.id
                            WHERE p.status_transaksi = 'berjalan'
                            ORDER BY p.tanggal_pinjam DESC
                        ");

                        if (mysqli_num_rows($q_peminjaman) > 0):
                            while ($p = mysqli_fetch_assoc($q_peminjaman)):
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3 font-medium text-slate-800"><?= $p['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $p['kelas']; ?></td>
                                <td class="p-3 text-slate-700 font-medium"><?= $p['judul']; ?></td>
                                <td class="p-3 text-slate-600"><?= date('d-m-Y', strtotime($p['tanggal_pinjam'])); ?></td>
                                <td class="p-3 text-center">
                                    <!-- Tombol untuk mengembalikan buku -->
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
                                <td colspan="5" class="p-6 text-center text-slate-400 text-sm">
                                    Tidak ada peminjaman aktif saat ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL DAFTAR SISWA -->
        <div class="bg-white rounded-2xl shadow p-6 border border-slate-200">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Daftar Siswa Terdaftar</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700 font-semibold uppercase text-xs">
                        <tr>
                            <th class="p-3 rounded-l-lg">Nomor Kartu</th>
                            <th class="p-3">Nama Siswa</th>
                            <th class="p-3 rounded-r-lg">Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        // Query mengambil seluruh data siswa
                        $q_siswa = mysqli_query($koneksi, "SELECT nomor_kartu, nama, kelas FROM siswa ORDER BY nama ASC");

                        if (mysqli_num_rows($q_siswa) > 0):
                            while ($s = mysqli_fetch_assoc($q_siswa)):
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3 font-mono font-medium text-blue-600"><?= $s['nomor_kartu']; ?></td>
                                <td class="p-3 font-medium text-slate-800"><?= $s['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $s['kelas']; ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="3" class="p-6 text-center text-slate-400 text-sm">
                                    Belum ada siswa yang terdaftar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>