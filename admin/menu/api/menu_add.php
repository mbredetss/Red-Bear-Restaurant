<?php
require_once '../../../database.php';

// Fungsi untuk mengirim respons JSON dan keluar
function json_response($message, $code = 200)
{
  http_response_code($code);
  echo json_encode(["message" => $message]);
  exit;
}

$koneksi = koneksiDatabase("red bear");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
  // Cek jika ada error saat upload
  if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    json_response("Error pada upload file: " . $_FILES['image']['error'], 400);
  }

  $file = $_FILES['image']['tmp_name'];
  $name = $_POST['name'];
  $jenis = $_POST['jenis'];

  // Handle file upload locally
  $image_name = $_FILES['image']['name'];
  $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
  if (empty($image_ext)) {
    $image_ext = 'jpg'; // Default extension
  }
  $new_image_name = uniqid() . '.' . $image_ext;
  $upload_dir = __DIR__ . '/../../../img/menu/';
  $upload_path = $upload_dir . $new_image_name;
  $db_path = 'img/menu/' . $new_image_name;

  // Pastikan direktori ada
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  if (!move_uploaded_file($file, $upload_path)) {
    json_response("Gagal memindahkan file yang diunggah.", 500);
  }

  // Simpan ke database
  // CATATAN: Pastikan tabel `menu` memiliki kolom `public_id`
  $stmt = $koneksi->prepare("INSERT INTO menu (public_id, image_path, jenis, tersedia) VALUES (?, ?, ?, TRUE)");
  $stmt->bind_param("sss", $name, $db_path, $jenis);

  if (!$stmt->execute()) {
    // Jika eksekusi gagal, hapus file yang sudah diunggah
    unlink($upload_path);
    json_response("Gagal menyimpan ke database: " . $stmt->error, 500);
  }
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

  json_response("Menu berhasil ditambahkan.");
} else {
  json_response("Data tidak lengkap.", 400);
}
