<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Memastikan user sudah login
// if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
//     header("Location: login.php");
//     exit();
// }

// Ambil username dari session dengan aman
// Jika tidak ada session username, pakai username default agar tidak error
$user_nama = $_SESSION['username'] ?? $_SESSION['user_aktif'] ?? 'Iffah';
$user_nama_safe = mysqli_real_escape_string($conn, $user_nama);

// 1. Ambil Data User Aktif
$q_user = mysqli_query($conn, "SELECT id, total_poin, foto FROM users WHERE username='$user_nama_safe'");
$d_user = mysqli_fetch_assoc($q_user);

$user_id = ($d_user) ? $d_user['id'] : 0;
$poin = ($d_user) ? (int)($d_user['total_poin'] ?? 0) : 0;
$foto_profil = ($d_user) ? $d_user['foto'] : '';

// 2. Query Riwayat & Leaderboard
$q_history = mysqli_query($conn, "SELECT * FROM books WHERE user_id='$user_id' ORDER BY id DESC LIMIT 5");
$q_leaderboard = mysqli_query($conn, "SELECT username, total_poin FROM users ORDER BY total_poin DESC LIMIT 5");

// 3. Logika Ranking LiteraSync
function getLiteraRank($p) {
    if ($p <= 50)   return ["Pembaca", "pembaca.png", 50];
    if ($p <= 150)  return ["Penyimak", "penyimak.png", 150];
    if ($p <= 300)  return ["Penelaah", "penelaah.png", 300];
    if ($p <= 500)  return ["Kurator", "kurator.png", 500];
    if ($p <= 750)  return ["Cendekia", "cendekia.png", 750];
    if ($p <= 1000) return ["Pujangga", "pujangga.png", 1000];
    if ($p <= 1500) return ["Empu", "empu.png", 1500];
    return ["Begawan", "begawan.png", 2000]; 
}

list($rank_nama, $rank_img, $max_poin) = getLiteraRank($poin);

$sisa_poin = max(0, $max_poin - $poin);
$estimasi_buku = ceil($sisa_poin / 15);

function getNextRankName($current) {
    $list = ["Pembaca", "Penyimak", "Penelaah", "Kurator", "Cendekia", "Pujangga", "Empu", "Begawan"];
    $idx = array_search($current, $list);
    return ($idx !== false && isset($list[$idx + 1])) ? $list[$idx + 1] : "Maksimal";
}
$rank_berikutnya = getNextRankName($rank_nama);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Literasync - Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fdfaf3; margin: 0; padding: 0; color: #333; overflow-x: hidden; }
        
        /* Header */
        .main-header {
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    padding: 15px 40px; /* Kita tambah sedikit padding atas-bawah */
    background: transparent; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky; 
    top: 0; 
    z-index: 1000; 
    height: 90px; /* Kita naikkan tingginya agar logo tidak terpotong */
    box-sizing: border-box;
}
       /* Cari bagian ini dan ganti nilainya */
/* 1. Atur wadahnya agar tidak membatasi gambar */
.logo-container {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding-left: 10px;
    /* Pastikan tidak ada background-color putih di sini */
    background: transparent !important; 
}

/* 2. Atur ukuran gambarnya secara langsung (Tanpa Scale!) */
.logo-container img {
    /* Kita gunakan height yang besar agar terlihat jelas */
    height: 70px; 
    width: auto;
    
    /* Hapus scale agar tidak membuat 'ghost box' putih */
    transform: none !important; 
    
    /* Memastikan gambar tetap tajam */
    image-rendering: -webkit-optimize-contrast;
    
    /* Jaga-jaga agar tidak ada background bawaan browser */
    background: transparent !important;
    border: none !important;
}

