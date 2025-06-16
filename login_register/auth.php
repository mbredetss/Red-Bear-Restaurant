<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../../home.php"); // arahkan kembali ke halaman home
    exit();
}
?>
