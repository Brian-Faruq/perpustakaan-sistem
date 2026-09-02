<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
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

// Aksi 1.5: Import Siswa dari File CSV / Excel (Anti-Duplikat & Default Password)
if (isset($_POST['import_siswa'])) {
    if (isset($_FILES['file_excel_siswa']) && $_FILES['file_excel_siswa']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_excel_siswa']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['file_excel_siswa']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($file_tmp, "r");
            $berhasil = 0;
            $duplikat = 0;
            $baris = 0;

            // Deteksi Delimiter (Koma, Titik Koma, atau Tab)
            $firstLine = fgets($handle);
            $delimiter = ',';
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }
            rewind($handle);

            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $baris++;
                if ($baris == 1) continue; // Lewati header tabel

                // Format Kolom CSV: [0] Nomor Kartu, [1] Nama, [2] Kelas, [3] Password (Opsional)
                $nomor_kartu = isset($data[0]) ? mysqli_real_escape_string($koneksi, trim($data[0], " \t\n\r\0\x0B\"'")) : '';
                $nama        = isset($data[1]) ? mysqli_real_escape_string($koneksi, trim($data[1], " \t\n\r\0\x0B\"'")) : '';
                $kelas       = isset($data[2]) ? mysqli_real_escape_string($koneksi, trim($data[2], " \t\n\r\0\x0B\"'")) : '';
                
                // Jika password di CSV kosong, gunakan default '123456'
                $raw_pass    = (isset($data[3]) && !empty(trim($data[3]))) ? trim($data[3], " \t\n\r\0\x0B\"'") : '123456';
                $pass_hash   = password_hash($raw_pass, PASSWORD_DEFAULT);

                if (!empty($nomor_kartu) && !empty($nama)) {
                    // Cek Duplikasi Nomor Kartu
                    $cek = mysqli_query($koneksi, "SELECT id FROM siswa WHERE nomor_kartu = '$nomor_kartu'");
                    
                    if (mysqli_num_rows($cek) == 0) {
                        $sql = "INSERT INTO siswa (nomor_kartu, nama, kelas, password) VALUES ('$nomor_kartu', '$nama', '$kelas', '$pass_hash')";
                        if (mysqli_query($koneksi, $sql)) {
                            $berhasil++;
                        }
                    } else {
                        $duplikat++;
                    }
                }
            }
            fclose($handle);

            // Respon Pesan SweetAlert
            if ($berhasil > 0) {
                $msg = "Berhasil mengimpor $berhasil data siswa dari file!";
                if ($duplikat > 0) {
                    $msg .= " ($duplikat siswa dilewati karena nomor kartu sudah terdaftar)";
                }
            } else {
                if ($duplikat > 0) {
                    $msg = "Tidak ada data siswa baru yang ditambahkan. Semua nomor kartu sudah terdaftar!";
                    $msg_type = 'error';
                } else {
                    $msg = "Gagal mengimpor data! Pastikan baris data di file CSV terisi dengan benar.";
                    $msg_type = 'error';
                }
            }
        } else {
            $msg = "Format file tidak valid! Harap upload file .csv";
            $msg_type = 'error';
        }
    } else {
        $msg = "Pilih file CSV terlebih dahulu!";
        $msg_type = 'error';
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

// Aksi 3.5: Hapus Semua Siswa via AJAX
if (isset($_POST['hapus_semua_siswa_ajax'])) {
    header('Content-Type: application/json');
    if (mysqli_query($koneksi, "DELETE FROM siswa")) {
        echo json_encode(['status' => 'success', 'message' => 'Semua data siswa berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus siswa. Pastikan siswa tidak memiliki riwayat/transaksi aktif.']);
    }
    exit;
}

// Aksi 4: Tambah Koleksi Buku Manual (Anti-Duplikat)
if (isset($_POST['tambah_buku'])) {
    $judul      = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $penulis    = mysqli_real_escape_string($koneksi, trim($_POST['penulis']));
    $sinopsis   = mysqli_real_escape_string($koneksi, trim($_POST['sinopsis']));
    
    // Default cover
    $cover_name = 'default_cover.jpg';
    
    // Cek jika ada upload gambar cover
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $new_name = time() . '_' . rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], 'uploads/' . $new_name)) {
            $cover_name = $new_name;
        }
    }

    // 1. Cek Duplikasi Data Buku di Database
    $cek_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE judul = '$judul' AND penulis = '$penulis' AND sinopsis = '$sinopsis' AND cover = '$cover_name'");
    
    if (mysqli_num_rows($cek_buku) > 0) {
        // Jika data sama persis sudah ada
        $msg = "Gagal! Buku dengan data tersebut sudah terdaftar di sistem.";
        $msg_type = 'error';
    } else {
        // Jika belum ada, lakukan insert
        $sql = "INSERT INTO buku (judul, penulis, sinopsis, cover) VALUES ('$judul', '$penulis', '$sinopsis', '$cover_name')";
        if (mysqli_query($koneksi, $sql)) {
            $msg = "Buku baru berhasil ditambahkan!";
        } else {
            $msg = "Gagal menambahkan buku.";
            $msg_type = 'error';
        }
    }
}

