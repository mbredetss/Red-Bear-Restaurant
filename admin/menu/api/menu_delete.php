<?php
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

if (isset($_GET['name'])) {
  $publicId = $_GET['name'];

  // Get image path from DB
  $stmt = $koneksi->prepare("SELECT image_path FROM menu WHERE public_id = ?");
  $stmt->bind_param("s", $publicId);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  if ($row && !empty($row['image_path'])) {
    // Delete local image file
    $local_image_path = __DIR__ . '/../../../../' . $row['image_path'];
    if (file_exists($local_image_path)) {
      unlink($local_image_path);
    }
  }
  $stmt->close();

  // Hapus deskripsi dari database
  $stmt = $koneksi->prepare("DELETE FROM menu WHERE public_id = ?");
  $stmt->bind_param("s", $publicId);
  $stmt->execute();
  $stmt->close();

  // Update file JSON
  $result = $koneksi->query("SELECT public_id, tersedia, jenis FROM menu");
  $menus = [];
  while ($row = $result->fetch_assoc()) {
    $menus[$row['public_id']] = [
      'tersedia' => (bool) $row['tersedia'],
      'jenis' => $row['jenis']
    ];
  }
  file_put_contents(__DIR__ . '/../menu.json', json_encode($menus, JSON_PRETTY_PRINT));

  echo json_encode(["message" => "Menu berhasil dihapus."]);
} else {
  http_response_code(400);
  echo json_encode(["message" => "Parameter tidak lengkap."]);
}