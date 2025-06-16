<?php
session_start();

// Hapus hanya data login, bukan semua session (jika ada data lain)
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['role']);

// Redirect balik ke home.php
header("Location: home.php"); // atur path sesuai struktur folder kamu
exit();
