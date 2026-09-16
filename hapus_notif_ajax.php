<?php
session_start();
include 'koneksi.php';

if (isset($_POST['id_notifikasi'])) {
    $id_notif = intval($_POST['id_notifikasi']);
    $siswa_id = $_SESSION['id'] ?? $_SESSION['siswa_id'] ?? $_SESSION['user_id'] ?? 0;

    // Hapus notifikasi milik siswa ini
    $query = mysqli_query($koneksi, "DELETE FROM notifikasi WHERE id = '$id_notif' AND siswa_id = '$siswa_id'");

    if ($query) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
exit;