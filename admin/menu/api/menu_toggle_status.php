<?php
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
  $publicId = $_POST['name'];

  // Ambil status saat ini dari database
  $stmt = $koneksi->prepare("SELECT tersedia FROM menu WHERE public_id = ?");
  $stmt->bind_param("s", $publicId);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  if (!$row) {
    echo json_encode(["message" => "Menu tidak ditemukan."]);
    exit;
  }

  // Toggle status tersedia
  $tersediaBaru = $row['tersedia'] ? 0 : 1;

  // Update status di database
  $update = $koneksi->prepare("UPDATE menu SET tersedia = ? WHERE public_id = ?");
  $update->bind_param("is", $tersediaBaru, $publicId);
  $update->execute();
  $update->close();

  // Perbarui file JSON
  $result = $koneksi->query("SELECT public_id, tersedia FROM menu");
  $menus = [];
  while ($row = $result->fetch_assoc()) {
    $menus[$row['public_id']] = ['tersedia' => (bool)$row['tersedia']];
  }
  file_put_contents(__DIR__ . '/../menu.json', json_encode($menus, JSON_PRETTY_PRINT));

  echo json_encode(["message" => "Status menu diperbarui."]);
} else {
  http_response_code(400);
  echo json_encode(["message" => "Data tidak lengkap."]);
}
