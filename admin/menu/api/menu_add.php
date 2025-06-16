<?php
include __DIR__ . '/../../../init.php';
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
  $file = $_FILES['image']['tmp_name'];
  $name = $_POST['name'];
  $jenis = $_POST['jenis'];

  // Upload ke Cloudinary
  $cloudinary->uploadApi()->upload($file, [
    'folder' => 'menu_items',
    'public_id' => $name
  ]);

  // Simpan ke database
  $stmt = $koneksi->prepare("INSERT INTO menu (public_id, jenis, tersedia) VALUES (?, ?, TRUE)");
  $stmt->bind_param("ss", $name, $jenis);
  $stmt->execute();
  $stmt->close();

  // Update file JSON
  $result = $koneksi->query("SELECT public_id, jenis, tersedia FROM menu");
  $menus = [];
  while ($row = $result->fetch_assoc()) {
    $menus[$row['public_id']] = [
      'tersedia' => (bool) $row['tersedia'],
      'jenis' => $row['jenis']
    ];
  }
  file_put_contents(__DIR__ . '/../menu.json', json_encode($menus, JSON_PRETTY_PRINT));

  echo json_encode(["message" => "Menu berhasil ditambahkan."]);
} else {
  http_response_code(400);
  echo json_encode(["message" => "Data tidak lengkap."]);
}
