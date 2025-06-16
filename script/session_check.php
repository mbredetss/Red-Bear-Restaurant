<?php
session_start();
require_once 'database.php'; // ganti path sesuai struktur project kamu

$koneksi = koneksiDatabase("red bear");

$user = null;
$saldo = 0;

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $query = "SELECT name, saldo FROM users WHERE id = ?";
  $stmt = $koneksi->prepare($query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $saldo = $user['saldo'];
  }
}
?>