/* 3. Sesuaikan Header agar menampung logo yang sudah besar */
.main-header {
    height: 100px; /* Kita tinggikan sedikit headernya */
    display: flex;
    align-items: center;
    padding: 0 40px;
    background: white;
}
        .search-input { width: 100%; height: 40px; padding: 0 20px; border-radius: 50px; border: 2px solid #F8B406; outline: none; font-family: 'Poppins'; }
        
        /* Layout Grid */
        .container { display: grid; grid-template-columns: 300px 1fr 340px; min-height: calc(100vh - 75px); }

        /* Sidebar Kiri */
        .sidebar-left { background: white; border-right: 1px solid #eee; padding-bottom: 30px; text-align: center; }
        .profile-header-yellow { background: #F8B406; height: 80px; border-radius: 0 0 40px 40px; margin-bottom: 50px; position: relative; }
        .profile-avatar {
            position: absolute; bottom: -40px; left: 50%; transform: translateX(-50%);
            width: 85px; height: 85px; border-radius: 50%; border: 4px solid white;
            background: #5d8a66; color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; overflow: hidden;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .rank-card-new { background: #2c3e50; margin: 20px; padding: 20px; border-radius: 20px; color: white; text-align: left; }
        .progress-container { background: rgba(255,255,255,0.1); height: 10px; border-radius: 10px; margin: 12px 0; overflow: hidden; }
        .progress-fill { height: 100%; background: #F8B406; border-radius: 10px; transition: 0.8s ease; }

        /* Main Content & Buttons */
        .menu-utama { display: flex; gap: 20px; margin-bottom: 35px; }
        .btn-menu { 
            flex: 1; padding: 30px 15px; border-radius: 25px; text-decoration: none; 
            color: white; text-align: center; font-weight: 700; font-size: 18px;
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            transition: 0.3s; box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        .btn-menu:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.15); }
        .btn-upload { background: linear-gradient(135deg, #f14e0d, #ff7043); }
        .btn-rekomendasi { background: linear-gradient(135deg, #ff7043, #ffa270); }

        /* Sidebar Kanan */
        .sidebar-right { background: #ffffff; border-left: 1px solid #eee; padding: 25px; }
        .rank-item-new { display: flex; align-items: center; padding: 15px; background: #fdfaf3; border-radius: 18px; margin-bottom: 15px; border: 1px solid #f1e9d5; }
        .rank-badge-pill { display: inline-flex; align-items: center; gap: 6px; background: #eee; padding: 2px 10px; border-radius: 20px; font-size: 10px; color: #666; font-weight: 600; }

        /* Modal */
        .modal-rank { display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 25px; border-radius: 20px; width: 85%; max-width: 400px; text-align: center; }
        .rank-table { width: 100%; margin-top: 15px; border-collapse: collapse; font-size: 13px; }
        .rank-table td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="logo-container">
        <a href="dashboard.php"><img src="logo.png" alt="logo"></a>
    </div>
    <div class="search-wrapper" style="flex: 1; max-width: 400px; margin: 0 20px;">
        <form action="cari.php" method="GET">
            <input type="text" name="keyword" placeholder="Cari petualanganmu..." class="search-input">
        </form>
    </div>
    <nav class="header-nav" style="display: flex; gap: 20px; align-items: center;">
        <a class="info-rank-link" style="text-decoration: none; color: #5d8a66; font-size: 14px; font-weight: 600; cursor: pointer;" onclick="openRankModal()"><i class='bx bx-info-circle'></i> Info Rank</a>
        <a href="profile.php" style="background: #F8B406; color: white; padding: 8px 18px; border-radius: 50px; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 5px;"><i class='bx bx-user-circle'></i> Profile</a>
    </nav>
</header>

<!-- Modal Info Rank -->
<div id="rankModal" class="modal-rank" onclick="closeRankModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h3 style="color: #2c3e50; margin-bottom: 5px;">Info Rank Literasync</h3>
        <table class="rank-table">
            <?php 
            $ranks = [
                ["Pembaca", "pembaca.png", "0-50 XP"], ["Penyimak", "penyimak.png", "51-150 XP"],
                ["Penelaah", "penyimak.png", "151-300 XP"], ["Kurator", "kurator.png", "301-500 XP"],
                ["Cendekia", "cendekia.png", "501-750 XP"], ["Pujangga", "pujangga.png", "751-1000 XP"],
                ["Empu", "empu.png", "1001-1500 XP"], ["Begawan", "begawan.png", "> 1500 XP"]
            ];
            foreach($ranks as $r):
            ?>
            <tr>
                <td style="width: 30px;"><img src="<?php echo $r[1]; ?>" style="width: 25px;"></td>
                <td><b><?php echo $r[0]; ?></b></td>
                <td style="text-align: right; color: #7f8c8d;"><?php echo $r[2]; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <button onclick="closeRankModal()" style="margin-top: 20px; background: #FF5722; color: white; border: none; padding: 10px 30px; border-radius: 50px; cursor: pointer; font-weight: 600;">Tutup</button>
    </div>
</div>

<div class="container">
    <!-- SIDEBAR KIRI -->
    <div class="sidebar-left">
        <div class="profile-header-yellow">
            <div class="profile-avatar">
                <?php if (!empty($foto_profil)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($foto_profil); ?>">
                <?php else: ?>
                    <?php echo strtoupper(substr($user_nama, 0, 2)); ?>
                <?php endif; ?>
            </div>
        </div>
        <h2 style="margin: 0; font-size: 22px;"><?php echo htmlspecialchars($user_nama); ?></h2>
        
        <div class="rank-card-new">
            <span style="font-size: 10px; opacity: 0.6; letter-spacing: 1px;">STATUS LITERASI</span>
            <div style="display: flex; align-items: center; gap: 10px; margin: 5px 0;">
                <img src="<?php echo $rank_img; ?>" style="width: 30px; height: 30px; object-fit: contain;">
                <h3 style="margin: 0; color: #F8B406;"><?php echo $rank_nama; ?></h3>
            </div>
            <div class="progress-container">
                <div class="progress-fill" style="width: <?php echo min(($poin/$max_poin)*100, 100); ?>%;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 10px;">
                <span>⭐ <?php echo $poin; ?> XP</span>
                <span style="opacity: 0.5;">Target: <?php echo $max_poin; ?></span>
            </div>
            <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 10px; font-size: 11px; border-left: 3px solid #F8B406;">
                <?php if ($rank_berikutnya != "Maksimal"): ?>
                    Butuh <b><?php echo $sisa_poin; ?> XP</b> lagi (±<b><?php echo $estimasi_buku; ?> buku</b>) ke <b><?php echo $rank_berikutnya; ?></b>! 🚀
                <?php else: ?>
                    👑 Kamu telah mencapai puncak sastra!
                <?php endif; ?>
            </div>
        </div>
        <a href="lapor_buku.php" style="background: #5d8a66; color: white; display: flex; align-items: center; gap: 10px; padding: 12px 20px; margin: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 13px;"><i class='bx bx-plus-circle'></i> Lapor Buku</a>
        <a href="pinjam_buku.php" style="background: #FF5722; color: white; display: flex; align-items: center; gap: 10px; padding: 12px 20px; margin: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 13px;"><i class='bx bx-book-open'></i> Pinjam Buku</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content" style="padding: 30px;">
        <h2 style="margin: 0 0 25px 0; font-size: 26px; display: inline-block; border-bottom: 4px solid #F8B406;">HOME</h2>
        
        <!-- Menu Tombol Besar -->
        <div class="menu-utama">
            <a href="tambah_karya.php" class="btn-menu btn-upload">
                <i class='bx bx-cloud-upload' style="font-size: 35px;"></i>
                Upload Karya
            </a>
        </div>

        <h3 style="margin-bottom: 20px;">Riwayat Membaca</h3>
        
        <?php if(mysqli_num_rows($q_history) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($q_history)): ?>
                <div style="background: #F8B406; border-radius: 20px; padding: 20px; margin-bottom: 20px; display: flex; gap: 20px; align-items: center;">
                    <div style="flex: 1; color: #2c3e50;">
                        <h3 style="margin: 0;"><?php echo htmlspecialchars($row['judul_buku']); ?></h3>
                        <p style="margin: 5px 0; font-size: 12px;">Penulis: <?php echo htmlspecialchars($row['penulis_buku']); ?></p>
                        <div style="font-size: 11px; font-weight: bold;">⭐ <?php echo (int)$row['rating']; ?>/10 | 📅 <?php echo date('d M Y', strtotime($row['tgl_input'])); ?></div>
                    </div>
                    <img src="uploads/<?php echo htmlspecialchars($row['cover_buku']); ?>" style="width: 80px; height: 110px; border-radius: 10px; object-fit: cover; border: 3px solid white;" onerror="this.src='https://via.placeholder.com/80x110?text=No+Cover'">
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #999; font-style: italic;">Belum ada riwayat buku. Ayo mulai lapor buku pertamamu!</p>
        <?php endif; ?>
    </div>

    <!-- LEADERBOARD -->
    <div class="sidebar-right">
        <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;"><i class='bx bxs-crown' style="color: #F8B406; font-size: 24px;"></i> Leaderboard</h3>
        <?php 
        $no = 1;
        while($row_rank = mysqli_fetch_assoc($q_leaderboard)): 
            $poin_lb = (int)$row_rank['total_poin'];
            list($n_rank, $i_img) = getLiteraRank($poin_lb);
            $medal = ($no == 1) ? "🥇" : (($no == 2) ? "🥈" : (($no == 3) ? "🥉" : $no));
        ?>
        <div class="rank-item-new">
            <div style="font-weight: 800; width: 30px; color: #bdc3c7; font-size: 18px;"><?php echo $medal; ?></div>
            <div style="flex-grow: 1;">
                <h4 style="margin:0; font-size: 14px; color: #2c3e50; font-weight: 600;"><?php echo htmlspecialchars($row_rank['username']); ?></h4>
                <div class="rank-badge-pill"><img src="<?php echo $i_img; ?>" style="width: 14px;"> <?php echo $n_rank; ?></div>
            </div>
            <div style="text-align: right; font-weight: 700; color: #F8B406; font-size: 14px;">⭐ <?php echo $poin_lb; ?></div>
        </div>
        <?php $no++; endwhile; ?>
    </div>
</div>

<script>
    function openRankModal() { document.getElementById('rankModal').style.display = 'block'; }
    function closeRankModal() { document.getElementById('rankModal').style.display = 'none'; }
    
    window.onclick = function(event) {
        let modal = document.getElementById('rankModal');
        if (event.target == modal) {
            closeRankModal();
        }
    }
</script>
</body>
</html>
