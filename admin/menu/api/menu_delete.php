<?php
include __DIR__ . '/../../../init.php';
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

if (isset($_GET['name'])) {
  $publicId = $_GET['name'];

  // Hapus gambar dari Cloudinary
  $cloudinary->uploadApi()->destroy("menu_items/$publicId");

  // Hapus deskripsi dari database
  $stmt = $koneksi->prepare("DELETE FROM menu WHERE public_id = ?");
  $stmt->bind_param("s", $publicId);
  $stmt->execute();
  $stmt->close();

  // Update file JSON
  $result = $koneksi->query("SELECT public_id, tersedia FROM menu");
  $menus = [];
  while ($row = $result->fetch_assoc()) {
    $menus[$row['public_id']] = ['tersedia' => (bool) $row['tersedia']];
  }
  file_put_contents(__DIR__ . '/../menu.json', json_encode($menus, JSON_PRETTY_PRINT));

  echo json_encode(["message" => "Menu berhasil dihapus."]);
} else {
  http_response_code(400);
  echo json_encode(["message" => "Parameter tidak lengkap."]);
}