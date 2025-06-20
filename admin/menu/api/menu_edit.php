<?php
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_name'], $_POST['new_name'])) {
  $oldName = $_POST['old_name'];
  $newName = $_POST['new_name'];

  // Update nama di database
  $stmt = $koneksi->prepare("UPDATE  menu SET public_id = ? WHERE public_id = ?");
  $stmt->bind_param("ss", $newName, $oldName);
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

  echo json_encode(["message" => "Nama menu berhasil diubah."]);
} else {
  http_response_code(400);
  echo json_encode(["message" => "Data tidak lengkap."]);
}