// Aksi 4.5: Import Buku dari File CSV / Excel (Anti-Duplikat)
if (isset($_POST['import_buku'])) {
    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_excel']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($file_tmp, "r");
            $berhasil = 0;
            $duplikat = 0;
            $baris = 0;

            // Deteksi Delimiter (Koma, Titik Koma, atau Tab)
            $firstLine = fgets($handle);
            $delimiter = ',';
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }
            rewind($handle);

            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $baris++;
                if ($baris == 1) continue; // Lewati header tabel

                $judul      = isset($data[0]) ? mysqli_real_escape_string($koneksi, trim($data[0], " \t\n\r\0\x0B\"'")) : '';
                $penulis    = isset($data[1]) ? mysqli_real_escape_string($koneksi, trim($data[1], " \t\n\r\0\x0B\"'")) : '';
                $sinopsis   = isset($data[2]) ? mysqli_real_escape_string($koneksi, trim($data[2], " \t\n\r\0\x0B\"'")) : '';
                $nama_cover = (isset($data[3]) && !empty(trim($data[3]))) ? mysqli_real_escape_string($koneksi, trim($data[3], " \t\n\r\0\x0B\"'")) : 'default_cover.jpg';

                if (!empty($judul)) {
                    // Cek Duplikasi ke Database sebelum INSERT
                    $cek = mysqli_query($koneksi, "SELECT * FROM buku WHERE judul = '$judul' AND penulis = '$penulis' AND sinopsis = '$sinopsis' AND cover = '$nama_cover'");
                    
                    if (mysqli_num_rows($cek) == 0) {
                        // Data belum ada -> Masukkan ke DB
                        $sql = "INSERT INTO buku (judul, penulis, sinopsis, cover) VALUES ('$judul', '$penulis', '$sinopsis', '$nama_cover')";
                        if (mysqli_query($koneksi, $sql)) {
                            $berhasil++;
                        }
                    } else {
                        // Data sama persis -> Lewati (Skip)
                        $duplikat++;
                    }
                }
            }
            fclose($handle);

            // Respon Pesan SweetAlert
            if ($berhasil > 0) {
                $msg = "Berhasil mengimpor $berhasil data buku dari file!";
                if ($duplikat > 0) {
                    $msg .= " ($duplikat buku dilewati karena sudah terdaftar)";
                }
            } else {
                if ($duplikat > 0) {
                    $msg = "Tidak ada data baru yang ditambahkan. Semua buku di file tersebut sudah terdaftar!";
                    $msg_type = 'error';
                } else {
                    $msg = "Gagal mengimpor data! Pastikan baris data di file CSV terisi dengan benar.";
                    $msg_type = 'error';
                }
            }
        } else {
            $msg = "Format file tidak valid! Harap upload file .csv";
            $msg_type = 'error';
        }
    } else {
        $msg = "Pilih file CSV terlebih dahulu!";
        $msg_type = 'error';
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

// Aksi 6.5: Hapus Semua Buku via AJAX
if (isset($_POST['hapus_semua_buku_ajax'])) {
    header('Content-Type: application/json');
    if (mysqli_query($koneksi, "DELETE FROM buku")) {
        echo json_encode(['status' => 'success', 'message' => 'Semua koleksi buku berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus buku. Pastikan tidak ada buku yang sedang dipinjam.']);
    }
    exit;
}

// Aksi 7: Transaksi Pinjam Buku
if (isset($_POST['pinjam_buku'])) {
    $nomor_kartu = mysqli_real_escape_string($koneksi, trim($_POST['nomor_kartu_pinjam']));
    $buku_id     = intval($_POST['buku_id']);
    $durasi_hari = isset($_POST['durasi_hari']) ? intval($_POST['durasi_hari']) : 3;
    $tgl_pinjam  = date('Y-m-d');

    $tgl_jatuh_tempo = date('Y-m-d', strtotime("+$durasi_hari days", strtotime($tgl_pinjam)));

    $q_siswa = mysqli_query($koneksi, "SELECT id, nama FROM siswa WHERE nomor_kartu='$nomor_kartu'");
    if (mysqli_num_rows($q_siswa) > 0) {
        $d_siswa  = mysqli_fetch_assoc($q_siswa);
        $siswa_id = $d_siswa['id'];

        $q_cek = mysqli_query($koneksi, "SELECT id FROM peminjaman WHERE siswa_id='$siswa_id' AND status_transaksi='berjalan'");
        if (mysqli_num_rows($q_cek) > 0) {
            $msg = "Gagal! Siswa " . htmlspecialchars($d_siswa['nama']) . " masih meminjam buku lain yang belum dikembalikan.";
            $msg_type = 'error';
        } else {
            $sql_insert = "INSERT INTO peminjaman (siswa_id, buku_id, tanggal_pinjam, durasi_hari, tanggal_jatuh_tempo, status_transaksi) 
                           VALUES ('$siswa_id', '$buku_id', '$tgl_pinjam', '$durasi_hari', '$tgl_jatuh_tempo', 'berjalan')";
            
            if (mysqli_query($koneksi, $sql_insert)) {
                mysqli_query($koneksi, "UPDATE buku SET status='dipinjam' WHERE id='$buku_id'");
                $msg = "Transaksi Peminjaman untuk " . htmlspecialchars($d_siswa['nama']) . " Berhasil!";
            } else {
                $msg = "Gagal menyimpan peminjaman: " . mysqli_error($koneksi);
                $msg_type = 'error';
            }
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

// Aksi 9.5: Hapus Semua Riwayat Peminjaman via AJAX
if (isset($_POST['hapus_semua_riwayat_ajax'])) {
    header('Content-Type: application/json');
    if (mysqli_query($koneksi, "DELETE FROM peminjaman WHERE status_transaksi = 'selesai'")) {
        echo json_encode(['status' => 'success', 'message' => 'Semua riwayat peminjaman berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus riwayat peminjaman.']);
    }
    exit;
}

// Aksi 10: Kirim Peringatan Notif ke Siswa
if (isset($_POST['kirim_peringatan'])) {
    $id_pinjam = intval($_POST['id_peminjaman']);
    
    $q_get = mysqli_query($koneksi, "SELECT p.siswa_id, b.judul FROM peminjaman p JOIN buku b ON p.buku_id = b.id WHERE p.id = $id_pinjam");
    if ($d_warn = mysqli_fetch_assoc($q_get)) {
        $siswa_id = $d_warn['siswa_id'];
        $judul_buku = $d_warn['judul'];
        $pesan_teks = "Waktu peminjaman buku \"" . $judul_buku . "\" habis, kembalikan sekarang!";
        $pesan = mysqli_real_escape_string($koneksi, $pesan_teks);
        
        $sql_notif = "INSERT INTO notifikasi (siswa_id, pesan, is_read, created_at) VALUES ('$siswa_id', '$pesan', 0, NOW())";
        
        if (mysqli_query($koneksi, $sql_notif)) {
            $msg = "Peringatan berhasil dikirimkan ke siswa!";
        } else {
            $msg = "Gagal mengirimkan peringatan: " . mysqli_error($koneksi);
            $msg_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Perpustakaan - Sekolah Impian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        .select2-container .select2-selection--single {
            height: 42px !important;
            border-radius: 0.75rem !important;
            border-color: #e2e8f0 !important;
            padding: 6px 8px !important;
            background-color: #f8fafc !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-brand-lightBg text-slate-800 min-h-screen">

    <!-- NAVBAR HEADER CLEAN UNTUK MOBILE -->
    <nav class="bg-brand-navy text-white px-4 sm:px-6 py-3 flex justify-between items-center shadow-md sticky top-0 z-30">
        <div class="flex items-center gap-2">
            <h1 class="font-black text-base sm:text-xl tracking-wide bg-gradient-to-r from-brand-amber to-brand-orange bg-clip-text text-transparent uppercase">
                PERPUSTAKAAN
            </h1>
            <span class="hidden sm:inline-block text-xs bg-brand-teal/20 text-brand-teal px-2 py-0.5 rounded font-bold uppercase tracking-wider">Admin</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden sm:inline text-xs sm:text-sm font-medium text-slate-200">
                Petugas: <b class="text-white"><?= htmlspecialchars($_SESSION['nama']); ?></b>
            </span>
            <a href="index.php" class="bg-rose-500 hover:bg-rose-600 text-white text-xs px-3.5 py-1.5 rounded-xl font-bold shadow transition active:scale-95">
                Logout
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-6">

        <!-- TAB SELECTION FORM UNTUK MOBILE (DEFAULT AKTIF TAB 3: TRANSAKSI PINJAM) -->
        <div class="md:hidden flex overflow-x-auto no-scrollbar gap-2 pb-1">
            <button onclick="switchFormTab('form-pinjam-tab', this)" class="form-tab-btn shrink-0 bg-brand-navy text-white px-3.5 py-2 rounded-xl text-xs font-bold shadow transition">
                1. Transaksi Pinjam
            </button>
            <button onclick="switchFormTab('form-reg-tab', this)" class="form-tab-btn shrink-0 bg-white text-slate-600 border border-slate-200 px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                2. Registrasi Siswa
            </button>
            <button onclick="switchFormTab('form-buku-tab', this)" class="form-tab-btn shrink-0 bg-white text-slate-600 border border-slate-200 px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                3. Tambah Buku
            </button>
        </div>

        <!-- CONTAINER FORM CONTROL PANEL -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">

            <!-- 1. Form Registrasi Siswa (Manual & Import Excel) -->
            <div id="form-reg-tab" class="form-tab-content hidden md:block bg-white p-5 rounded-3xl shadow-lg border border-slate-100 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm sm:text-base mb-3 text-brand-navy flex items-center gap-2">
                        <span class="bg-brand-orange text-white text-xs w-6 h-6 rounded-full flex items-center justify-center font-bold">1</span>
                        Registrasi Siswa (Tap Kartu)
                    </h3>
                    
                    <!-- Form 1: Input Siswa Manual -->
                    <form action="" method="POST" class="space-y-3 pb-4 border-b border-slate-100">
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nomor Kartu (Tap di sini):</label>
                            <input type="text" name="nomor_kartu" required placeholder="Tap kartu siswa..." class="w-full p-2.5 border border-amber-300 rounded-xl text-sm bg-amber-50/60 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition font-medium">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Siswa:</label>
                            <input type="text" name="nama" required class="w-full p-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kelas:</label>
                            <input type="text" name="kelas" required class="w-full p-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Password Akun Siswa:</label>
                            <input type="password" name="password" required class="w-full p-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition">
                        </div>
                        <button type="submit" name="tambah_siswa" class="w-full bg-gradient-to-r from-brand-orange to-brand-amber hover:opacity-95 text-white py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-brand-orange/20 transition active:scale-[0.98]">
                            Simpan Data Siswa
                        </button>
                    </form>

                    <!-- Form 2: Import Siswa dari File Excel (CSV) -->
                    <form action="" method="POST" enctype="multipart/form-data" class="pt-4 space-y-2">
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">
                            📁 Atau Import Massal (Excel / CSV):
                        </label>
                        <input type="file" name="file_excel_siswa" accept=".csv" required class="w-full p-1 border border-emerald-200 rounded-xl text-xs bg-emerald-50/50 focus:outline-none">
                        <button type="submit" name="import_siswa" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition active:scale-[0.98] flex items-center justify-center gap-1">
                            📊 Upload & Import Excel
                        </button>
                    </form>
                </div>
            </div>

            <!-- 2. Form Tambah Buku (Manual & Import Excel) -->
            <div id="form-buku-tab" class="form-tab-content hidden md:block bg-white p-5 rounded-3xl shadow-lg border border-slate-100 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm sm:text-base mb-3 text-brand-navy flex items-center gap-2">
                        <span class="bg-brand-teal text-white text-xs w-6 h-6 rounded-full flex items-center justify-center font-bold">2</span>
                        Tambah Koleksi Buku
                    </h3>
                    
                    <!-- Form 1: Tambah Buku Manual -->
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-3 pb-4 border-b border-slate-100">
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Judul Buku:</label>
                            <input type="text" name="judul" required class="w-full p-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Penulis:</label>
                            <input type="text" name="penulis" required class="w-full p-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sinopsis Singkat:</label>
                            <textarea name="sinopsis" rows="2" required class="w-full p-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition"></textarea>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cover Buku (JPG/PNG):</label>
                            <input type="file" name="cover" accept="image/*" class="w-full p-1 border border-slate-200 rounded-xl text-xs bg-slate-50 focus:outline-none">
                        </div>
                        <button type="submit" name="tambah_buku" class="w-full bg-brand-teal hover:bg-teal-600 text-white py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-brand-teal/20 transition active:scale-[0.98]">
                            + Tambah Buku Manual
                        </button>
                    </form>

                    <!-- Form 2: Import dari File Excel (CSV) -->
                    <form action="" method="POST" enctype="multipart/form-data" class="pt-4 space-y-2">
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">
                            📁 Atau Import Massal (Excel / CSV):
                        </label>
                        <input type="file" name="file_excel" accept=".csv" required class="w-full p-1 border border-emerald-200 rounded-xl text-xs bg-emerald-50/50 focus:outline-none">
                        <button type="submit" name="import_buku" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition active:scale-[0.98] flex items-center justify-center gap-1">
                            📊 Upload & Import Excel
                        </button>
                    </form>
                </div>
            </div>

            <!-- 3. Form Transaksi Pinjam Buku (DEFAULT DISPLAY MOBILE & AUTOFOCUS FOCUS) -->
            <div id="form-pinjam-tab" class="form-tab-content block md:block bg-white p-5 rounded-3xl shadow-lg border border-slate-100 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm sm:text-base mb-3 text-brand-navy flex items-center gap-2">
                        <span class="bg-brand-navy text-white text-xs w-6 h-6 rounded-full flex items-center justify-center font-bold">3</span>
                        Transaksi Pinjam Buku
                    </h3>
                    <form action="" method="POST" class="space-y-3">
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tap Kartu Peminjam:</label>
                            <input type="text" id="input_nomor_kartu_pinjam" name="nomor_kartu_pinjam" required autofocus placeholder="Tap kartu siswa..." class="w-full p-2.5 border border-amber-300 rounded-xl text-sm bg-amber-50/60 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition font-medium">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Buku (Tersedia):</label>
                            <select name="buku_id" class="select2-buku w-full" required>
                                <option value="">-- Pilih Buku --</option>
                                <?php
                                $q_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE status = 'tersedia' ORDER BY judul ASC");
                                while ($b = mysqli_fetch_assoc($q_buku)) {
                                    echo "<option value='".$b['id']."'>".htmlspecialchars($b['judul'])." - ".$b['penulis']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Durasi Peminjaman (Hari):</label>
                            <select name="durasi_hari" class="w-full p-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-teal focus:outline-none transition">
                                <?php for ($i = 1; $i <= 7; $i++): ?>
                                    <option value="<?= $i; ?>" <?= $i === 3 ? 'selected' : ''; ?>><?= $i; ?> Hari</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" name="pinjam_buku" class="w-full bg-brand-navy hover:bg-slate-800 text-white py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-brand-navy/20 transition active:scale-[0.98] mt-2">Proses Peminjaman</button>
                    </form>
                </div>
            </div>

        </div>

        <!-- TABEL KONTROL DATA PERPUSTAKAAN -->
        <div class="flex overflow-x-auto no-scrollbar gap-2 sm:gap-3 border-b border-slate-200 pb-3 -mx-4 px-4 sm:mx-0 sm:px-0">
            <button onclick="openTab('pinjam-tab', this)" class="tab-btn shrink-0 bg-brand-navy text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md transition">
                📋 Peminjaman Aktif
            </button>
            <button onclick="openTab('riwayat-tab', this)" class="tab-btn shrink-0 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition">
                📜 Riwayat Peminjaman
            </button>
            <button onclick="openTab('siswa-tab', this)" class="tab-btn shrink-0 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition">
                👨‍🎓 Daftar Siswa
            </button>
            <button onclick="openTab('buku-tab', this)" class="tab-btn shrink-0 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition">
                📚 Daftar Buku
            </button>
        </div>

        <!-- TABEL DATA PEMINJAMAN AKTIF -->
        <div id="pinjam-tab" class="tab-content bg-white rounded-3xl shadow-xl p-4 sm:p-6 border border-slate-100">
            <?php
            $q_peminjaman = mysqli_query($koneksi, "
                SELECT p.id AS id_pinjam, s.nama, s.kelas, s.nomor_kartu, b.judul, p.tanggal_pinjam, p.durasi_hari, p.tanggal_jatuh_tempo 
                FROM peminjaman p
                JOIN siswa s ON p.siswa_id = s.id
                JOIN buku b ON p.buku_id = b.id
                WHERE p.status_transaksi = 'berjalan'
                ORDER BY p.tanggal_pinjam DESC
            ");
            $total_pinjam = mysqli_num_rows($q_peminjaman);
            ?>

            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-5">
                <h2 class="text-base sm:text-lg font-bold text-brand-navy">Daftar Peminjaman Aktif</h2>
                <div class="flex items-center gap-2">
                    <input type="text" id="tapKartuReturn" onkeyup="filterPeminjamanTap()" onkeydown="preventRfidEnter(event)" placeholder="Cari siswa..." autocomplete="off" class="border border-slate-200 rounded-xl px-3 py-1.5 sm:py-2 text-xs sm:text-sm w-full sm:w-60 focus:outline-none focus:ring-2 focus:ring-brand-teal bg-slate-50">
                    <span class="bg-brand-orange/10 text-brand-orange border border-brand-orange/20 text-xs font-black px-3 py-1.5 sm:py-2 rounded-xl whitespace-nowrap">
                        Total: <?= $total_pinjam; ?>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] sm:text-xs tracking-wider">
                        <tr>
                            <th class="p-3 rounded-l-xl">Nama Siswa</th>
                            <th class="p-3">Kelas</th>
                            <th class="p-3">Judul Buku</th>
                            <th class="p-3">Tanggal Pinjam</th>
                            <th class="p-3">Target Durasi</th>
                            <th class="p-3">Sisa Waktu</th>
                            <th class="p-3 text-center rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        if ($total_pinjam > 0):
                            while ($p = mysqli_fetch_assoc($q_peminjaman)):
                        ?>
                            <tr class="row-peminjaman hover:bg-slate-50 transition" data-kartu="<?= htmlspecialchars($p['nomor_kartu']); ?>" data-nama="<?= htmlspecialchars($p['nama']); ?>">
                                <td class="p-3 font-semibold text-slate-800 cell-nama"><?= $p['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $p['kelas']; ?></td>
                                <td class="p-3 text-slate-700 font-medium"><?= $p['judul']; ?></td>
                                <td class="p-3 text-slate-500 whitespace-nowrap"><?= date('d-m-Y', strtotime($p['tanggal_pinjam'])); ?></td>
                                <td class="p-3 text-slate-500 whitespace-nowrap"><?= $p['durasi_hari']; ?> Hari</td>
                                <td class="p-3 font-semibold whitespace-nowrap">
                                    <span class="countdown-timer" data-target="<?= $p['tanggal_jatuh_tempo']; ?> 23:59:59">Memuat...</span>
                                </td>
                                <td class="p-3 text-center flex justify-center items-center gap-1.5">
                                    <form action="" method="POST" onsubmit="return confirmAction(event, 'Yakin ingin kembalikan buku ini?', this);">
                                        <input type="hidden" name="id_peminjaman" value="<?= $p['id_pinjam']; ?>">
                                        <input type="hidden" name="kembalikan_buku" value="1">
                                        <button type="submit" class="bg-brand-teal hover:bg-teal-600 text-white text-[11px] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg font-bold transition">
                                            Kembalikan
                                        </button>
                                    </form>
                                    <form action="" method="POST" onsubmit="return confirmAction(event, 'Kirim notifikasi peringatan pengembalian ke siswa ini?', this);">
                                        <input type="hidden" name="id_peminjaman" value="<?= $p['id_pinjam']; ?>">
                                        <input type="hidden" name="kirim_peringatan" value="1">
                                        <button type="submit" class="bg-brand-amber hover:bg-amber-600 text-white text-[11px] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg font-bold transition flex items-center gap-1">
                                            ⚠️ <span class="hidden sm:inline">Peringatkan</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="7" class="p-6 text-center text-slate-400 text-xs sm:text-sm">Tidak ada peminjaman aktif saat ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL RIWAYAT PEMINJAMAN -->
        <div id="riwayat-tab" class="tab-content hidden bg-white rounded-3xl shadow-xl p-4 sm:p-6 border border-slate-100">
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-5">
                <h2 class="text-base sm:text-lg font-bold text-brand-navy">Daftar Riwayat Peminjaman</h2>
                <div class="flex items-center gap-2">
                    <!-- Tombol Hapus Semua Riwayat (Sebelah Kiri Pencarian) -->
                    <form action="" method="POST" id="formHapusSemuaRiwayat" onsubmit="return confirmHapusSemuaRiwayat(event, this);">
                        <input type="hidden" name="hapus_semua_riwayat" value="1">
                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-xs px-3 py-2 rounded-xl font-bold shadow-sm transition whitespace-nowrap flex items-center gap-1">
                            🗑️ Hapus Semua
                        </button>
                    </form>

                    <input type="text" id="searchRiwayat" onkeyup="filterRiwayat()" placeholder="Cari nama siswa..." class="w-full sm:w-64 p-2 border border-slate-200 rounded-xl text-xs sm:text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-teal">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm" id="tableRiwayat">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] sm:text-xs tracking-wider">
                        <tr>
                            <th class="p-3 rounded-l-xl">Nama Siswa</th>
                            <th class="p-3">Kelas</th>
                            <th class="p-3">Judul Buku</th>
                            <th class="p-3">Tanggal Pinjam</th>
                            <th class="p-3">Tanggal Kembali</th>
                            <th class="p-3 text-center rounded-r-xl">Aksi</th>
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
                                <td class="p-3 font-semibold text-slate-800 cell-nama-riwayat"><?= $r['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $r['kelas']; ?></td>
                                <td class="p-3 text-slate-700 font-medium"><?= $r['judul']; ?></td>
                                <td class="p-3 text-slate-500 whitespace-nowrap"><?= date('d-m-Y', strtotime($r['tanggal_pinjam'])); ?></td>
                                <td class="p-3 text-brand-teal font-bold whitespace-nowrap"><?= date('d-m-Y', strtotime($r['tanggal_kembali'])); ?></td>
                                <td class="p-3 text-center">
                                    <form action="" method="POST" onsubmit="return confirmAction(event, 'Yakin ingin hapus riwayat ini?', this);">
                                        <input type="hidden" name="id_riwayat" value="<?= $r['id_riwayat']; ?>">
                                        <input type="hidden" name="hapus_riwayat" value="1">
                                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-[11px] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg font-bold transition">
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
                                <td colspan="6" class="p-6 text-center text-slate-400 text-xs sm:text-sm">Belum ada riwayat peminjaman.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL DAFTAR SISWA -->
        <div id="siswa-tab" class="tab-content hidden bg-white rounded-3xl shadow-xl p-4 sm:p-6 border border-slate-100">
            <?php
            $q_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama ASC");
            $total_siswa = mysqli_num_rows($q_siswa);
            ?>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-5">
                <h2 class="text-base sm:text-lg font-bold text-brand-navy">Daftar Siswa Terdaftar</h2>
                <div class="flex items-center gap-2">
                    <!-- Tombol Hapus Semua Siswa (Sebelah Kiri Pencarian) -->
                    <form action="" method="POST" onsubmit="return confirmHapusSemuaSiswa(event);">
                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-xs px-3 py-2 rounded-xl font-bold shadow-sm transition whitespace-nowrap flex items-center gap-1">
                            🗑️ Hapus Semua
                        </button>
                    </form>

                    <input type="text" id="searchSiswa" onkeyup="filterSiswa()" placeholder="Cari nama siswa..." class="w-full sm:w-64 p-2 border border-slate-200 rounded-xl text-xs sm:text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-teal">
                    <span class="bg-brand-teal/10 text-brand-teal border border-brand-teal/20 text-xs font-black px-3 py-2 rounded-xl whitespace-nowrap">
                        Total: <?= $total_siswa; ?>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm" id="tableSiswa">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] sm:text-xs tracking-wider">
                        <tr>
                            <th class="p-3 rounded-l-xl">Nomor Kartu</th>
                            <th class="p-3">Nama Siswa</th>
                            <th class="p-3">Kelas</th>
                            <th class="p-3 text-center rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        if ($total_siswa > 0):
                            while ($s = mysqli_fetch_assoc($q_siswa)):
                        ?>
                            <tr class="row-siswa hover:bg-slate-50 transition">
                                <td class="p-3 font-mono font-bold text-brand-orange"><?= $s['nomor_kartu']; ?></td>
                                <td class="p-3 font-semibold text-slate-800 cell-nama"><?= $s['nama']; ?></td>
                                <td class="p-3 text-slate-600"><?= $s['kelas']; ?></td>
                                <td class="p-3 text-center flex justify-center gap-1.5">
                                    <button onclick='openEditSiswaModal(<?= json_encode($s); ?>)' class="bg-brand-amber hover:bg-amber-600 text-white text-[11px] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg font-bold transition">
                                        Edit
                                    </button>
                                    <form action="" method="POST" onsubmit="return confirmAction(event, 'Yakin ingin menghapus siswa ini?', this);">
                                        <input type="hidden" name="id_siswa" value="<?= $s['id']; ?>">
                                        <input type="hidden" name="hapus_siswa" value="1">
                                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-[11px] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg font-bold transition">
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
                                <td colspan="4" class="p-6 text-center text-slate-400 text-xs sm:text-sm">Belum ada siswa yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL DAFTAR BUKU KOLEKSI -->
        <div id="buku-tab" class="tab-content hidden bg-white rounded-3xl shadow-xl p-4 sm:p-6 border border-slate-100">
            <?php
            $q_buku_all = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY judul ASC");
            $total_buku = mysqli_num_rows($q_buku_all);
            ?>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-5">
                <h2 class="text-base sm:text-lg font-bold text-brand-navy">Daftar Buku Koleksi</h2>
                <div class="flex items-center gap-2">
                    <!-- Tombol Hapus Semua Buku (Sebelah Kiri Pencarian) -->
                    <form action="" method="POST" id="formHapusSemuaBuku" onsubmit="return confirmHapusSemuaBuku(event, this);">
                        <input type="hidden" name="hapus_semua_buku" value="1">
                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-xs px-3 py-2 rounded-xl font-bold shadow-sm transition whitespace-nowrap flex items-center gap-1">
                            🗑️ Hapus Semua
                        </button>
                    </form>

                    <input type="text" id="searchBuku" onkeyup="filterBuku()" placeholder="Cari judul buku..." class="w-full sm:w-64 p-2 border border-slate-200 rounded-xl text-xs sm:text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-teal">
                    <span class="bg-brand-navy/10 text-brand-navy border border-brand-navy/20 text-xs font-black px-3 py-2 rounded-xl whitespace-nowrap">
                        Total: <?= $total_buku; ?>
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm" id="tableBuku">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] sm:text-xs tracking-wider">
                        <tr>
                            <th class="p-3 rounded-l-xl">Judul Buku</th>
                            <th class="p-3">Penulis</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        if ($total_buku > 0):
                            while ($b = mysqli_fetch_assoc($q_buku_all)):
                        ?>
                            <tr class="row-buku hover:bg-slate-50 transition">
                                <td class="p-3 font-semibold text-slate-800 cell-judul"><?= $b['judul']; ?></td>
                                <td class="p-3 text-slate-600"><?= $b['penulis']; ?></td>
                                <td class="p-3 text-center">
                                    <?php if ($b['status'] === 'tersedia'): ?>
                                        <span class="bg-emerald-100 text-emerald-700 text-[10px] sm:text-xs px-2.5 py-1 rounded-full font-bold">Tersedia</span>
                                    <?php else: ?>
                                        <span class="bg-amber-100 text-amber-700 text-[10px] sm:text-xs px-2.5 py-1 rounded-full font-bold">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-center flex justify-center gap-1.5">
                                    <button onclick='openEditBukuModal(<?= json_encode($b); ?>)' class="bg-brand-amber hover:bg-amber-600 text-white text-[11px] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg font-bold transition">
                                        Edit
                                    </button>
                                    <form action="" method="POST" onsubmit="return confirmAction(event, 'Yakin ingin menghapus buku ini?', this);">
                                        <input type="hidden" name="id_buku" value="<?= $b['id']; ?>">
                                        <input type="hidden" name="hapus_buku" value="1">
                                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-[11px] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg font-bold transition">
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
                                <td colspan="4" class="p-6 text-center text-slate-400 text-xs sm:text-sm">Belum ada buku yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL EDIT SISWA -->
    <div id="modalSiswa" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl shadow-2xl p-5 sm:p-6 w-full max-w-md space-y-4 border border-slate-100">
            <h3 class="font-bold text-base sm:text-lg text-brand-navy border-b pb-3">Edit Data Siswa</h3>
            <form action="" method="POST" class="space-y-3">
                <input type="hidden" name="id_siswa" id="edit_siswa_id">
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nomor Kartu:</label>
                    <input type="text" name="nomor_kartu" id="edit_siswa_kartu" required class="w-full p-2.5 border rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-brand-teal focus:outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Siswa:</label>
                    <input type="text" name="nama" id="edit_siswa_nama" required class="w-full p-2.5 border rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-brand-teal focus:outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kelas:</label>
                    <input type="text" name="kelas" id="edit_siswa_kelas" required class="w-full p-2.5 border rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-brand-teal focus:outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Password (Kosongkan jika tak diubah):</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full p-2.5 border rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-brand-teal focus:outline-none">
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" onclick="closeModal('modalSiswa')" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">Batal</button>
                    <button type="submit" name="edit_siswa" class="px-4 py-2 bg-brand-orange text-white text-xs font-bold rounded-xl hover:opacity-95 shadow-md shadow-brand-orange/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT BUKU -->
    <div id="modalBuku" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl shadow-2xl p-5 sm:p-6 w-full max-w-md space-y-4 border border-slate-100">
            <h3 class="font-bold text-base sm:text-lg text-brand-navy border-b pb-3">Edit Data Buku</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="id_buku" id="edit_buku_id">
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Judul Buku:</label>
                    <input type="text" name="judul" id="edit_buku_judul" required class="w-full p-2.5 border rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-brand-teal focus:outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Penulis:</label>
                    <input type="text" name="penulis" id="edit_buku_penulis" required class="w-full p-2.5 border rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-brand-teal focus:outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sinopsis Singkat:</label>
                    <textarea name="sinopsis" id="edit_buku_sinopsis" rows="3" required class="w-full p-2.5 border rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-brand-teal focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ganti Cover (Kosongkan jika tidak diubah):</label>
                    <input type="file" name="cover" accept="image/*" class="w-full p-1 border rounded-xl text-xs bg-slate-50">
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" onclick="closeModal('modalBuku')" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">Batal</button>
                    <button type="submit" name="edit_buku" class="px-4 py-2 bg-brand-orange text-white text-xs font-bold rounded-xl hover:opacity-95 shadow-md shadow-brand-orange/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto Focus Langsung ke Input Tap Kartu Pinjam Saat Refresh Halaman
        window.addEventListener('DOMContentLoaded', () => {
            const pinjamInput = document.getElementById('input_nomor_kartu_pinjam');
            if (pinjamInput) {
                pinjamInput.focus();
                pinjamInput.select();
            }
        });

        <?php if ($msg): ?>
            Swal.fire({
                icon: '<?= $msg_type; ?>',
                title: '<?= $msg_type == 'success' ? 'Berhasil!' : 'Gagal!'; ?>',
                text: '<?= addslashes($msg); ?>',
                confirmButtonColor: '#1B365D',
                customClass: {
                    popup: 'rounded-3xl'
                }
            });
        <?php endif; ?>

        function confirmAction(e, message, form) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F37021',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-3xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }

        // Switch Tab Navigasi Form Khusus HP
        function switchFormTab(targetTabId, btn) {
            const contents = document.querySelectorAll('.form-tab-content');
            contents.forEach(el => {
                el.classList.add('hidden');
            });
            document.getElementById(targetTabId).classList.remove('hidden');

            const btns = document.querySelectorAll('.form-tab-btn');
            btns.forEach(b => {
                b.className = "form-tab-btn shrink-0 bg-white text-slate-600 border border-slate-200 px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm transition";
            });
            btn.className = "form-tab-btn shrink-0 bg-brand-navy text-white px-3.5 py-2 rounded-xl text-xs font-bold shadow transition";
            
            // Focus ke input pinjam jika tab pinjam dibuka
            if (targetTabId === 'form-pinjam-tab') {
                document.getElementById('input_nomor_kartu_pinjam').focus();
            }
        }

        // Switch Tab Navigasi Tabel Data
        function openTab(tabName, btnElement) {
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.add('hidden'));

            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.className = "tab-btn shrink-0 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition";
            });

            document.getElementById(tabName).classList.remove('hidden');
            btnElement.className = "tab-btn shrink-0 bg-brand-navy text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md transition";
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

        function filterSiswa() {
            let input = document.getElementById('searchSiswa').value.toLowerCase();
            let rows = document.querySelectorAll('.row-siswa');
            rows.forEach(row => {
                let nama = row.querySelector('.cell-nama').textContent.toLowerCase();
                row.style.display = nama.includes(input) ? "" : "none";
            });
        }

        function filterBuku() {
            let input = document.getElementById('searchBuku').value.toLowerCase();
            let rows = document.querySelectorAll('.row-buku');
            rows.forEach(row => {
                let judul = row.querySelector('.cell-judul').textContent.toLowerCase();
                row.style.display = judul.includes(input) ? "" : "none";
            });
        }

        function filterRiwayat() {
            let input = document.getElementById('searchRiwayat').value.toLowerCase();
            let rows = document.querySelectorAll('.row-riwayat');
            rows.forEach(row => {
                let nama = row.querySelector('.cell-nama-riwayat').textContent.toLowerCase();
                row.style.display = nama.includes(input) ? "" : "none";
            });
        }

        function preventRfidEnter(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                return false;
            }
        }

        function filterPeminjamanTap() {
            let input = document.getElementById('tapKartuReturn').value.toLowerCase().trim();
            let rows = document.querySelectorAll('.row-peminjaman');
            rows.forEach(row => {
                let kartu = (row.getAttribute('data-kartu') || '').toLowerCase();
                let nama = (row.getAttribute('data-nama') || '').toLowerCase();
                row.style.display = (kartu.includes(input) || nama.includes(input)) ? "" : "none";
            });
        }

        $(document).ready(function() {
            $('.select2-buku').select2({
                placeholder: "-- Pilih Buku --",
                allowClear: true,
                width: '100%'
            });
        });

        function updateCountdown() {
            const timers = document.querySelectorAll('.countdown-timer');
            timers.forEach(timer => {
                const targetDate = new Date(timer.getAttribute('data-target')).getTime();
                const now = new Date().getTime();
                const diff = targetDate - now;

                if (diff <= 0) {
                    timer.innerHTML = '<span class="text-rose-600 font-bold bg-rose-100 px-2 py-0.5 rounded text-[10px] sm:text-xs">Terlambat</span>';
                } else {
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    timer.innerHTML = `<span class="text-brand-teal bg-teal-50 border border-teal-200 px-2 py-0.5 rounded text-[10px] sm:text-xs font-mono font-bold">${days}h ${hours}j ${minutes}m ${seconds}s</span>`;
                }
            });
        }

        // Konfirmasi & Hapus Semua Buku
        function confirmHapusSemuaBuku(e) {
            e.preventDefault();
            
            // 1. Alert Konfirmasi
            Swal.fire({
                title: 'HAPUS SEMUA BUKU?',
                text: "Tindakan ini akan menghapus seluruh koleksi buku secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E11D48',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-3xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Loading indicator saat memproses
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menghapus data...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    // Kirim request ke PHP via Fetch/AJAX
                    let formData = new FormData();
                    formData.append('hapus_semua_buku_ajax', '1');

                    fetch('admin_dashboard.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        // 2. Alert Berhasil / Gagal setelah eksekusi
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#1B365D',
                                customClass: { popup: 'rounded-3xl' }
                            }).then(() => {
                                location.reload(); // Refresh halaman setelah klik OK
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#1B365D',
                                customClass: { popup: 'rounded-3xl' }
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada server.' });
                    });
                }
            });
            return false;
        }

        // Konfirmasi & Hapus Semua Riwayat Peminjaman
        function confirmHapusSemuaRiwayat(e) {
            e.preventDefault();
            
            // 1. Alert Konfirmasi
            Swal.fire({
                title: 'HAPUS SEMUA RIWAYAT?',
                text: "Tindakan ini akan menghapus seluruh riwayat peminjaman secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E11D48',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-3xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Loading indicator saat memproses
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menghapus riwayat...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    // Kirim request ke PHP via Fetch/AJAX
                    let formData = new FormData();
                    formData.append('hapus_semua_riwayat_ajax', '1');

                    fetch('admin_dashboard.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        // 2. Alert Berhasil / Gagal setelah eksekusi
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#1B365D',
                                customClass: { popup: 'rounded-3xl' }
                            }).then(() => {
                                location.reload(); // Refresh halaman setelah klik OK
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#1B365D',
                                customClass: { popup: 'rounded-3xl' }
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada server.' });
                    });
                }
            });
            return false;
        }

        // Konfirmasi & Hapus Semua Siswa
        function confirmHapusSemuaSiswa(e) {
            e.preventDefault();
            
            // 1. Alert Konfirmasi
            Swal.fire({
                title: 'HAPUS SEMUA SISWA?',
                text: "Tindakan ini akan menghapus seluruh data siswa terdaftar secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E11D48',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-3xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Loading indicator saat memproses
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menghapus data siswa...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    // Kirim request ke PHP via Fetch/AJAX
                    let formData = new FormData();
                    formData.append('hapus_semua_siswa_ajax', '1');

                    fetch('admin_dashboard.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        // 2. Alert Berhasil / Gagal setelah eksekusi
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#1B365D',
                                customClass: { popup: 'rounded-3xl' }
                            }).then(() => {
                                location.reload(); // Refresh halaman setelah klik OK
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#1B365D',
                                customClass: { popup: 'rounded-3xl' }
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada server.' });
                    });
                }
            });
            return false;
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>
</html>