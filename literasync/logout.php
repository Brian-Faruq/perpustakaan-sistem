<?php
session_start();
session_destroy(); // Hapus semua ingatan login
header("Location: login.php");
?>